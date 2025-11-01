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
				'logo_size' => get_option('ert_qr_logo_size', 15),
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
		$logo_size = $this->sanitize_logo_size($atts['logo_size']);

		// Get logo
		$logo_id = get_option('ert_qr_logo', 0);
		$logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';

		// Generate unique ID for this QR code
		$qr_id = 'ert-qr-' . md5($base_url . $size . $label);

		// Enqueue shortcode script
		$this->enqueue_shortcode_script($qr_id, $base_url, $size);

		// Generate HTML
		return $this->generate_html($qr_id, $size, $label, $padding, $border_radius, $container_color, $border_color, $logo_url, $logo_size);
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
	 * Sanitize logo size parameter
	 *
	 * @param mixed $logo_size Logo size percentage
	 * @return int Clamped logo size between 5-30 percent
	 */
	private function sanitize_logo_size(mixed $logo_size): int {
		$logo_size = absint($logo_size);
		return max(5, min(30, $logo_size));
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
		// Inline script for QR code generation
		// This is minimal and specific to each shortcode instance
		wp_add_inline_script(
			'jquery',
			"
			(function() {
				function initQR_" . md5($qr_id) . "() {
					const container = document.getElementById('" . esc_js($qr_id) . "');
					if (!container) return;

					function getCookie(name) {
						const value = '; ' + document.cookie;
						const parts = value.split('; ' + name + '=');
						if (parts.length === 2) return parts.pop().split(';').shift();
						return 'homepage';
					}

					const referralCode = getCookie('ert_referral') || 'homepage';
					const baseUrl = " . wp_json_encode($base_url) . ";
					const size = " . absint($size) . ";
					const finalUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'r=' + encodeURIComponent(referralCode);
					const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&data=' + encodeURIComponent(finalUrl);

					const qrImg = container.querySelector('.easyreferraltracker-qr-code');
					if (qrImg) qrImg.src = qrUrl;
				}

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', initQR_" . md5($qr_id) . ");
				} else {
					initQR_" . md5($qr_id) . "();
				}
			})();
			"
		);
	}

	/**
	 * Generate HTML for QR code
	 *
	 * @param string $qr_id           QR code element ID
	 * @param int    $size            QR code size
	 * @param string $label           Label text
	 * @param int    $padding         Padding value
	 * @param int    $border_radius   Border radius value
	 * @param string $container_color Container background color
	 * @param string $border_color    Border color
	 * @param string $logo_url        Logo URL (optional)
	 * @param int    $logo_size       Logo size percentage (5-30)
	 * @return string HTML output
	 */
	private function generate_html(string $qr_id, int $size, string $label, int $padding, int $border_radius, string $container_color, string $border_color, string $logo_url, int $logo_size = 15): string {
		ob_start();
		?>
		<div class="easyreferraltracker-qr-wrapper" id="<?php echo esc_attr($qr_id); ?>" style="text-align: center; margin: 20px auto;">
			<div class="easyreferraltracker-qr-container" style="display: inline-block; position: relative; padding: <?php echo esc_attr($padding); ?>px; background: <?php echo esc_attr($container_color); ?>; border: 2px solid <?php echo esc_attr($border_color); ?>; border-radius: <?php echo esc_attr($border_radius); ?>px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
				<img class="easyreferraltracker-qr-code" src="" alt="<?php echo esc_attr($label); ?>" style="display: block; width: <?php echo esc_attr($size); ?>px; height: <?php echo esc_attr($size); ?>px;">
				<?php if ($logo_url) : ?>
				<!-- Logo background for better visibility -->
				<?php $logo_bg_size = $size * ($logo_size + 3) / 100; ?>
				<?php $logo_actual_size = $size * $logo_size / 100; ?>
				<div style="position: absolute; width: <?php echo esc_attr($logo_bg_size); ?>px; height: <?php echo esc_attr($logo_bg_size); ?>px; top: <?php echo esc_attr($padding + ($size - $logo_bg_size) / 2); ?>px; left: <?php echo esc_attr($padding + ($size - $logo_bg_size) / 2); ?>px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
				<img class="easyreferraltracker-qr-logo" src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="position: absolute; width: <?php echo esc_attr($logo_actual_size); ?>px; height: <?php echo esc_attr($logo_actual_size); ?>px; top: <?php echo esc_attr($padding + ($size - $logo_actual_size) / 2); ?>px; left: <?php echo esc_attr($padding + ($size - $logo_actual_size) / 2); ?>px; border-radius: 6px; object-fit: cover; object-position: center; padding: 2px;">
				<?php endif; ?>
			</div>
			<?php if ($label) : ?>
			<p class="easyreferraltracker-qr-label" style="margin-top: 15px; font-size: 16px; color: #333; font-weight: 500;"><?php echo esc_html($label); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
