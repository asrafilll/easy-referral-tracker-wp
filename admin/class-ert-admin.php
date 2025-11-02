<?php
/**
 * Admin Class
 *
 * Handles admin area functionality including menus and scripts
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Admin
 *
 * Manages WordPress admin area integration
 */
class ERT_Admin {

	/**
	 * Dashboard handler
	 *
	 * @var ERT_Dashboard
	 */
	private ERT_Dashboard $dashboard;

	/**
	 * QR Generator handler
	 *
	 * @var ERT_QR_Generator
	 */
	private ERT_QR_Generator $qr_generator;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize admin components
		$this->dashboard = new ERT_Dashboard();
		$this->qr_generator = new ERT_QR_Generator();

		// Register hooks
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
	}

	/**
	 * Add admin menu pages
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		// Main dashboard page
		add_menu_page(
			__('Referral Tracker', 'easyreferraltracker'),
			__('Referral Tracker', 'easyreferraltracker'),
			'manage_options',
			'easyreferraltracker',
			array($this->dashboard, 'render_page'),
			'dashicons-chart-line',
			30
		);

		// QR Generator submenu
		add_submenu_page(
			'easyreferraltracker',
			__('QR Code Generator', 'easyreferraltracker'),
			__('QR Generator', 'easyreferraltracker'),
			'manage_options',
			'easyreferraltracker-qr-generator',
			array($this->qr_generator, 'render_page')
		);

		// Settings submenu
		add_submenu_page(
			'easyreferraltracker',
			__('Settings', 'easyreferraltracker'),
			__('Settings', 'easyreferraltracker'),
			'manage_options',
			'easyreferraltracker-settings',
			array($this, 'render_settings_page')
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook
	 * @return void
	 */
	public function enqueue_admin_scripts(string $hook): void {
		// Only load on our plugin pages
		if (strpos($hook, 'easyreferraltracker') === false) {
			return;
		}

		// Enqueue admin CSS
		wp_enqueue_style(
			'ert-admin-css',
			EASYREFERRALTRACKER_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			EASYREFERRALTRACKER_VERSION
		);

		wp_enqueue_style(
			'ert-dashboard-css',
			EASYREFERRALTRACKER_PLUGIN_URL . 'admin/css/dashboard.css',
			array(),
			EASYREFERRALTRACKER_VERSION
		);

		// Enqueue media uploader for QR logo (on QR generator page)
		if ($hook === 'easyreferraltracker_page_easyreferraltracker-qr-generator' || strpos($hook, 'qr-generator') !== false) {
			wp_enqueue_media();
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('wp-color-picker');

			// Enqueue QR generator JavaScript
			wp_enqueue_script(
				'ert-qr-generator-js',
				EASYREFERRALTRACKER_PLUGIN_URL . 'admin/js/qr-generator.js',
				array('jquery', 'wp-color-picker'),
				EASYREFERRALTRACKER_VERSION,
				true
			);
		}
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		// Security: Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'easyreferraltracker'));
		}

		include EASYREFERRALTRACKER_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Get dashboard instance
	 *
	 * @return ERT_Dashboard
	 */
	public function get_dashboard(): ERT_Dashboard {
		return $this->dashboard;
	}

	/**
	 * Get QR generator instance
	 *
	 * @return ERT_QR_Generator
	 */
	public function get_qr_generator(): ERT_QR_Generator {
		return $this->qr_generator;
	}
}
