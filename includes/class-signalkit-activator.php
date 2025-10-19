<?php
/**
 * Fired during plugin activation.
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Activator {
    
    /**
     * Activate the plugin.
     */
    public static function activate() {
        // Safe logging that checks if function exists
        self::safe_log('Activator: Setting up default options');
        
        // Set default options for BOTH banners
        $default_settings = array(
            // Global Settings
            'site_name' => get_bloginfo('name'),
            
            // Google News Follow Banner Settings
            'follow_enabled' => true,
            'follow_google_news_url' => '',
            'follow_button_text' => __('Follow Us On Google News', 'signalkit-for-google'),
            'follow_banner_headline' => __('Stay Updated with [site_name]', 'signalkit-for-google'),
            'follow_banner_description' => __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit-for-google'),
            'follow_primary_color' => '#4285f4',
            'follow_secondary_color' => '#ffffff',
            'follow_accent_color' => '#34a853',
            'follow_position' => 'bottom_left',
            'follow_show_on' => 'all',
            'follow_animation' => 'slide_in',
            'follow_dismissible' => true,
            'follow_dismiss_duration' => 7,
            'follow_show_frequency' => 'once_per_day',
            'follow_mobile_enabled' => true,
            'follow_desktop_enabled' => true,
            'follow_show_on_posts' => true,
            'follow_show_on_pages' => true,
            'follow_show_on_homepage' => true,
            'follow_show_on_archive' => false,
            
            // Preferred Source Banner Settings
            'preferred_enabled' => true,
            'preferred_google_preferences_url' => '',
            'preferred_educational_post_url' => '',
            'preferred_button_text' => __('Add As A Preferred Source On Google', 'signalkit-for-google'),
            'preferred_banner_headline' => __('Tap Here To Add "[site_name]" As A Trusted Source', 'signalkit-for-google'),
            'preferred_banner_description' => __('Get priority access to our news and updates in your Google News feed.', 'signalkit-for-google'),
            'preferred_educational_text' => __('Want To Know More on Google preferred sources?', 'signalkit-for-google'),
            'preferred_primary_color' => '#4285f4',
            'preferred_secondary_color' => '#ffffff',
            'preferred_accent_color' => '#ea4335',
            'preferred_position' => 'bottom_right',
            'preferred_show_on' => 'all',
            'preferred_animation' => 'slide_in',
            'preferred_dismissible' => true,
            'preferred_dismiss_duration' => 7,
            'preferred_show_frequency' => 'once_per_day',
            'preferred_mobile_enabled' => true,
            'preferred_desktop_enabled' => true,
            'preferred_show_on_posts' => true,
            'preferred_show_on_pages' => true,
            'preferred_show_on_homepage' => true,
            'preferred_show_on_archive' => false,
            'preferred_show_educational_link' => true,
        );
        
        add_option('signalkit_settings', $default_settings);
        
        // Safe version constant usage
        $version = defined('SIGNALKIT_VERSION') ? SIGNALKIT_VERSION : '1.0.0';
        add_option('signalkit_version', $version);
        
        // Initialize analytics for BOTH banners
        $analytics_data = array(
            'follow' => array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
                'first_seen' => current_time('mysql'),
                'last_updated' => current_time('mysql'),
            ),
            'preferred' => array(
                'impressions' => 0,
                'clicks' => 0,
                'dismissals' => 0,
                'first_seen' => current_time('mysql'),
                'last_updated' => current_time('mysql'),
            ),
        );
        
        add_option('signalkit_analytics', $analytics_data);
        
        self::safe_log('Activator: Default options set successfully for both banners', $default_settings);
        
        // Clear any existing cache
        wp_cache_flush();
        
        self::safe_log('Activator: Cache flushed');
    }
    
    /**
     * Safe logging function that checks if the main log function exists
     */
    private static function safe_log($message, $data = null) {
        if (function_exists('signalkit_log')) {
            signalkit_log($message, $data);
        } elseif (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log('SignalKit: ' . $message);
            if ($data !== null) {
                error_log('SignalKit Data: ' . print_r($data, true));
            }
        }
    }
}