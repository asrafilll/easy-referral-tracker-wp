<?php
/**
 * Plugin Name: EasyReferralTracker
 * Plugin URI: https://github.com/asrafilll/easyreferraltracker
 * Description: Privacy-focused referral tracking system for app downloads with analytics dashboard and dynamic QR codes
 * Version: 2.0.0
 * Author: EasyReferralTracker Team
 * Author URI: https://github.com/asrafilll/easyreferraltracker
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easyreferraltracker
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('EASYREFERRALTRACKER_VERSION', '2.0.0');
define('EASYREFERRALTRACKER_DB_VERSION', '1.0.0');
define('EASYREFERRALTRACKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EASYREFERRALTRACKER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Autoloader for plugin classes
 *
 * Automatically loads classes following WordPress naming conventions
 *
 * @param string $class_name The name of the class to load
 * @return void
 */
function ert_autoloader($class_name) {
	// Only load classes with ERT_ prefix
	if (strpos($class_name, 'ERT_') !== 0) {
		return;
	}

	// Convert class name to file name
	// ERT_Plugin -> class-ert-plugin.php
	// ERT_Database -> class-ert-database.php
	$class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';

	// Define possible paths
	$paths = array(
		EASYREFERRALTRACKER_PLUGIN_DIR . 'includes/',
		EASYREFERRALTRACKER_PLUGIN_DIR . 'admin/',
		EASYREFERRALTRACKER_PLUGIN_DIR . 'public/',
	);

	// Try to find and load the class file
	foreach ($paths as $path) {
		$file = $path . $class_file;
		if (file_exists($file)) {
			require_once $file;
			return;
		}
	}
}

// Register autoloader
spl_autoload_register('ert_autoloader');

/**
 * Initialize the plugin
 *
 * @return void
 */
function ert_init_plugin() {
	// Initialize the main plugin class
	ERT_Plugin::get_instance();
}

// Hook plugin initialization
add_action('plugins_loaded', 'ert_init_plugin', 0);

/**
 * Activation hook
 *
 * @return void
 */
function ert_activate_plugin() {
	// Ensure classes are loaded
	require_once EASYREFERRALTRACKER_PLUGIN_DIR . 'includes/class-ert-plugin.php';
	require_once EASYREFERRALTRACKER_PLUGIN_DIR . 'includes/class-ert-database.php';

	// Run activation
	$plugin = ERT_Plugin::get_instance();
	$plugin->activate();
}

/**
 * Deactivation hook
 *
 * @return void
 */
function ert_deactivate_plugin() {
	// Ensure classes are loaded
	require_once EASYREFERRALTRACKER_PLUGIN_DIR . 'includes/class-ert-plugin.php';

	// Run deactivation
	$plugin = ERT_Plugin::get_instance();
	$plugin->deactivate();
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'ert_activate_plugin');
register_deactivation_hook(__FILE__, 'ert_deactivate_plugin');
