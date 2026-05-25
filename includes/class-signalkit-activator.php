<?php
/**
 * Fired during plugin activation.
 *
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Activator {
    
    /**
     * Activate the plugin - ENHANCED VERSION WITH NEW FIELDS.
     */
    public static function activate() {
        // Multisite support: activate for all blogs if network-activated
        if (is_multisite() && !empty($_GET['networkwide'])) {
            self::activate_multisite();
            return;
        }
        
        self::activate_single_site();
    }
    
    /**
     * Activate for a single site (or current blog in multisite).
     */
    private static function activate_single_site($blog_id = null) {
        if ($blog_id) {
            switch_to_blog($blog_id);
        }
        
        // Get existing settings (in case of re-activation or upgrade)
        $existing_settings = get_option('signalkit_settings', array());
        $is_upgrade = !empty($existing_settings);
        
        // Get default settings with ALL new fields
        $default_settings = self::get_default_settings();
        
        // Merge with existing settings (preserve user data on re-activation/upgrade)
        $settings = wp_parse_args($existing_settings, $default_settings);
        
        // Update or add option
        if ($is_upgrade) {
            update_option('signalkit_settings', $settings);
        } else {
            add_option('signalkit_settings', $settings, '', 'yes');
        }
        
        // Set/update version
        $new_version = defined('SIGNALKIT_VERSION') ? SIGNALKIT_VERSION : '1.0.0';
        update_option('signalkit_version', $new_version);
        
        // Initialize or update analytics
        self::initialize_analytics();
        
        // Create custom banner submissions table
        self::create_submissions_table();
        
        // Clear any existing cache
        wp_cache_flush();
        
        // Set activation timestamp
        if (!$is_upgrade) {
            add_option('signalkit_activated_at', current_time('mysql'), '', 'yes');
        }
        
        if ($blog_id) {
            restore_current_blog();
        }
    }
    
    /**
     * Activate across all sites in a multisite network.
     */
    private static function activate_multisite() {
        global $wpdb;
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
        foreach ($blog_ids as $blog_id) {
            self::activate_single_site($blog_id);
        }
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
            'enable_rate_limiting' => 0,
            'enable_csp' => 0,
            'enable_google_fonts' => 0,
            'analytics_tracking' => 1,
            'import_export_key' => '',
            
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
            // NEW: Button Colors
            'follow_button_text_color' => '#ffffff',
            'follow_button_bg_color' => '#4285f4',
            
            // NEW: Gradient Settings
            'follow_gradient_start' => '#4285f4',
            'follow_gradient_end' => '#34a853',
            'follow_gradient_angle' => 135,
            'follow_border_color' => '',
            
            // Size Settings - Enhanced
            'follow_banner_width' => 380,
            'follow_banner_padding' => 20,
            'follow_border_radius' => 16,
            'follow_icon_size' => 52,
            'follow_font_size_headline' => 16,
            'follow_font_size_description' => 14,
            'follow_font_size_button' => 14,
            
            // NEW: Style Presets
            'follow_banner_style' => 'glassmorphism',
            'follow_button_style' => 'default',
            'follow_icon_style' => 'circle',
            
            // NEW: Visibility & Effects
            'follow_visibility_mode' => 'auto',
            'follow_enable_glow' => 0,
            'follow_enable_float' => 0,
            'follow_glow_intensity' => 20,
            'follow_backdrop_blur' => 12,
            'follow_backdrop_opacity' => 95,
            
            // Position & Animation
            'follow_position' => 'bottom_left',
            'follow_mobile_position' => 'bottom',
            'follow_mobile_stack_order' => 1,
            'follow_animation' => 'slide_in',
            
            // Dismissal Settings
            'follow_dismissible' => 1,
            'follow_close_button_size' => 28,
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
            'preferred_primary_color' => '#ea4335',
            'preferred_secondary_color' => '#ffffff',
            'preferred_accent_color' => '#fbbc04',
            'preferred_text_color' => '#1a1a1a',
            // NEW: Button Colors
            'preferred_button_text_color' => '#ffffff',
            'preferred_button_bg_color' => '#ea4335',
            
            // NEW: Gradient Settings
            'preferred_gradient_start' => '#ea4335',
            'preferred_gradient_end' => '#fbbc04',
            'preferred_gradient_angle' => 135,
            'preferred_border_color' => '',
            
            // Size Settings - Enhanced
            'preferred_banner_width' => 380,
            'preferred_banner_padding' => 24,
            'preferred_border_radius' => 12,
            'preferred_icon_size' => 52,
            'preferred_font_size_headline' => 16,
            'preferred_font_size_description' => 14,
            'preferred_font_size_button' => 13,
            
            // NEW: Style Presets
            'preferred_banner_style' => 'preferred-star',
            'preferred_button_style' => 'default',
            'preferred_icon_style' => 'circle',
            
            // NEW: Visibility & Effects
            'preferred_visibility_mode' => 'auto',
            'preferred_enable_glow' => 0,
            'preferred_enable_float' => 0,
            'preferred_glow_intensity' => 20,
            'preferred_backdrop_blur' => 12,
            'preferred_backdrop_opacity' => 95,
            
            // Position & Animation
            'preferred_position' => 'bottom_right',
            'preferred_mobile_position' => 'bottom',
            'preferred_mobile_stack_order' => 2,
            'preferred_animation' => 'slide_in',
            
            // Dismissal Settings
            'preferred_dismissible' => 1,
            'preferred_close_button_size' => 28,
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
                    /* translators: %s: WordPress version */
                    __('SignalKit for Google requires WordPress 5.0 or higher. You are running version %s.', 'signalkit'),
                    $wp_version
                )
            );
        }
        
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            return new WP_Error(
                'php_version_too_low',
                sprintf(
                    /* translators: %s: PHP version */
                    __('SignalKit for Google requires PHP 7.2 or higher. You are running version %s.', 'signalkit'),
                    PHP_VERSION
                )
            );
        }
        
        return true;
    }
    
    /**
     * Create submissions table for custom banner
     * Called during plugin activation only
     */
    private static function create_submissions_table() {
        global $wpdb;
        
        $table_name = esc_sql($wpdb->prefix . 'signalkit_submissions');
        $charset_collate = $wpdb->get_charset_collate();
        
        // Atomic CREATE TABLE with IF NOT EXISTS (eliminates race condition)
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names cannot be prepared, esc_sql is required
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            name varchar(255) DEFAULT '',
            banner_type varchar(50) DEFAULT 'newsletter',
            page_url varchar(500) DEFAULT '',
            ip_address varchar(45) DEFAULT '',
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY banner_type (banner_type),
            KEY submitted_at (submitted_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}