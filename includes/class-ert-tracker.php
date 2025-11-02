<?php
/**
 * Tracker Class
 *
 * Handles frontend tracking of referrals and script injection
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Tracker
 *
 * Manages referral tracking and frontend scripts
 */
class ERT_Tracker {

	/**
	 * Database handler
	 *
	 * @var ERT_Database
	 */
	private ERT_Database $database;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->database = new ERT_Database();

		// Hook into WordPress
		add_action('init', array($this, 'track_referral'), 1);
		add_action('wp_footer', array($this, 'enqueue_tracker_script'), 999);
	}

	/**
	 * Track referral code from URL
	 *
	 * Privacy-focused: no IP, no sessions
	 *
	 * @return void
	 */
	public function track_referral(): void {
		// Check if referral code exists in URL
		if (isset($_GET['r']) && !empty($_GET['r'])) {
			// Security: Sanitize and validate referral code
			$referral_code = sanitize_text_field(wp_unslash($_GET['r']));

			// Security: Validate referral code format (alphanumeric, dash, underscore only, max 100 chars)
			if (!preg_match('/^[a-zA-Z0-9_-]{1,100}$/', $referral_code)) {
				return;
			}
		} else {
			// No referral code in URL - check if user already has a cookie
			$existing_cookie = isset($_COOKIE['ert_referral']) ? sanitize_text_field($_COOKIE['ert_referral']) : '';

			// If no existing cookie, set default from settings
			if (empty($existing_cookie)) {
				$referral_code = get_option('ert_default_referral', 'direct');
			} else {
				// User already has a referral code, don't override
				return;
			}
		}

		// Check if already tracked for this referral code using cookie
		$tracking_cookie = 'ert_tracked_' . md5($referral_code);

		if (!isset($_COOKIE[$tracking_cookie])) {
			// Track the visit - PRIVACY: No IP, no user agent, no session
			$this->database->record_visit($referral_code, $this->get_request_uri());

			// Set tracking cookie to prevent duplicates
			$cookie_days = absint(get_option('ert_cookie_days', 30));
			$this->set_secure_cookie($tracking_cookie, time(), $cookie_days);
		}

		// Set or update referral cookie
		$cookie_days = absint(get_option('ert_cookie_days', 30));
		$this->set_secure_cookie('ert_referral', $referral_code, $cookie_days);
	}

	/**
	 * Set secure cookie with proper flags
	 *
	 * @param string $name  Cookie name
	 * @param mixed  $value Cookie value
	 * @param int    $days  Days until expiration
	 * @return void
	 */
	private function set_secure_cookie(string $name, mixed $value, int $days): void {
		$cookie_options = array(
			'expires' => time() + ($days * 24 * 60 * 60),
			'path' => '/',
			'domain' => '',
			'secure' => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		);

		setcookie($name, $value, $cookie_options);
	}

	/**
	 * Get sanitized request URI
	 *
	 * @return string
	 */
	private function get_request_uri(): string {
		if (empty($_SERVER['REQUEST_URI'])) {
			return '';
		}
		// Security: Sanitize and limit length
		return substr(esc_url_raw($_SERVER['REQUEST_URI']), 0, 500);
	}

	/**
	 * Enqueue tracker script
	 *
	 * Loads the external JavaScript file for tracking
	 *
	 * @return void
	 */
	public function enqueue_tracker_script(): void {
		// Enqueue the tracker script
		wp_enqueue_script(
			'ert-tracker',
			EASYREFERRALTRACKER_PLUGIN_URL . 'public/js/tracker.js',
			array(),
			EASYREFERRALTRACKER_VERSION,
			true
		);

		// Localize script with settings
		$cookie_days = absint(get_option('ert_cookie_days', 30));
		$provider_token = sanitize_text_field(get_option('ert_provider_token', ''));

		wp_localize_script(
			'ert-tracker',
			'ertSettings',
			array(
				'cookieName' => 'ert_referral',
				'cookieDays' => $cookie_days,
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ert_track_click'),
				'providerToken' => $provider_token,
			)
		);
	}
}
