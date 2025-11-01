<?php
/**
 * AJAX Handler Class
 *
 * Handles AJAX requests for click tracking
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_AJAX_Handler
 *
 * Processes AJAX requests with security and rate limiting
 */
class ERT_AJAX_Handler {

	/**
	 * Database handler
	 *
	 * @var ERT_Database
	 */
	private $database;

	/**
	 * Rate limiter
	 *
	 * @var ERT_Rate_Limiter
	 */
	private $rate_limiter;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->database = new ERT_Database();
		$this->rate_limiter = new ERT_Rate_Limiter();

		// Register AJAX handlers
		add_action('wp_ajax_ert_track_click', array($this, 'track_click'));
		add_action('wp_ajax_nopriv_ert_track_click', array($this, 'track_click'));
	}

	/**
	 * Track link click via AJAX
	 *
	 * Enhanced security with nonce verification and rate limiting
	 *
	 * @return void
	 */
	public function track_click() {
		// Security: Verify nonce
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ert_track_click')) {
			wp_send_json_error(array('message' => 'Invalid security token'), 403);
			wp_die();
		}

		// Check rate limit
		$this->rate_limiter->check_rate_limit();

		// Security: Check if required fields exist
		if (!isset($_POST['referral_code']) || !isset($_POST['platform'])) {
			wp_send_json_error(array('message' => 'Missing required fields'), 400);
			wp_die();
		}

		// Security: Sanitize and validate inputs
		$referral_code = sanitize_text_field(wp_unslash($_POST['referral_code']));
		$platform = sanitize_text_field(wp_unslash($_POST['platform']));

		// Security: Validate referral code format
		if (!preg_match('/^[a-zA-Z0-9_-]{1,100}$/', $referral_code)) {
			wp_send_json_error(array('message' => 'Invalid referral code format'), 400);
			wp_die();
		}

		// Security: Validate platform
		if (!in_array($platform, array('ios', 'android'), true)) {
			wp_send_json_error(array('message' => 'Invalid platform'), 400);
			wp_die();
		}

		// Record the click
		$result = $this->database->record_click($referral_code, $platform);

		if ($result) {
			wp_send_json_success(array('message' => 'Tracked'), 200);
		} else {
			wp_send_json_error(array('message' => 'Database error'), 500);
		}

		wp_die();
	}
}
