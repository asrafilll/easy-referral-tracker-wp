<?php
/**
 * Shortcodes Class
 *
 * Handles shortcode registration and rendering
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Shortcodes
 *
 * Registers and renders plugin shortcodes
 */
class ERT_Shortcodes {

	/**
	 * QR Cache instance
	 *
	 * @var ERT_QR_Cache|null
	 */
	private ?ERT_QR_Cache $qr_cache = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode('easyreferraltracker_qr', array($this, 'render_qr_code'));
		
		// Initialize QR cache
		$this->qr_cache = new ERT_QR_Cache();
	}

	/**
	 * Get current visitor's referral code
	 *
	 * @return string Referral code from cookie, URL parameter, or default
	 */
	private function get_current_visitor_referral(): string {
		// Check cookie first
		if (isset($_COOKIE['ert_referral'])) {
			return sanitize_text_field($_COOKIE['ert_referral']);
		}

		// Check URL parameter
		if (isset($_GET['r'])) {
			return sanitize_text_field($_GET['r']);
		}

		// Return default referral
		return get_option('ert_default_referral', 'direct');
	}

	/**
	 * Generate QR code URL using local cache
	 *
	 * @param string $base_url Base URL for QR code
	 * @param int    $size     QR code size
	 * @param string $referral_code Referral code to include
	 * @return string|false QR code image URL or false on failure
	 */
	private function generate_qr_url(string $base_url, int $size, string $referral_code) {
		// Try to get cached QR or generate new one
		$qr_url = $this->qr_cache->get_or_generate_qr($referral_code, $base_url, $size);

		// Return false if local generation fails - no external API fallback
		if (false === $qr_url) {
			error_log('EasyReferralTracker: QR code generation failed for referral code: ' . $referral_code);
			return false;
		}

		return $qr_url;
	}

	/**
	 * Render QR code shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_qr_code(array $atts): string {
		// Parse attributes with defaults
		$atts = shortcode_atts(
			array(
				'size' => get_option('ert_qr_size', 300),
				'url' => get_option('ert_qr_base_url', home_url('/download')),
				'padding' => get_option('ert_qr_padding', 20),
				'border_radius' => get_option('ert_qr_border_radius', 10),
				'container_color' => get_option('ert_qr_container_color', '#FFFFFF'),
				'border_color' => get_option('ert_qr_border_color', '#E5E7EB'),
			),
			$atts,
			'easyreferraltracker_qr'
		);

		// Sanitize and validate
		$size = $this->sanitize_size($atts['size']);
		$base_url = esc_url($atts['url']);
		$padding = $this->sanitize_padding($atts['padding']);
		$border_radius = $this->sanitize_border_radius($atts['border_radius']);
		$container_color = sanitize_hex_color($atts['container_color']);
		$border_color = sanitize_hex_color($atts['border_color']);

		// Get current visitor's referral code
		$referral_code = $this->get_current_visitor_referral();

		// Generate QR code URL server-side
		$qr_url = $this->generate_qr_url($base_url, $size, $referral_code);

		// Generate unique ID for this QR code
		$qr_id = 'ert-qr-' . uniqid();

		// Generate HTML with pre-generated QR URL
		return $this->generate_html_server_side($qr_id, $size, $qr_url, $padding, $border_radius, $container_color, $border_color);
	}

	/**
	 * Sanitize size parameter
	 *
	 * @param mixed $size Size value
	 * @return int Clamped size between 100-500
	 */
	private function sanitize_size(mixed $size): int {
		$size = absint($size);
		return max(100, min(500, $size));
	}

	/**
	 * Sanitize padding parameter
	 *
	 * @param mixed $padding Padding value
	 * @return int Clamped padding between 0-100
	 */
	private function sanitize_padding(mixed $padding): int {
		$padding = absint($padding);
		return max(0, min(100, $padding));
	}

	/**
	 * Sanitize border radius parameter
	 *
	 * @param mixed $border_radius Border radius value
	 * @return int Clamped border radius between 0-50
	 */
	private function sanitize_border_radius(mixed $border_radius): int {
		$border_radius = absint($border_radius);
		return max(0, min(50, $border_radius));
	}

	/**
	 * Generate HTML for QR code with server-generated image URL
	 *
	 * @param string $qr_id           QR code element ID
	 * @param int    $size            QR code size
	 * @param string $qr_url          Pre-generated QR code image URL
	 * @param int    $padding         Padding value
	 * @param int    $border_radius   Border radius value
	 * @param string $container_color Container color
	 * @param string $border_color    Border color
	 * @return string HTML output
	 */
	private function generate_html_server_side(string $qr_id, int $size, string $qr_url, int $padding, int $border_radius, string $container_color, string $border_color): string {
		ob_start();
		?>
		<div class="easyreferraltracker-qr-wrapper" 
			 id="<?php echo esc_attr($qr_id); ?>" 
			 style="text-align: center; margin: 20px auto;">
			<div class="easyreferraltracker-qr-container" style="display: inline-block; position: relative; padding: <?php echo esc_attr($padding); ?>px; background: <?php echo esc_attr($container_color); ?>; border: 2px solid <?php echo esc_attr($border_color); ?>; border-radius: <?php echo esc_attr($border_radius); ?>px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
				<img class="easyreferraltracker-qr-code" 
					 src="<?php echo esc_url($qr_url); ?>" 
					 alt="QR Code" 
					 style="display: block; width: <?php echo esc_attr($size); ?>px; height: <?php echo esc_attr($size); ?>px; max-width: 100%; height: auto;" 
					 loading="lazy">
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

}
