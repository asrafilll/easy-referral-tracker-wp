<?php
/**
 * Settings Class
 *
 * Handles WordPress Settings API registration
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Settings
 *
 * Manages plugin settings registration and validation
 */
class ERT_Settings {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Register all plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		// General settings
		$this->register_general_settings();

		// App Store settings
		$this->register_appstore_settings();

		// QR Code settings
		$this->register_qr_settings();
	}

	/**
	 * Register general settings
	 *
	 * @return void
	 */
	private function register_general_settings() {
		register_setting('ert_general_settings', 'ert_cookie_days', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 30,
		));

		register_setting('ert_general_settings', 'ert_rate_limit_user', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 50,
		));

		register_setting('ert_general_settings', 'ert_rate_limit_global', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 1000,
		));
	}

	/**
	 * Register App Store settings
	 *
	 * @return void
	 */
	private function register_appstore_settings() {
		register_setting('ert_appstore_settings', 'ert_ios_app_id', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		));

		register_setting('ert_appstore_settings', 'ert_android_package', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		));

		register_setting('ert_appstore_settings', 'ert_provider_token', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		));
	}

	/**
	 * Register QR Code settings
	 *
	 * @return void
	 */
	private function register_qr_settings() {
		register_setting('ert_qr_settings', 'ert_qr_base_url', array(
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default' => home_url('/download'),
		));

		register_setting('ert_qr_settings', 'ert_qr_size', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 300,
		));

		register_setting('ert_qr_settings', 'ert_qr_label', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => 'Scan to Download',
		));

		register_setting('ert_qr_settings', 'ert_qr_logo', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 0,
		));

		register_setting('ert_qr_settings', 'ert_qr_padding', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 20,
		));

		register_setting('ert_qr_settings', 'ert_qr_border_radius', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 10,
		));

		register_setting('ert_qr_settings', 'ert_qr_container_color', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '#FFFFFF',
		));

		register_setting('ert_qr_settings', 'ert_qr_border_color', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '#E5E7EB',
		));
	}

	/**
	 * Get setting value
	 *
	 * @param string $key     Setting key
	 * @param mixed  $default Default value
	 * @return mixed
	 */
	public static function get($key, $default = '') {
		return get_option($key, $default);
	}

	/**
	 * Update setting value
	 *
	 * @param string $key   Setting key
	 * @param mixed  $value Setting value
	 * @return bool
	 */
	public static function update($key, $value) {
		return update_option($key, $value);
	}

	/**
	 * Delete setting
	 *
	 * @param string $key Setting key
	 * @return bool
	 */
	public static function delete($key) {
		return delete_option($key);
	}
}
