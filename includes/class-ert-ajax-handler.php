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
	private ERT_Database $database;

	/**
	 * Rate limiter
	 *
	 * @var ERT_Rate_Limiter
	 */
	private ERT_Rate_Limiter $rate_limiter;

	/**
	 * Error handler
	 *
	 * @var ERT_Error_Handler
	 */
	private ERT_Error_Handler $error_handler;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->database = new ERT_Database();
		$this->rate_limiter = new ERT_Rate_Limiter();
		$this->error_handler = ERT_Error_Handler::get_instance();

		// Register AJAX handlers
		add_action('wp_ajax_ert_track_click', array($this, 'track_click'));
		add_action('wp_ajax_nopriv_ert_track_click', array($this, 'track_click'));
		add_action('wp_ajax_ert_clear_qr_cache', array($this, 'clear_qr_cache'));
	}

	/**
	 * Track link click via AJAX
	 *
	 * Enhanced security with nonce verification and rate limiting
	 *
	 * @return void
	 */
	public function track_click(): void {
		// Security: Verify nonce
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ert_track_click')) {
			$this->error_handler->log_security_event(
				'Invalid nonce in track_click AJAX request',
				['post_data' => $_POST],
				'ERT_AJAX_Handler::track_click'
			);
			wp_send_json_error(array('message' => 'Invalid security token'), 403);
			wp_die();
		}

		// Check rate limit
		try {
			$this->rate_limiter->check_rate_limit();
		} catch (Exception $e) {
			$this->error_handler->log_error(
				'Rate limit exceeded in track_click',
				ERT_Error_Handler::LEVEL_WARNING,
				['exception' => $e->getMessage()],
				'ERT_AJAX_Handler::track_click'
			);
			throw $e;
		}

		// Security: Check if required fields exist
		if (!isset($_POST['referral_code']) || !isset($_POST['platform'])) {
			$this->error_handler->log_validation_error(
				'required_fields',
				$_POST,
				'missing referral_code or platform',
				'ERT_AJAX_Handler::track_click'
			);
			wp_send_json_error(array('message' => 'Missing required fields'), 400);
			wp_die();
		}

		// Security: Sanitize and validate inputs
		$referral_code = sanitize_text_field(wp_unslash($_POST['referral_code']));
		$platform = sanitize_text_field(wp_unslash($_POST['platform']));

		// Security: Validate referral code format
		if (!preg_match('/^[a-zA-Z0-9_-]{1,100}$/', $referral_code)) {
			$this->error_handler->log_validation_error(
				'referral_code',
				$referral_code,
				'invalid format (must be alphanumeric, dash, underscore, 1-100 chars)',
				'ERT_AJAX_Handler::track_click'
			);
			wp_send_json_error(array('message' => 'Invalid referral code format'), 400);
			wp_die();
		}

		// Security: Validate platform
		if (!in_array($platform, array('ios', 'android'), true)) {
			$this->error_handler->log_validation_error(
				'platform',
				$platform,
				'must be ios or android',
				'ERT_AJAX_Handler::track_click'
			);
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

	/**
	 * Clear QR code cache via AJAX
	 *
	 * @return void
	 */
	public function clear_qr_cache(): void {
		// Security: Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Unauthorized'), 403);
			wp_die();
		}

		// Security: Verify nonce
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ert_clear_qr_cache')) {
			$this->error_handler->log_security_event(
				'Invalid nonce in clear_qr_cache AJAX request',
				['post_data' => $_POST],
				'ERT_AJAX_Handler::clear_qr_cache'
			);
			wp_send_json_error(array('message' => 'Invalid security token'), 403);
			wp_die();
		}

		try {
			$qr_cache = new ERT_QR_Cache();
			$deleted = $qr_cache->clear_all();

			wp_send_json_success(array(
				'message' => sprintf(__('Successfully cleared %d QR code(s) from cache.', 'easyreferraltracker'), $deleted),
				'deleted' => $deleted
			));
		} catch (Throwable $e) {
			$this->error_handler->handle_exception($e);
			wp_send_json_error(array('message' => 'Failed to clear cache'), 500);
		}

		wp_die();
	}
}
