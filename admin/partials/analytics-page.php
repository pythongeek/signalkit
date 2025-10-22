<?php
/**
 * Analytics Page Template
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get analytics data
$analytics = SignalKit_Analytics::get_analytics('all');

// Calculate combined stats
$combined = array(
    'impressions' => ($analytics['follow']['impressions'] ?? 0) + ($analytics['preferred']['impressions'] ?? 0),
    'clicks' => ($analytics['follow']['clicks'] ?? 0) + ($analytics['preferred']['clicks'] ?? 0),
    'dismissals' => ($analytics['follow']['dismissals'] ?? 0) + ($analytics['preferred']['dismissals'] ?? 0),
);
$combined['ctr'] = $combined['impressions'] > 0 ? round(($combined['clicks'] / $combined['impressions']) * 100, 2) : 0;
?>

<div class="wrap signalkit-analytics-page">
    <h1><?php _e('SignalKit Analytics', 'signalkit-for-google'); ?></h1>
    
    <div class="signalkit-analytics-container">
        
        <!-- Combined Analytics -->
        <div class="signalkit-analytics-card signalkit-combined-analytics">
            <div class="signalkit-card-header">
                <h2><?php _e('📊 Combined Performance', 'signalkit-for-google'); ?></h2>
            </div>
            
            <div class="signalkit-stats-grid">
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon">👁️</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($combined['impressions'])); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Total Impressions', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon">👆</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($combined['clicks'])); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Total Clicks', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-icon">📈</div>
                    <div class="signalkit-stat-value"><?php echo esc_html($combined['ctr']); ?>%</div>
                    <div class="signalkit-stat-label"><?php _e('Overall CTR', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-icon">❌</div>
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($combined['dismissals'])); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Total Dismissals', 'signalkit-for-google'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Follow Banner Analytics -->
        <div class="signalkit-analytics-card">
            <div class="signalkit-card-header">
                <h2><?php _e('🔔 Follow Banner', 'signalkit-for-google'); ?></h2>
                <button class="button button-secondary signalkit-reset-analytics" data-banner-type="follow">
                    <?php _e('Reset', 'signalkit-for-google'); ?>
                </button>
            </div>
            
            <div class="signalkit-stats-grid">
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['follow']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Impressions', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['follow']['clicks'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Clicks', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-value"><?php echo esc_html($analytics['follow']['ctr'] ?? 0); ?>%</div>
                    <div class="signalkit-stat-label"><?php _e('CTR', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['follow']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Dismissals', 'signalkit-for-google'); ?></div>
                </div>
            </div>
            
            <?php if (isset($analytics['follow']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php 
                    printf(
                        __('Last updated: %s', 'signalkit-for-google'),
                        esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($analytics['follow']['last_updated'])))
                    );
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Preferred Banner Analytics -->
        <div class="signalkit-analytics-card">
            <div class="signalkit-card-header">
                <h2><?php _e('⭐ Preferred Source Banner', 'signalkit-for-google'); ?></h2>
                <button class="button button-secondary signalkit-reset-analytics" data-banner-type="preferred">
                    <?php _e('Reset', 'signalkit-for-google'); ?>
                </button>
            </div>
            
            <div class="signalkit-stats-grid">
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['preferred']['impressions'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Impressions', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['preferred']['clicks'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Clicks', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box signalkit-stat-highlight">
                    <div class="signalkit-stat-value"><?php echo esc_html($analytics['preferred']['ctr'] ?? 0); ?>%</div>
                    <div class="signalkit-stat-label"><?php _e('CTR', 'signalkit-for-google'); ?></div>
                </div>
                
                <div class="signalkit-stat-box">
                    <div class="signalkit-stat-value"><?php echo esc_html(number_format($analytics['preferred']['dismissals'] ?? 0)); ?></div>
                    <div class="signalkit-stat-label"><?php _e('Dismissals', 'signalkit-for-google'); ?></div>
                </div>
            </div>
            
            <?php if (isset($analytics['preferred']['last_updated'])): ?>
                <div class="signalkit-last-updated">
                    <?php 
                    printf(
                        __('Last updated: %s', 'signalkit-for-google'),
                        esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($analytics['preferred']['last_updated'])))
                    );
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
    
    <!-- Reset All Button -->
    <div style="margin-top: 20px;">
        <button class="button button-secondary signalkit-reset-analytics" data-banner-type="all">
            <?php _e('🔄 Reset All Analytics', 'signalkit-for-google'); ?>
        </button>
        <p class="description"><?php _e('This will reset analytics for both banners.', 'signalkit-for-google'); ?></p>
    </div>
</div>