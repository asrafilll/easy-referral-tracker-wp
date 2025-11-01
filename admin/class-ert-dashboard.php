<?php
/**
 * Dashboard Class
 *
 * Handles dashboard page logic and data preparation
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Dashboard
 *
 * Prepares and displays dashboard analytics
 */
class ERT_Dashboard {

	/**
	 * Database handler
	 *
	 * @var ERT_Database
	 */
	private $database;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->database = new ERT_Database();
	}

	/**
	 * Render dashboard page
	 *
	 * @return void
	 */
	public function render_page() {
		// Security: Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'easyreferraltracker'));
		}

		// Get dashboard data
		$data = $this->get_dashboard_data();

		// Extract data for template
		extract($data);

		// Include template
		include EASYREFERRALTRACKER_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Get all dashboard data
	 *
	 * @return array Dashboard statistics and data
	 */
	public function get_dashboard_data() {
		return array(
			'total_visits' => $this->database->get_total_visits(),
			'unique_referrals' => $this->database->get_unique_referrals(),
			'total_clicks' => $this->database->get_total_clicks(),
			'today_visits' => $this->database->get_today_visits(),
			'top_referrals' => $this->database->get_top_referrals(20),
			'recent_activity' => $this->database->get_recent_activity(50),
		);
	}

	/**
	 * Get total visits
	 *
	 * @return int
	 */
	public function get_total_visits() {
		return $this->database->get_total_visits();
	}

	/**
	 * Get unique referrals count
	 *
	 * @return int
	 */
	public function get_unique_referrals() {
		return $this->database->get_unique_referrals();
	}

	/**
	 * Get total clicks
	 *
	 * @return int
	 */
	public function get_total_clicks() {
		return $this->database->get_total_clicks();
	}

	/**
	 * Get today's visits
	 *
	 * @return int
	 */
	public function get_today_visits() {
		return $this->database->get_today_visits();
	}

	/**
	 * Get top performing referrals
	 *
	 * @param int $limit Number of results
	 * @return array
	 */
	public function get_top_referrals($limit = 20) {
		return $this->database->get_top_referrals($limit);
	}

	/**
	 * Get recent activity
	 *
	 * @param int $limit Number of results
	 * @return array
	 */
	public function get_recent_activity($limit = 50) {
		return $this->database->get_recent_activity($limit);
	}

	/**
	 * Calculate click rate
	 *
	 * @param int $visits Total visits
	 * @param int $clicks Total clicks
	 * @return float Click rate percentage
	 */
	public function calculate_click_rate($visits, $clicks) {
		if ($visits === 0) {
			return 0.0;
		}
		return ($clicks / $visits) * 100;
	}
}
