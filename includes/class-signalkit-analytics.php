<?php
/**
 * Handle analytics and tracking.
 *
 * @package SignalKit
 * @version 2.0.0
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
 *          SET impressions = %d 
 *          WHERE banner_type = %s",
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
 *          WHERE banner_type = %s",
 *         $banner_type
 *     )
 * );
 *
 * // ❌ UNSAFE - NEVER DO THIS
 * $banner_type = $_POST['banner_type']; // Unsanitized
 * $wpdb->query(
 *     "UPDATE {$wpdb->prefix}signalkit_analytics 
 *      SET impressions = {$_POST['count']} 
 *      WHERE banner_type = '{$banner_type}'"
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
     * @param string $banner_type 'follow', 'preferred', or 'custom' (validated)
     * @return bool Success status
     */
    public static function track_impression($banner_type = 'follow') {
        // Validate banner type - SECURITY: Whitelist validation
        if (!in_array($banner_type, ['follow', 'preferred', 'custom'], true)) {

            return false;
        }

        // Check rate limit
        if (self::is_rate_limited('impression')) {

            return false;
        }

        // Acquire lock to prevent race conditions
        $lock_key = 'signalkit_analytics_lock_' . $banner_type;
        $lock_acquired = false;
        $lock_timeout = 5; // seconds
        $lock_start = time();
        
        // Try to acquire lock using transient (auto-expires, no cleanup needed)
        while (!$lock_acquired && (time() - $lock_start) < $lock_timeout) {
            $existing_lock = get_transient($lock_key);
            if ($existing_lock === false) {
                // No lock exists, try to set one with 5-second TTL
                set_transient($lock_key, time(), 5);
                $lock_acquired = true;
            } else {
                // Lock exists, wait and retry
                usleep(50000); // 50ms delay before retry
            }
        }
        
        if (!$lock_acquired) {

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

            } else {

            }
            
            return $saved;
            
        } finally {
            // Always release lock
            delete_transient($lock_key);
        }
    }
    
    /**
     * Track click with race condition protection.
     *
     * SECURITY: Uses WordPress Options API - safe from SQL injection
     *
     * @param string $banner_type 'follow', 'preferred', or 'custom' (validated)
     * @return array|false Analytics data or false on failure
     */
    public static function track_click($banner_type = 'follow') {
        // SECURITY: Whitelist validation prevents injection
        if (!in_array($banner_type, ['follow', 'preferred', 'custom'], true)) {

            return false;
        }

        // Check rate limit
        if (self::is_rate_limited('click')) {

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
                    delete_transient($lock_key);
                } else {
                    usleep(50000);
                }
            }
        }
        
        if (!$lock_acquired) {

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

                return $analytics;
            } else {

                return false;
            }
            
        } finally {
            delete_transient($lock_key);
        }
    }
    
    /**
     * Track dismissal with race condition protection.
     *
     * SECURITY: Uses WordPress Options API - safe from SQL injection
     *
     * @param string $banner_type 'follow', 'preferred', or 'custom' (validated)
     * @return array|false Analytics data or false on failure
     */
    public static function track_dismissal($banner_type = 'follow') {
        // SECURITY: Whitelist validation
        if (!in_array($banner_type, ['follow', 'preferred', 'custom'], true)) {

            return false;
        }

        // Check rate limit
        if (self::is_rate_limited('dismissal')) {

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
                    delete_transient($lock_key);
                } else {
                    usleep(50000);
                }
            }
        }
        
        if (!$lock_acquired) {

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

                return $analytics;
            } else {

                return false;
            }
            
        } finally {
            delete_transient($lock_key);
        }
    }
    
    /**
     * Track form submission (for custom banner).
     *
     * SECURITY: Uses WordPress Options API - safe from SQL injection
     *
     * @return bool Success status
     */
    public static function track_submission() {
        $banner_type = 'custom';

        // Check rate limit
        if (self::is_rate_limited('submission')) {

            return false;
        }

        // Acquire lock to prevent race conditions
        $lock_key = 'signalkit_analytics_lock_submissions';
        $lock_acquired = false;
        $lock_timeout = 5;
        $lock_start = time();
        
        while (!$lock_acquired && (time() - $lock_start) < $lock_timeout) {
            $lock_acquired = add_option($lock_key, time(), '', 'no');
            if (!$lock_acquired) {
                $lock_time = get_option($lock_key, 0);
                if ((time() - $lock_time) > 5) {
                    delete_transient($lock_key);
                } else {
                    usleep(50000);
                }
            }
        }
        
        if (!$lock_acquired) {

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
                    'submissions' => 0,
                    'first_seen' => current_time('mysql'),
                );
            }
            
            if (!isset($analytics[$banner_type]['submissions'])) {
                $analytics[$banner_type]['submissions'] = 0;
            }
            
            $analytics[$banner_type]['submissions']++;
            $analytics[$banner_type]['last_updated'] = current_time('mysql');
            
            $retries = 3;
            $saved = false;
            
            while ($retries > 0 && !$saved) {
                $saved = update_option('signalkit_analytics', $analytics);
                
                if (!$saved) {
                    wp_cache_delete('signalkit_analytics', 'options');
                    $analytics = get_option('signalkit_analytics', array());
                    
                    if (!isset($analytics[$banner_type]['submissions'])) {
                        $analytics[$banner_type]['submissions'] = 0;
                    }
                    
                    $analytics[$banner_type]['submissions']++;
                    $analytics[$banner_type]['last_updated'] = current_time('mysql');
                    $retries--;
                    
                    if ($retries > 0) {
                        usleep(100000);
                    }
                }
            }
            
            if ($saved) {

                return true;
            } else {

                return false;
            }
            
        } finally {
            delete_transient($lock_key);
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
        
        if (!isset($analytics['custom'])) {
            $analytics['custom'] = $default_data;
        }
        
        if ($banner_type === 'all') {
            foreach (['follow', 'preferred', 'custom'] as $type) {
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
                'custom' => $reset_data,
            );

        } else {
            if (in_array($banner_type, ['follow', 'preferred', 'custom'], true)) {
                $analytics[$banner_type] = $reset_data;

            } else {

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
        $custom = $analytics['custom'];
        
        return array(
            'total_impressions' => $follow['impressions'] + $preferred['impressions'] + $custom['impressions'],
            'total_clicks' => $follow['clicks'] + $preferred['clicks'] + $custom['clicks'],
            'total_dismissals' => $follow['dismissals'] + $preferred['dismissals'] + $custom['dismissals'],
            'combined_ctr' => self::calculate_combined_ctr($follow, $preferred, $custom),
            'follow' => $follow,
            'preferred' => $preferred,
            'custom' => $custom,
        );
    }
    
    /**
     * Calculate combined CTR for all banners.
     *
     * @param array $follow Follow banner data
     * @param array $preferred Preferred banner data
     * @param array $custom Custom banner data
     * @return float Combined CTR percentage
     */
    private static function calculate_combined_ctr($follow, $preferred, $custom = array()) {
        $total_impressions = $follow['impressions'] + $preferred['impressions'] + ($custom['impressions'] ?? 0);
        $total_clicks = $follow['clicks'] + $preferred['clicks'] + ($custom['clicks'] ?? 0);
        
        if ($total_impressions === 0) {
            return 0;
        }
        
        return round(($total_clicks / $total_impressions) * 100, 2);
    }

    /**
     * Check if the current user IP is rate limited for a specific action.
     *
     * SECURITY FIX v1.0.0:
     * - Changed from 'signalkit_options' to 'signalkit_settings' (correct key)
     * - Changed from 'on' string to truthy check (matches checkbox format)
     * - Uses same settings as admin AJAX handlers for consistency
     *
     * @param string $action The action being performed (e.g., 'impression', 'click').
     * @return bool True if limited, false otherwise.
     */
    private static function is_rate_limited($action = '') {
        // FIXED: Use correct option key
        $options = get_option('signalkit_settings', array());
        
        // FIXED: Check as boolean (checkboxes return 1/0, not 'on')
        $is_enabled = !empty($options['enable_rate_limiting']);

        // If disabled in settings, skip check
        if (!$is_enabled) {
            return false;
        }

        // Default limits (can be made configurable in admin settings if needed)
        $limit_count = 10; // 10 requests
        $limit_time = 60;  // per 60 seconds

        $ip = self::get_user_ip();
        // Use md5 on IP for transient key (safe, fixed length)
        $transient_key = 'signalkit_rl_' . $action . '_' . md5($ip); 
        
        $hits = (int) get_transient($transient_key);

        if ($hits >= $limit_count) {
            // Already limited
            return true;
        }

        // Increment hits and set transient
        $hits++;
        set_transient($transient_key, $hits, $limit_time);

        return false; // Not limited yet
    }

    /**
     * Get the user's IP address, checking common headers.
     *
     * SECURITY: Validation-first approach (Envato/CodeCanyon requirement)
     * - Validates IP format IMMEDIATELY after raw $_SERVER access
     * - Prevents header spoofing by rejecting invalid IPs before processing
     * - Only sanitizes after validation passes
     *
     * @return string The validated and sanitized IP address.
     */
    private static function get_user_ip() {
        // Check common proxy headers in order of reliability
        $headers = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        );
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                // Get raw value first
                $raw_ip = sanitize_text_field(wp_unslash($_SERVER[$header]));
                if (empty($raw_ip)) {
                    continue;
                }
                
                // Handle comma-separated IPs (from proxies) - get first IP
                if (strpos($raw_ip, ',') !== false) {
                    $ips = explode(',', $raw_ip);
                    $raw_ip = trim($ips[0]);
                }
                
                // SECURITY: Validate IP format IMMEDIATELY after raw access
                // This is the definitive check that prevents header spoofing
                if (!filter_var($raw_ip, FILTER_VALIDATE_IP)) {
                    // Invalid IP format - skip this header and try next
                    continue;
                }
                
                // Only sanitize AFTER validation passes
                // At this point we know it's a valid IP format
                return sanitize_text_field($raw_ip);
            }
        }

        // Fallback for invalid/missing IP
        return '0.0.0.0';
    }
}