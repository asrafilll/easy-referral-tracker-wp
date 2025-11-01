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
	private string $table_visits;

	/**
	 * Clicks table name
	 *
	 * @var string
	 */
	private string $table_clicks;

	/**
	 * WordPress database object
	 *
	 * @var wpdb
	 */
	private wpdb $wpdb;

	/**
	 * Error handler instance
	 *
	 * @var ERT_Error_Handler
	 */
	private ERT_Error_Handler $error_handler;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table_visits = $wpdb->prefix . 'ert_referral_visits';
		$this->table_clicks = $wpdb->prefix . 'ert_link_clicks';
		$this->error_handler = ERT_Error_Handler::get_instance();
	}

	/**
	 * Create database tables
	 *
	 * Creates privacy-focused tables (no IP, no User Agent)
	 *
	 * @return void
	 */
	public function create_tables(): void {
		$charset_collate = $this->wpdb->get_charset_collate();

		// Referral visits table - PRIVACY FOCUSED
		$sql_visits = "CREATE TABLE IF NOT EXISTS {$this->table_visits} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			referral_code varchar(100) NOT NULL,
			landing_page varchar(500) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY referral_code (referral_code(20)),
			KEY created_at (created_at),
			KEY referral_created (referral_code(20), created_at),
			KEY date_only (created_at)
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
			KEY clicked_at (clicked_at),
			KEY referral_platform (referral_code(20), platform(10)),
			KEY platform_date (platform(10), clicked_at)
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
	public function check_db_version(): void {
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
	private function upgrade_database(): void {
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
	public function record_visit(string $referral_code, string $landing_page): bool {
		$result = $this->wpdb->insert(
			$this->table_visits,
			array(
				'referral_code' => $referral_code,
				'landing_page' => $landing_page,
			),
			array('%s', '%s')
		);

		if (false === $result) {
			$this->error_handler->log_database_error(
				$this->wpdb->last_query,
				$this->wpdb->last_error,
				'ERT_Database::record_visit'
			);
		} else {
			// Invalidate cache when new data is added
			$this->invalidate_analytics_cache();
		}

		return false !== $result;
	}

	/**
	 * Record a link click
	 *
	 * @param string $referral_code The referral code
	 * @param string $platform      The platform (ios or android)
	 * @return bool True on success, false on failure
	 */
	public function record_click(string $referral_code, string $platform): bool {
		$result = $this->wpdb->insert(
			$this->table_clicks,
			array(
				'referral_code' => $referral_code,
				'platform' => $platform,
			),
			array('%s', '%s')
		);

		if (false === $result) {
			$this->error_handler->log_database_error(
				$this->wpdb->last_query,
				$this->wpdb->last_error,
				'ERT_Database::record_click'
			);
		} else {
			// Invalidate cache when new data is added
			$this->invalidate_analytics_cache();
		}

		return false !== $result;
	}

	/**
	 * Get total number of visits
	 *
	 * @return int
	 */
	public function get_total_visits(): int {
		return ERT_Cache::remember(
			'total_visits',
			function() {
				$count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_visits}");
				return absint($count);
			},
			3600 // Cache for 1 hour
		);
	}

	/**
	 * Get number of unique referrals
	 *
	 * @return int
	 */
	public function get_unique_referrals(): int {
		return ERT_Cache::remember(
			'unique_referrals',
			function() {
				$count = $this->wpdb->get_var("SELECT COUNT(DISTINCT referral_code) FROM {$this->table_visits}");
				return absint($count);
			},
			3600 // Cache for 1 hour
		);
	}

	/**
	 * Get total number of clicks
	 *
	 * @return int
	 */
	public function get_total_clicks(): int {
		return ERT_Cache::remember(
			'total_clicks',
			function() {
				$count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_clicks}");
				return absint($count);
			},
			3600 // Cache for 1 hour
		);
	}

	/**
	 * Get today's visits count
	 *
	 * @return int
	 */
	public function get_today_visits(): int {
		$today = current_time('Y-m-d');
		return ERT_Cache::remember(
			"today_visits_{$today}",
			function() use ($today) {
				$count = $this->wpdb->get_var(
					$this->wpdb->prepare(
						"SELECT COUNT(*) FROM {$this->table_visits} WHERE DATE(created_at) = %s",
						$today
					)
				);
				return absint($count);
			},
			1800 // Cache for 30 minutes (more frequent updates for today's data)
		);
	}

	/**
	 * Get top performing referrals
	 *
	 * @param int $limit Number of results to return
	 * @return array
	 */
	public function get_top_referrals(int $limit = 20): array {
		return ERT_Cache::remember(
			"top_referrals_{$limit}",
			function() use ($limit) {
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
			},
			1800 // Cache for 30 minutes
		);
	}

	/**
	 * Get recent activity
	 *
	 * @param int $limit Number of results to return
	 * @return array
	 */
	public function get_recent_activity(int $limit = 50): array {
		return ERT_Cache::remember(
			"recent_activity_{$limit}",
			function() use ($limit) {
				$results = $this->wpdb->get_results(
					$this->wpdb->prepare(
						"SELECT * FROM {$this->table_visits} ORDER BY created_at DESC LIMIT %d",
						$limit
					),
					ARRAY_A
				);

				return $results ? $results : array();
			},
			600 // Cache for 10 minutes (recent activity changes frequently)
		);
	}

	/**
	 * Get visits table name
	 *
	 * @return string
	 */
	public function get_visits_table(): string {
		return $this->table_visits;
	}

	/**
	 * Get clicks table name
	 *
	 * @return string
	 */
	public function get_clicks_table(): string {
		return $this->table_clicks;
	}

	/**
	 * Invalidate analytics cache
	 *
	 * Called when new data is inserted to ensure fresh results
	 *
	 * @return void
	 */
	private function invalidate_analytics_cache(): void {
		$cache_keys = [
			'total_visits',
			'unique_referrals', 
			'total_clicks',
			'today_visits_' . current_time('Y-m-d'),
			'top_referrals_20',
			'recent_activity_50',
		];

		foreach ($cache_keys as $key) {
			ERT_Cache::delete($key);
		}
	}
}
