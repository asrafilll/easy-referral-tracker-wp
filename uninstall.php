<?php
/**
 * Uninstall Script for EasyReferralTracker
 *
 * This file is executed when the plugin is deleted via WordPress admin.
 * It removes all database tables and options created by the plugin.
 *
 * @package EasyReferralTracker
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Security: Check if user has permission to delete plugins
if (!current_user_can('delete_plugins')) {
    exit;
}

global $wpdb;

// Delete database tables
$table_visits = $wpdb->prefix . 'ert_referral_visits';
$table_clicks = $wpdb->prefix . 'ert_link_clicks';

$wpdb->query("DROP TABLE IF EXISTS $table_visits");
$wpdb->query("DROP TABLE IF EXISTS $table_clicks");

// Delete all plugin options
delete_option('ert_cookie_days');
delete_option('ert_rate_limit_user');
delete_option('ert_rate_limit_global');
delete_option('ert_ios_app_id');
delete_option('ert_android_package');
delete_option('ert_provider_token');
delete_option('ert_qr_base_url');
delete_option('ert_qr_size');
delete_option('ert_qr_label');
delete_option('ert_qr_logo');
delete_option('ert_db_version');

// Delete all transients (rate limiting caches)
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ert_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ert_%'");

// Note: We don't delete cookies as they are client-side and will expire naturally
// Cookies: ert_referral, ert_tracked_*, ert_rate_limit_user

// Clear any cached data
wp_cache_flush();