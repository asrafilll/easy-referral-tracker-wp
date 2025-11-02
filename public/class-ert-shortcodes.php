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
		$qr_id = 'ert-qr-' . md5($base_url . $size . $label);

		// Enqueue shortcode script
		$this->enqueue_shortcode_script($qr_id, $base_url, $size);

		// Generate HTML
		return $this->generate_html($qr_id, $size, $label, $padding, $border_radius, $container_color, $border_color);
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
	 * Enqueue shortcode script
	 *
	 * @param string $qr_id    QR code element ID
	 * @param string $base_url Base URL for QR
	 * @param int    $size     QR code size
	 * @return void
	 */
	private function enqueue_shortcode_script(string $qr_id, string $base_url, int $size): void {
		// Cache-friendly approach: Add script that works with all caching plugins
		add_action('wp_footer', function() use ($qr_id, $base_url, $size) {
			echo '<script type="text/javascript">';
			echo "
			// Cache-friendly QR code generation - works with WP Rocket, LiteSpeed, etc.
			(function() {
				'use strict';
				
				var qrId = '" . esc_js($qr_id) . "';
				var baseUrl = " . wp_json_encode($base_url) . ";
				var qrSize = " . absint($size) . ";
				var initAttempts = 0;
				var maxAttempts = 50; // Try for up to 5 seconds
				
				function getCookie(name) {
					var value = '; ' + document.cookie;
					var parts = value.split('; ' + name + '=');
					if (parts.length === 2) return parts.pop().split(';').shift();
					return null;
				}
				
				function generateQRCode() {
					var container = document.getElementById(qrId);
					if (!container) {
						initAttempts++;
						if (initAttempts < maxAttempts) {
							setTimeout(generateQRCode, 100);
						}
						return;
					}
					
					// Get referral code from cookie or URL parameter
					var referralCode = getCookie('ert_referral');
					if (!referralCode) {
						// Fallback: check URL parameters manually for browser compatibility
						var search = window.location.search;
						var match = search.match(/[?&]r=([^&]*)/);
						referralCode = match ? decodeURIComponent(match[1]) : '" . esc_js(get_option('ert_default_referral', 'direct')) . "';
					}
					
					// Normalize base URL - ensure trailing slash
					var normalizedUrl = baseUrl;
					if (!normalizedUrl.endsWith('/') && normalizedUrl.indexOf('?') === -1) {
						normalizedUrl += '/';
					}
					
					// Build final URL with referral code
					var separator = normalizedUrl.indexOf('?') !== -1 ? '&' : '?';
					var finalUrl = normalizedUrl + separator + 'r=' + encodeURIComponent(referralCode);
					
					// Generate QR code URL using reliable QR service
					var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' + qrSize + 'x' + qrSize + '&data=' + encodeURIComponent(finalUrl);
					
					// Update QR code image
					var qrImg = container.querySelector('.easyreferraltracker-qr-code');
					if (qrImg) {
						// Add error handling for QR image loading
						qrImg.onerror = function() {
							// Fallback to Google Charts QR service if first fails
							this.src = 'https://chart.googleapis.com/chart?chs=' + qrSize + 'x' + qrSize + '&cht=qr&chl=' + encodeURIComponent(finalUrl) + '&chld=M|2';
						};
						
						qrImg.onload = function() {
							// Smooth fade-in effect
							var wrapper = container;
							wrapper.style.opacity = '1';
							wrapper.style.transform = 'scale(1)';
						};
						
						qrImg.src = qrUrl;
						qrImg.alt = 'QR Code for ' + referralCode;
					}
				}
				
				// Multiple initialization strategies for cache compatibility
				function initQR() {
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', generateQRCode);
					} else {
						generateQRCode();
					}
				}
				
				// Immediate execution
				initQR();
				
				// Backup initialization for aggressive caching
				if (window.addEventListener) {
					window.addEventListener('load', generateQRCode, false);
				} else if (window.attachEvent) {
					window.attachEvent('onload', generateQRCode);
				}
				
			})();
			";
			echo '</script>';
		});
	}

	/**
	 * Generate HTML for QR code
	 *
	 * @param string $qr_id           QR code element ID
	 * @param int    $size            QR code size
	 * @param string $label           Label text
	 * @param int    $padding         Padding value
	 * @param int    $border_radius   Border radius value
	 * @param string $container_color Container color
	 * @param string $border_color    Border color
	 * @return string HTML output
	 */
	private function generate_html(string $qr_id, int $size, string $label, int $padding, int $border_radius, string $container_color, string $border_color): string {
		ob_start();
		?>
		<div class="easyreferraltracker-qr-wrapper" id="<?php echo esc_attr($qr_id); ?>" style="text-align: center; margin: 20px auto; opacity: 0.3; transition: opacity 0.3s ease, transform 0.3s ease; transform: scale(0.95);">
			<div class="easyreferraltracker-qr-container" style="display: inline-block; position: relative; padding: <?php echo esc_attr($padding); ?>px; background: <?php echo esc_attr($container_color); ?>; border: 2px solid <?php echo esc_attr($border_color); ?>; border-radius: <?php echo esc_attr($border_radius); ?>px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
				<img class="easyreferraltracker-qr-code" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzk5OTk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxvYWRpbmcuLi48L3RleHQ+PC9zdmc+" alt="<?php echo esc_attr($label); ?>" style="display: block; width: <?php echo esc_attr($size); ?>px; height: <?php echo esc_attr($size); ?>px; max-width: 100%; height: auto;">
			</div>
			<?php if (!empty($label)): ?>
				<p style="margin-top: 15px; font-size: 16px; color: #333; font-weight: 500;"><?php echo esc_html($label); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
