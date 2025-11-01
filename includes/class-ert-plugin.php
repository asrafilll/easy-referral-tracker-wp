<?php
/**
 * Main Plugin Class
 *
 * Orchestrates all plugin components and handles initialization
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Plugin
 *
 * Main plugin orchestrator using Singleton pattern
 */
class ERT_Plugin {

	/**
	 * Singleton instance
	 *
	 * @var ERT_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Database handler instance
	 *
	 * @var ERT_Database
	 */
	private $database;

	/**
	 * Tracker instance
	 *
	 * @var ERT_Tracker
	 */
	private $tracker;

	/**
	 * Admin instance
	 *
	 * @var ERT_Admin
	 */
	private $admin;

	/**
	 * AJAX handler instance
	 *
	 * @var ERT_AJAX_Handler
	 */
	private $ajax_handler;

	/**
	 * Settings instance
	 *
	 * @var ERT_Settings
	 */
	private $settings;

	/**
	 * Shortcodes instance
	 *
	 * @var ERT_Shortcodes
	 */
	private $shortcodes;

	/**
	 * Get singleton instance
	 *
	 * @return ERT_Plugin
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * Private to enforce Singleton pattern
	 */
	private function __construct() {
		// Register activation/deactivation hooks
		register_activation_hook(EASYREFERRALTRACKER_PLUGIN_DIR . 'easyreferraltracker.php', array($this, 'activate'));
		register_deactivation_hook(EASYREFERRALTRACKER_PLUGIN_DIR . 'easyreferraltracker.php', array($this, 'deactivate'));

		// Initialize plugin
		add_action('plugins_loaded', array($this, 'init'));
	}

	/**
	 * Initialize plugin components
	 *
	 * Checks WordPress version and initializes all plugin components
	 *
	 * @return void
	 */
	public function init() {
		// Security: Check WordPress version
		if (version_compare(get_bloginfo('version'), '5.0', '<')) {
			add_action('admin_notices', array($this, 'old_wordpress_notice'));
			return;
		}

		// Initialize database handler
		$this->database = new ERT_Database();

		// Initialize tracker (frontend)
		$this->tracker = new ERT_Tracker();

		// Initialize AJAX handler
		$this->ajax_handler = new ERT_AJAX_Handler();

		// Initialize settings
		$this->settings = new ERT_Settings();

		// Initialize shortcodes
		$this->shortcodes = new ERT_Shortcodes();

		// Initialize admin (only in admin area)
		if (is_admin()) {
			$this->admin = new ERT_Admin();
		}

		// Check database version
		add_action('admin_init', array($this->database, 'check_db_version'));
	}

	/**
	 * Display admin notice for old WordPress versions
	 *
	 * @return void
	 */
	public function old_wordpress_notice() {
		echo '<div class="notice notice-error"><p>';
		echo esc_html__('EasyReferralTracker requires WordPress 5.0 or higher.', 'easyreferraltracker');
		echo '</p></div>';
	}

	/**
	 * Plugin activation
	 *
	 * Creates database tables and sets default options
	 *
	 * @return void
	 */
	public function activate() {
		// Security: Check user capabilities
		if (!current_user_can('activate_plugins')) {
			return;
		}

		// Initialize database handler if not already done
		if (!isset($this->database)) {
			$this->database = new ERT_Database();
		}

		// Create database tables
		$this->database->create_tables();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Set default options
		add_option('ert_cookie_days', 30, '', 'no');
		add_option('ert_rate_limit_user', 50, '', 'no');
		add_option('ert_rate_limit_global', 1000, '', 'no');
		add_option('ert_db_version', EASYREFERRALTRACKER_DB_VERSION, '', 'no');
		add_option('ert_qr_base_url', home_url('/download'), '', 'no');
		add_option('ert_qr_size', 300, '', 'no');
		add_option('ert_qr_label', 'Scan to Download', '', 'no');
		add_option('ert_qr_padding', 20, '', 'no');
		add_option('ert_qr_border_radius', 10, '', 'no');
		add_option('ert_qr_container_color', '#FFFFFF', '', 'no');
		add_option('ert_qr_border_color', '#E5E7EB', '', 'no');
	}

	/**
	 * Plugin deactivation
	 *
	 * Cleanup tasks on deactivation
	 *
	 * @return void
	 */
	public function deactivate() {
		// Security: Check user capabilities
		if (!current_user_can('activate_plugins')) {
			return;
		}

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Get database handler instance
	 *
	 * @return ERT_Database
	 */
	public function get_database() {
		return $this->database;
	}

	/**
	 * Get tracker instance
	 *
	 * @return ERT_Tracker
	 */
	public function get_tracker() {
		return $this->tracker;
	}

	/**
	 * Get admin instance
	 *
	 * @return ERT_Admin|null
	 */
	public function get_admin() {
		return $this->admin;
	}
}
