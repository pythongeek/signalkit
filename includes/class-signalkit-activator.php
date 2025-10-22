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
     * Activate the plugin - PRODUCTION READY VERSION.
     */
    public static function activate() {
        self::safe_log('Activator: Starting plugin activation');
        
        // Get existing settings (in case of re-activation)
        $existing_settings = get_option('signalkit_settings', array());
        
        // Set default options for BOTH banners
        $default_settings = array(
            // Global Settings
            'site_name' => get_bloginfo('name'),
            
            // Google News Follow Banner Settings
            'follow_enabled' => 0, // Disabled by default until configured
            'follow_google_news_url' => '',
            'follow_button_text' => __('Follow Us On Google News', 'signalkit-for-google'),
            'follow_banner_headline' => __('Stay Updated with [site_name]', 'signalkit-for-google'),
            'follow_banner_description' => __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit-for-google'),
            'follow_primary_color' => '#4285f4',
            'follow_secondary_color' => '#ffffff',
            'follow_accent_color' => '#34a853',
            'follow_position' => 'bottom_left',
            'follow_animation' => 'slide_in',
            'follow_dismissible' => 1,
            'follow_dismiss_duration' => 7,
            'follow_show_frequency' => 'always', // Changed to 'always' for testing
            'follow_mobile_enabled' => 1,
            'follow_desktop_enabled' => 1,
            'follow_show_on_posts' => 1,
            'follow_show_on_pages' => 1,
            'follow_show_on_homepage' => 1,
            'follow_show_on_archive' => 0,
            
            // Preferred Source Banner Settings
            'preferred_enabled' => 0, // Disabled by default until configured
            'preferred_google_preferences_url' => '',
            'preferred_educational_post_url' => '',
            'preferred_button_text' => __('Add As A Preferred Source On Google', 'signalkit-for-google'),
            'preferred_banner_headline' => __('Tap Here To Add "[site_name]" As A Trusted Source', 'signalkit-for-google'),
            'preferred_banner_description' => __('Get priority access to our news and updates in your Google News feed.', 'signalkit-for-google'),
            'preferred_educational_text' => __('Want To Know More on Google preferred sources?', 'signalkit-for-google'),
            'preferred_show_educational_link' => 1,
            'preferred_primary_color' => '#4285f4',
            'preferred_secondary_color' => '#ffffff',
            'preferred_accent_color' => '#ea4335',
            'preferred_position' => 'bottom_right',
            'preferred_animation' => 'slide_in',
            'preferred_dismissible' => 1,
            'preferred_dismiss_duration' => 7,
            'preferred_show_frequency' => 'always', // Changed to 'always' for testing
            'preferred_mobile_enabled' => 1,
            'preferred_desktop_enabled' => 1,
            'preferred_show_on_posts' => 1,
            'preferred_show_on_pages' => 1,
            'preferred_show_on_homepage' => 1,
            'preferred_show_on_archive' => 0,
        );
        
        // Merge with existing settings (preserve user data on re-activation)
        $settings = wp_parse_args($existing_settings, $default_settings);
        
        // Update or add option
        if (empty($existing_settings)) {
            add_option('signalkit_settings', $settings, '', 'yes');
            self::safe_log('Activator: Created new settings');
        } else {
            update_option('signalkit_settings', $settings);
            self::safe_log('Activator: Updated existing settings');
        }
        
        // Set version
        $version = defined('SIGNALKIT_VERSION') ? SIGNALKIT_VERSION : '1.0.0';
        update_option('signalkit_version', $version);
        
        // Initialize analytics for BOTH banners if not exists
        $existing_analytics = get_option('signalkit_analytics', array());
        
        if (empty($existing_analytics)) {
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
            
            add_option('signalkit_analytics', $analytics_data, '', 'yes');
            self::safe_log('Activator: Analytics initialized');
        }
        
        // Log final settings for verification
        $final_settings = get_option('signalkit_settings');
        self::safe_log('Activator: Final settings count = ' . count($final_settings));
        self::safe_log('Activator: Follow enabled = ' . ($final_settings['follow_enabled'] ?? 'not set'));
        self::safe_log('Activator: Preferred enabled = ' . ($final_settings['preferred_enabled'] ?? 'not set'));
        
        // Clear any existing cache
        wp_cache_flush();
        
        self::safe_log('Activator: Plugin activation completed successfully');
    }
    
    /**
     * Safe logging function that checks if the main log function exists
     */
    private static function safe_log($message, $data = null) {
        if (function_exists('signalkit_log')) {
            signalkit_log($message, $data);
        } elseif (defined('WP_DEBUG') && WP_DEBUG === true) {
            $timestamp = current_time('d-M-Y H:i:s T');
            error_log('[' . $timestamp . '] [SignalKit] ' . $message);
            if ($data !== null) {
                error_log('[' . $timestamp . '] [SignalKit] Data: ' . print_r($data, true));
            }
        }
    }
}