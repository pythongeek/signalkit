<?php
/**
 * Handle display rules and conditional logic.
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Display_Rules {
    
    /**
     * Check if banner should be displayed based on rules.
     * PRODUCTION READY - Bypasses most restrictions when enabled
     * 
     * @param string $banner_type 'follow' or 'preferred'
     */
    public static function should_display($banner_type = 'follow') {
        $settings = get_option('signalkit_settings', array());
        $prefix = $banner_type . '_';
        
        // CRITICAL: Check if banner type is enabled
        if (empty($settings[$prefix . 'enabled'])) {
            signalkit_log("Display Rules ({$banner_type}): Banner is disabled");
            return false;
        }
        
        // Check if dismissed (still respect user choice)
        if (self::is_dismissed($banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Banner is dismissed by user");
            return false;
        }
        
        // Check device type
        if (!self::check_device_compatibility($settings, $banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Device not compatible");
            return false;
        }
        
        // Check page rules - RELAXED
        if (!self::check_page_rules($settings, $banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Page rules not met");
            return false;
        }
        
        // Check frequency - RELAXED
        if (!self::check_frequency($settings, $banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Frequency limit reached");
            return false;
        }
        
        signalkit_log("Display Rules ({$banner_type}): ✓ All conditions met - banner will display");
        return true;
    }
    
    /**
     * Check if banner is dismissed.
     */
    private static function is_dismissed($banner_type) {
        $cookie_name = 'signalkit_dismissed_' . $banner_type;
        return isset($_COOKIE[$cookie_name]);
    }
    
    /**
     * Check device compatibility - RELAXED.
     * If neither is explicitly set, assume both are enabled.
     */
    private static function check_device_compatibility($settings, $banner_type) {
        $is_mobile = wp_is_mobile();
        $prefix = $banner_type . '_';
        
        $mobile_enabled = isset($settings[$prefix . 'mobile_enabled']) ? $settings[$prefix . 'mobile_enabled'] : 1;
        $desktop_enabled = isset($settings[$prefix . 'desktop_enabled']) ? $settings[$prefix . 'desktop_enabled'] : 1;
        
        // If both are disabled, still show (bypass mode)
        if (empty($mobile_enabled) && empty($desktop_enabled)) {
            signalkit_log("Display Rules ({$banner_type}): Both devices disabled, bypassing - will show");
            return true;
        }
        
        if ($is_mobile && empty($mobile_enabled)) {
            return false;
        }
        
        if (!$is_mobile && empty($desktop_enabled)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check page rules - PRODUCTION READY with bypass.
     * If no page types are selected, show on all pages.
     */
    private static function check_page_rules($settings, $banner_type) {
        $prefix = $banner_type . '_';
        
        // Get all page type settings
        $show_on_posts = isset($settings[$prefix . 'show_on_posts']) ? $settings[$prefix . 'show_on_posts'] : 0;
        $show_on_pages = isset($settings[$prefix . 'show_on_pages']) ? $settings[$prefix . 'show_on_pages'] : 0;
        $show_on_homepage = isset($settings[$prefix . 'show_on_homepage']) ? $settings[$prefix . 'show_on_homepage'] : 0;
        $show_on_archive = isset($settings[$prefix . 'show_on_archive']) ? $settings[$prefix . 'show_on_archive'] : 0;
        
        // BYPASS MODE: If nothing is checked, show everywhere
        if (empty($show_on_posts) && empty($show_on_pages) && empty($show_on_homepage) && empty($show_on_archive)) {
            signalkit_log("Display Rules ({$banner_type}): No page types selected, showing on all pages (bypass mode)");
            return true;
        }
        
        // Check specific page types
        if (is_front_page() || is_home()) {
            $result = !empty($show_on_homepage);
            signalkit_log("Display Rules ({$banner_type}): Homepage check = " . ($result ? 'true' : 'false'));
            return $result;
        }
        
        if (is_single()) {
            $result = !empty($show_on_posts);
            signalkit_log("Display Rules ({$banner_type}): Single post check = " . ($result ? 'true' : 'false'));
            return $result;
        }
        
        if (is_page()) {
            $result = !empty($show_on_pages);
            signalkit_log("Display Rules ({$banner_type}): Page check = " . ($result ? 'true' : 'false'));
            return $result;
        }
        
        if (is_archive() || is_category() || is_tag()) {
            $result = !empty($show_on_archive);
            signalkit_log("Display Rules ({$banner_type}): Archive check = " . ($result ? 'true' : 'false'));
            return $result;
        }
        
        // Default: show if we're not sure what page type it is
        signalkit_log("Display Rules ({$banner_type}): Unknown page type, showing by default");
        return true;
    }
    
    /**
     * Check display frequency - RELAXED.
     */
    private static function check_frequency($settings, $banner_type) {
        $prefix = $banner_type . '_';
        $frequency = isset($settings[$prefix . 'show_frequency']) ? $settings[$prefix . 'show_frequency'] : 'always';
        
        // Always show
        if ($frequency === 'always') {
            return true;
        }
        
        $session_cookie = 'signalkit_session_' . $banner_type;
        $daily_cookie = 'signalkit_daily_' . $banner_type;
        
        if ($frequency === 'once_per_session' && isset($_COOKIE[$session_cookie])) {
            return false;
        }
        
        if ($frequency === 'once_per_day' && isset($_COOKIE[$daily_cookie])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Set frequency cookie.
     */
    public static function set_frequency_cookie($settings, $banner_type) {
        $prefix = $banner_type . '_';
        $frequency = isset($settings[$prefix . 'show_frequency']) ? $settings[$prefix . 'show_frequency'] : 'always';
        
        if ($frequency === 'once_per_session') {
            if (!isset($_COOKIE['signalkit_session_' . $banner_type])) {
                setcookie('signalkit_session_' . $banner_type, '1', 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
                signalkit_log("Display Rules ({$banner_type}): Set session cookie");
            }
        }
        
        if ($frequency === 'once_per_day') {
            if (!isset($_COOKIE['signalkit_daily_' . $banner_type])) {
                setcookie('signalkit_daily_' . $banner_type, '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
                signalkit_log("Display Rules ({$banner_type}): Set daily cookie");
            }
        }
    }
}