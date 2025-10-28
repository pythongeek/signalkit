<?php
/**
 * Handle analytics and tracking.
 *
 * @package SignalKit_For_Google
 * @version 2.2.0 - PRODUCTION: Enhanced logging and error handling
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Analytics {
    
    /**
     * Track impression.
     * 
     * @param string $banner_type 'follow' or 'preferred'
     * @return bool Success status
     */
    public static function track_impression($banner_type = 'follow') {
        // Validate banner type
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            signalkit_log("Analytics: Invalid banner type for impression", ['type' => $banner_type]);
            return false;
        }

        $analytics = get_option('signalkit_analytics', array());
        
        // Initialize if not exists
        if (!isset($analytics[$banner_type])) {
            $analytics[$banner_type] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
                'first_seen' => current_time('mysql'),
            );
        }
        
        // Ensure impressions key exists
        if (!isset($analytics[$banner_type]['impressions'])) {
            $analytics[$banner_type]['impressions'] = 0;
        }
        
        // Increment impression
        $analytics[$banner_type]['impressions']++;
        $analytics[$banner_type]['last_updated'] = current_time('mysql');
        
        // Save to database
        $saved = update_option('signalkit_analytics', $analytics);
        
        if ($saved) {
            signalkit_log("Analytics ({$banner_type}): Impression tracked", [
                'total' => $analytics[$banner_type]['impressions'],
                'timestamp' => $analytics[$banner_type]['last_updated']
            ]);
        } else {
            signalkit_log("Analytics ({$banner_type}): Failed to save impression", [
                'attempted_count' => $analytics[$banner_type]['impressions']
            ]);
        }
        
        return $saved;
    }
    
    /**
     * Track click.
     * 
     * @param string $banner_type 'follow' or 'preferred'
     * @return array|false Analytics data or false on failure
     */
    public static function track_click($banner_type = 'follow') {
        // Validate banner type
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            signalkit_log("Analytics: Invalid banner type for click", ['type' => $banner_type]);
            return false;
        }

        $analytics = get_option('signalkit_analytics', array());
        
        // Initialize if not exists
        if (!isset($analytics[$banner_type])) {
            $analytics[$banner_type] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
                'first_seen' => current_time('mysql'),
            );
        }
        
        // Ensure clicks key exists
        if (!isset($analytics[$banner_type]['clicks'])) {
            $analytics[$banner_type]['clicks'] = 0;
        }
        
        // Increment click
        $analytics[$banner_type]['clicks']++;
        $analytics[$banner_type]['last_updated'] = current_time('mysql');
        
        // Save to database
        $saved = update_option('signalkit_analytics', $analytics);
        
        if ($saved) {
            signalkit_log("Analytics ({$banner_type}): Click tracked", [
                'total' => $analytics[$banner_type]['clicks'],
                'impressions' => $analytics[$banner_type]['impressions'],
                'ctr' => self::calculate_ctr($analytics[$banner_type])
            ]);
            return $analytics;
        } else {
            signalkit_log("Analytics ({$banner_type}): Failed to save click");
            return false;
        }
    }
    
    /**
     * Track dismissal.
     * 
     * @param string $banner_type 'follow' or 'preferred'
     * @return array|false Analytics data or false on failure
     */
    public static function track_dismissal($banner_type = 'follow') {
        // Validate banner type
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            signalkit_log("Analytics: Invalid banner type for dismissal", ['type' => $banner_type]);
            return false;
        }

        $analytics = get_option('signalkit_analytics', array());
        
        // Initialize if not exists
        if (!isset($analytics[$banner_type])) {
            $analytics[$banner_type] = array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
                'first_seen' => current_time('mysql'),
            );
        }
        
        // Ensure dismissals key exists
        if (!isset($analytics[$banner_type]['dismissals'])) {
            $analytics[$banner_type]['dismissals'] = 0;
        }
        
        // Increment dismissal
        $analytics[$banner_type]['dismissals']++;
        $analytics[$banner_type]['last_updated'] = current_time('mysql');
        
        // Save to database
        $saved = update_option('signalkit_analytics', $analytics);
        
        if ($saved) {
            signalkit_log("Analytics ({$banner_type}): Dismissal tracked", [
                'total' => $analytics[$banner_type]['dismissals'],
                'dismissal_rate' => self::calculate_dismissal_rate($analytics[$banner_type])
            ]);
            return $analytics;
        } else {
            signalkit_log("Analytics ({$banner_type}): Failed to save dismissal");
            return false;
        }
    }
    
    /**
     * Get analytics data.
     * 
     * @param string $banner_type 'follow', 'preferred', or 'all'
     * @return array Analytics data
     */
    public static function get_analytics($banner_type = 'all') {
        $analytics = get_option('signalkit_analytics', array());
        
        // Ensure both banner types exist with defaults
        $default_data = array(
            'impressions' => 0,
            'clicks' => 0,
            'dismissals' => 0,
            'ctr' => 0,
            'first_seen' => null,
            'last_updated' => null,
        );
        
        if (!isset($analytics['follow'])) {
            $analytics['follow'] = $default_data;
        }
        
        if (!isset($analytics['preferred'])) {
            $analytics['preferred'] = $default_data;
        }
        
        // Return all analytics
        if ($banner_type === 'all') {
            foreach (['follow', 'preferred'] as $type) {
                if (isset($analytics[$type])) {
                    $analytics[$type]['ctr'] = self::calculate_ctr($analytics[$type]);
                    $analytics[$type]['dismissal_rate'] = self::calculate_dismissal_rate($analytics[$type]);
                }
            }
            return $analytics;
        }
        
        // Return specific banner analytics
        if (isset($analytics[$banner_type])) {
            $analytics[$banner_type]['ctr'] = self::calculate_ctr($analytics[$banner_type]);
            $analytics[$banner_type]['dismissal_rate'] = self::calculate_dismissal_rate($analytics[$banner_type]);
            return $analytics[$banner_type];
        }
        
        // Return default if banner type doesn't exist
        return $default_data;
    }
    
    /**
     * Calculate CTR (Click-Through Rate).
     * 
     * @param array $data Analytics data
     * @return float CTR percentage
     */
    private static function calculate_ctr($data) {
        $impressions = isset($data['impressions']) ? intval($data['impressions']) : 0;
        $clicks = isset($data['clicks']) ? intval($data['clicks']) : 0;
        
        if ($impressions === 0) {
            return 0;
        }
        
        return round(($clicks / $impressions) * 100, 2);
    }
    
    /**
     * Calculate dismissal rate.
     * 
     * @param array $data Analytics data
     * @return float Dismissal rate percentage
     */
    private static function calculate_dismissal_rate($data) {
        $impressions = isset($data['impressions']) ? intval($data['impressions']) : 0;
        $dismissals = isset($data['dismissals']) ? intval($data['dismissals']) : 0;
        
        if ($impressions === 0) {
            return 0;
        }
        
        return round(($dismissals / $impressions) * 100, 2);
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
            signalkit_log("Analytics: Reset completed for ALL banners");
        } else {
            if (in_array($banner_type, ['follow', 'preferred'], true)) {
                $analytics[$banner_type] = $reset_data;
                signalkit_log("Analytics: Reset completed for {$banner_type}");
            } else {
                signalkit_log("Analytics: Invalid banner type for reset", ['type' => $banner_type]);
                return false;
            }
        }
        
        return update_option('signalkit_analytics', $analytics);
    }
    
    /**
     * Get analytics summary for admin dashboard.
     * 
     * @return array Summary data
     */
    public static function get_summary() {
        $analytics = self::get_analytics('all');
        
        $follow = $analytics['follow'];
        $preferred = $analytics['preferred'];
        
        return array(
            'total_impressions' => $follow['impressions'] + $preferred['impressions'],
            'total_clicks' => $follow['clicks'] + $preferred['clicks'],
            'total_dismissals' => $follow['dismissals'] + $preferred['dismissals'],
            'combined_ctr' => self::calculate_combined_ctr($follow, $preferred),
            'follow' => $follow,
            'preferred' => $preferred,
        );
    }
    
    /**
     * Calculate combined CTR for both banners.
     * 
     * @param array $follow Follow banner data
     * @param array $preferred Preferred banner data
     * @return float Combined CTR percentage
     */
    private static function calculate_combined_ctr($follow, $preferred) {
        $total_impressions = $follow['impressions'] + $preferred['impressions'];
        $total_clicks = $follow['clicks'] + $preferred['clicks'];
        
        if ($total_impressions === 0) {
            return 0;
        }
        
        return round(($total_clicks / $total_impressions) * 100, 2);
    }
}