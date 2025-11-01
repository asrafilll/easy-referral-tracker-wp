<?php
/**
 * QR Generator Class
 *
 * Handles QR code generation page logic
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_QR_Generator
 *
 * Manages QR code generation admin page
 */
class ERT_QR_Generator {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Constructor intentionally left empty
		// All initialization happens in render_page()
	}

	/**
	 * Render QR generator page
	 *
	 * @return void
	 */
	public function render_page() {
		// Security: Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'easyreferraltracker'));
		}

		// Include template
		include EASYREFERRALTRACKER_PLUGIN_DIR . 'admin/views/qr-generator.php';
	}

	/**
	 * Get QR code settings
	 *
	 * @return array QR code settings
	 */
	public function get_qr_settings() {
		return array(
			'base_url' => get_option('ert_qr_base_url', home_url('/download')),
			'size' => get_option('ert_qr_size', 300),
			'label' => get_option('ert_qr_label', 'Scan to Download'),
			'logo' => get_option('ert_qr_logo', 0),
			'padding' => get_option('ert_qr_padding', 20),
			'border_radius' => get_option('ert_qr_border_radius', 10),
			'container_color' => get_option('ert_qr_container_color', '#FFFFFF'),
			'border_color' => get_option('ert_qr_border_color', '#E5E7EB'),
		);
	}

	/**
	 * Generate QR code URL
	 *
	 * @param string $url          URL to encode
	 * @param string $referral     Referral code
	 * @param int    $size         QR code size
	 * @return string QR code image URL
	 */
	public function generate_qr_url($url, $referral = 'test', $size = 300) {
		// Build final URL with referral
		$final_url = $url . (strpos($url, '?') !== false ? '&' : '?') . 'r=' . urlencode($referral);

		// Generate QR code URL using QR Server API
		return 'https://api.qrserver.com/v1/create-qr-code/?size=' . absint($size) . 'x' . absint($size) . '&data=' . urlencode($final_url);
	}

	/**
	 * Get shortcode example
	 *
	 * @return string Shortcode example text
	 */
	public function get_shortcode_example() {
		return '[easyreferraltracker_qr]';
	}

	/**
	 * Get shortcode with attributes example
	 *
	 * @return string Shortcode with attributes example
	 */
	public function get_shortcode_with_atts_example() {
		return '[easyreferraltracker_qr size="250" label="Download App"]';
	}
}
