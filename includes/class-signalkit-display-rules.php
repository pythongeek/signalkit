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
     * 
     * @param string $banner_type 'follow' or 'preferred'
     */
    public static function should_display($banner_type = 'follow') {
        $settings = get_option('signalkit_settings', array());
        $prefix = $banner_type . '_';
        
        // Check if banner type is enabled
        if (empty($settings[$prefix . 'enabled'])) {
            signalkit_log("Display Rules ({$banner_type}): Banner is disabled");
            return false;
        }
        
        // Check if dismissed
        if (self::is_dismissed($banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Banner is dismissed");
            return false;
        }
        
        // Check device type
        if (!self::check_device_compatibility($settings, $banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Device not compatible");
            return false;
        }
        
        // Check page rules
        if (!self::check_page_rules($settings, $banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Page rules not met");
            return false;
        }
        
        // Check frequency
        if (!self::check_frequency($settings, $banner_type)) {
            signalkit_log("Display Rules ({$banner_type}): Frequency limit reached");
            return false;
        }
        
        signalkit_log("Display Rules ({$banner_type}): All conditions met - banner will display");
        return true;
    }
    
    /**
     * Check if banner is dismissed.
     */
    private static function is_dismissed($banner_type) {
        $cookie_name = 'signalkit_dismissed_' . $banner_type;
        if (isset($_COOKIE[$cookie_name])) {
            signalkit_log("Display Rules ({$banner_type}): Dismissed cookie found", $_COOKIE[$cookie_name]);
            return true;
        }
        return false;
    }
    
    /**
     * Check device compatibility.
     */
    private static function check_device_compatibility($settings, $banner_type) {
        $is_mobile = wp_is_mobile();
        $prefix = $banner_type . '_';
        
        if ($is_mobile && empty($settings[$prefix . 'mobile_enabled'])) {
            return false;
        }
        
        if (!$is_mobile && empty($settings[$prefix . 'desktop_enabled'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check page rules with enhanced post/page specific controls.
     */
    private static function check_page_rules($settings, $banner_type) {
        $prefix = $banner_type . '_';
        
        // Check specific page types
        if (is_front_page() || is_home()) {
            return !empty($settings[$prefix . 'show_on_homepage']);
        }
        
        if (is_single()) {
            return !empty($settings[$prefix . 'show_on_posts']);
        }
        
        if (is_page()) {
            return !empty($settings[$prefix . 'show_on_pages']);
        }
        
        if (is_archive() || is_category() || is_tag()) {
            return !empty($settings[$prefix . 'show_on_archive']);
        }
        
        // Legacy support for 'show_on' setting
        $show_on = isset($settings[$prefix . 'show_on']) ? $settings[$prefix . 'show_on'] : 'all';
        
        if ($show_on === 'all') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check display frequency.
     */
    private static function check_frequency($settings, $banner_type) {
        $prefix = $banner_type . '_';
        $frequency = isset($settings[$prefix . 'show_frequency']) ? $settings[$prefix . 'show_frequency'] : 'always';
        
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
            setcookie('signalkit_session_' . $banner_type, '1', 0, COOKIEPATH, COOKIE_DOMAIN);
        }
        
        if ($frequency === 'once_per_day') {
            setcookie('signalkit_daily_' . $banner_type, '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        }
    }
}