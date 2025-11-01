<?php
/**
 * Error Handler Class
 *
 * Centralized error handling and logging system
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Error_Handler
 *
 * Manages error handling, logging, and user notifications
 */
class ERT_Error_Handler {

	/**
	 * Error levels
	 */
	public const LEVEL_DEBUG = 'debug';
	public const LEVEL_INFO = 'info';
	public const LEVEL_WARNING = 'warning';
	public const LEVEL_ERROR = 'error';
	public const LEVEL_CRITICAL = 'critical';

	/**
	 * Singleton instance
	 *
	 * @var ERT_Error_Handler|null
	 */
	private static ?ERT_Error_Handler $instance = null;

	/**
	 * Error log storage
	 *
	 * @var array
	 */
	private array $error_log = [];

	/**
	 * Maximum number of errors to store in memory
	 *
	 * @var int
	 */
	private int $max_errors = 100;

	/**
	 * Get singleton instance
	 *
	 * @return ERT_Error_Handler
	 */
	public static function get_instance(): ERT_Error_Handler {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Register WordPress error handling
		add_action('wp_ajax_ert_clear_error_log', array($this, 'clear_error_log'));
	}

	/**
	 * Log an error with context
	 *
	 * @param string $message Error message
	 * @param string $level   Error level
	 * @param array  $context Additional context data
	 * @param string $source  Source of the error (class, function, etc.)
	 * @return void
	 */
	public function log_error(string $message, string $level = self::LEVEL_ERROR, array $context = [], string $source = ''): void {
		$error = [
			'timestamp' => current_time('timestamp'),
			'level' => $level,
			'message' => $message,
			'source' => $source,
			'context' => $context,
			'user_id' => get_current_user_id(),
			'url' => $this->get_current_url(),
		];

		// Add to memory log
		$this->error_log[] = $error;

		// Trim log if it gets too large
		if (count($this->error_log) > $this->max_errors) {
			array_shift($this->error_log);
		}

		// Log to WordPress debug log if WP_DEBUG is enabled
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log(sprintf(
				'[EasyReferralTracker] [%s] %s in %s - Context: %s',
				strtoupper($level),
				$message,
				$source,
				wp_json_encode($context)
			));
		}

