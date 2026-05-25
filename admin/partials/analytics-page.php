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
$analytics = SignalKit_Analytics::get_analytics('all');

// Calculate combined stats (all banners)
$combined = array(
    'impressions' => ($analytics['follow']['impressions'] ?? 0) + ($analytics['preferred']['impressions'] ?? 0) + ($analytics['custom']['impressions'] ?? 0),
    'clicks' => ($analytics['follow']['clicks'] ?? 0) + ($analytics['preferred']['clicks'] ?? 0) + ($analytics['custom']['clicks'] ?? 0),
    'dismissals' => ($analytics['follow']['dismissals'] ?? 0) + ($analytics['preferred']['dismissals'] ?? 0) + ($analytics['custom']['dismissals'] ?? 0),
);
$combined['ctr'] = $combined['impressions'] > 0 ? round(($combined['clicks'] / $combined['impressions']) * 100, 2) : 0;
?>


<div class="wrap signalkit-analytics-page">
    <!-- Branding Logo Header -->
    <div class="signalkit-analytics-header-logo">
        <?php
        // Include the branding logo component
        $logo_file = SIGNALKIT_PLUGIN_DIR . 'admin/partials/branding-logo.php';
        if (file_exists($logo_file)) {
            // Pass custom arguments for analytics page (smaller size)
            $args = array(
                'size' => 'small',
                'show_text' => true,
                'animate' => true
            );
            include $logo_file;
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
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($combined['impressions'])); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Total Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon">👆</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($combined['clicks'])); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Total Clicks', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-icon">📈</div>
                    <div class="signalkit-stat-value"><?php echo esc_html($combined['ctr']); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Overall CTR', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-dismiss"></span></div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($combined['dismissals'])); ?></div>
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
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['follow']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['follow']['clicks'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Clicks', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-value"><?php echo esc_html($analytics['follow']['ctr'] ?? 0); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('CTR', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['follow']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
            
            <?php if (isset($analytics['follow']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php
                    printf(
                        /* translators: %s: Last updated date/time */
                        esc_html__('Last updated: %s', 'signalkit'),
                        esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($analytics['follow']['last_updated'])))
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
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['preferred']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['preferred']['clicks'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Clicks', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-value"><?php echo esc_html($analytics['preferred']['ctr'] ?? 0); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('CTR', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['preferred']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
            
            <?php if (isset($analytics['preferred']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php
                    printf(
                        /* translators: %s: Last updated date/time */
                        esc_html__('Last updated: %s', 'signalkit'),
                        esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($analytics['preferred']['last_updated'])))
                    );
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php 
        // Get custom banner stats
        $settings = get_option('signalkit_settings', array());
        $custom_enabled = !empty($settings['custom_enabled']);
        
        // Get submission count from database
        global $wpdb;
        $submissions_table = $wpdb->prefix . 'signalkit_submissions';
        $submission_count = 0;
        
        $table_check = $wpdb->get_var(
            $wpdb->prepare("SHOW TABLES LIKE %s", $submissions_table)
        );
        
        if ($table_check === $submissions_table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
            $submission_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM `" . esc_sql($submissions_table) . "`");
        }
        
        // Calculate custom banner conversion rate
        $custom_impressions = $analytics['custom']['impressions'] ?? 0;
        $custom_conversion = $custom_impressions > 0 ? round(($submission_count / $custom_impressions) * 100, 2) : 0;
        ?>
        
        <div class="signalkit-analytics-card">
            <div class="signalkit-card-header">
                <h2><span aria-hidden="true">📧</span> <?php esc_html_e('Custom Banner (Lead Capture)', 'signalkit'); ?></h2>
                <div class="signalkit-card-actions">
                    <?php if ($custom_enabled): ?>
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
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['custom']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-success">
                    <div class="signalkit-stat-icon">📨</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($submission_count)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Submissions', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-chart-bar"></span></div>
                    <div class="signalkit-stat-value"><?php echo esc_html($custom_conversion); ?>%</div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Conversion Rate', 'signalkit'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-dismiss"></span></div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['custom']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
            
            <?php if (isset($analytics['custom']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php
                    printf(
                        /* translators: %s: Last updated date/time */
                        esc_html__('Last updated: %s', 'signalkit'),
                        esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($analytics['custom']['last_updated'])))
                    );
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if ($submission_count > 0): ?>
                
                <?php 
                // Fetch recent leads
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
                $recent_leads = $wpdb->get_results("SELECT id, email, name, submitted_at FROM `" . esc_sql($submissions_table) . "` ORDER BY submitted_at DESC LIMIT 5");
                if ($recent_leads): 
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
                            <?php foreach ($recent_leads as $lead): ?>
                                <tr>
                                    <td><?php echo esc_html($lead->email); ?></td>
                                    <td><?php echo esc_html($lead->name ?: '-'); ?></td>
                                    <td><?php echo esc_html(wp_date(get_option('date_format'), strtotime($lead->submitted_at))); ?></td>
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


