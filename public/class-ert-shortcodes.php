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
				'label' => get_option('ert_qr_label', 'Scan to Download'),
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
		$label = sanitize_text_field($atts['label']);
		$padding = $this->sanitize_padding($atts['padding']);
		$border_radius = $this->sanitize_border_radius($atts['border_radius']);
		$container_color = sanitize_hex_color($atts['container_color']);
		$border_color = sanitize_hex_color($atts['border_color']);

		// Generate unique ID for this QR code
		$qr_id = 'ert-qr-' . uniqid();

		// Enqueue global QR manager script (only once per page)
		$this->enqueue_global_qr_manager();

		// Generate HTML with data attributes
		return $this->generate_html_with_data_attrs($qr_id, $size, $base_url, $label, $padding, $border_radius, $container_color, $border_color);
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
			// Global QR Code Manager - Single script for all QR codes on page
			(function() {
				'use strict';
				
				var qrCache = {}; // Cache QR URLs to prevent duplicate API calls
				var defaultReferral = '" . esc_js(get_option('ert_default_referral', 'direct')) . "';
				
				// Shared cookie function
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
						var urlParams = new URLSearchParams(window.location.search);
						referralCode = urlParams.get('r') || defaultReferral;
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
				
				// Generate QR URL with caching
				function generateQRUrl(baseUrl, size, referralCode) {
					var cacheKey = baseUrl + '|' + size + '|' + referralCode;
					if (qrCache[cacheKey]) {
						return qrCache[cacheKey];
					}
					
					var normalizedUrl = normalizeUrl(baseUrl);
					var separator = normalizedUrl.indexOf('?') !== -1 ? '&' : '?';
					var finalUrl = normalizedUrl + separator + 'r=' + encodeURIComponent(referralCode);
					var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&data=' + encodeURIComponent(finalUrl) + '&ecc=M&margin=2';
					
					qrCache[cacheKey] = qrUrl;
					return qrUrl;
				}
				
				// Process all QR codes on the page
				function processAllQRCodes() {
					var referralCode = getReferralCode();
					var qrContainers = document.querySelectorAll('[data-ert-qr]');
					
					qrContainers.forEach(function(container) {
						var baseUrl = container.getAttribute('data-ert-base-url');
						var size = parseInt(container.getAttribute('data-ert-size'));
						var qrImg = container.querySelector('.easyreferraltracker-qr-code');
						
						if (qrImg && baseUrl && size) {
							var qrUrl = generateQRUrl(baseUrl, size, referralCode);
							
							// Add error handling
							qrImg.onerror = function() {
								var fallbackUrl = 'https://chart.googleapis.com/chart?chs=' + size + 'x' + size + '&cht=qr&chl=' + encodeURIComponent(normalizeUrl(baseUrl) + (normalizeUrl(baseUrl).indexOf('?') !== -1 ? '&' : '?') + 'r=' + encodeURIComponent(referralCode)) + '&chld=M|2';
								this.src = fallbackUrl;
							};
							
							// Add load handler for smooth animation
							qrImg.onload = function() {
								container.style.opacity = '1';
								container.style.transform = 'scale(1)';
							};
							
							qrImg.src = qrUrl;
							qrImg.alt = 'QR Code for ' + referralCode;
						}
					});
				}
				
				// Initialize when DOM is ready
				function initQRManager() {
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', processAllQRCodes);
					} else {
						processAllQRCodes();
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
	 * @param string $label           Label text
	 * @param int    $padding         Padding value
	 * @param int    $border_radius   Border radius value
	 * @param string $container_color Container background color
	 * @param string $border_color    Border color
	 * @return string HTML output
	 */
	private function generate_html_with_data_attrs(string $qr_id, int $size, string $base_url, string $label, int $padding, int $border_radius, string $container_color, string $border_color): string {
		ob_start();
		?>
		<div class="easyreferraltracker-qr-wrapper" 
			 id="<?php echo esc_attr($qr_id); ?>" 
			 data-ert-qr="true"
			 data-ert-base-url="<?php echo esc_attr($base_url); ?>"
			 data-ert-size="<?php echo esc_attr($size); ?>"
			 style="text-align: center; margin: 20px auto; opacity: 0.3; transition: opacity 0.3s ease, transform 0.3s ease; transform: scale(0.95);">
			<div class="easyreferraltracker-qr-container" style="display: inline-block; position: relative; padding: <?php echo esc_attr($padding); ?>px; background: <?php echo esc_attr($container_color); ?>; border: 2px solid <?php echo esc_attr($border_color); ?>; border-radius: <?php echo esc_attr($border_radius); ?>px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
				<img class="easyreferraltracker-qr-code" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzk5OTk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxvYWRpbmcuLi48L3RleHQ+PC9zdmc+" alt="<?php echo esc_attr($label); ?>" style="display: block; width: <?php echo esc_attr($size); ?>px; height: <?php echo esc_attr($size); ?>px; max-width: 100%; height: auto;">
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
