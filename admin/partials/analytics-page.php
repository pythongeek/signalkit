<?php
/**
 * Analytics Page Template
 *
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue analytics styles
wp_enqueue_style(
    'signalkit-analytics',
    SIGNALKIT_PLUGIN_URL . 'admin/css/signalkit-analytics.css',
    array(),
    SIGNALKIT_VERSION
);

// Template partial variables - intentionally unprefixed as these are passed from including context
// Get analytics data
$signalkit_analytics_data = SignalKit_Analytics::get_analytics('all');

// Calculate combined stats (all banners)
$signalkit_combined = array(
    'impressions' => ($signalkit_analytics_data['follow']['impressions'] ?? 0) + ($signalkit_analytics_data['preferred']['impressions'] ?? 0) + ($signalkit_analytics_data['custom']['impressions'] ?? 0),
    'clicks' => ($signalkit_analytics_data['follow']['clicks'] ?? 0) + ($signalkit_analytics_data['preferred']['clicks'] ?? 0) + ($signalkit_analytics_data['custom']['clicks'] ?? 0),
    'dismissals' => ($signalkit_analytics_data['follow']['dismissals'] ?? 0) + ($signalkit_analytics_data['preferred']['dismissals'] ?? 0) + ($signalkit_analytics_data['custom']['dismissals'] ?? 0),
);
$signalkit_combined['ctr'] = $signalkit_combined['impressions'] > 0 ? round(($signalkit_combined['clicks'] / $signalkit_combined['impressions']) * 100, 2) : 0;
?>


<div class="wrap signalkit-analytics-page">
    <!-- Branding Logo Header -->
    <div class="signalkit-analytics-header-logo">
        <?php
        // Include the branding logo component
        $signalkit_logo_file = SIGNALKIT_PLUGIN_DIR . 'admin/partials/branding-logo.php';
        if (file_exists($signalkit_logo_file)) {
            // Pass custom arguments for analytics page (smaller size)
            $signalkit_args = array(
                'size' => 'small',
                'show_text' => true,
                'animate' => true
            );
            include $signalkit_logo_file;
        }
        ?>
    </div>
    
    <h1><?php esc_html_e('SignalKit Analytics', 'signalkit'); ?></h1>

    
    <div class="signalkit-analytics-container">
        
        <div class="signalkit-analytics-card signalkit-combined-analytics">
            <div class="signalkit-card-header">
                <h2><?php esc_html_e('Combined Performance', 'signalkit'); ?></h2>
            </div>
            
            <div class="signalkit-stats-grid">
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon">👁️</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_combined['impressions'])); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Total Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon">👆</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_combined['clicks'])); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Total Clicks', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-icon">📈</div>
                    <div class="signalkit-stat-value"><?php echo esc_html($signalkit_combined['ctr']); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Overall CTR', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-dismiss"></span></div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_combined['dismissals'])); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Total Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
        </div>
        
        <div class="signalkit-analytics-card">
            <div class="signalkit-card-header">
                <h2><span aria-hidden="true">🔔</span> <?php esc_html_e('Follow Banner', 'signalkit'); ?></h2>
                <button class="button button-secondary signalkit-reset-analytics" data-banner-type="follow">
                    <?php esc_html_e('Reset', 'signalkit'); ?>
                </button>
            </div>
            
            <div class="signalkit-stats-grid">
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['follow']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['follow']['clicks'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Clicks', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-value"><?php echo esc_html($signalkit_analytics_data['follow']['ctr'] ?? 0); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('CTR', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['follow']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
            
            <?php if (isset($signalkit_analytics_data['follow']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php
                    printf(
                        /* translators: %s: Last updated date/time */
                        esc_html__('Last updated: %s', 'signalkit'),
                        esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($signalkit_analytics_data['follow']['last_updated'])))
                    );
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="signalkit-analytics-card">
            <div class="signalkit-card-header">
                <h2><span aria-hidden="true">⭐</span> <?php esc_html_e('Preferred Source Banner', 'signalkit'); ?></h2>
                <button class="button button-secondary signalkit-reset-analytics" data-banner-type="preferred">
                    <?php esc_html_e('Reset', 'signalkit'); ?>
                </button>
            </div>
            
            <div class="signalkit-stats-grid">
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['preferred']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['preferred']['clicks'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Clicks', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-value"><?php echo esc_html($signalkit_analytics_data['preferred']['ctr'] ?? 0); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('CTR', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['preferred']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
            
            <?php if (isset($signalkit_analytics_data['preferred']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php
                    printf(
                        /* translators: %s: Last updated date/time */
                        esc_html__('Last updated: %s', 'signalkit'),
                        esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($signalkit_analytics_data['preferred']['last_updated'])))
                    );
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php 
        // Get custom banner stats
        $signalkit_settings = get_option('signalkit_settings', array());
        $signalkit_custom_enabled = !empty($signalkit_settings['custom_enabled']);
        
        // Get submission count from database
        global $wpdb;
        $signalkit_submissions_table = $wpdb->prefix . 'signalkit_submissions';
        $signalkit_submission_count = 0;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema check
        $signalkit_table_check = $wpdb->get_var(
            $wpdb->prepare("SHOW TABLES LIKE %s", $signalkit_submissions_table)
        );
        
        if ($signalkit_table_check === $signalkit_submissions_table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
            $signalkit_submission_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `%s`", $signalkit_submissions_table));
        }
        
        // Calculate custom banner conversion rate
        $signalkit_custom_impressions = $signalkit_analytics_data['custom']['impressions'] ?? 0;
        $signalkit_custom_conversion = $signalkit_custom_impressions > 0 ? round(($signalkit_submission_count / $signalkit_custom_impressions) * 100, 2) : 0;
        ?>
        
        <div class="signalkit-analytics-card">
            <div class="signalkit-card-header">
                <h2><span aria-hidden="true">📧</span> <?php esc_html_e('Custom Banner (Lead Capture)', 'signalkit'); ?></h2>
                <div class="signalkit-card-actions">
                    <?php if ($signalkit_custom_enabled): ?>
                        <span class="signalkit-status-badge signalkit-status-active"><?php esc_html_e('Active', 'signalkit'); ?></span>
                    <?php else: ?>
                        <span class="signalkit-status-badge signalkit-status-inactive"><?php esc_html_e('Inactive', 'signalkit'); ?></span>
                    <?php endif; ?>
                    <button class="button button-secondary signalkit-reset-analytics" data-banner-type="custom">
                        <?php esc_html_e('Reset', 'signalkit'); ?>
                    </button>
                </div>
            </div>
            
            <div class="signalkit-stats-grid">
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon">👁️</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['custom']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-success">
                    <div class="signalkit-stat-icon">📨</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_submission_count)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Submissions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-chart-bar"></span></div>
                    <div class="signalkit-stat-value"><?php echo esc_html($signalkit_custom_conversion); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Conversion Rate', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-dismiss"></span></div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($signalkit_analytics_data['custom']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
            
            <?php if (isset($signalkit_analytics_data['custom']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php
                    printf(
                        /* translators: %s: Last updated date/time */
                        esc_html__('Last updated: %s', 'signalkit'),
                        esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($signalkit_analytics_data['custom']['last_updated'])))
                    );
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if ($signalkit_submission_count > 0): ?>
                
                <?php 
                // Fetch recent leads
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
                $signalkit_recent_leads = $wpdb->get_results($wpdb->prepare("SELECT id, email, name, submitted_at FROM `%s` ORDER BY submitted_at DESC LIMIT 5", $signalkit_submissions_table));
                if ($signalkit_recent_leads): 
                ?>
                <div class="signalkit-recent-leads">
                    <h3><?php esc_html_e('Recent Leads', 'signalkit'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Email', 'signalkit'); ?></th>
                                <th><?php esc_html_e('Name', 'signalkit'); ?></th>
                                <th><?php esc_html_e('Date', 'signalkit'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($signalkit_recent_leads as $signalkit_lead): ?>
                                <tr>
                                    <td><?php echo esc_html($signalkit_lead->email); ?></td>
                                    <td><?php echo esc_html($signalkit_lead->name ?: '-'); ?></td>
                                    <td><?php echo esc_html(wp_date(get_option('date_format'), strtotime($signalkit_lead->submitted_at))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="signalkit-analytics-cta">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=signalkit-submissions')); ?>" class="button button-primary">
                        <?php esc_html_e('View All Submissions', 'signalkit'); ?> →
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
    
    <div style="margin-top: 20px;">
        <button class="button button-secondary signalkit-reset-analytics" data-banner-type="all">
            <?php esc_html_e('Reset All Analytics', 'signalkit'); ?>
        </button>
        <p class="description"><?php esc_html_e('This will reset analytics for all banners (does not delete submissions).', 'signalkit'); ?></p>
    </div>
</div>