		// Store persistent log for critical errors
		if (in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL], true)) {
			$this->store_persistent_error($error);
		}

		// Trigger action for external logging systems
		do_action('ert_error_logged', $error);
	}

	/**
	 * Log a database error
	 *
	 * @param string $query   SQL query that failed
	 * @param string $error   Database error message
	 * @param string $source  Source of the error
	 * @return void
	 */
	public function log_database_error(string $query, string $error, string $source = ''): void {
		$this->log_error(
			'Database query failed',
			self::LEVEL_ERROR,
			[
				'query' => $query,
				'db_error' => $error,
				'mysql_errno' => mysql_errno(),
			],
			$source
		);
	}

	/**
	 * Log a validation error
	 *
	 * @param string $field   Field that failed validation
	 * @param mixed  $value   Value that was invalid
	 * @param string $rule    Validation rule that failed
	 * @param string $source  Source of the error
	 * @return void
	 */
	public function log_validation_error(string $field, mixed $value, string $rule, string $source = ''): void {
		$this->log_error(
			'Validation failed',
			self::LEVEL_WARNING,
			[
				'field' => $field,
				'value' => $value,
				'rule' => $rule,
			],
			$source
		);
	}

	/**
	 * Log a security event
	 *
	 * @param string $event   Security event description
	 * @param array  $context Additional context
	 * @param string $source  Source of the event
	 * @return void
	 */
	public function log_security_event(string $event, array $context = [], string $source = ''): void {
		$context['user_ip'] = $this->get_sanitized_ip();
		$context['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

		$this->log_error(
			$event,
			self::LEVEL_CRITICAL,
			$context,
			$source
		);
	}

	/**
	 * Get recent errors
	 *
	 * @param int    $limit  Number of errors to return
	 * @param string $level  Filter by error level
	 * @return array
	 */
	public function get_recent_errors(int $limit = 50, string $level = ''): array {
		$errors = $this->error_log;

		// Filter by level if specified
		if (!empty($level)) {
			$errors = array_filter($errors, function($error) use ($level) {
				return $error['level'] === $level;
			});
		}

		// Sort by timestamp (newest first)
		usort($errors, function($a, $b) {
			return $b['timestamp'] - $a['timestamp'];
		});

		return array_slice($errors, 0, $limit);
	}

	/**
	 * Get error statistics
	 *
	 * @return array
	 */
	public function get_error_stats(): array {
		$stats = [
			'total' => count($this->error_log),
			'by_level' => [],
			'by_source' => [],
			'recent_24h' => 0,
		];

		$day_ago = current_time('timestamp') - DAY_IN_SECONDS;

		foreach ($this->error_log as $error) {
			// Count by level
			$level = $error['level'];
			$stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;

			// Count by source
			$source = $error['source'] ?: 'unknown';
			$stats['by_source'][$source] = ($stats['by_source'][$source] ?? 0) + 1;

			// Count recent errors
			if ($error['timestamp'] > $day_ago) {
				$stats['recent_24h']++;
			}
		}

		return $stats;
	}

	/**
	 * Clear error log
	 *
	 * @return void
	 */
	public function clear_error_log(): void {
		// Security check for AJAX request
		if (defined('DOING_AJAX') && DOING_AJAX) {
			if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'] ?? '', 'ert_clear_errors')) {
				wp_send_json_error('Unauthorized', 403);
			}
		}

		$this->error_log = [];
		delete_option('ert_persistent_errors');

		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_send_json_success('Error log cleared');
		}
	}

	/**
	 * Store error persistently for critical errors
	 *
	 * @param array $error Error data
	 * @return void
	 */
	private function store_persistent_error(array $error): void {
		$persistent_errors = get_option('ert_persistent_errors', []);
		$persistent_errors[] = $error;

		// Keep only last 50 persistent errors
		if (count($persistent_errors) > 50) {
			$persistent_errors = array_slice($persistent_errors, -50);
		}

		update_option('ert_persistent_errors', $persistent_errors, false);
	}

	/**
	 * Get current URL safely
	 *
	 * @return string
	 */
	private function get_current_url(): string {
		if (empty($_SERVER['REQUEST_URI'])) {
			return '';
		}
		return esc_url_raw($_SERVER['REQUEST_URI']);
	}

	/**
	 * Get sanitized IP address (first 3 octets only for privacy)
	 *
	 * @return string
	 */
	private function get_sanitized_ip(): string {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if (empty($ip)) {
			return 'unknown';
		}

		// For privacy, only store first 3 octets of IPv4
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$parts = explode('.', $ip);
			return implode('.', array_slice($parts, 0, 3)) . '.xxx';
		}

		// For IPv6, only store first 64 bits
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$parts = explode(':', $ip);
			return implode(':', array_slice($parts, 0, 4)) . '::xxxx';
		}

		return 'invalid';
	}

	/**
	 * Handle uncaught exceptions
	 *
	 * @param Throwable $exception The uncaught exception
	 * @return void
	 */
	public function handle_exception(Throwable $exception): void {
		$this->log_error(
			$exception->getMessage(),
			self::LEVEL_CRITICAL,
			[
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTraceAsString(),
			],
			get_class($exception)
		);
	}

	/**
	 * Create admin notice for errors
	 *
	 * @param string $message Error message
	 * @param string $type    Notice type (error, warning, info, success)
	 * @return void
	 */
	public function add_admin_notice(string $message, string $type = 'error'): void {
		add_action('admin_notices', function() use ($message, $type) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p><strong>EasyReferralTracker:</strong> %s</p></div>',
				esc_attr($type),
				esc_html($message)
			);
		});
	}

	/**
	 * Check if error logging is enabled
	 *
	 * @return bool
	 */
	public function is_logging_enabled(): bool {
		return apply_filters('ert_error_logging_enabled', true);
	}
}