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
	 * Static flag to ensure global script is only enqueued once
	 */
	private static bool $global_script_enqueued = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode('easyreferraltracker_qr', array($this, 'render_qr_code'));
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

		// Generate unique ID for this QR code
		$qr_id = 'ert-qr-' . uniqid();

		// Enqueue global QR manager script (only once per page)
		$this->enqueue_global_qr_manager();

		// Generate HTML with data attributes
		return $this->generate_html_with_data_attrs($qr_id, $size, $base_url, $padding, $border_radius, $container_color, $border_color);
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
	 * Enqueue global QR manager script (once per page)
	 *
	 * @return void
	 */
	private function enqueue_global_qr_manager(): void {
		// Only enqueue once per page
		if (self::$global_script_enqueued) {
			return;
		}
		self::$global_script_enqueued = true;

		add_action('wp_footer', function() {
			echo '<script type="text/javascript">';
			echo "
			// Global QR Code Manager - Browser Compatible Version
			(function() {
				'use strict';
				
				var defaultReferral = '" . esc_js(get_option('ert_default_referral', 'direct')) . "';
				var processedQRs = {}; // Track processed QR codes
				
				// Browser-compatible cookie function
				function getCookie(name) {
					var value = '; ' + document.cookie;
					var parts = value.split('; ' + name + '=');
					if (parts.length === 2) return parts.pop().split(';').shift();
					return null;
				}
				
				// Get referral code once for all QR codes
				function getReferralCode() {
					var referralCode = getCookie('ert_referral');
					if (!referralCode) {
						// Browser-compatible URL parameter parsing
						var search = window.location.search;
						var match = search.match(/[?&]r=([^&]*)/);
						referralCode = match ? decodeURIComponent(match[1]) : defaultReferral;
					}
					return referralCode;
				}
				
				// Normalize URL to ensure trailing slash
				function normalizeUrl(url) {
					if (!url.endsWith('/') && url.indexOf('?') === -1) {
						url += '/';
					}
					return url;
				}
				
				// Process a single QR code
				function processQRCode(container, index, callback) {
					var qrId = container.id;
					
					// Skip if already processed
					if (processedQRs[qrId]) {
						if (callback) callback();
						return;
					}
					processedQRs[qrId] = true;
					
					var baseUrl = container.getAttribute('data-ert-base-url');
					var size = parseInt(container.getAttribute('data-ert-size'));
					var qrImg = container.querySelector('.easyreferraltracker-qr-code');
					
					if (!qrImg || !baseUrl || !size) {
						if (callback) callback();
						return;
					}
					
					var referralCode = getReferralCode();
					var normalizedUrl = normalizeUrl(baseUrl);
					var separator = normalizedUrl.indexOf('?') !== -1 ? '&' : '?';
					var finalUrl = normalizedUrl + separator + 'r=' + encodeURIComponent(referralCode);
					var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&data=' + encodeURIComponent(finalUrl);
					
					var fallbackUsed = false;
					var imageLoaded = false;
					
					// Add error handling
					qrImg.onerror = function() {
						if (!fallbackUsed && !imageLoaded) {
							fallbackUsed = true;
							this.src = 'https://chart.googleapis.com/chart?chs=' + size + 'x' + size + '&cht=qr&chl=' + encodeURIComponent(finalUrl) + '&chld=M|2';
						}
					};
					
					// Add load handler
					qrImg.onload = function() {
						if (!imageLoaded) {
							imageLoaded = true;
							container.style.opacity = '1';
							container.style.transform = 'scale(1)';
							// Process next QR code after this one loads
							if (callback) setTimeout(callback, 100);
						}
					};
					
					// Timeout fallback
					setTimeout(function() {
						if (!imageLoaded && !fallbackUsed) {
							fallbackUsed = true;
							qrImg.src = 'https://chart.googleapis.com/chart?chs=' + size + 'x' + size + '&cht=qr&chl=' + encodeURIComponent(finalUrl) + '&chld=M|2';
						}
						// Ensure callback runs even if image fails
						if (!imageLoaded && callback) {
							setTimeout(callback, 100);
						}
					}, 3000);
					
					// Set the image source to start loading
					qrImg.src = qrUrl;
					qrImg.alt = 'QR Code for ' + referralCode;
				}
				
				// Process all QR codes sequentially (browser compatible)
				function processAllQRCodes() {
					// Browser-compatible way to find elements with data-ert-qr
					var allDivs = document.getElementsByTagName('div');
					var qrContainers = [];
					
					// Manual filtering for better browser compatibility
					for (var i = 0; i < allDivs.length; i++) {
						if (allDivs[i].getAttribute('data-ert-qr') === 'true') {
							qrContainers.push(allDivs[i]);
						}
					}
					
					var currentIndex = 0;
					
					function processNext() {
						if (currentIndex < qrContainers.length) {
							processQRCode(qrContainers[currentIndex], currentIndex, function() {
								currentIndex++;
								processNext();
							});
						}
					}
					
					// Start processing if we have QR codes
					if (qrContainers.length > 0) {
						processNext();
					}
				}
				
				// Initialize when DOM is ready
				function initQRManager() {
					var initAttempts = 0;
					var maxAttempts = 50;
					
					function tryInit() {
						// Browser-compatible element detection
						var allDivs = document.getElementsByTagName('div');
						var qrCount = 0;
						
						for (var i = 0; i < allDivs.length; i++) {
							if (allDivs[i].getAttribute('data-ert-qr') === 'true') {
								qrCount++;
							}
						}
						
						if (qrCount > 0) {
							processAllQRCodes();
						} else {
							initAttempts++;
							if (initAttempts < maxAttempts) {
								setTimeout(tryInit, 100);
							}
						}
					}
					
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', tryInit);
					} else {
						tryInit();
					}
				}
				
				// Execute initialization
				initQRManager();
				
			})();
			";
			echo '</script>';
		}, 99);
	}

	/**
	 * Generate HTML for QR code with data attributes
	 *
	 * @param string $qr_id           QR code element ID
	 * @param int    $size            QR code size
	 * @param string $base_url        Base URL for QR code
	 * @param int    $padding         Padding value
	 * @param int    $border_radius   Border radius value
	 * @param string $container_color Container color
	 * @param string $border_color    Border color
	 * @return string HTML output
	 */
	private function generate_html_with_data_attrs(string $qr_id, int $size, string $base_url, int $padding, int $border_radius, string $container_color, string $border_color): string {
		ob_start();
		?>
		<div class="easyreferraltracker-qr-wrapper" 
			 id="<?php echo esc_attr($qr_id); ?>" 
			 data-ert-qr="true"
			 data-ert-base-url="<?php echo esc_attr($base_url); ?>"
			 data-ert-size="<?php echo esc_attr($size); ?>"
			 style="text-align: center; margin: 20px auto; opacity: 0.3; transition: opacity 0.3s ease, transform 0.3s ease; transform: scale(0.95);">
			<div class="easyreferraltracker-qr-container" style="display: inline-block; position: relative; padding: <?php echo esc_attr($padding); ?>px; background: <?php echo esc_attr($container_color); ?>; border: 2px solid <?php echo esc_attr($border_color); ?>; border-radius: <?php echo esc_attr($border_radius); ?>px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
				<img class="easyreferraltracker-qr-code" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzk5OTk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxvYWRpbmcuLi48L3RleHQ+PC9zdmc+" alt="QR Code" style="display: block; width: <?php echo esc_attr($size); ?>px; height: <?php echo esc_attr($size); ?>px; max-width: 100%; height: auto;">
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
