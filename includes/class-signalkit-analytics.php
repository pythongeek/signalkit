<?php
/**
 * Handle analytics and tracking.
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Analytics {
    
    /**
     * Track impression.
     * 
     * @param string $banner_type 'follow' or 'preferred'
     */
    public static function track_impression($banner_type = 'follow') {
        $analytics = get_option('signalkit_analytics', array());
        
        if (!isset($analytics[$banner_type])) {
            $analytics[$banner_type] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
            );
        }
        
        if (!isset($analytics[$banner_type]['impressions'])) {
            $analytics[$banner_type]['impressions'] = 0;
        }
        
        $analytics[$banner_type]['impressions']++;
        $analytics[$banner_type]['last_updated'] = current_time('mysql');
        
        update_option('signalkit_analytics', $analytics);
        
        signalkit_log("Analytics ({$banner_type}): Impression tracked", array('total' => $analytics[$banner_type]['impressions']));
    }
    
    /**
     * Track click.
     * 
     * @param string $banner_type 'follow' or 'preferred'
     */
    public static function track_click($banner_type = 'follow') {
        $analytics = get_option('signalkit_analytics', array());
        
        if (!isset($analytics[$banner_type])) {
            $analytics[$banner_type] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
            );
        }
        
        if (!isset($analytics[$banner_type]['clicks'])) {
            $analytics[$banner_type]['clicks'] = 0;
        }
        
        $analytics[$banner_type]['clicks']++;
        $analytics[$banner_type]['last_updated'] = current_time('mysql');
        
        update_option('signalkit_analytics', $analytics);
        
        signalkit_log("Analytics ({$banner_type}): Click tracked", array('total' => $analytics[$banner_type]['clicks']));
        
        return $analytics;
    }
    
    /**
     * Track dismissal.
     * 
     * @param string $banner_type 'follow' or 'preferred'
     */
    public static function track_dismissal($banner_type = 'follow') {
        $analytics = get_option('signalkit_analytics', array());
        
        if (!isset($analytics[$banner_type])) {
            $analytics[$banner_type] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
            );
        }
        
        if (!isset($analytics[$banner_type]['dismissals'])) {
            $analytics[$banner_type]['dismissals'] = 0;
        }
        
        $analytics[$banner_type]['dismissals']++;
        $analytics[$banner_type]['last_updated'] = current_time('mysql');
        
        update_option('signalkit_analytics', $analytics);
        
        signalkit_log("Analytics ({$banner_type}): Dismissal tracked", array('total' => $analytics[$banner_type]['dismissals']));
        
        return $analytics;
    }
    
    /**
     * Get analytics data.
     * 
     * @param string $banner_type 'follow', 'preferred', or 'all'
     * @return array Analytics data
     */
    public static function get_analytics($banner_type = 'all') {
        $analytics = get_option('signalkit_analytics', array());
        
        // Ensure both banner types exist
        if (!isset($analytics['follow'])) {
            $analytics['follow'] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
                'ctr' => 0,
            );
        }
        
        if (!isset($analytics['preferred'])) {
            $analytics['preferred'] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
                'ctr' => 0,
            );
        }
        
        if ($banner_type === 'all') {
            // Calculate CTR for both banners
            foreach (['follow', 'preferred'] as $type) {
                if (isset($analytics[$type])) {
                    $impressions = isset($analytics[$type]['impressions']) ? intval($analytics[$type]['impressions']) : 0;
                    $clicks = isset($analytics[$type]['clicks']) ? intval($analytics[$type]['clicks']) : 0;
                    $analytics[$type]['ctr'] = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
                }
            }
            return $analytics;
        }
        
        // Return specific banner analytics
        if (isset($analytics[$banner_type])) {
            $impressions = isset($analytics[$banner_type]['impressions']) ? intval($analytics[$banner_type]['impressions']) : 0;
            $clicks = isset($analytics[$banner_type]['clicks']) ? intval($analytics[$banner_type]['clicks']) : 0;
            $analytics[$banner_type]['ctr'] = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
            return $analytics[$banner_type];
        }
        
        // Return default if banner type doesn't exist
        return array(
            'impressions' => 0,
            'clicks' => 0,
            'dismissals' => 0,
            'ctr' => 0,
        );
    }
    
    /**
     * Reset analytics.
     * 
     * @param string $banner_type 'follow', 'preferred', or 'all'
     * @return bool Success status
     */
    public static function reset_analytics($banner_type = 'all') {
        $analytics = get_option('signalkit_analytics', array());
        
        $reset_data = array(
            'impressions' => 0,
            'clicks' => 0,
            'dismissals' => 0,
            'first_seen' => current_time('mysql'),
            'last_updated' => current_time('mysql'),
        );
        
        if ($banner_type === 'all') {
            $analytics = array(
                'follow' => $reset_data,
                'preferred' => $reset_data,
            );
        } else {
            $analytics[$banner_type] = $reset_data;
        }
        
        update_option('signalkit_analytics', $analytics);
        
        signalkit_log("Analytics: Reset completed for {$banner_type}");
        
        return true;
    }
}