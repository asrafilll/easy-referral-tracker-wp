<?php
/**
 * Rate Limiter Class
 *
 * Cookie-based rate limiting (GDPR-compliant, no IP tracking)
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Rate_Limiter
 *
 * Handles rate limiting for tracking requests
 */
class ERT_Rate_Limiter {

	/**
	 * Check if request should be rate limited
	 *
	 * Two-layer protection: per-user (cookie-based) and global (site-wide)
	 *
	 * @throws Exception If rate limit is exceeded
	 * @return void
	 */
	public function check_rate_limit(): void {
		// Layer 1: Per-user rate limit (cookie-based)
		$this->check_user_rate_limit();

		// Layer 2: Global site-wide rate limit (prevents DDoS)
		$this->check_global_rate_limit();
	}

	/**
	 * Check per-user rate limit
	 *
	 * Uses cookies to track individual user requests
	 *
	 * @return void
	 */
	private function check_user_rate_limit(): void {
		$user_limit_cookie = 'ert_rate_limit_user';
		$user_limit = absint(get_option('ert_rate_limit_user', 50));

		if (isset($_COOKIE[$user_limit_cookie])) {
			$user_count = absint($_COOKIE[$user_limit_cookie]);
			if ($user_count >= $user_limit) {
				wp_die(
					esc_html__('Rate limit exceeded. Please try again later.', 'easyreferraltracker'),
					429
				);
			}
			$this->set_secure_cookie($user_limit_cookie, $user_count + 1, 0);
		} else {
			$this->set_secure_cookie($user_limit_cookie, 1, 0);
		}
	}

	/**
	 * Check global site-wide rate limit
	 *
	 * Uses transients to track total site requests
	 *
	 * @return void
	 */
	private function check_global_rate_limit(): void {
		$global_key = 'ert_global_rate_limit';
		$global_count = get_transient($global_key);
		$global_limit = absint(get_option('ert_rate_limit_global', 1000));

		if ($global_count === false) {
			set_transient($global_key, 1, HOUR_IN_SECONDS);
		} elseif ($global_count >= $global_limit) {
			wp_die(
				esc_html__('Site rate limit exceeded. Please try again later.', 'easyreferraltracker'),
				429
			);
		} else {
			set_transient($global_key, $global_count + 1, HOUR_IN_SECONDS);
		}
	}

	/**
	 * Set secure cookie with proper flags
	 *
	 * @param string $name  Cookie name
	 * @param mixed  $value Cookie value
	 * @param int    $days  Days until expiration (0 = 1 hour)
	 * @return void
	 */
	private function set_secure_cookie(string $name, mixed $value, int $days): void {
		$expires = ($days === 0) ? time() + HOUR_IN_SECONDS : time() + ($days * 24 * 60 * 60);

		$cookie_options = array(
			'expires' => $expires,
			'path' => '/',
			'domain' => '',
			'secure' => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		);

		setcookie($name, $value, $cookie_options);
	}
}
