/**
 * Frontend Tracking Script
 *
 * Handles referral code capture and App Store link modification
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

(function() {
	'use strict';

	// Settings from localized script
	const COOKIE_NAME = ertSettings.cookieName;
	const COOKIE_DAYS = ertSettings.cookieDays;
	const AJAX_URL = ertSettings.ajaxUrl;
	const NONCE = ertSettings.nonce;
	const PROVIDER_TOKEN = ertSettings.providerToken;

	/**
	 * Get cookie value by name
	 *
	 * @param {string} name Cookie name
	 * @return {string|null} Cookie value or null
	 */
	function getCookie(name) {
		const value = '; ' + document.cookie;
		const parts = value.split('; ' + name + '=');
		if (parts.length === 2) {
			return parts.pop().split(';').shift();
		}
		return null;
	}

	/**
	 * Set cookie with proper security flags
	 *
	 * @param {string} name  Cookie name
	 * @param {string} value Cookie value
	 * @param {number} days  Days until expiration
	 * @return {void}
	 */
	function setCookie(name, value, days) {
		const date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		const expires = "expires=" + date.toUTCString();
		const secure = window.location.protocol === 'https:' ? '; Secure' : '';
		document.cookie = name + "=" + encodeURIComponent(value) + "; " + expires + "; path=/; SameSite=Lax" + secure;
	}

	/**
	 * Track link click via AJAX
	 *
	 * @param {string} platform      Platform (ios or android)
	 * @param {string} referralCode  Referral code
	 * @return {void}
	 */
	function trackLinkClick(platform, referralCode) {
		// Security: Validate inputs
		if (!platform || !referralCode) return;
		if (!['ios', 'android'].includes(platform)) return;

		// Send AJAX request
		const formData = new FormData();
		formData.append('action', 'ert_track_click');
		formData.append('nonce', NONCE);
		formData.append('referral_code', referralCode);
		formData.append('platform', platform);

		fetch(AJAX_URL, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		}).catch(function(error) {
			// Silent error handling
		});
	}

	/**
	 * Capture referral code from URL
	 *
	 * @return {string|null} Referral code or null
	 */
	function captureReferralCode() {
		const urlParams = new URLSearchParams(window.location.search);
		const referralCode = urlParams.get('r');

		if (referralCode) {
			// Security: Validate referral code format
			if (/^[a-zA-Z0-9_-]{1,100}$/.test(referralCode)) {
				setCookie(COOKIE_NAME, referralCode, COOKIE_DAYS);
				return referralCode;
			}
		}

		return getCookie(COOKIE_NAME);
	}

	/**
	 * Update App Store links with referral code
	 *
	 * @return {void}
	 */
	function updateAppStoreLinks() {
		const referralCode = getCookie(COOKIE_NAME);

		if (!referralCode) {
			return;
		}

		// Security: Validate referral code before using
		if (!/^[a-zA-Z0-9_-]{1,100}$/.test(referralCode)) {
			return;
		}

		// Update iOS links
		const iosLinks = document.querySelectorAll('a[href*="apps.apple.com"]');

		iosLinks.forEach(function(link) {
			try {
				const url = new URL(link.href);
				url.searchParams.set('ct', referralCode);
				url.searchParams.set('mt', '8');
				if (PROVIDER_TOKEN) {
					url.searchParams.set('pt', PROVIDER_TOKEN);
				}
				link.href = url.toString();

				// Add click tracking
				link.addEventListener('click', function(e) {
					trackLinkClick('ios', referralCode);
				}, { once: true });

			} catch (e) {
				// Silent error handling
			}
		});

		// Update Android links
		const androidLinks = document.querySelectorAll('a[href*="play.google.com"]');

		androidLinks.forEach(function(link) {
			try {
				const url = new URL(link.href);
				const referrerValue = 'utm_source=referral&utm_medium=website&utm_campaign=app&utm_content=' + encodeURIComponent(referralCode);
				url.searchParams.set('referrer', referrerValue);
				link.href = url.toString();

				// Add click tracking
				link.addEventListener('click', function(e) {
					trackLinkClick('android', referralCode);
				}, { once: true });

			} catch (e) {
				// Silent error handling
			}
		});
	}

	/**
	 * Initialize tracker
	 *
	 * @return {void}
	 */
	function init() {
		captureReferralCode();
		updateAppStoreLinks();
	}

	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
