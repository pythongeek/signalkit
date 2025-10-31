<?php
/**
 * Handle analytics and tracking.
 *
 * @package SignalKit_For_Google
 * @version 1.0.0 - SECURITY: Race condition protection added
 * 
 * IMPORTANT SECURITY NOTES FOR DEVELOPERS:
 * ========================================
 * 
 * This plugin uses WordPress's Options API (update_option/get_option) which is SAFE
 * from SQL injection because WordPress handles all escaping internally.
 * 
 * ⚠️ WARNING: If you modify this code to use direct database queries, you MUST:
 * 
 * 1. ALWAYS use $wpdb->prepare() for ALL queries with user input
 * 2. NEVER concatenate user input directly into SQL strings
 * 3. Use %d for integers, %s for strings, %f for floats
 * 
 * SAFE EXAMPLES (if you need direct queries):
 * -------------------------------------------
 * 
 * // ✅ SAFE - Using $wpdb->prepare()
 * global $wpdb;
 * $banner_type = 'follow';
 * $impressions = 100;
 * $wpdb->query(
 *     $wpdb->prepare(
 *         "UPDATE {$wpdb->prefix}signalkit_analytics 
 *         SET impressions = %d 
 *         WHERE banner_type = %s",
 *         $impressions,
 *         $banner_type
 *     )
 * );
 * 
 * // ✅ SAFE - Reading data
 * $banner_type = sanitize_text_field($_POST['banner_type']);
 * $result = $wpdb->get_row(
 *     $wpdb->prepare(
 *         "SELECT * FROM {$wpdb->prefix}signalkit_analytics 
 *         WHERE banner_type = %s",
 *         $banner_type
 *     )
 * );
 * 
 * // ❌ UNSAFE - NEVER DO THIS
 * $banner_type = $_POST['banner_type']; // Unsanitized
 * $wpdb->query(
 *     "UPDATE {$wpdb->prefix}signalkit_analytics 
 *     SET impressions = {$_POST['count']} 
 *     WHERE banner_type = '{$banner_type}'"
 * );
 * // ^ This allows SQL injection attacks!
 * 
 * ADDITIONAL SECURITY REQUIREMENTS:
 * ---------------------------------
 * - Always validate input types: in_array(), is_numeric(), etc.
 * - Sanitize before processing: sanitize_text_field(), absint(), etc.
 * - Use wp_verify_nonce() for all AJAX/form submissions
 * - Check user capabilities: current_user_can('manage_options')
 * 
 * WordPress Database API Documentation:
 * https://developer.wordpress.org/reference/classes/wpdb/
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Analytics {
    
    /**
     * Track impression with race condition protection.
     * 
     * SECURITY: Uses WordPress Options API - safe from SQL injection
     * Data is automatically serialized and escaped by WordPress
     * Race condition protection via transactional locking
     * 
     * @param string $banner_type 'follow' or 'preferred' (validated)
     * @return bool Success status
     */
    public static function track_impression($banner_type = 'follow') {
        // Validate banner type - SECURITY: Whitelist validation
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            signalkit_log("Analytics: Invalid banner type for impression", ['type' => $banner_type]);
            return false;
        }

        // Acquire lock to prevent race conditions
        $lock_key = 'signalkit_analytics_lock_' . $banner_type;
        $lock_acquired = false;
        $lock_timeout = 5; // seconds
        $lock_start = time();
        
        // Try to acquire lock
        while (!$lock_acquired && (time() - $lock_start) < $lock_timeout) {
            $lock_acquired = add_option($lock_key, time(), '', 'no');
            if (!$lock_acquired) {
                // Check if lock is stale (older than 5 seconds)
                $lock_time = get_option($lock_key, 0);
                if ((time() - $lock_time) > 5) {
                    delete_option($lock_key);
                } else {
                    usleep(50000); // 50ms delay before retry
                }
            }
        }
        
        if (!$lock_acquired) {
            signalkit_log("Analytics ({$banner_type}): Failed to acquire lock for impression");
            return false;
        }
        
        try {
            // Force fresh read from database
            wp_cache_delete('signalkit_analytics', 'options');
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
            
            // Save with retry logic
            $retries = 3;
            $saved = false;
            
            while ($retries > 0 && !$saved) {
                $saved = update_option('signalkit_analytics', $analytics);
                
                if (!$saved) {
                    // Re-read and retry
                    wp_cache_delete('signalkit_analytics', 'options');
                    $analytics = get_option('signalkit_analytics', array());
                    
                    if (!isset($analytics[$banner_type]['impressions'])) {
                        $analytics[$banner_type]['impressions'] = 0;
                    }
                    
                    $analytics[$banner_type]['impressions']++;
                    $analytics[$banner_type]['last_updated'] = current_time('mysql');
                    $retries--;
                    
                    if ($retries > 0) {
                        usleep(100000); // 100ms delay
                    }
                }
            }
            
            if ($saved) {
                signalkit_log("Analytics ({$banner_type}): Impression tracked", [
                    'total' => $analytics[$banner_type]['impressions'],
                    'timestamp' => $analytics[$banner_type]['last_updated']
                ]);
            } else {
                signalkit_log("Analytics ({$banner_type}): Failed to save impression after retries", [
                    'attempted_count' => $analytics[$banner_type]['impressions']
                ]);
            }
            
            return $saved;
            
        } finally {
            // Always release lock
            delete_option($lock_key);
        }
    }
    
    /**
     * Track click with race condition protection.
     * 
     * SECURITY: Uses WordPress Options API - safe from SQL injection
     * 
     * @param string $banner_type 'follow' or 'preferred' (validated)
     * @return array|false Analytics data or false on failure
     */
    public static function track_click($banner_type = 'follow') {
        // SECURITY: Whitelist validation prevents injection
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            signalkit_log("Analytics: Invalid banner type for click", ['type' => $banner_type]);
            return false;
        }

        // Acquire lock to prevent race conditions
        $lock_key = 'signalkit_analytics_lock_' . $banner_type;
        $lock_acquired = false;
        $lock_timeout = 5;
        $lock_start = time();
        
        while (!$lock_acquired && (time() - $lock_start) < $lock_timeout) {
            $lock_acquired = add_option($lock_key, time(), '', 'no');
            if (!$lock_acquired) {
                $lock_time = get_option($lock_key, 0);
                if ((time() - $lock_time) > 5) {
                    delete_option($lock_key);
                } else {
                    usleep(50000);
                }
            }
        }
        
        if (!$lock_acquired) {
            signalkit_log("Analytics ({$banner_type}): Failed to acquire lock for click");
            return false;
        }
        
        try {
            wp_cache_delete('signalkit_analytics', 'options');
            $analytics = get_option('signalkit_analytics', array());
            
            if (!isset($analytics[$banner_type])) {
                $analytics[$banner_type] = array(
                    'impressions' => 0,
                    'clicks' => 0,
                    'dismissals' => 0,
                    'first_seen' => current_time('mysql'),
                );
            }
            
            if (!isset($analytics[$banner_type]['clicks'])) {
                $analytics[$banner_type]['clicks'] = 0;
            }
            
            $analytics[$banner_type]['clicks']++;
            $analytics[$banner_type]['last_updated'] = current_time('mysql');
            
            $retries = 3;
            $saved = false;
            
            while ($retries > 0 && !$saved) {
                $saved = update_option('signalkit_analytics', $analytics);
                
                if (!$saved) {
                    wp_cache_delete('signalkit_analytics', 'options');
                    $analytics = get_option('signalkit_analytics', array());
                    
                    if (!isset($analytics[$banner_type]['clicks'])) {
                        $analytics[$banner_type]['clicks'] = 0;
                    }
                    
                    $analytics[$banner_type]['clicks']++;
                    $analytics[$banner_type]['last_updated'] = current_time('mysql');
                    $retries--;
                    
                    if ($retries > 0) {
                        usleep(100000);
                    }
                }
            }
            
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
            
        } finally {
            delete_option($lock_key);
        }
    }
    
    /**
     * Track dismissal with race condition protection.
     * 
     * SECURITY: Uses WordPress Options API - safe from SQL injection
     * 
     * @param string $banner_type 'follow' or 'preferred' (validated)
     * @return array|false Analytics data or false on failure
     */
    public static function track_dismissal($banner_type = 'follow') {
        // SECURITY: Whitelist validation
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            signalkit_log("Analytics: Invalid banner type for dismissal", ['type' => $banner_type]);
            return false;
        }

        // Acquire lock to prevent race conditions
        $lock_key = 'signalkit_analytics_lock_' . $banner_type;
        $lock_acquired = false;
        $lock_timeout = 5;
        $lock_start = time();
        
        while (!$lock_acquired && (time() - $lock_start) < $lock_timeout) {
            $lock_acquired = add_option($lock_key, time(), '', 'no');
            if (!$lock_acquired) {
                $lock_time = get_option($lock_key, 0);
                if ((time() - $lock_time) > 5) {
                    delete_option($lock_key);
                } else {
                    usleep(50000);
                }
            }
        }
        
        if (!$lock_acquired) {
            signalkit_log("Analytics ({$banner_type}): Failed to acquire lock for dismissal");
            return false;
        }
        
        try {
            wp_cache_delete('signalkit_analytics', 'options');
            $analytics = get_option('signalkit_analytics', array());
            
            if (!isset($analytics[$banner_type])) {
                $analytics[$banner_type] = array(
                    'impressions' => 0,
                    'clicks' => 0,
                    'dismissals' => 0,
                    'first_seen' => current_time('mysql'),
                );
            }
            
            if (!isset($analytics[$banner_type]['dismissals'])) {
                $analytics[$banner_type]['dismissals'] = 0;
            }
            
            $analytics[$banner_type]['dismissals']++;
            $analytics[$banner_type]['last_updated'] = current_time('mysql');
            
            $retries = 3;
            $saved = false;
            
            while ($retries > 0 && !$saved) {
                $saved = update_option('signalkit_analytics', $analytics);
                
                if (!$saved) {
                    wp_cache_delete('signalkit_analytics', 'options');
                    $analytics = get_option('signalkit_analytics', array());
                    
                    if (!isset($analytics[$banner_type]['dismissals'])) {
                        $analytics[$banner_type]['dismissals'] = 0;
                    }
                    
                    $analytics[$banner_type]['dismissals']++;
                    $analytics[$banner_type]['last_updated'] = current_time('mysql');
                    $retries--;
                    
                    if ($retries > 0) {
                        usleep(100000);
                    }
                }
            }
            
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
            
        } finally {
            delete_option($lock_key);
        }
    }
    
    /**
     * Get analytics data.
     * 
     * SECURITY: Uses WordPress Options API - safe from SQL injection
     * 
     * @param string $banner_type 'follow', 'preferred', or 'all'
     * @return array Analytics data
     */
    public static function get_analytics($banner_type = 'all') {
        $analytics = get_option('signalkit_analytics', array());
        
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
        
        if ($banner_type === 'all') {
            foreach (['follow', 'preferred'] as $type) {
                if (isset($analytics[$type])) {
                    $analytics[$type]['ctr'] = self::calculate_ctr($analytics[$type]);
                    $analytics[$type]['dismissal_rate'] = self::calculate_dismissal_rate($analytics[$type]);
                }
            }
            return $analytics;
        }
        
        if (isset($analytics[$banner_type])) {
            $analytics[$banner_type]['ctr'] = self::calculate_ctr($analytics[$banner_type]);
            $analytics[$banner_type]['dismissal_rate'] = self::calculate_dismissal_rate($analytics[$banner_type]);
            return $analytics[$banner_type];
        }
        
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
     * SECURITY: Uses WordPress Options API - safe from SQL injection
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
