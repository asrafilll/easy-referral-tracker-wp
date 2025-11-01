<?php
/**
 * Dashboard View Template
 *
 * Displays analytics dashboard with statistics and recent activity
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap easyreferraltracker-dashboard">
	<h1><?php esc_html_e('EasyReferralTracker Dashboard', 'easyreferraltracker'); ?></h1>
	<p class="description"><?php esc_html_e('Privacy-focused referral tracking with no personal data collection', 'easyreferraltracker'); ?></p>

	<!-- Statistics Cards -->
	<div class="ert-stats-grid">
		<div class="ert-stat-card ert-stat-primary">
			<div class="ert-stat-content">
				<h3><?php esc_html_e('Total Visits', 'easyreferraltracker'); ?></h3>
				<p class="ert-stat-number"><?php echo number_format(absint($total_visits)); ?></p>
			</div>
		</div>

		<div class="ert-stat-card ert-stat-success">
			<div class="ert-stat-content">
				<h3><?php esc_html_e('Unique Referrals', 'easyreferraltracker'); ?></h3>
				<p class="ert-stat-number"><?php echo number_format(absint($unique_referrals)); ?></p>
			</div>
		</div>

		<div class="ert-stat-card ert-stat-info">
			<div class="ert-stat-content">
				<h3><?php esc_html_e('Total Clicks', 'easyreferraltracker'); ?></h3>
				<p class="ert-stat-number"><?php echo number_format(absint($total_clicks)); ?></p>
			</div>
		</div>

		<div class="ert-stat-card ert-stat-danger">
			<div class="ert-stat-content">
				<h3><?php esc_html_e("Today's Visits", 'easyreferraltracker'); ?></h3>
				<p class="ert-stat-number"><?php echo number_format(absint($today_visits)); ?></p>
			</div>
		</div>

		<div class="ert-stat-card ert-stat-secondary">
			<div class="ert-stat-content">
				<h3><?php esc_html_e('Click Rate', 'easyreferraltracker'); ?></h3>
				<p class="ert-stat-number">
					<?php
					$click_rate = $total_visits > 0 ? ($total_clicks / $total_visits) * 100 : 0;
					echo number_format($click_rate, 1) . '%';
					?>
				</p>
			</div>
		</div>
	</div>

	<!-- Top Performing Referrals -->
	<div class="ert-section">
		<h2><?php esc_html_e('Top Performing Referrals', 'easyreferraltracker'); ?></h2>

		<?php if (empty($top_referrals)) : ?>
			<div class="ert-empty-state">
				<p>🔍 <?php esc_html_e('No referral data yet. Share your referral links to get started!', 'easyreferraltracker'); ?></p>
				<p><?php esc_html_e('Example link:', 'easyreferraltracker'); ?> <code><?php echo esc_url(home_url('/?r=yourcode')); ?></code></p>
			</div>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Referral Code', 'easyreferraltracker'); ?></th>
						<th><?php esc_html_e('Visits', 'easyreferraltracker'); ?></th>
						<th><?php esc_html_e('Clicks', 'easyreferraltracker'); ?></th>
						<th><?php esc_html_e('Click Rate', 'easyreferraltracker'); ?></th>
						<th><?php esc_html_e('Last Used', 'easyreferraltracker'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($top_referrals as $referral) : ?>
					<tr>
						<td><strong><?php echo esc_html($referral['referral_code']); ?></strong></td>
						<td><?php echo number_format(absint($referral['visits'])); ?></td>
						<td><?php echo number_format(absint($referral['clicks'])); ?></td>
						<td>
							<?php
							$rate = $referral['visits'] > 0 ? ($referral['clicks'] / $referral['visits']) * 100 : 0;
							echo number_format($rate, 1) . '%';
							?>
						</td>
						<td><?php echo esc_html(date_i18n('M d, Y H:i', strtotime($referral['last_used']))); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<!-- Recent Activity -->
	<div class="ert-section">
		<h2><?php esc_html_e('Recent Activity', 'easyreferraltracker'); ?></h2>
		<p class="description">
			<?php esc_html_e('Last 50 visits. Privacy-focused: No IP addresses or user agents collected.', 'easyreferraltracker'); ?>
		</p>

		<?php if (empty($recent_activity)) : ?>
			<div class="ert-empty-state">
				<p><?php esc_html_e('No recent activity', 'easyreferraltracker'); ?></p>
			</div>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Date/Time', 'easyreferraltracker'); ?></th>
						<th><?php esc_html_e('Referral Code', 'easyreferraltracker'); ?></th>
						<th><?php esc_html_e('Landing Page', 'easyreferraltracker'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($recent_activity as $activity) : ?>
					<tr>
						<td><?php echo esc_html(date_i18n('M d, Y H:i:s', strtotime($activity['created_at']))); ?></td>
						<td><strong><?php echo esc_html($activity['referral_code']); ?></strong></td>
						<td><code><?php echo esc_html($activity['landing_page']); ?></code></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<!-- How to Use -->
	<div class="ert-section ert-help-section">
		<h2>📚 <?php esc_html_e('How to Use EasyReferralTracker', 'easyreferraltracker'); ?></h2>
		<ol>
			<li><?php esc_html_e('Create referral links in this format:', 'easyreferraltracker'); ?> <code><?php echo esc_url(home_url('/?r=YOURCODE')); ?></code></li>
			<li><?php esc_html_e('Share these links with your users, affiliates, or marketing campaigns', 'easyreferraltracker'); ?></li>
			<li><?php esc_html_e('EasyReferralTracker automatically tracks visits and modifies your App Store links', 'easyreferraltracker'); ?></li>
			<li><?php esc_html_e('View analytics and track performance from this dashboard', 'easyreferraltracker'); ?></li>
			<li><?php esc_html_e('Generate dynamic QR codes from the QR Generator page', 'easyreferraltracker'); ?></li>
		</ol>

		<p><strong><?php esc_html_e('App Store Links:', 'easyreferraltracker'); ?></strong> <?php esc_html_e('EasyReferralTracker automatically adds referral codes to any links containing:', 'easyreferraltracker'); ?></p>
		<ul>
			<li><code>apps.apple.com</code> - <?php esc_html_e('iOS App Store', 'easyreferraltracker'); ?></li>
			<li><code>play.google.com</code> - <?php esc_html_e('Google Play Store', 'easyreferraltracker'); ?></li>
		</ul>

		<p><strong>🔒 <?php esc_html_e('Privacy-First:', 'easyreferraltracker'); ?></strong> <?php esc_html_e('EasyReferralTracker does not collect IP addresses, user agents, or any personal information. Fully GDPR compliant.', 'easyreferraltracker'); ?></p>
	</div>
</div>
