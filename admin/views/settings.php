<?php
/**
 * Settings View Template
 *
 * Admin settings page for general settings and app store configuration
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap easyreferraltracker-settings">
	<h1><?php esc_html_e('EasyReferralTracker Settings', 'easyreferraltracker'); ?></h1>

	<?php settings_errors('ert_messages'); ?>

	<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">

		<!-- Left Column: Settings Forms -->
		<div>
			<!-- General Settings -->
			<div class="ert-settings-section">
				<h2><?php esc_html_e('General Settings', 'easyreferraltracker'); ?></h2>

				<form method="post" action="options.php">
					<?php settings_fields('ert_general_settings'); ?>

					<table class="form-table">
						<!-- Cookie Duration -->
						<tr>
							<th scope="row">
								<label for="ert_cookie_days">
									<?php esc_html_e('Cookie Duration (Days)', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="number"
									   id="ert_cookie_days"
									   name="ert_cookie_days"
									   value="<?php echo esc_attr(get_option('ert_cookie_days', 30)); ?>"
									   min="1"
									   max="365"
									   class="regular-text">
								<p class="description">
									<?php esc_html_e('How long to remember the referral code (1-365 days). Default: 30 days.', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Per-User Rate Limit -->
						<tr>
							<th scope="row">
								<label for="ert_rate_limit_user">
									<?php esc_html_e('Per-User Rate Limit', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="number"
									   id="ert_rate_limit_user"
									   name="ert_rate_limit_user"
									   value="<?php echo esc_attr(get_option('ert_rate_limit_user', 50)); ?>"
									   min="10"
									   max="500"
									   class="regular-text">
								<span><?php esc_html_e('clicks per hour', 'easyreferraltracker'); ?></span>
								<p class="description">
									<?php esc_html_e('Maximum tracking requests per user per hour (cookie-based). Default: 50.', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Global Rate Limit -->
						<tr>
							<th scope="row">
								<label for="ert_rate_limit_global">
									<?php esc_html_e('Global Rate Limit', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="number"
									   id="ert_rate_limit_global"
									   name="ert_rate_limit_global"
									   value="<?php echo esc_attr(get_option('ert_rate_limit_global', 1000)); ?>"
									   min="100"
									   max="10000"
									   class="regular-text">
								<span><?php esc_html_e('clicks per hour (site-wide)', 'easyreferraltracker'); ?></span>
								<p class="description">
									<?php esc_html_e('Maximum tracking requests for entire site per hour. Protects against DDoS. Default: 1000.', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
			</div>

			<!-- App Store Configuration -->
			<div class="ert-settings-section">
				<h2><?php esc_html_e('App Store Configuration', 'easyreferraltracker'); ?></h2>

				<form method="post" action="options.php">
					<?php settings_fields('ert_appstore_settings'); ?>

					<table class="form-table">
						<!-- iOS App ID -->
						<tr>
							<th scope="row">
								<label for="ert_ios_app_id">
									<?php esc_html_e('iOS App ID', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="text"
									   id="ert_ios_app_id"
									   name="ert_ios_app_id"
									   value="<?php echo esc_attr(get_option('ert_ios_app_id', '')); ?>"
									   class="regular-text"
									   placeholder="123456789">
								<p class="description">
									<?php esc_html_e('Your App Store app ID (numbers only). Example: 123456789', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Provider Token -->
						<tr>
							<th scope="row">
								<label for="ert_provider_token">
									<?php esc_html_e('Apple Provider Token (pt)', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="text"
									   id="ert_provider_token"
									   name="ert_provider_token"
									   value="<?php echo esc_attr(get_option('ert_provider_token', '')); ?>"
									   class="regular-text"
									   placeholder="12345">
								<p class="description">
									<?php esc_html_e('Get this from App Store Connect > App Analytics > Campaigns. Required for iOS analytics.', 'easyreferraltracker'); ?>
									<a href="https://developer.apple.com/help/app-store-connect/view-app-analytics/manage-campaigns/" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e('Learn more', 'easyreferraltracker'); ?> ↗
									</a>
								</p>
							</td>
						</tr>

						<!-- Android Package Name -->
						<tr>
							<th scope="row">
								<label for="ert_android_package">
									<?php esc_html_e('Android Package Name', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="text"
									   id="ert_android_package"
									   name="ert_android_package"
									   value="<?php echo esc_attr(get_option('ert_android_package', '')); ?>"
									   class="regular-text"
									   placeholder="com.yourapp.android">
								<p class="description">
									<?php esc_html_e('Your Google Play package name. Example: com.yourapp.android', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Default Referral Code -->
						<tr>
							<th scope="row">
								<label for="ert_default_referral">
									<?php esc_html_e('Default Referral Code', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="text"
									   id="ert_default_referral"
									   name="ert_default_referral"
									   value="<?php echo esc_attr(get_option('ert_default_referral', 'direct')); ?>"
									   class="regular-text"
									   placeholder="direct">
								<p class="description">
									<?php esc_html_e('Default referral code for visitors without a specific referral. Common values: direct, organic, none. Default: direct', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
			</div>
		</div>

		<!-- Right Column: Help & Info -->
		<div>
			<!-- GDPR Compliance Notice -->
			<div class="ert-info-box ert-privacy-box">
				<h3>🔒 <?php esc_html_e('Privacy & GDPR', 'easyreferraltracker'); ?></h3>
				<p><?php esc_html_e('EasyReferralTracker is fully GDPR compliant:', 'easyreferraltracker'); ?></p>
				<ul>
					<li>❌ <?php esc_html_e('No IP addresses collected', 'easyreferraltracker'); ?></li>
					<li>❌ <?php esc_html_e('No user agents tracked', 'easyreferraltracker'); ?></li>
					<li>❌ <?php esc_html_e('No personal data stored', 'easyreferraltracker'); ?></li>
					<li>✅ <?php esc_html_e('Cookie-based tracking only', 'easyreferraltracker'); ?></li>
					<li>✅ <?php esc_html_e('Minimal data collection', 'easyreferraltracker'); ?></li>
				</ul>
				<p><strong><?php esc_html_e('What we collect:', 'easyreferraltracker'); ?></strong></p>
				<ul>
					<li><?php esc_html_e('Referral code', 'easyreferraltracker'); ?></li>
					<li><?php esc_html_e('Landing page URL', 'easyreferraltracker'); ?></li>
					<li><?php esc_html_e('Visit timestamp', 'easyreferraltracker'); ?></li>
				</ul>
			</div>


			<!-- Testing Instructions -->
			<div class="ert-info-box">
				<h3>🧪 <?php esc_html_e('Testing Your Setup', 'easyreferraltracker'); ?></h3>
				<ol>
					<li><?php esc_html_e('Save your settings above', 'easyreferraltracker'); ?></li>
					<li><?php esc_html_e('Visit:', 'easyreferraltracker'); ?>
						<br><code><?php echo esc_url(home_url('/?r=test123')); ?></code>
					</li>
					<li><?php esc_html_e('Check your app download links', 'easyreferraltracker'); ?></li>
					<li><?php esc_html_e('View the Dashboard to see tracked data', 'easyreferraltracker'); ?></li>
				</ol>
			</div>

		</div>
	</div>

	<!-- App Store Link Examples -->
	<div class="ert-help-box">
		<h2><?php esc_html_e('App Store Link Examples', 'easyreferraltracker'); ?></h2>

		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
			<div>
				<h3><?php esc_html_e('iOS (Apple App Store)', 'easyreferraltracker'); ?></h3>
				<p><?php esc_html_e('Your links should look like:', 'easyreferraltracker'); ?></p>
				<code>https://apps.apple.com/app/yourapp/id<?php echo esc_html(get_option('ert_ios_app_id', 'YOUR_APP_ID')); ?></code>
				<p><?php esc_html_e('EasyReferralTracker will automatically add:', 'easyreferraltracker'); ?></p>
				<ul>
					<li><code>ct=referral_code</code> - <?php esc_html_e('Campaign token', 'easyreferraltracker'); ?></li>
					<li><code>mt=8</code> - <?php esc_html_e('Media type', 'easyreferraltracker'); ?></li>
					<?php if (get_option('ert_provider_token')) : ?>
					<li><code>pt=<?php echo esc_html(get_option('ert_provider_token')); ?></code> - <?php esc_html_e('Provider token', 'easyreferraltracker'); ?></li>
					<?php endif; ?>
				</ul>
			</div>

			<div>
				<h3><?php esc_html_e('Android (Google Play)', 'easyreferraltracker'); ?></h3>
				<p><?php esc_html_e('Your links should look like:', 'easyreferraltracker'); ?></p>
				<code>https://play.google.com/store/apps/details?id=<?php echo esc_html(get_option('ert_android_package', 'com.your.app')); ?></code>
				<p><?php esc_html_e('EasyReferralTracker will automatically add:', 'easyreferraltracker'); ?></p>
				<ul>
					<li><code>referrer=utm_source=referral&amp;utm_medium=website&amp;utm_campaign=app&amp;utm_content=referral_code</code></li>
				</ul>
			</div>
		</div>

		<!-- QR Cache Management Section -->
		<div class="ert-settings-section" style="margin-top: 30px;">
			<h2><?php esc_html_e('QR Code Cache Management', 'easyreferraltracker'); ?></h2>
			
			<?php
			$qr_cache = new ERT_QR_Cache();
			$stats = $qr_cache->get_stats();
			?>
			
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e('Cache Statistics', 'easyreferraltracker'); ?></th>
					<td>
						<p><strong><?php esc_html_e('Cached QR Codes:', 'easyreferraltracker'); ?></strong> <?php echo esc_html($stats['count']); ?></p>
						<p><strong><?php esc_html_e('Total Size:', 'easyreferraltracker'); ?></strong> <?php echo esc_html($stats['size_formatted']); ?></p>
						<p><strong><?php esc_html_e('Directory:', 'easyreferraltracker'); ?></strong> <code><?php echo esc_html($stats['directory']); ?></code></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Clear Cache', 'easyreferraltracker'); ?></th>
					<td>
						<button type="button" id="ert-clear-qr-cache" class="button button-secondary">
							<?php esc_html_e('Clear All QR Codes', 'easyreferraltracker'); ?>
						</button>
						<p class="description">
							<?php esc_html_e('This will delete all cached QR codes. They will be regenerated on next visit.', 'easyreferraltracker'); ?>
						</p>
						<div id="ert-clear-cache-result" style="margin-top: 10px;"></div>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#ert-clear-qr-cache').on('click', function() {
		var button = $(this);
		var resultDiv = $('#ert-clear-cache-result');
		
		button.prop('disabled', true).text('<?php esc_attr_e('Clearing...', 'easyreferraltracker'); ?>');
		resultDiv.html('');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ert_clear_qr_cache',
				nonce: '<?php echo wp_create_nonce('ert_clear_qr_cache'); ?>'
			},
			success: function(response) {
				if (response.success) {
					resultDiv.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					resultDiv.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
					button.prop('disabled', false).text('<?php esc_attr_e('Clear All QR Codes', 'easyreferraltracker'); ?>');
				}
			},
			error: function() {
				resultDiv.html('<div class="notice notice-error inline"><p><?php esc_html_e('An error occurred. Please try again.', 'easyreferraltracker'); ?></p></div>');
				button.prop('disabled', false).text('<?php esc_attr_e('Clear All QR Codes', 'easyreferraltracker'); ?>');
			}
		});
	});
});
</script>
