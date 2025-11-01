<?php
/**
 * Admin Dashboard Template
 * File: templates/dashboard.php
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
        
        <?php if (empty($top_referrals)): ?>
            <div class="ert-empty-state">
                <p>🔍 <?php esc_html_e('No referral data yet. Share your referral links to get started!', 'easyreferraltracker'); ?></p>
                <p><?php esc_html_e('Example link:', 'easyreferraltracker'); ?> <code><?php echo esc_url(home_url('/?r=yourcode')); ?></code></p>
            </div>
        <?php else: ?>
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
                    <?php foreach ($top_referrals as $referral): ?>
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
        
        <?php if (empty($recent_activity)): ?>
            <div class="ert-empty-state">
                <p><?php esc_html_e('No recent activity', 'easyreferraltracker'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date/Time', 'easyreferraltracker'); ?></th>
                        <th><?php esc_html_e('Referral Code', 'easyreferraltracker'); ?></th>
                        <th><?php esc_html_e('Landing Page', 'easyreferraltracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity as $activity): ?>
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

<style>
.easyreferraltracker-dashboard {
    padding: 20px;
}

.ert-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.ert-stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    gap: 15px;
    align-items: center;
    border-left: 4px solid;
}

.ert-stat-primary { border-color: #2271b1; }
.ert-stat-success { border-color: #00a32a; }
.ert-stat-info { border-color: #72aee6; }
.ert-stat-warning { border-color: #dba617; }
.ert-stat-danger { border-color: #d63638; }
.ert-stat-secondary { border-color: #8c8f94; }

.ert-stat-content h3 {
    margin: 0;
    font-size: 14px;
    color: #666;
    font-weight: normal;
}

.ert-stat-number {
    margin: 5px 0 0 0;
    font-size: 28px;
    font-weight: bold;
    color: #1d2327;
}

.ert-section {
    background: #fff;
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ert-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.ert-empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}

.ert-empty-state p {
    font-size: 16px;
}

.ert-help-section {
    background: #f0f6fc;
    border-left: 4px solid #2271b1;
}

.ert-help-section ol, .ert-help-section ul {
    line-height: 2;
}

.ert-help-section code {
    background: #fff;
    padding: 2px 6px;
    border-radius: 3px;
}

.description {
    color: #666;
    font-style: italic;
}
</style>