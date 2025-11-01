<?php
/**
 * Database Handler Class
 *
 * Handles all database operations including table creation,
 * schema upgrades, and analytics queries
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Database
 *
 * Manages database tables and queries for the plugin
 */
class ERT_Database {

	/**
	 * Visits table name
	 *
	 * @var string
	 */
	private $table_visits;

	/**
	 * Clicks table name
	 *
	 * @var string
	 */
	private $table_clicks;

	/**
	 * WordPress database object
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table_visits = $wpdb->prefix . 'ert_referral_visits';
		$this->table_clicks = $wpdb->prefix . 'ert_link_clicks';
	}

	/**
	 * Create database tables
	 *
	 * Creates privacy-focused tables (no IP, no User Agent)
	 *
	 * @return void
	 */
	public function create_tables() {
		$charset_collate = $this->wpdb->get_charset_collate();

		// Referral visits table - PRIVACY FOCUSED
		$sql_visits = "CREATE TABLE IF NOT EXISTS {$this->table_visits} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			referral_code varchar(100) NOT NULL,
			landing_page varchar(500) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY referral_code (referral_code(20)),
			KEY created_at (created_at)
		) $charset_collate;";

		// Link clicks table - PRIVACY FOCUSED
		$sql_clicks = "CREATE TABLE IF NOT EXISTS {$this->table_clicks} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			referral_code varchar(100) NOT NULL,
			platform varchar(20) NOT NULL,
			clicked_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY referral_code (referral_code(20)),
			KEY platform (platform(10)),
			KEY clicked_at (clicked_at)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_visits);
		dbDelta($sql_clicks);
	}

	/**
	 * Check and upgrade database if needed
	 *
	 * @return void
	 */
	public function check_db_version() {
		$current_db_version = get_option('ert_db_version', '0');

		if (version_compare($current_db_version, EASYREFERRALTRACKER_DB_VERSION, '<')) {
			$this->upgrade_database();
			update_option('ert_db_version', EASYREFERRALTRACKER_DB_VERSION);
		}
	}

	/**
	 * Upgrade database schema
	 *
	 * Removes old privacy-invasive columns and recreates tables
	 *
	 * @return void
	 */
	private function upgrade_database() {
		// Drop old IP and user_agent columns if they exist (privacy cleanup)
		$this->wpdb->query("ALTER TABLE {$this->table_visits} DROP COLUMN IF EXISTS visitor_ip");
		$this->wpdb->query("ALTER TABLE {$this->table_visits} DROP COLUMN IF EXISTS user_agent");
		$this->wpdb->query("ALTER TABLE {$this->table_visits} DROP COLUMN IF EXISTS session_id");
		$this->wpdb->query("ALTER TABLE {$this->table_clicks} DROP COLUMN IF EXISTS visitor_ip");

		// Recreate tables with new schema
		$this->create_tables();
	}

	/**
	 * Record a referral visit
	 *
	 * @param string $referral_code The referral code
	 * @param string $landing_page  The landing page URL
	 * @return bool True on success, false on failure
	 */
	public function record_visit($referral_code, $landing_page) {
		$result = $this->wpdb->insert(
			$this->table_visits,
			array(
				'referral_code' => $referral_code,
				'landing_page' => $landing_page,
			),
			array('%s', '%s')
		);

		return false !== $result;
	}

	/**
	 * Record a link click
	 *
	 * @param string $referral_code The referral code
	 * @param string $platform      The platform (ios or android)
	 * @return bool True on success, false on failure
	 */
	public function record_click($referral_code, $platform) {
		$result = $this->wpdb->insert(
			$this->table_clicks,
			array(
				'referral_code' => $referral_code,
				'platform' => $platform,
			),
			array('%s', '%s')
		);

		return false !== $result;
	}

	/**
	 * Get total number of visits
	 *
	 * @return int
	 */
	public function get_total_visits() {
		$count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_visits}");
		return absint($count);
	}

	/**
	 * Get number of unique referrals
	 *
	 * @return int
	 */
	public function get_unique_referrals() {
		$count = $this->wpdb->get_var("SELECT COUNT(DISTINCT referral_code) FROM {$this->table_visits}");
		return absint($count);
	}

	/**
	 * Get total number of clicks
	 *
	 * @return int
	 */
	public function get_total_clicks() {
		$count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_clicks}");
		return absint($count);
	}

	/**
	 * Get today's visits count
	 *
	 * @return int
	 */
	public function get_today_visits() {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table_visits} WHERE DATE(created_at) = %s",
				current_time('Y-m-d')
			)
		);
		return absint($count);
	}

	/**
	 * Get top performing referrals
	 *
	 * @param int $limit Number of results to return
	 * @return array
	 */
	public function get_top_referrals($limit = 20) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT v.referral_code,
						COUNT(DISTINCT v.id) as visits,
						COUNT(DISTINCT c.id) as clicks,
						MAX(v.created_at) as last_used
				 FROM {$this->table_visits} v
				 LEFT JOIN {$this->table_clicks} c ON v.referral_code = c.referral_code
				 GROUP BY v.referral_code
				 ORDER BY visits DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Get recent activity
	 *
	 * @param int $limit Number of results to return
	 * @return array
	 */
	public function get_recent_activity($limit = 50) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_visits} ORDER BY created_at DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Get visits table name
	 *
	 * @return string
	 */
	public function get_visits_table() {
		return $this->table_visits;
	}

	/**
	 * Get clicks table name
	 *
	 * @return string
	 */
	public function get_clicks_table() {
		return $this->table_clicks;
	}
}
