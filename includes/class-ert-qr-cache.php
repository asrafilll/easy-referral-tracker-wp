<?php
/**
 * QR Code Cache Manager
 *
 * Handles local QR code generation and file-based caching
 *
 * @package EasyReferralTracker
 * @since 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_QR_Cache
 *
 * Manages QR code generation and caching
 */
class ERT_QR_Cache {

	/**
	 * Upload directory path for QR codes
	 *
	 * @var string
	 */
	private string $upload_dir;

	/**
	 * Upload URL for QR codes
	 *
	 * @var string
	 */
	private string $upload_url;

	/**
	 * Error handler instance
	 *
	 * @var ERT_Error_Handler|null
	 */
	private ?ERT_Error_Handler $error_handler = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->error_handler = ERT_Error_Handler::get_instance();
		$this->setup_directories();
		$this->load_qr_library();
	}

	/**
	 * Setup upload directories for QR codes
	 *
	 * @return void
	 */
	private function setup_directories(): void {
		$upload_base = wp_upload_dir();
		$this->upload_dir = trailingslashit($upload_base['basedir']) . 'ert-qr';
		$this->upload_url = trailingslashit($upload_base['baseurl']) . 'ert-qr';

		// Create directory if it doesn't exist
		if (!file_exists($this->upload_dir)) {
			wp_mkdir_p($this->upload_dir);

			// Add .htaccess for security
			$htaccess = $this->upload_dir . '/.htaccess';
			if (!file_exists($htaccess)) {
				file_put_contents($htaccess, "# Protect QR directory\n<Files *.php>\nDeny from all\n</Files>");
			}

			// Add index.php to prevent directory listing
			$index = $this->upload_dir . '/index.php';
			if (!file_exists($index)) {
				file_put_contents($index, '<?php // Silence is golden');
			}
		}
	}

	/**
	 * Load PHPQRCode library
	 *
	 * @return void
	 */
	private function load_qr_library(): void {
		$qr_lib = EASYREFERRALTRACKER_PLUGIN_DIR . 'includes/vendor/phpqrcode/qrlib.php';
		if (file_exists($qr_lib)) {
			require_once $qr_lib;
		} else {
			$this->error_handler->log_error(
				'PHPQRCode library not found',
				ERT_Error_Handler::LEVEL_ERROR,
				['path' => $qr_lib],
				'ERT_QR_Cache::load_qr_library'
			);
		}
	}

	/**
	 * Get cached QR code or generate new one
	 *
	 * @param string $referral_code Referral code
	 * @param string $base_url      Base URL for QR code
	 * @param int    $size          QR code size
	 * @return string|false QR code URL or false on failure
	 */
	public function get_or_generate_qr(string $referral_code, string $base_url, int $size = 300) {
		// Sanitize referral code for filename
		$filename = $this->sanitize_filename($referral_code, $size);
		$filepath = $this->upload_dir . '/' . $filename;
		$fileurl = $this->upload_url . '/' . $filename;

		// Check if cached version exists
		if (file_exists($filepath)) {
			// Check if file is not corrupted
			if (filesize($filepath) > 100) {
				return $fileurl;
			} else {
				// File corrupted, delete and regenerate
				@unlink($filepath);
			}
		}

		// Generate new QR code
		return $this->generate_qr($referral_code, $base_url, $size, $filepath, $fileurl);
	}

	/**
	 * Generate QR code and save to file
	 *
	 * @param string $referral_code Referral code
	 * @param string $base_url      Base URL
	 * @param int    $size          QR size
	 * @param string $filepath      File path to save
	 * @param string $fileurl       File URL to return
	 * @return string|false File URL or false on failure
	 */
	private function generate_qr(string $referral_code, string $base_url, int $size, string $filepath, string $fileurl) {
		try {
			// Check if QRcode class exists
			if (!class_exists('QRcode')) {
				$this->error_handler->log_error(
					'QRcode class not found',
					ERT_Error_Handler::LEVEL_ERROR,
					[],
					'ERT_QR_Cache::generate_qr'
				);
				return false;
			}

			// Build final URL with referral code
			$normalized_url = $base_url;
			if (!str_ends_with($normalized_url, '/') && !str_contains($normalized_url, '?')) {
				$normalized_url .= '/';
			}
			$separator = str_contains($normalized_url, '?') ? '&' : '?';
			$final_url = $normalized_url . $separator . 'r=' . urlencode($referral_code);

			// Calculate QR pixel size (higher for better quality)
			$pixel_size = max(4, min(10, ceil($size / 100)));

			// Generate QR code
			// Parameters: data, output_file, error_correction_level, pixel_size, margin
			QRcode::png($final_url, $filepath, QR_ECLEVEL_L, $pixel_size, 2);

			// Verify file was created
			if (!file_exists($filepath) || filesize($filepath) < 100) {
				$this->error_handler->log_error(
					'QR code generation failed',
					ERT_Error_Handler::LEVEL_ERROR,
					['filepath' => $filepath],
					'ERT_QR_Cache::generate_qr'
				);
				return false;
			}

			return $fileurl;

		} catch (Throwable $e) {
			$this->error_handler->handle_exception($e);
			return false;
		}
	}

	/**
	 * Sanitize filename for QR code
	 *
	 * @param string $referral_code Referral code
	 * @param int    $size          Size for cache busting
	 * @return string Sanitized filename
	 */
	private function sanitize_filename(string $referral_code, int $size): string {
		// Create hash to avoid filesystem issues with special characters
		$hash = md5($referral_code . $size);
		return 'qr-' . $hash . '.png';
	}

	/**
	 * Get QR cache directory path
	 *
	 * @return string Directory path
	 */
	public function get_cache_dir(): string {
		return $this->upload_dir;
	}

	/**
	 * Clear specific QR code from cache
	 *
	 * @param string $referral_code Referral code
	 * @param int    $size          Size
	 * @return bool Success
	 */
	public function clear_qr(string $referral_code, int $size = 300): bool {
		$filename = $this->sanitize_filename($referral_code, $size);
		$filepath = $this->upload_dir . '/' . $filename;

		if (file_exists($filepath)) {
			return @unlink($filepath);
		}

		return true;
	}

	/**
	 * Clear all QR codes from cache
	 *
	 * @return int Number of files deleted
	 */
	public function clear_all(): int {
		$count = 0;
		$files = glob($this->upload_dir . '/qr-*.png');

		if ($files) {
			foreach ($files as $file) {
				if (is_file($file) && @unlink($file)) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Cleanup old QR codes
	 *
	 * @param int $days Delete files older than X days (default: 90)
	 * @return int Number of files deleted
	 */
	public function cleanup_old(int $days = 90): int {
		$count = 0;
		$threshold = time() - ($days * 24 * 60 * 60);
		$files = glob($this->upload_dir . '/qr-*.png');

		if ($files) {
			foreach ($files as $file) {
				if (is_file($file) && filemtime($file) < $threshold) {
					if (@unlink($file)) {
						$count++;
					}
				}
			}
		}

		return $count;
	}

	/**
	 * Get cache statistics
	 *
	 * @return array Cache stats
	 */
	public function get_stats(): array {
		$files = glob($this->upload_dir . '/qr-*.png');
		$total_size = 0;

		if ($files) {
			foreach ($files as $file) {
				if (is_file($file)) {
					$total_size += filesize($file);
				}
			}
		}

		return array(
			'count' => count($files ?: []),
			'size' => $total_size,
			'size_formatted' => size_format($total_size),
			'directory' => $this->upload_dir,
		);
	}
}
