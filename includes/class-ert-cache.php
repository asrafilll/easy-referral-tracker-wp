<?php
/**
 * Cache Handler Class
 *
 * Handles query caching and performance optimization
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ERT_Cache
 *
 * Manages caching for database queries and analytics
 */
class ERT_Cache {

	/**
	 * Cache group prefix
	 */
	private const CACHE_GROUP = 'easyreferraltracker';

	/**
	 * Default cache expiration (1 hour)
	 */
	private const DEFAULT_EXPIRATION = 3600;

	/**
	 * Cache statistics
	 *
	 * @var array
	 */
	private static array $stats = [
		'hits' => 0,
		'misses' => 0,
		'sets' => 0,
		'deletes' => 0,
	];

	/**
	 * Get cached data
	 *
	 * @param string $key Cache key
	 * @param string $group Cache group (optional)
	 * @return mixed|false Returns cached data or false if not found
	 */
	public static function get(string $key, string $group = ''): mixed {
		$cache_key = self::build_cache_key($key);
		$cache_group = $group ?: self::CACHE_GROUP;

		$cached_data = wp_cache_get($cache_key, $cache_group);

		if (false !== $cached_data) {
			self::$stats['hits']++;
			return $cached_data;
		}

		self::$stats['misses']++;
		return false;
	}

	/**
	 * Set cached data
	 *
	 * @param string $key Cache key
	 * @param mixed  $data Data to cache
	 * @param int    $expiration Cache expiration in seconds
	 * @param string $group Cache group (optional)
	 * @return bool True on success
	 */
	public static function set(string $key, mixed $data, int $expiration = self::DEFAULT_EXPIRATION, string $group = ''): bool {
		$cache_key = self::build_cache_key($key);
		$cache_group = $group ?: self::CACHE_GROUP;

		$result = wp_cache_set($cache_key, $data, $cache_group, $expiration);

		if ($result) {
			self::$stats['sets']++;
		}

		return $result;
	}

	/**
	 * Delete cached data
	 *
	 * @param string $key Cache key
	 * @param string $group Cache group (optional)
	 * @return bool True on success
	 */
	public static function delete(string $key, string $group = ''): bool {
		$cache_key = self::build_cache_key($key);
		$cache_group = $group ?: self::CACHE_GROUP;

		$result = wp_cache_delete($cache_key, $cache_group);

		if ($result) {
			self::$stats['deletes']++;
		}

		return $result;
	}

	/**
	 * Flush all plugin cache
	 *
	 * @return bool True on success
	 */
	public static function flush(): bool {
		// WordPress doesn't have a direct way to flush by group
		// So we'll use a cache version approach
		$cache_version = time();
		return wp_cache_set('cache_version', $cache_version, self::CACHE_GROUP, 0);
	}

	/**
	 * Get or set cached data with callback
	 *
	 * @param string   $key Cache key
	 * @param callable $callback Function to call if cache miss
	 * @param int      $expiration Cache expiration in seconds
	 * @param string   $group Cache group (optional)
	 * @return mixed Cached or computed data
	 */
	public static function remember(string $key, callable $callback, int $expiration = self::DEFAULT_EXPIRATION, string $group = ''): mixed {
		$cached_data = self::get($key, $group);

		if (false !== $cached_data) {
			return $cached_data;
		}

		$fresh_data = call_user_func($callback);
		
		if (null !== $fresh_data) {
			self::set($key, $fresh_data, $expiration, $group);
		}

		return $fresh_data;
	}

	/**
	 * Get cache statistics
	 *
	 * @return array Cache statistics
	 */
	public static function get_stats(): array {
		$total_requests = self::$stats['hits'] + self::$stats['misses'];
		$hit_rate = $total_requests > 0 ? (self::$stats['hits'] / $total_requests) * 100 : 0;

		return array_merge(self::$stats, [
			'total_requests' => $total_requests,
			'hit_rate' => round($hit_rate, 2),
		]);
	}

	/**
	 * Reset cache statistics
	 *
	 * @return void
	 */
	public static function reset_stats(): void {
		self::$stats = [
			'hits' => 0,
			'misses' => 0,
			'sets' => 0,
			'deletes' => 0,
		];
	}

	/**
	 * Build cache key with versioning
	 *
	 * @param string $key Original cache key
	 * @return string Versioned cache key
	 */
	private static function build_cache_key(string $key): string {
		$cache_version = wp_cache_get('cache_version', self::CACHE_GROUP);
		
		if (false === $cache_version) {
			$cache_version = time();
			wp_cache_set('cache_version', $cache_version, self::CACHE_GROUP, 0);
		}

		return sprintf('%s_%s_%s', self::CACHE_GROUP, $cache_version, $key);
	}

	/**
	 * Cache dashboard analytics data
	 *
	 * @param string $key Analytics cache key
	 * @param mixed  $data Analytics data
	 * @param int    $expiration Cache expiration (default: 5 minutes for analytics)
	 * @return bool True on success
	 */
	public static function cache_analytics(string $key, mixed $data, int $expiration = 300): bool {
		return self::set("analytics_{$key}", $data, $expiration);
	}

	/**
	 * Get cached analytics data
	 *
	 * @param string $key Analytics cache key
	 * @return mixed|false Cached data or false if not found
	 */
	public static function get_analytics(string $key): mixed {
		return self::get("analytics_{$key}");
	}

	/**
	 * Clear analytics cache
	 *
	 * @return void
	 */
	public static function clear_analytics(): void {
		$analytics_keys = [
			'total_visits',
			'unique_referrals',
			'total_clicks',
			'today_visits',
			'top_referrals',
			'recent_activity',
			'dashboard_stats',
		];

		foreach ($analytics_keys as $key) {
			self::delete("analytics_{$key}");
		}
	}

	/**
	 * Check if caching is enabled
	 *
	 * @return bool True if caching is enabled
	 */
	public static function is_enabled(): bool {
		// Check if object cache is available
		return wp_using_ext_object_cache() || function_exists('wp_cache_get');
	}

	/**
	 * Warm up cache with essential data
	 *
	 * @return void
	 */
	public static function warm_up(): void {
		if (!self::is_enabled()) {
			return;
		}

		// This would be called by a cron job or after cache flush
		// to pre-populate frequently accessed data
		
		// Example: Pre-cache dashboard stats
		do_action('ert_cache_warm_up');
	}

	/**
	 * Get memory usage information
	 *
	 * @return array Memory usage stats
	 */
	public static function get_memory_info(): array {
		return [
			'php_memory_limit' => ini_get('memory_limit'),
			'php_memory_usage' => memory_get_usage(true),
			'php_memory_peak' => memory_get_peak_usage(true),
			'wp_object_cache' => wp_using_ext_object_cache() ? 'enabled' : 'disabled',
		];
	}
}