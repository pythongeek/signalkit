<?php
/**
 * Handle display rules and conditional logic.
 * Version: 2.0.1 - SECURITY HARDENED: Enhanced cookie security with SameSite
 *
 * @package SignalKit
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Display_Rules {
    
    /**
     * Check if banner should be displayed based on rules.
     * * @param string $banner_type 'follow' or 'preferred'
     */
    public static function should_display($banner_type = 'follow') {
        $settings = get_option('signalkit_settings', array());
        $prefix = $banner_type . '_';
        
        // DEBUG: Force display if param present (Bypass all rules)
        // SECURITY: Only available when WP_DEBUG is enabled (Envato compliant)
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['signalkit_test']) && sanitize_text_field(wp_unslash($_GET['signalkit_test'])) === '1') {
                return true;
            }
        }
        
        // CRITICAL: Check if banner type is enabled
        if (empty($settings[$prefix . 'enabled'])) {
            return false;
        }
        
        // CACHE COMPATIBILITY UPDATE:
        // We removed server-side cookie checks (is_dismissed, check_frequency) 
        // to ensure the banner HTML is always cached correctly.
        // The display logic is now handled 100% by JavaScript in the browser.
        

        
        // Check device type (Keep in PHP as it's User-Agent based and usually cache-compatible or handled by separate mobile cache)
        // However, for strict caching, it's safer to handle in JS or keep mobile specific cache.
        // We'll keep this one check in PHP for performance, but if you use aggressive caching, 
        // you might want to move this to JS too. For now, it's fine.
        if (!self::check_device_compatibility($settings, $banner_type)) {
            return false;
        }
        
        // Check page rules (Always safe in PHP as URL doesn't change for cached page)
        if (!self::check_page_rules($settings, $banner_type)) {
            return false;
        }
        
        /*
        // Moved to JS: Check frequency
        if (!self::check_frequency($settings, $banner_type)) {
            return false;
        }
        */
        
        // NEW: Alternating logic if both follow/preferred banners enabled and on single post
        // Custom banner is excluded from this alternating logic
        if (in_array($banner_type, array('follow', 'preferred')) && is_single() && !empty($settings['follow_enabled']) && !empty($settings['preferred_enabled'])) {
            $post_id = get_the_ID();
            $show_follow = ($post_id % 2 === 0);
            $alternate_result = ($banner_type === 'follow') ? $show_follow : !$show_follow;
            return $alternate_result;
        }
        
        return true;
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
            return true;
        }
        
        // Detect current page type
        $should_show = false;
        
        if (is_front_page() || is_home()) {
            $should_show = $show_on_homepage;
        } elseif (is_single()) {
            $should_show = $show_on_posts;
        } elseif (is_page()) {
            $should_show = $show_on_pages;
        } elseif (is_archive() || is_category() || is_tag() || is_tax()) {
            $should_show = $show_on_archive;
        } else {
            // Unknown page type - show by default
            $should_show = true;
        }
        
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
     * Set frequency cookie
     * SECURITY HARDENED: SameSite attribute, proper secure flag
     */
    public static function set_frequency_cookie($settings, $banner_type) {
        $prefix = $banner_type . '_';
        $frequency = isset($settings[$prefix . 'show_frequency']) ? $settings[$prefix . 'show_frequency'] : 'always';
        
        if ($frequency === 'once_per_session') {
            $cookie_name = 'signalkit_session_' . $banner_type;
            if (!isset($_COOKIE[$cookie_name])) {
                $expiry = 0; // Session cookie
                self::set_secure_cookie($cookie_name, '1', $expiry);
            }
        }
        
        if ($frequency === 'once_per_day') {
            $cookie_name = 'signalkit_daily_' . $banner_type;
            if (!isset($_COOKIE[$cookie_name])) {
                $expiry = time() + DAY_IN_SECONDS;
                self::set_secure_cookie($cookie_name, '1', $expiry);
            }
        }
    }
    
    /**
     * Set secure cookie with SameSite attribute
     * SECURITY: Prevents CSRF attacks, ensures secure transmission
     * * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $expiry Expiry timestamp (0 for session)
     */
    private static function set_secure_cookie($name, $value, $expiry = 0) {
        // Stop if headers already sent to prevent errors
        if (headers_sent()) return;

        // PHP 7.3+ supports array options
        if (PHP_VERSION_ID >= 70300) {
            setcookie($name, $value, array(
                'expires'  => $expiry,
                'path'     => COOKIEPATH,
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax'
            ));
        } else {
            // Fallback for PHP < 7.3 - manually construct header with SameSite
            $cookie_string = sprintf(
                '%s=%s; expires=%s; path=%s; domain=%s%s%s; SameSite=Lax',
                $name,
                $value,
                $expiry > 0 ? gmdate('D, d-M-Y H:i:s T', $expiry) : '',
                COOKIEPATH,
                COOKIE_DOMAIN,
                is_ssl() ? '; Secure' : '',
                '; HttpOnly'
            );
            header('Set-Cookie: ' . $cookie_string, false);
        }
    }
    
    /**
     * Delete cookie securely
     * * @param string $name Cookie name
     */
    private static function delete_cookie($name) {
        if (headers_sent()) return;

        if (PHP_VERSION_ID >= 70300) {
            setcookie($name, '', array(
                'expires'  => time() - 3600,
                'path'     => COOKIEPATH,
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax'
            ));
        } else {
            setcookie($name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        }
        unset($_COOKIE[$name]);
    }
}