<?php
/**
 * Handle display rules and conditional logic.
 * Version: 2.4.0 - ADDED: Alternating logic for banners on single posts
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
        
        // NEW: Alternating logic if both banners enabled and on single post
        if (is_single() && !empty($settings['follow_enabled']) && !empty($settings['preferred_enabled'])) {
            $post_id = get_the_ID();
            $show_follow = ($post_id % 2 === 0);
            $alternate_result = ($banner_type === 'follow') ? $show_follow : !$show_follow;
            signalkit_log("Display Rules ({$banner_type}): Alternating on single post ID {$post_id} - Show: " . ($alternate_result ? 'YES' : 'NO'));
            return $alternate_result;
        }
        
        signalkit_log("Display Rules ({$banner_type}): ✓ All conditions met - banner will display");
        return true;
    }
    
    /**
     * Check if banner is dismissed - FIXED: Unique cookie names
     */
    private static function is_dismissed($banner_type) {
        $cookie_name = 'signalkit_dismissed_' . $banner_type;
        $dismissed = isset($_COOKIE[$cookie_name]);
        
        if ($dismissed) {
            signalkit_log("Display Rules ({$banner_type}): Cookie found - dismissed until: " . 
                         (isset($_COOKIE[$cookie_name]) ? date('Y-m-d H:i:s', intval($_COOKIE[$cookie_name])) : 'unknown'));
        }
        
        return $dismissed;
    }
    
    /**
     * Check device compatibility.
     */
    private static function check_device_compatibility($settings, $banner_type) {
        $is_mobile = wp_is_mobile();
        $prefix = $banner_type . '_';
        
        // Default to enabled if not set
        $mobile_enabled = isset($settings[$prefix . 'mobile_enabled']) ? $settings[$prefix . 'mobile_enabled'] : 1;
        $desktop_enabled = isset($settings[$prefix . 'desktop_enabled']) ? $settings[$prefix . 'desktop_enabled'] : 1;
        
        // If both are disabled, still show (bypass mode)
        if (empty($mobile_enabled) && empty($desktop_enabled)) {
            signalkit_log("Display Rules ({$banner_type}): Both devices disabled, bypassing - will show");
            return true;
        }
        
        if ($is_mobile && empty($mobile_enabled)) {
            signalkit_log("Display Rules ({$banner_type}): Mobile device blocked");
            return false;
        }
        
        if (!$is_mobile && empty($desktop_enabled)) {
            signalkit_log("Display Rules ({$banner_type}): Desktop device blocked");
            return false;
        }
        
        signalkit_log("Display Rules ({$banner_type}): Device check passed (Mobile: " . ($is_mobile ? 'yes' : 'no') . ")");
        return true;
    }
    
    /**
     * Check page rules - FIXED: Better detection and logging
     */
    private static function check_page_rules($settings, $banner_type) {
        $prefix = $banner_type . '_';
        $show_on_posts = !empty($settings[$prefix . 'show_on_posts']);
        $show_on_pages = !empty($settings[$prefix . 'show_on_pages']);
        $show_on_homepage = !empty($settings[$prefix . 'show_on_homepage']);
        $show_on_archive = !empty($settings[$prefix . 'show_on_archive']);
        
        // If no page types selected, show on all (bypass mode)
        if (!$show_on_posts && !$show_on_pages && !$show_on_homepage && !$show_on_archive) {
            signalkit_log("Display Rules ({$banner_type}): No page types selected, showing on all pages (bypass mode)");
            return true;
        }
        
        // Detect current page type
        $current_page_type = 'unknown';
        $should_show = false;
        
        if (is_front_page() || is_home()) {
            $current_page_type = 'homepage';
            $should_show = $show_on_homepage;
        } elseif (is_single()) {
            $current_page_type = 'single_post';
            $should_show = $show_on_posts;
        } elseif (is_page()) {
            $current_page_type = 'page';
            $should_show = $show_on_pages;
        } elseif (is_archive() || is_category() || is_tag() || is_tax()) {
            $current_page_type = 'archive';
            $should_show = $show_on_archive;
        } else {
            // Unknown page type - show by default
            $current_page_type = 'unknown (' . get_post_type() . ')';
            $should_show = true;
        }
        
        signalkit_log("Display Rules ({$banner_type}): Page type: {$current_page_type}, Should show: " . ($should_show ? 'YES' : 'NO') . 
                     " | Settings: [Posts={$show_on_posts}, Pages={$show_on_pages}, Home={$show_on_homepage}, Archive={$show_on_archive}]");
        
        return $should_show;
    }
    
    /**
     * Check display frequency.
     */
    private static function check_frequency($settings, $banner_type) {
        $prefix = $banner_type . '_';
        $frequency = isset($settings[$prefix . 'show_frequency']) ? $settings[$prefix . 'show_frequency'] : 'always';
        
        // Always show
        if ($frequency === 'always') {
            signalkit_log("Display Rules ({$banner_type}): Frequency check: always (passed)");
            return true;
        }
        
        $session_cookie = 'signalkit_session_' . $banner_type;
        $daily_cookie = 'signalkit_daily_' . $banner_type;
        
        if ($frequency === 'once_per_session' && isset($_COOKIE[$session_cookie])) {
            signalkit_log("Display Rules ({$banner_type}): Frequency check: once_per_session (blocked - already shown)");
            return false;
        }
        
        if ($frequency === 'once_per_day' && isset($_COOKIE[$daily_cookie])) {
            signalkit_log("Display Rules ({$banner_type}): Frequency check: once_per_day (blocked - already shown today)");
            return false;
        }
        
        signalkit_log("Display Rules ({$banner_type}): Frequency check: {$frequency} (passed)");
        return true;
    }
    
    /**
     * Set frequency cookie - FIXED: Better cookie handling
     */
    public static function set_frequency_cookie($settings, $banner_type) {
        $prefix = $banner_type . '_';
        $frequency = isset($settings[$prefix . 'show_frequency']) ? $settings[$prefix . 'show_frequency'] : 'always';
        
        if ($frequency === 'once_per_session') {
            $cookie_name = 'signalkit_session_' . $banner_type;
            if (!isset($_COOKIE[$cookie_name])) {
                $expiry = 0; // Session cookie
                setcookie($cookie_name, '1', $expiry, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
                signalkit_log("Display Rules ({$banner_type}): Set session cookie: {$cookie_name}");
            }
        }
        
        if ($frequency === 'once_per_day') {
            $cookie_name = 'signalkit_daily_' . $banner_type;
            if (!isset($_COOKIE[$cookie_name])) {
                $expiry = time() + DAY_IN_SECONDS;
                setcookie($cookie_name, '1', $expiry, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
                signalkit_log("Display Rules ({$banner_type}): Set daily cookie: {$cookie_name} (expires: " . date('Y-m-d H:i:s', $expiry) . ")");
            }
        }
    }
}