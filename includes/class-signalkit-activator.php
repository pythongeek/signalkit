<?php
/**
 * Fired during plugin activation.
 *
 * @package SignalKit
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Activator {
    
    /**
     * Activate the plugin - ENHANCED VERSION WITH NEW FIELDS.
     */
    public static function activate() {
        self::safe_log('Activator: Starting plugin activation/upgrade');
        
        // Get existing settings (in case of re-activation or upgrade)
        $existing_settings = get_option('signalkit_settings', array());
        $is_upgrade = !empty($existing_settings);
        
        // Get default settings with ALL new fields
        $default_settings = self::get_default_settings();
        
        // Merge with existing settings (preserve user data on re-activation/upgrade)
        // wp_parse_args handles adding new defaults on upgrade transparently.
        $settings = wp_parse_args($existing_settings, $default_settings);
        
        // Update or add option
        if ($is_upgrade) {
            update_option('signalkit_settings', $settings);
            self::safe_log('Activator: Updated existing settings (upgrade mode)', array(
                'old_count' => count($existing_settings),
                'new_count' => count($settings),
                'added_fields' => count($settings) - count($existing_settings)
            ));
        } else {
            add_option('signalkit_settings', $settings, '', 'yes');
            self::safe_log('Activator: Created new settings (fresh install)');
        }
        
        // Set/update version
        $old_version = get_option('signalkit_version', '0.0.0');
        $new_version = defined('SIGNALKIT_VERSION') ? SIGNALKIT_VERSION : '1.0.0';
        
        update_option('signalkit_version', $new_version);
        
        if ($old_version !== $new_version) {
            self::safe_log('Activator: Version updated', array(
                'from' => $old_version,
                'to' => $new_version
            ));
        }
        
        // Initialize or update analytics
        self::initialize_analytics();
        
        // Log final verification
        $final_settings = get_option('signalkit_settings');
        self::safe_log('Activator: Activation completed', array(
            'total_fields' => count($final_settings),
            'follow_enabled' => $final_settings['follow_enabled'] ?? 0,
            'preferred_enabled' => $final_settings['preferred_enabled'] ?? 0,
            'version' => $new_version,
            'is_upgrade' => $is_upgrade
        ));
        
        // Clear any existing cache
        wp_cache_flush();
        
        // Set activation timestamp
        if (!$is_upgrade) {
            add_option('signalkit_activated_at', current_time('mysql'), '', 'yes');
        }
        
        self::safe_log('Activator: Plugin activation completed successfully');
    }
    
    /**
     * Get default settings for both banners with ALL fields.
     *
     * @return array Complete default settings
     */
    private static function get_default_settings() {
        return array(
            // ========================================
            // GLOBAL SETTINGS
            // ========================================
            
            'site_name' => get_bloginfo('name'),
            'enable_rate_limiting' => 0, // Added per audit
            
            // ========================================
            // FOLLOW BANNER SETTINGS
            // ========================================
            
            // Basic Settings
            'follow_enabled' => 0,
            'follow_google_news_url' => '',
            
            // Content Settings
            'follow_button_text' => __('Follow Us On Google News', 'signalkit'),
            'follow_banner_headline' => __('Stay Updated with [site_name]', 'signalkit'),
            'follow_banner_description' => __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit'),
            
            // Color Settings
            'follow_primary_color' => '#4285f4',
            'follow_secondary_color' => '#ffffff',
            'follow_accent_color' => '#34a853',
            'follow_text_color' => '#1a1a1a',
            
            // Size Settings - NEW
            'follow_banner_size' => 'standard',
            
            // Position & Animation
            'follow_position' => 'bottom_left',
            'follow_animation' => 'slide_in',
            
            // Dismissal Settings
            'follow_dismissible' => 1,
            'follow_dismiss_duration' => 7,
            
            // Display Settings
            'follow_show_frequency' => 'once_per_day',
            'follow_mobile_enabled' => 1,
            'follow_desktop_enabled' => 1,
            
            // Page Type Settings
            'follow_show_on_posts' => 1,
            'follow_show_on_pages' => 1,
            'follow_show_on_homepage' => 1,
            'follow_show_on_archive' => 0,
            
            // ========================================
            // PREFERRED SOURCE BANNER SETTINGS
            // ========================================
            
            // Basic Settings
            'preferred_enabled' => 0,
            'preferred_google_preferences_url' => '',
            'preferred_educational_post_url' => '',
            
            // Content Settings
            'preferred_button_text' => __('Add As A Preferred Source', 'signalkit'),
            'preferred_banner_headline' => __('Add [site_name] As A Trusted Source', 'signalkit'),
            'preferred_banner_description' => __('Get priority access to our news and updates in your Google News feed.', 'signalkit'),
            'preferred_educational_text' => __('Learn More', 'signalkit'),
            'preferred_show_educational_link' => 1,
            
            // Color Settings
            'preferred_primary_color' => '#4285f4',
            'preferred_secondary_color' => '#ffffff',
            'preferred_accent_color' => '#ea4335',
            'preferred_text_color' => '#1a1a1a',
            
            // Size Settings - NEW
            'preferred_banner_size' => 'standard',
            
            // Position & Animation
            'preferred_position' => 'bottom_right',
            'preferred_animation' => 'slide_in',
            
            // Dismissal Settings
            'preferred_dismissible' => 1,
            'preferred_dismiss_duration' => 7,
            
            // Display Settings
            'preferred_show_frequency' => 'once_per_day',
            'preferred_mobile_enabled' => 1,
            'preferred_desktop_enabled' => 1,
            
            // Page Type Settings
            'preferred_show_on_posts' => 1,
            'preferred_show_on_pages' => 1,
            'preferred_show_on_homepage' => 1,
            'preferred_show_on_archive' => 0,
        );
    }
    
    /**
     * Initialize analytics for both banners.
     */
    private static function initialize_analytics() {
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
        } else {
            $updated = false;
            
            if (!isset($existing_analytics['follow'])) {
                $existing_analytics['follow'] = array(
                    'impressions' => 0,
                    'clicks' => 0,
                    'dismissals' => 0,
                    'first_seen' => current_time('mysql'),
                    'last_updated' => current_time('mysql'),
                );
                $updated = true;
            }
            
            if (!isset($existing_analytics['preferred'])) {
                $existing_analytics['preferred'] = array(
                    'impressions' => 0,
                    'clicks' => 0,
                    'dismissals' => 0,
                    'first_seen' => current_time('mysql'),
                    'last_updated' => current_time('mysql'),
                );
                $updated = true;
            }
            
            if ($updated) {
                update_option('signalkit_analytics', $existing_analytics);
                self::safe_log('Activator: Analytics updated with missing banner types');
            }
        }
    }
    
    /**
     * Safe logging function.
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
    
    /**
     * Check system requirements.
     */
    public static function check_requirements() {
        global $wp_version;
        
        if (version_compare($wp_version, '5.0', '<')) {
            return new WP_Error(
                'wp_version_too_low',
                sprintf(
                    __('SignalKit for Google requires WordPress 5.0 or higher. You are running version %s.', 'signalkit'),
                    $wp_version
                )
            );
        }
        
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            return new WP_Error(
                'php_version_too_low',
                sprintf(
                    __('SignalKit for Google requires PHP 7.2 or higher. You are running version %s.', 'signalkit'),
                    PHP_VERSION
                )
            );
        }
        
        return true;
    }
}