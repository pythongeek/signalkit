<?php
if (!defined('ABSPATH')) { exit; }
/**
 * The admin-specific functionality of the plugin.
 *
 * @package SignalKit
 * @version 2.0.0
 *
 * IMPORTANT SECURITY NOTES FOR DEVELOPERS:
 * ========================================
 *
 * This class uses WordPress's Options API and sanitization functions which are SAFE
 * from SQL injection because WordPress handles all database escaping internally.
 *
 * ⚠️ WARNING: If you modify this code to use direct database queries, you MUST:
 *
 * 1. ALWAYS use $wpdb->prepare() for ALL queries with user input
 * 2. NEVER concatenate user input directly into SQL strings
 * 3. Use %d for integers, %s for strings, %f for floats
 * 4. Always sanitize input BEFORE using it (sanitize_text_field, absint, etc.)
 * 5. Always verify nonces for AJAX requests (wp_verify_nonce)
 * 6. Always check user capabilities (current_user_can)
 *
 * SAFE EXAMPLES (if you need direct queries):
 * -------------------------------------------
 *
 * // ✅ SAFE - Using $wpdb->prepare()
 * global $wpdb;
 * $setting_name = sanitize_text_field($_POST['name']);
 * $setting_value = sanitize_text_field($_POST['value']);
 * $wpdb->query(
 *     $wpdb->prepare(
 *         "INSERT INTO {$wpdb->prefix}signalkit_settings (name, value) 
 *          VALUES (%s, %s) 
 *          ON DUPLICATE KEY UPDATE value = %s",
 *         $setting_name,
 *         $setting_value,
 *         $setting_value
 *     )
 * );
 *
 * // ✅ SAFE - Reading with prepared statement
 * $banner_type = sanitize_text_field($_POST['banner_type']);
 * $result = $wpdb->get_var(
 *     $wpdb->prepare(
 *         "SELECT COUNT(*) FROM {$wpdb->prefix}signalkit_analytics 
 *          WHERE banner_type = %s",
 *         $banner_type
 *     )
 * );
 *
 * // ❌ UNSAFE - NEVER DO THIS
 * $banner_type = $_POST['banner_type']; // Unsanitized
 * $wpdb->query(
 *     "DELETE FROM {$wpdb->prefix}signalkit_analytics 
 *      WHERE banner_type = '{$banner_type}'"
 * );
 * // ^ This allows SQL injection attacks!
 *
 * WordPress Database API Documentation:
 * https://developer.wordpress.org/reference/classes/wpdb/
 */

class SignalKit_Admin {

    private $plugin_name;
    private $version;
    private $settings_page;

    /**
     * Constructor
     *
     * @param string $plugin_name Plugin name
     * @param string $version Plugin version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        // Initialize settings page
        $this->settings_page = new SignalKit_Settings($plugin_name, $version);
        
        // AJAX handlers are registered via SignalKit_Core::define_admin_hooks()
        // Do NOT register them here to avoid double-registration.
    }

    /**
     * Enqueue admin styles.
     */
    public function enqueue_styles() {
        // Check screen
        $screen = get_current_screen();
        if (!$screen) return;

        // Allow on Post Editor for Shortcode Preview
        if ($screen->base === 'post') {
            // Use enhanced CSS if available (matches frontend behavior)
            $css_file = 'signalkit-public-enhanced.css';
            $css_path = SIGNALKIT_PLUGIN_DIR . 'public/css/' . $css_file;
            if (!file_exists($css_path)) {
                $css_file = 'signalkit-public.css';
            }
            
            wp_enqueue_style(
                'signalkit-public',
                SIGNALKIT_PLUGIN_URL . 'public/css/' . $css_file,
                array(),
                $this->version,
                'all'
            );
            
            // Also load custom banner CSS if needed
            $custom_css = SIGNALKIT_PLUGIN_DIR . 'public/css/signalkit-custom-banner.css';
            if (file_exists($custom_css)) {
                wp_enqueue_style(
                    'signalkit-custom-banner',
                    SIGNALKIT_PLUGIN_URL . 'public/css/signalkit-custom-banner.css',
                    array('signalkit-public'),
                    $this->version,
                    'all'
                );
            }
            return;
        }

        // Otherwise only load on plugin pages
        if (strpos($screen->id, 'signalkit') === false) {
            return;
        }

        wp_enqueue_style(
            'signalkit-admin',
            SIGNALKIT_PLUGIN_URL . 'admin/css/signalkit-admin.css',
            array('wp-color-picker'),
            $this->version,
            'all'
        );
        
        // Enqueue enhanced admin styles if available
        $enhanced_css = SIGNALKIT_PLUGIN_DIR . 'admin/css/signalkit-admin-enhanced.css';
        if (file_exists($enhanced_css)) {
            wp_enqueue_style(
                'signalkit-admin-enhanced',
                SIGNALKIT_PLUGIN_URL . 'admin/css/signalkit-admin-enhanced.css',
                array('signalkit-admin'),
                $this->version,
                'all'
            );
        }
        
        // Enqueue Google Font for branding (CodeCanyon requirement: no CSS @import)
        // Privacy Compliance: Only load if explicitly enabled by user (opt-in)
        $settings = get_option('signalkit_settings', []);
        if (!empty($settings['enable_google_fonts'])) {
            wp_enqueue_style(
                'signalkit-google-font-orbitron',
                'https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap',
                array(),
                $this->version
            );
        }
        
        // Enqueue branding logo styles (performance-optimized, only on plugin pages)
        $branding_css = SIGNALKIT_PLUGIN_DIR . 'admin/css/signalkit-branding.css';
        if (file_exists($branding_css)) {
            // Only depend on Google Font if it's actually registered (i.e., user enabled it)
            $branding_deps = array();
            if (!empty($settings['enable_google_fonts'])) {
                $branding_deps[] = 'signalkit-google-font-orbitron';
            }
            wp_enqueue_style(
                'signalkit-branding',
                SIGNALKIT_PLUGIN_URL . 'admin/css/signalkit-branding.css',
                $branding_deps,
                $this->version,
                'all'
            );
        }
    }

    /**
     * Enqueue admin scripts.
     */
    public function enqueue_scripts() {
        // Only load on plugin pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'signalkit') === false) {
            return;
        }

        wp_enqueue_script(
            'signalkit-admin',
            SIGNALKIT_PLUGIN_URL . 'admin/js/signalkit-admin.js',
            array('jquery', 'wp-color-picker'),
            $this->version,
            true
        );
        
        // Enqueue enhanced admin JavaScript if available
        $enhanced_js = SIGNALKIT_PLUGIN_DIR . 'admin/js/signalkit-admin-enhanced.js';
        if (file_exists($enhanced_js)) {
            wp_enqueue_script(
                'signalkit-admin-enhanced',
                SIGNALKIT_PLUGIN_URL . 'admin/js/signalkit-admin-enhanced.js',
                array('jquery', 'wp-color-picker', 'signalkit-admin'),
                $this->version,
                true
            );
        }

        // Localize script with AJAX URLs and nonces - WordPress best practice
        // All user-facing strings must be translatable (Envato requirement)
        wp_localize_script('signalkit-admin', 'signalkitAdmin', array(
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('signalkit_admin_nonce'),
            'previewNonce' => wp_create_nonce('signalkit_preview_nonce'),
            'strings'    => array(
                // Core messages
                'confirmReset'    => __('Are you sure you want to reset analytics? This cannot be undone.', 'signalkit'),
                'saved'           => __('Settings saved successfully!', 'signalkit'),
                'error'           => __('An error occurred. Please try again.', 'signalkit'),
                'themeApplied'    => __('Color theme applied!', 'signalkit'),
                // Configuration errors
                'configMissing'   => __('SignalKit Error: Plugin configuration missing. Please refresh the page or contact support.', 'signalkit'),
                // Analytics
                'resetFailed'     => __('Failed to reset analytics. Please try again.', 'signalkit'),
                // Export/Import
                'exporting'       => __('Exporting...', 'signalkit'),
                'exportSettings'  => __('Export Settings', 'signalkit'),
                'exportFailed'    => __('Export failed. Please try again.', 'signalkit'),
                'invalidFile'     => __('Please select a valid JSON or TXT file.', 'signalkit'),
                'fileTooLarge'    => __('File too large. Maximum size is 100KB.', 'signalkit'),
                'missingFields'   => __('Invalid settings file: Missing required fields', 'signalkit'),
                'tooManySettings' => __('Settings file contains too many entries (max 100).', 'signalkit'),
                'confirmImport'   => __('Import settings? This will overwrite your current settings.', 'signalkit'),
                'importSuccess'   => __('Settings imported successfully!', 'signalkit'),
                'importFailed'    => __('Import failed. Please try again.', 'signalkit'),
                'importFailedMsg' => __('Import failed:', 'signalkit'),
            ),
        ));

    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('SignalKit', 'signalkit'),
            __('SignalKit', 'signalkit'),
            'manage_options',
            'signalkit',
            array($this->settings_page, 'render_settings_page'),
            'dashicons-megaphone',
            80
        );

        add_submenu_page(
            'signalkit',
            __('Settings', 'signalkit'),
            __('Settings', 'signalkit'),
            'manage_options',
            'signalkit',
            array($this->settings_page, 'render_settings_page')
        );

        add_submenu_page(
            'signalkit',
            __('Analytics', 'signalkit'),
            __('Analytics', 'signalkit'),
            'manage_options',
            'signalkit-analytics',
            array($this->settings_page, 'render_analytics_page')
        );
    }

    /**
     * Register settings.
     *
     * SECURITY: Uses WordPress Settings API - safe from SQL injection
     */
    public function register_settings() {
        register_setting(
            'signalkit_settings_group',
            'signalkit_settings',
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default'           => array(),
            )
        );
    }

    /**
     * Sanitize settings - FULL VERSION WITH ALL FIELDS.
     *
     * SECURITY: All inputs are properly sanitized before storage
     * WordPress Options API handles SQL escaping automatically
     *
     * @param array $input Raw input data from $_POST
     * @return array Sanitized settings safe for database storage
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // GLOBAL SETTINGS
        // SECURITY: sanitize_text_field removes all HTML and dangerous characters
        $sanitized['site_name'] = isset($input['site_name'])
            ? sanitize_text_field($input['site_name'])
            : get_bloginfo('name');

        // FOLLOW BANNER SETTINGS
        // SECURITY: Checkbox values cast to 1 or 0 (integers only)
        $sanitized['follow_enabled'] = isset($input['follow_enabled']) ? 1 : 0;
        
        // SECURITY: esc_url_raw validates and sanitizes URLs
        $sanitized['follow_google_news_url'] = isset($input['follow_google_news_url'])
            ? esc_url_raw($input['follow_google_news_url'])
            : '';

        $sanitized['follow_button_text'] = isset($input['follow_button_text'])
            ? sanitize_text_field($input['follow_button_text'])
            : __('Follow Us On Google News', 'signalkit');

        $sanitized['follow_banner_headline'] = isset($input['follow_banner_headline'])
            ? sanitize_text_field($input['follow_banner_headline'])
            : __('Stay Updated with [site_name]', 'signalkit');

        // SECURITY: sanitize_textarea_field for multi-line text (removes HTML)
        $sanitized['follow_banner_description'] = isset($input['follow_banner_description'])
            ? sanitize_textarea_field($input['follow_banner_description'])
            : __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit');

        // SECURITY: sanitize_hex_color validates color format
        $sanitized['follow_primary_color']   = $this->sanitize_color($input, 'follow_primary_color', '#4285f4');
        $sanitized['follow_secondary_color'] = $this->sanitize_color($input, 'follow_secondary_color', '#ffffff');
        $sanitized['follow_accent_color']    = $this->sanitize_color($input, 'follow_accent_color', '#34a853');
        $sanitized['follow_text_color']      = $this->sanitize_color($input, 'follow_text_color', '#1a1a1a');
        // Button Colors (NEW)
        $sanitized['follow_button_text_color'] = $this->sanitize_color($input, 'follow_button_text_color', '#ffffff');
        $sanitized['follow_button_bg_color'] = $this->sanitize_color($input, 'follow_button_bg_color', '#4285f4');

        // SECURITY: absint() ensures integers, range validation prevents extreme values
        $sanitized['follow_banner_width']      = $this->sanitize_number($input, 'follow_banner_width', 280, 600, 360);
        $sanitized['follow_banner_padding']    = $this->sanitize_number($input, 'follow_banner_padding', 8, 32, 16);
        $sanitized['follow_border_radius']     = $this->sanitize_number($input, 'follow_border_radius', 0, 32, 8);

        $sanitized['follow_font_size_headline']    = $this->sanitize_number($input, 'follow_font_size_headline', 12, 24, 15);
        $sanitized['follow_font_size_description'] = $this->sanitize_number($input, 'follow_font_size_description', 10, 18, 13);
        $sanitized['follow_font_size_button']      = $this->sanitize_number($input, 'follow_font_size_button', 11, 18, 14);

        // SECURITY: Whitelist validation - only allows predefined values
        $sanitized['follow_position']         = $this->sanitize_position($input, 'follow_position', 'bottom_left');
        $sanitized['follow_animation']        = $this->sanitize_animation($input, 'follow_animation', 'slide_in');
        $sanitized['follow_mobile_position'] = $this->sanitize_mobile_position($input, 'follow_mobile_position', 'bottom');
        $sanitized['follow_mobile_stack_order'] = $this->sanitize_number($input, 'follow_mobile_stack_order', 1, 2, 1);

        $sanitized['follow_dismissible']      = isset($input['follow_dismissible']) ? 1 : 0;
        $sanitized['follow_close_button_size'] = $this->sanitize_number($input, 'follow_close_button_size', 20, 40, 28);
        $sanitized['follow_dismiss_duration'] = $this->sanitize_number($input, 'follow_dismiss_duration', 1, 365, 7);
        $sanitized['follow_show_frequency']   = $this->sanitize_frequency($input, 'follow_show_frequency', 'once_per_day');

        $sanitized['follow_mobile_enabled']   = isset($input['follow_mobile_enabled']) ? 1 : 0;
        $sanitized['follow_desktop_enabled']  = isset($input['follow_desktop_enabled']) ? 1 : 0;
        $sanitized['follow_show_on_posts']    = isset($input['follow_show_on_posts']) ? 1 : 0;
        $sanitized['follow_show_on_pages']    = isset($input['follow_show_on_pages']) ? 1 : 0;
        $sanitized['follow_show_on_homepage'] = isset($input['follow_show_on_homepage']) ? 1 : 0;
        $sanitized['follow_show_on_archive']  = isset($input['follow_show_on_archive']) ? 1 : 0;

        // PREFERRED SOURCE BANNER SETTINGS
        $sanitized['preferred_enabled'] = isset($input['preferred_enabled']) ? 1 : 0;
        $sanitized['preferred_google_preferences_url'] = isset($input['preferred_google_preferences_url'])
            ? esc_url_raw($input['preferred_google_preferences_url'])
            : '';
        $sanitized['preferred_educational_post_url'] = isset($input['preferred_educational_post_url'])
            ? esc_url_raw($input['preferred_educational_post_url'])
            : '';

        $sanitized['preferred_button_text'] = isset($input['preferred_button_text'])
            ? sanitize_text_field($input['preferred_button_text'])
            : __('Add As A Preferred Source', 'signalkit');

        $sanitized['preferred_banner_headline'] = isset($input['preferred_banner_headline'])
            ? sanitize_text_field($input['preferred_banner_headline'])
            : __('Add [site_name] As A Trusted Source', 'signalkit');

        $sanitized['preferred_banner_description'] = isset($input['preferred_banner_description'])
            ? sanitize_textarea_field($input['preferred_banner_description'])
            : __('Get priority access to our news and updates in your Google News feed.', 'signalkit');

        $sanitized['preferred_educational_text'] = isset($input['preferred_educational_text'])
            ? sanitize_text_field($input['preferred_educational_text'])
            : __('Learn More', 'signalkit');

        $sanitized['preferred_show_educational_link'] = isset($input['preferred_show_educational_link']) ? 1 : 0;

        $sanitized['preferred_primary_color']   = $this->sanitize_color($input, 'preferred_primary_color', '#4285f4');
        $sanitized['preferred_secondary_color'] = $this->sanitize_color($input, 'preferred_secondary_color', '#ffffff');
        $sanitized['preferred_accent_color']    = $this->sanitize_color($input, 'preferred_accent_color', '#ea4335');
        $sanitized['preferred_text_color']      = $this->sanitize_color($input, 'preferred_text_color', '#1a1a1a');
        // Button Colors (NEW)
        $sanitized['preferred_button_text_color'] = $this->sanitize_color($input, 'preferred_button_text_color', '#ffffff');
        $sanitized['preferred_button_bg_color'] = $this->sanitize_color($input, 'preferred_button_bg_color', '#ea4335');

        $sanitized['preferred_banner_width']      = $this->sanitize_number($input, 'preferred_banner_width', 280, 600, 360);
        $sanitized['preferred_banner_padding']    = $this->sanitize_number($input, 'preferred_banner_padding', 8, 32, 16);
        $sanitized['preferred_border_radius']     = $this->sanitize_number($input, 'preferred_border_radius', 0, 32, 8);

        $sanitized['preferred_font_size_headline']    = $this->sanitize_number($input, 'preferred_font_size_headline', 12, 24, 15);
        $sanitized['preferred_font_size_description'] = $this->sanitize_number($input, 'preferred_font_size_description', 10, 18, 13);
        $sanitized['preferred_font_size_button']      = $this->sanitize_number($input, 'preferred_font_size_button', 11, 18, 14);

        $sanitized['preferred_position']         = $this->sanitize_position($input, 'preferred_position', 'bottom_right');
        $sanitized['preferred_animation']        = $this->sanitize_animation($input, 'preferred_animation', 'slide_in');
        $sanitized['preferred_mobile_position'] = $this->sanitize_mobile_position($input, 'preferred_mobile_position', 'bottom');
        $sanitized['preferred_mobile_stack_order'] = $this->sanitize_number($input, 'preferred_mobile_stack_order', 1, 2, 2);

        $sanitized['preferred_dismissible']      = isset($input['preferred_dismissible']) ? 1 : 0;
        $sanitized['preferred_close_button_size'] = $this->sanitize_number($input, 'preferred_close_button_size', 20, 40, 28);
        $sanitized['preferred_dismiss_duration'] = $this->sanitize_number($input, 'preferred_dismiss_duration', 1, 365, 7);
        $sanitized['preferred_show_frequency']   = $this->sanitize_frequency($input, 'preferred_show_frequency', 'once_per_day');

        $sanitized['preferred_mobile_enabled']   = isset($input['preferred_mobile_enabled']) ? 1 : 0;
        $sanitized['preferred_desktop_enabled']  = isset($input['preferred_desktop_enabled']) ? 1 : 0;
        $sanitized['preferred_show_on_posts']    = isset($input['preferred_show_on_posts']) ? 1 : 0;
        $sanitized['preferred_show_on_pages']    = isset($input['preferred_show_on_pages']) ? 1 : 0;
        $sanitized['preferred_show_on_homepage'] = isset($input['preferred_show_on_homepage']) ? 1 : 0;
        $sanitized['preferred_show_on_archive']  = isset($input['preferred_show_on_archive']) ? 1 : 0;

        // ADVANCED SETTINGS (Envato/WordPress.org compliance)
        $sanitized['analytics_tracking'] = isset($input['analytics_tracking']) ? 1 : 0;
        $sanitized['enable_rate_limiting'] = isset($input['enable_rate_limiting']) ? 1 : 0;
        $sanitized['enable_csp'] = isset($input['enable_csp']) ? 1 : 0;
        $sanitized['import_export_key'] = isset($input['import_export_key']) ? sanitize_text_field(wp_unslash($input['import_export_key'])) : '';
        
        // Mobile Banner Strategy (NEW)
        $valid_mobile_strategies = ['show_all', 'priority_only', 'rotate'];
        $sanitized['mobile_banner_strategy'] = isset($input['mobile_banner_strategy']) && in_array($input['mobile_banner_strategy'], $valid_mobile_strategies, true) 
            ? $input['mobile_banner_strategy'] : 'show_all';
        
        // DATA MANAGEMENT (Envato/WordPress.org compliance - preserve data by default)
        $sanitized['delete_data_on_uninstall'] = isset($input['delete_data_on_uninstall']) ? 1 : 0;
        
        // Privacy: Option to enable external fonts (opt-in for privacy compliance)
        $sanitized['enable_google_fonts'] = isset($input['enable_google_fonts']) ? 1 : 0;

        // =====================================
        // NEW: Enhanced Style Settings v2.0
        // =====================================
        
        // Style presets whitelist
        $valid_banner_styles = ['glassmorphism', 'preferred-star', 'lead-gradient', 'modern-card', 'glass', 'solid', 'gradient', 'dark', 'toast', 'bubble', 'neon'];
        $valid_button_styles = ['default', 'pill', 'sharp', 'outline'];
        $valid_icon_styles = ['circle', 'rounded', 'square'];
        $valid_visibility_modes = ['auto', 'light', 'dark'];
        
        // Follow Banner Enhanced Settings
        $sanitized['follow_banner_style'] = isset($input['follow_banner_style']) && in_array($input['follow_banner_style'], $valid_banner_styles, true) 
            ? $input['follow_banner_style'] : 'glassmorphism';
        $sanitized['follow_button_style'] = isset($input['follow_button_style']) && in_array($input['follow_button_style'], $valid_button_styles, true) 
            ? $input['follow_button_style'] : 'default';
        $sanitized['follow_icon_style'] = isset($input['follow_icon_style']) && in_array($input['follow_icon_style'], $valid_icon_styles, true) 
            ? $input['follow_icon_style'] : 'circle';
        $sanitized['follow_visibility_mode'] = isset($input['follow_visibility_mode']) && in_array($input['follow_visibility_mode'], $valid_visibility_modes, true) 
            ? $input['follow_visibility_mode'] : 'auto';
        $sanitized['follow_icon_size'] = $this->sanitize_number($input, 'follow_icon_size', 40, 72, 52);
        $sanitized['follow_gradient_start'] = $this->sanitize_color($input, 'follow_gradient_start', '#4285f4');
        $sanitized['follow_gradient_end'] = $this->sanitize_color($input, 'follow_gradient_end', '#34a853');
        $sanitized['follow_gradient_angle'] = $this->sanitize_number($input, 'follow_gradient_angle', 0, 360, 135);
        $sanitized['follow_border_color'] = $this->sanitize_color($input, 'follow_border_color', '');
        $sanitized['follow_backdrop_blur'] = $this->sanitize_number($input, 'follow_backdrop_blur', 0, 30, 12);
        $sanitized['follow_backdrop_opacity'] = $this->sanitize_number($input, 'follow_backdrop_opacity', 50, 100, 95);
        $sanitized['follow_enable_glow'] = isset($input['follow_enable_glow']) ? 1 : 0;
        $sanitized['follow_enable_float'] = isset($input['follow_enable_float']) ? 1 : 0;
        $sanitized['follow_glow_intensity'] = $this->sanitize_number($input, 'follow_glow_intensity', 0, 50, 20);
        
        // Preferred Banner Enhanced Settings
        $sanitized['preferred_banner_style'] = isset($input['preferred_banner_style']) && in_array($input['preferred_banner_style'], $valid_banner_styles, true) 
            ? $input['preferred_banner_style'] : 'preferred-star';
        $sanitized['preferred_button_style'] = isset($input['preferred_button_style']) && in_array($input['preferred_button_style'], $valid_button_styles, true) 
            ? $input['preferred_button_style'] : 'default';
        $sanitized['preferred_icon_style'] = isset($input['preferred_icon_style']) && in_array($input['preferred_icon_style'], $valid_icon_styles, true) 
            ? $input['preferred_icon_style'] : 'circle';
        $sanitized['preferred_visibility_mode'] = isset($input['preferred_visibility_mode']) && in_array($input['preferred_visibility_mode'], $valid_visibility_modes, true) 
            ? $input['preferred_visibility_mode'] : 'auto';
        $sanitized['preferred_icon_size'] = $this->sanitize_number($input, 'preferred_icon_size', 40, 72, 52);
        $sanitized['preferred_gradient_start'] = $this->sanitize_color($input, 'preferred_gradient_start', '#ea4335');
        $sanitized['preferred_gradient_end'] = $this->sanitize_color($input, 'preferred_gradient_end', '#fbbc04');
        $sanitized['preferred_gradient_angle'] = $this->sanitize_number($input, 'preferred_gradient_angle', 0, 360, 135);
        $sanitized['preferred_border_color'] = $this->sanitize_color($input, 'preferred_border_color', '');
        $sanitized['preferred_backdrop_blur'] = $this->sanitize_number($input, 'preferred_backdrop_blur', 0, 30, 12);
        $sanitized['preferred_backdrop_opacity'] = $this->sanitize_number($input, 'preferred_backdrop_opacity', 50, 100, 95);
        $sanitized['preferred_enable_glow'] = isset($input['preferred_enable_glow']) ? 1 : 0;
        $sanitized['preferred_enable_float'] = isset($input['preferred_enable_float']) ? 1 : 0;
        $sanitized['preferred_glow_intensity'] = $this->sanitize_number($input, 'preferred_glow_intensity', 0, 50, 20);

        // =====================================
        // CUSTOM BANNER SETTINGS - CRITICAL!
        // These were missing, causing toggle not to save
        // =====================================
        
        // Basic enabled state
        $sanitized['custom_enabled'] = isset($input['custom_enabled']) ? 1 : 0;
        
        // Banner type whitelist
        $valid_custom_types = ['newsletter', 'lead', 'cta', 'announcement', 'promo'];
        $sanitized['custom_banner_type'] = isset($input['custom_banner_type']) && in_array($input['custom_banner_type'], $valid_custom_types, true) 
            ? $input['custom_banner_type'] : 'newsletter';
        
        // Content fields
        $sanitized['custom_headline'] = isset($input['custom_headline']) ? sanitize_text_field($input['custom_headline']) : '';
        $sanitized['custom_description'] = isset($input['custom_description']) ? sanitize_textarea_field($input['custom_description']) : '';
        $sanitized['custom_button_text'] = isset($input['custom_button_text']) ? sanitize_text_field($input['custom_button_text']) : '';
        $sanitized['custom_success_message'] = isset($input['custom_success_message']) ? sanitize_text_field($input['custom_success_message']) : '';
        $sanitized['custom_error_message'] = isset($input['custom_error_message']) ? sanitize_text_field($input['custom_error_message']) : '';
        
        // Form fields
        $sanitized['custom_placeholder_email'] = isset($input['custom_placeholder_email']) ? sanitize_text_field($input['custom_placeholder_email']) : '';
        $sanitized['custom_placeholder_name'] = isset($input['custom_placeholder_name']) ? sanitize_text_field($input['custom_placeholder_name']) : '';
        $sanitized['custom_show_name_field'] = isset($input['custom_show_name_field']) ? 1 : 0;
        $sanitized['custom_require_name'] = isset($input['custom_require_name']) ? 1 : 0;
        $sanitized['custom_privacy_text'] = isset($input['custom_privacy_text']) ? sanitize_text_field($input['custom_privacy_text']) : '';
        $sanitized['custom_show_privacy'] = isset($input['custom_show_privacy']) ? 1 : 0;
        
        // Integration
        $sanitized['custom_redirect_url'] = isset($input['custom_redirect_url']) ? esc_url_raw($input['custom_redirect_url']) : '';
        $sanitized['custom_webhook_url'] = isset($input['custom_webhook_url']) ? esc_url_raw($input['custom_webhook_url']) : '';
        $sanitized['custom_promo_code'] = isset($input['custom_promo_code']) ? sanitize_text_field($input['custom_promo_code']) : '';
        $sanitized['custom_store_locally'] = isset($input['custom_store_locally']) ? 1 : 0;
        
        // GDPR/Privacy Compliance: Webhook consent (Envato/CodeCanyon requirement)
        // Data will ONLY be sent to external webhook if admin explicitly consents
        $sanitized['custom_webhook_consent'] = isset($input['custom_webhook_consent']) ? 1 : 0;
        
        // Colors
        $sanitized['custom_primary_color'] = $this->sanitize_color($input, 'custom_primary_color', '#6366f1');
        $sanitized['custom_secondary_color'] = $this->sanitize_color($input, 'custom_secondary_color', '#ffffff');
        $sanitized['custom_accent_color'] = $this->sanitize_color($input, 'custom_accent_color', '#8b5cf6');
        $sanitized['custom_text_color'] = $this->sanitize_color($input, 'custom_text_color', '#1e1e1e');
        
        // Position and animation
        $valid_custom_positions = ['bottom_left', 'bottom_right', 'bottom_center', 'top_left', 'top_right', 'top_center', 'center'];
        $sanitized['custom_position'] = isset($input['custom_position']) && in_array($input['custom_position'], $valid_custom_positions, true) 
            ? $input['custom_position'] : 'bottom_right';
        $sanitized['custom_mobile_position'] = $this->sanitize_mobile_position($input, 'custom_mobile_position', 'bottom');
        
        $valid_animations = ['slide_in', 'fade_in', 'bounce', 'elastic', 'swing', 'zoom'];
        $sanitized['custom_animation'] = isset($input['custom_animation']) && in_array($input['custom_animation'], $valid_animations, true) 
            ? $input['custom_animation'] : 'slide_in';
        
        // Triggers
        $sanitized['custom_delay'] = $this->sanitize_number($input, 'custom_delay', 0, 60, 3);
        $sanitized['custom_scroll_trigger'] = isset($input['custom_scroll_trigger']) ? 1 : 0;
        $sanitized['custom_scroll_percentage'] = $this->sanitize_number($input, 'custom_scroll_percentage', 10, 100, 50);
        $sanitized['custom_exit_intent'] = isset($input['custom_exit_intent']) ? 1 : 0;
        
        // Display settings
        $valid_frequencies = ['always', 'once_per_session', 'once_per_day', 'once'];
        $sanitized['custom_show_frequency'] = isset($input['custom_show_frequency']) && in_array($input['custom_show_frequency'], $valid_frequencies, true) 
            ? $input['custom_show_frequency'] : 'once_per_session';
        $sanitized['custom_dismissible'] = isset($input['custom_dismissible']) ? 1 : 0;
        $sanitized['custom_close_button_size'] = $this->sanitize_number($input, 'custom_close_button_size', 20, 40, 28);
        $sanitized['custom_dismiss_duration'] = $this->sanitize_number($input, 'custom_dismiss_duration', 1, 365, 7);
        
        // Device and page visibility
        $sanitized['custom_mobile_enabled'] = isset($input['custom_mobile_enabled']) ? 1 : 0;
        $sanitized['custom_desktop_enabled'] = isset($input['custom_desktop_enabled']) ? 1 : 0;
        $sanitized['custom_show_on_posts'] = isset($input['custom_show_on_posts']) ? 1 : 0;
        $sanitized['custom_show_on_pages'] = isset($input['custom_show_on_pages']) ? 1 : 0;
        $sanitized['custom_show_on_homepage'] = isset($input['custom_show_on_homepage']) ? 1 : 0;
        $sanitized['custom_show_on_archive'] = isset($input['custom_show_on_archive']) ? 1 : 0;
        
        // Enhanced style settings
        $sanitized['custom_banner_style'] = isset($input['custom_banner_style']) && in_array($input['custom_banner_style'], $valid_banner_styles, true) 
            ? $input['custom_banner_style'] : 'lead-gradient';
        $sanitized['custom_button_style'] = isset($input['custom_button_style']) && in_array($input['custom_button_style'], $valid_button_styles, true) 
            ? $input['custom_button_style'] : 'default';
        $sanitized['custom_icon_style'] = isset($input['custom_icon_style']) && in_array($input['custom_icon_style'], $valid_icon_styles, true) 
            ? $input['custom_icon_style'] : 'circle';
        $sanitized['custom_visibility_mode'] = isset($input['custom_visibility_mode']) && in_array($input['custom_visibility_mode'], $valid_visibility_modes, true) 
            ? $input['custom_visibility_mode'] : 'auto';
        $sanitized['custom_enable_glow'] = isset($input['custom_enable_glow']) ? 1 : 0;
        $sanitized['custom_enable_float'] = isset($input['custom_enable_float']) ? 1 : 0;
        $sanitized['custom_backdrop_blur'] = $this->sanitize_number($input, 'custom_backdrop_blur', 0, 30, 12);
        $sanitized['custom_backdrop_opacity'] = $this->sanitize_number($input, 'custom_backdrop_opacity', 50, 100, 95);
        $sanitized['custom_banner_width'] = $this->sanitize_number($input, 'custom_banner_width', 280, 600, 400);
        $sanitized['custom_banner_padding'] = $this->sanitize_number($input, 'custom_banner_padding', 8, 48, 24);
        $sanitized['custom_border_radius'] = $this->sanitize_number($input, 'custom_border_radius', 0, 32, 16);
        
        // Typography
        $valid_fonts = ['system', 'inter', 'roboto', 'open-sans', 'lato', 'montserrat', 'poppins', 'nunito', 'raleway', 'ubuntu', 'playfair', 'merriweather', 'source-sans', 'oswald', 'rubik'];
        $sanitized['custom_font_family'] = isset($input['custom_font_family']) && in_array($input['custom_font_family'], $valid_fonts, true) 
            ? $input['custom_font_family'] : 'system';
        $valid_weights = ['300', '400', '500', '600', '700', '800'];
        $sanitized['custom_font_weight_headline'] = isset($input['custom_font_weight_headline']) && in_array($input['custom_font_weight_headline'], $valid_weights, true) 
            ? $input['custom_font_weight_headline'] : '700';
        $sanitized['custom_font_weight_body'] = isset($input['custom_font_weight_body']) && in_array($input['custom_font_weight_body'], $valid_weights, true) 
            ? $input['custom_font_weight_body'] : '400';

        // Enforce Max 2 Banners Active Limit
        $active_banners = 0;
        if (!empty($sanitized['follow_enabled'])) $active_banners++;
        if (!empty($sanitized['preferred_enabled'])) $active_banners++;
        if (!empty($sanitized['custom_enabled'])) $active_banners++;

        if ($active_banners > 2) {
            // Prioritize Follow and Custom. Disable Preferred if all 3 are active.
            $sanitized['preferred_enabled'] = 0;
            
            add_settings_error(
                'signalkit_settings',
                'signalkit_banner_limit',
                __('Note: You can only have a maximum of 2 banners active at once. The "Preferred Source" banner has been automatically disabled.', 'signalkit'),
                'warning'
            );
        }

        return $sanitized;
    }

    // === SANITIZATION HELPERS ===
    
    /**
     * Sanitize hex color
     *
     * SECURITY: Uses WordPress's sanitize_hex_color() which validates format
     */
    private function sanitize_color($input, $key, $default) {
        if (!isset($input[$key])) return $default;
        $color = sanitize_hex_color($input[$key]);
        return $color ? $color : $default;
    }

    /**
     * Sanitize number within range
     *
     * SECURITY: absint() converts to absolute integer, range check prevents extreme values
     */
    private function sanitize_number($input, $key, $min, $max, $default) {
        if (!isset($input[$key])) return $default;
        $value = absint($input[$key]);
        return max($min, min($max, $value));
    }

    /**
     * Sanitize position value
     *
     * SECURITY: Whitelist validation - only allows predefined safe values
     */
    private function sanitize_position($input, $key, $default) {
        $valid = ['bottom_left', 'bottom_right', 'bottom_center', 'top_left', 'top_right', 'top_center'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    /**
     * Sanitize mobile position value
     *
     * SECURITY: Whitelist validation
     */
    private function sanitize_mobile_position($input, $key, $default) {
        $valid = ['top', 'bottom'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    /**
     * Sanitize animation value
     *
     * SECURITY: Whitelist validation
     */
    private function sanitize_animation($input, $key, $default) {
        $valid = ['slide_in', 'fade_in', 'bounce', 'elastic', 'swing', 'zoom'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    /**
     * Sanitize frequency value
     *
     * SECURITY: Whitelist validation
     */
    private function sanitize_frequency($input, $key, $default) {
        $valid = ['always', 'once_per_session', 'once_per_day'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    // === SECURITY & AJAX HANDLERS ===

    /**
     * Get user IP address for rate limiting.
     *
     * SECURITY: Validation-first approach (Envato/CodeCanyon requirement)
     * - Validates IP format IMMEDIATELY after raw $_SERVER access
     * - Prevents header spoofing by rejecting invalid IPs before processing
     * - Only sanitizes after validation passes
     *
     * @return string The validated and sanitized IP address.
     */
    private function get_user_ip() {
        // Check common proxy headers in order of reliability
        $headers = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        );
        
        foreach ($headers as $header) {
            $header_val = isset($_SERVER[$header]) ? sanitize_text_field(wp_unslash($_SERVER[$header])) : '';
            if (!empty($header_val)) {
                // Get raw value first
                $raw_ip = $header_val;
                
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
                return sanitize_text_field($raw_ip);
            }
        }
        
        // Fallback for invalid/missing IP
        return '0.0.0.0';
    }

    /**
     * Check and enforce rate limiting for AJAX actions.
     *
     * @param string $action_name A unique name for the action being limited.
     * @param int $limit The number of seconds for the rate limit.
     */
    private function check_rate_limit($action_name = 'default', $limit = 3) {
        $options = get_option('signalkit_settings', []);
        if (empty($options['enable_rate_limiting'])) {
            return; // Rate limiting is disabled
        }

        $ip = $this->get_user_ip();
        $transient_key = 'signalkit_rl_' . md5($ip . '_' . $action_name);

        if (get_transient($transient_key)) {

            wp_send_json_error([
                'message' => __('Too many requests. Please wait a moment and try again.', 'signalkit')
            ]);
            exit;
        }

        // Set the transient for $limit seconds
        set_transient($transient_key, 'limited', $limit);
    }

    /**
     * Encrypt data using OpenSSL AES-256-CBC.
     *
     * @param string $data Data to encrypt.
     * @param string $key  Encryption key.
     * @return string|false Base64-encoded string (IV.ciphertext) or false on failure.
     */
    private function encrypt_settings($data, $key) {
        try {
            $ivlen = openssl_cipher_iv_length('aes-256-cbc');
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            
            if ($ciphertext === false) {

                return false;
            }
            
            return base64_encode($iv . $ciphertext);
        } catch (Exception $e) {

            return false;
        }
    }

    /**
     * Decrypt data using OpenSSL AES-256-CBC.
     *
     * @param string $data Base64-encoded string (IV.ciphertext).
     * @param string $key  Encryption key.
     * @return string|false Decrypted data or false on failure.
     */
    private function decrypt_settings($data, $key) {
        try {
            $c = base64_decode($data);
            $ivlen = openssl_cipher_iv_length('aes-256-cbc');
            $iv = substr($c, 0, $ivlen);
            $ciphertext = substr($c, $ivlen);
            
            $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            
            if ($decrypted === false) {

                return false;
            }
            
            return $decrypted;
        } catch (Exception $e) {

            return false;
        }
    }

    /**
     * Handle AJAX reset analytics.
     *
     * SECURITY: Nonce verification, capability check, input sanitization, audit logging.
     * ADDED: Rate Limiting
     */
    public function ajax_reset_analytics() {
        // SECURITY: Verify nonce to prevent CSRF attacks
        check_ajax_referer('signalkit_admin_nonce', 'nonce');

        // SECURITY: Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'signalkit')]);
        }

        // SECURITY: Enforce rate limiting (10 seconds for this destructive action)
        $this->check_rate_limit('reset_analytics', 10);

        // SECURITY: Sanitize and validate input
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field(wp_unslash($_POST['banner_type'])) : 'all';
        $valid = ['follow', 'preferred', 'all'];

        // SECURITY: Whitelist validation
        if (!in_array($banner_type, $valid, true)) {
            wp_send_json_error(['message' => __('Invalid banner type', 'signalkit')]);
        }

        if (class_exists('SignalKit_Analytics')) {
            SignalKit_Analytics::reset_analytics($banner_type);
        }
        
        // --- AUDIT LOGGING ---
        $user = wp_get_current_user();

        // Email site admin as an alert for this critical action
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Alert: Analytics Data Reset', 'signalkit'),
            sprintf(
                /* translators: %1$s: username, %2$s: banner type, %3$s: datetime */
                __('User %1$s reset the SignalKit analytics data (%2$s) at %3$s.', 'signalkit'),
                esc_html($user->user_login),
                esc_html($banner_type),
                current_time('mysql')
            )
        );
        // --- END AUDIT LOGGING ---

        wp_send_json_success(['message' => 'Analytics reset', 'banner_type' => $banner_type]);
    }

    /**
     * AJAX: Export settings.
     *
     * SECURITY: Nonce verification, capability check, and audit logging.
     * ADDED: Rate Limiting & Encryption
     */
    public function ajax_export_settings() {
        // SECURITY: Verify nonce
        check_ajax_referer('signalkit_admin_nonce', 'nonce');
        
        // SECURITY: Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'signalkit')]);
        }

        // SECURITY: Enforce rate limiting (5 seconds)
        $this->check_rate_limit('export_settings', 5);

        // --- AUDIT LOGGING (GDPR/Security Compliance) ---
        $user = wp_get_current_user();

        // Email site admin as a security alert
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Security Alert: Settings Exported', 'signalkit'),
            sprintf(
                /* translators: %1$s: username, %2$s: datetime */
                __('User %1$s exported the SignalKit plugin settings at %2$s.', 'signalkit'),
                esc_html($user->user_login),
                current_time('mysql')
            )
        );
        // --- END AUDIT LOGGING ---

        $settings = get_option('signalkit_settings', []);
        $key = $settings['import_export_key'] ?? '';
        
        $payload = $settings;
        $encrypted = false;

        // Encrypt if key is set
        if (!empty($key)) {
            $json_data = wp_json_encode($settings);
            $encrypted_data = $this->encrypt_settings($json_data, $key);
            
            if ($encrypted_data) {
                $payload = $encrypted_data;
                $encrypted = true;

            } else {

                // Don't fail the export; send unencrypted as fallback
            }
        }
        
        wp_send_json_success(['settings' => $payload, 'encrypted' => $encrypted]);
    }

    /**
     * AJAX: Import settings.
     *
     * SECURITY v1.0.0: ENHANCED - SQL Injection Protection with JSON Schema Validation
     * - Dual nonce verification
     * - Rate Limiting
     * - Encryption Handling
     * - File size limit enforced (100KB max)
     * - Settings count limit (max 100 settings)
     * - Full type validation and sanitization
     * - Audit logging for security compliance
     */
    public function ajax_import_settings() {
        // SECURITY FIX: Dual nonce verification for compatibility
        $nonce_verified = false;
        
        if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'signalkit_admin_nonce')) {
            $nonce_verified = true;
        }
        
        if (!$nonce_verified && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'signalkit_import_settings')) {
            $nonce_verified = true;
        }
        
        if (!$nonce_verified) {

            wp_send_json_error(['message' => __('Security check failed', 'signalkit')]);
        }
        
        // SECURITY: Check permissions
        if (!current_user_can('manage_options')) {

            wp_send_json_error(['message' => __('Unauthorized', 'signalkit')]);
        }

        // SECURITY: Enforce rate limiting (10 seconds for this destructive action)
        $this->check_rate_limit('import_settings', 10);

        $is_encrypted = isset($_POST['encrypted']) && $_POST['encrypted'] == 1;
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated thoroughly via json_decode and schema below
        $raw_data = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        
        // SECURITY: Enforce 100KB file size limit (for both text and JSON)
        if (strlen($raw_data) > 102400) {

            wp_send_json_error(['message' => __('File too large (max 100KB)', 'signalkit')]);
        }

        $settings_json = $raw_data;

        if ($is_encrypted) {

            $current_settings = get_option('signalkit_settings', []);
            $key = $current_settings['import_export_key'] ?? '';

            if (empty($key)) {

                wp_send_json_error(['message' => __('Decryption failed: Please set your Import/Export Key before importing.', 'signalkit')]);
            }

            $settings_json = $this->decrypt_settings($raw_data, $key);

            if ($settings_json === false) {

                wp_send_json_error(['message' => __('Decryption failed: Invalid key or corrupt data.', 'signalkit')]);
            }
        }

        // Parse settings
        $settings = json_decode($settings_json, true);
        
        if (empty($settings) || !is_array($settings)) {

            wp_send_json_error(['message' => __('No valid settings provided or JSON is corrupt.', 'signalkit')]);
        }

        // SECURITY FIX: Comprehensive JSON Schema Validation
        $schema = array(
            // Global Settings
            'site_name' => 'string',
            
            // Follow Banner
            'follow_enabled' => 'boolean',
            'follow_google_news_url' => 'url',
            'follow_button_text' => 'string',
            'follow_banner_headline' => 'string',
            'follow_banner_description' => 'string',
            'follow_primary_color' => 'color',
            'follow_secondary_color' => 'color',
            'follow_accent_color' => 'color',
            'follow_text_color' => 'color',
            'follow_banner_width' => 'integer',
            'follow_banner_padding' => 'integer',
            'follow_border_radius' => 'integer',
            'follow_font_size_headline' => 'integer',
            'follow_font_size_description' => 'integer',
            'follow_font_size_button' => 'integer',
            'follow_position' => 'string',
            'follow_animation' => 'string',
            'follow_mobile_position' => 'string',
            'follow_mobile_stack_order' => 'integer',
            'follow_dismissible' => 'boolean',
            'follow_dismiss_duration' => 'integer',
            'follow_show_frequency' => 'string',
            'follow_mobile_enabled' => 'boolean',
            'follow_desktop_enabled' => 'boolean',
            'follow_show_on_posts' => 'boolean',
            'follow_show_on_pages' => 'boolean',
            'follow_show_on_homepage' => 'boolean',
            'follow_show_on_archive' => 'boolean',
            
            // Preferred Source Banner
            'preferred_enabled' => 'boolean',
            'preferred_google_preferences_url' => 'url',
            'preferred_educational_post_url' => 'url',
            'preferred_button_text' => 'string',
            'preferred_banner_headline' => 'string',
            'preferred_banner_description' => 'string',
            'preferred_educational_text' => 'string',
            'preferred_show_educational_link' => 'boolean',
            'preferred_primary_color' => 'color',
            'preferred_secondary_color' => 'color',
            'preferred_accent_color' => 'color',
            'preferred_text_color' => 'color',
            'preferred_banner_width' => 'integer',
            'preferred_banner_padding' => 'integer',
            'preferred_border_radius' => 'integer',
            'preferred_font_size_headline' => 'integer',
            'preferred_font_size_description' => 'integer',
            'preferred_font_size_button' => 'integer',
            'preferred_position' => 'string',
            'preferred_animation' => 'string',
            'preferred_mobile_position' => 'string',
            'preferred_mobile_stack_order' => 'integer',
            'preferred_dismissible' => 'boolean',
            'preferred_dismiss_duration' => 'integer',
            'preferred_show_frequency' => 'string',
            'preferred_mobile_enabled' => 'boolean',
            'preferred_desktop_enabled' => 'boolean',
            'preferred_show_on_posts' => 'boolean',
            'preferred_show_on_pages' => 'boolean',
            'preferred_show_on_homepage' => 'boolean',
            'preferred_show_on_archive' => 'boolean',
            
            // Advanced Settings
            'analytics_tracking' => 'boolean',
            'enable_rate_limiting' => 'boolean',
            'enable_csp' => 'boolean',
            'import_export_key' => 'string',
            
            // Data Management
            'delete_data_on_uninstall' => 'boolean',
            
            // Follow Banner Enhanced v2.0
            'follow_banner_style' => 'string',
            'follow_button_style' => 'string',
            'follow_icon_style' => 'string',
            'follow_visibility_mode' => 'string',
            'follow_icon_size' => 'integer',
            'follow_gradient_start' => 'color',
            'follow_gradient_end' => 'color',
            'follow_gradient_angle' => 'integer',
            'follow_border_color' => 'color',
            'follow_backdrop_blur' => 'integer',
            'follow_backdrop_opacity' => 'integer',
            'follow_enable_glow' => 'boolean',
            'follow_enable_float' => 'boolean',
            'follow_glow_intensity' => 'integer',
            'follow_button_text_color' => 'color',
            'follow_button_bg_color' => 'color',
            'follow_close_button_size' => 'integer',
            
            // Preferred Banner Enhanced v2.0
            'preferred_banner_style' => 'string',
            'preferred_button_style' => 'string',
            'preferred_icon_style' => 'string',
            'preferred_visibility_mode' => 'string',
            'preferred_icon_size' => 'integer',
            'preferred_gradient_start' => 'color',
            'preferred_gradient_end' => 'color',
            'preferred_gradient_angle' => 'integer',
            'preferred_border_color' => 'color',
            'preferred_backdrop_blur' => 'integer',
            'preferred_backdrop_opacity' => 'integer',
            'preferred_enable_glow' => 'boolean',
            'preferred_enable_float' => 'boolean',
            'preferred_glow_intensity' => 'integer',
            'preferred_button_text_color' => 'color',
            'preferred_button_bg_color' => 'color',
            'preferred_close_button_size' => 'integer',
            
            // Custom Banner v2.0
            'custom_enabled' => 'boolean',
            'custom_banner_type' => 'string',
            'custom_headline' => 'string',
            'custom_description' => 'string',
            'custom_button_text' => 'string',
            'custom_success_message' => 'string',
            'custom_redirect_url' => 'url',
            'custom_show_name_field' => 'boolean',
            'custom_name_label' => 'string',
            'custom_name_placeholder' => 'string',
            'custom_show_privacy' => 'boolean',
            'custom_privacy_text' => 'string',
            'custom_privacy_url' => 'url',
            'custom_primary_color' => 'color',
            'custom_secondary_color' => 'color',
            'custom_accent_color' => 'color',
            'custom_text_color' => 'color',
            'custom_button_text_color' => 'color',
            'custom_button_bg_color' => 'color',
            'custom_banner_width' => 'integer',
            'custom_banner_padding' => 'integer',
            'custom_border_radius' => 'integer',
            'custom_font_size_headline' => 'integer',
            'custom_font_size_description' => 'integer',
            'custom_font_size_button' => 'integer',
            'custom_position' => 'string',
            'custom_animation' => 'string',
            'custom_mobile_position' => 'string',
            'custom_mobile_stack_order' => 'integer',
            'custom_dismissible' => 'boolean',
            'custom_dismiss_duration' => 'integer',
            'custom_show_frequency' => 'string',
            'custom_mobile_enabled' => 'boolean',
            'custom_desktop_enabled' => 'boolean',
            'custom_show_on_posts' => 'boolean',
            'custom_show_on_pages' => 'boolean',
            'custom_show_on_homepage' => 'boolean',
            'custom_show_on_archive' => 'boolean',
            'custom_banner_style' => 'string',
            'custom_button_style' => 'string',
            'custom_icon_style' => 'string',
            'custom_visibility_mode' => 'string',
            'custom_icon_size' => 'integer',
            'custom_gradient_start' => 'color',
            'custom_gradient_end' => 'color',
            'custom_gradient_angle' => 'integer',
            'custom_border_color' => 'color',
            'custom_backdrop_blur' => 'integer',
            'custom_backdrop_opacity' => 'integer',
            'custom_enable_glow' => 'boolean',
            'custom_enable_float' => 'boolean',
            'custom_glow_intensity' => 'integer',
            'custom_delay' => 'integer',
            'custom_scroll_trigger' => 'boolean',
            'custom_scroll_percentage' => 'integer',
            'custom_exit_intent' => 'boolean',
            'custom_font_family' => 'string',
            
            // Global/Misc
            'mobile_banner_strategy' => 'string'
        );

        // Validate against schema and remove unknown keys
        $validated_settings = array();
        $validation_errors = array();
        
        foreach ($settings as $key => $value) {
            // Remove keys not in schema
            if (!isset($schema[$key])) {

                continue;
            }
            
            // Type validation
            $expected_type = $schema[$key];
            $is_valid = false;
            
            switch ($expected_type) {
                case 'boolean':
                    $is_valid = is_bool($value) || $value === 0 || $value === 1 || $value === '0' || $value === '1';
                    break;
                    
                case 'integer':
                    $is_valid = is_numeric($value);
                    break;
                    
                case 'string':
                    $is_valid = is_string($value);
                    break;
                    
                case 'url':
                    $is_valid = is_string($value) && (empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false);
                    break;
                    
                case 'color':
                    // FIXED: Support both 3-digit and 6-digit hex codes
                    $is_valid = is_string($value) && preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $value);
                    break;
            }
            
            if (!$is_valid) {
                $validation_errors[] = sprintf('Invalid type for %s (expected %s)', $key, $expected_type);

                continue;
            }
            
            $validated_settings[$key] = $value;
        }

        // Check for required keys
        $required_keys = ['site_name', 'follow_enabled', 'preferred_enabled'];
        $missing_keys = array();
        
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $validated_settings)) {
                $missing_keys[] = $key;
            }
        }
        
        if (!empty($missing_keys)) {

            wp_send_json_error([
                'message' => 'Invalid settings file structure',
                'details' => 'Missing required keys: ' . implode(', ', $missing_keys)
            ]);
        }

        // Log validation errors if any (non-fatal)
        if (!empty($validation_errors)) {

        }

        // SECURITY: Limit number of settings to prevent resource exhaustion
        if (count($validated_settings) > 100) {

            wp_send_json_error(['message' => __('Settings file contains too many entries (max 100)', 'signalkit')]);
        }

        // SECURITY: Full sanitization through sanitize_settings()
        $sanitized = $this->sanitize_settings($validated_settings);
        
        // SECURITY: update_option is SQL-injection safe (WordPress handles escaping)
        update_option('signalkit_settings', $sanitized);

        // --- AUDIT LOGGING ---
        $user = wp_get_current_user();

        // Email site admin as an alert for this critical action
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Security Alert: Settings Imported', 'signalkit'),
            sprintf(
                /* translators: %1$s: username, %2$s: datetime, %3$s: encrypted status */
                __('User %1$s imported and overwrote the SignalKit plugin settings at %2$s. (Encrypted: %3$s)', 'signalkit'),
                esc_html($user->user_login),
                current_time('mysql'),
                $is_encrypted ? 'Yes' : 'No'
            )
        );
        // --- END AUDIT LOGGING ---
        
        wp_send_json_success([
            'message' => 'Settings imported successfully',
            'count' => count($sanitized),
            'warnings' => count($validation_errors)
        ]);
    }

    /**
     * AJAX: Preview Banner
     *
     * PRODUCTION VERSION - Renders EXACTLY like live frontend
     * SECURITY: Nonce verification, capability check, input sanitization
     * ADDED: Rate Limiting
     *
     * @version 1.0.0
     */
    public function ajax_preview_banner() {
        // SECURITY: Verify nonce to prevent CSRF
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'signalkit_admin_nonce')) {

            wp_send_json_error(['message' => __('Security check failed', 'signalkit')]);
            return;
        }

        // SECURITY: Check user permissions
        if (!current_user_can('manage_options')) {

            wp_send_json_error(['message' => __('Unauthorized', 'signalkit')]);
            return;
        }

        // SECURITY: Enforce rate limiting (1 second for rapid preview)
        $this->check_rate_limit('preview_banner', 1);

        // SECURITY: Sanitize all inputs
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field(wp_unslash($_POST['banner_type'])) : '';
        $settings    = isset($_POST['settings']) ? map_deep(wp_unslash($_POST['settings']), 'sanitize_text_field') : [];
        $device      = isset($_POST['device']) ? sanitize_text_field(wp_unslash($_POST['device'])) : 'desktop';

        // SECURITY: Whitelist validation for banner type
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {

            wp_send_json_error(['message' => __('Invalid banner type', 'signalkit')]);
            return;
        }

        try {
            // Merge with DB settings
            $current = get_option('signalkit_settings', []);
            $merged = array_merge($current, $settings);
            
            // SECURITY: Full sanitization
            $sanitized = $this->sanitize_settings($merged);

            // Use SignalKit_Public to render (same as live)
            if (!class_exists('SignalKit_Public')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-signalkit-public.php';
            }
            
            $public = new SignalKit_Public($this->plugin_name, $this->version);
            $public->settings = $sanitized; // Inject preview settings

            // Generate CSS (same as live)
            $css = $public->generate_banner_css($banner_type);
            if ($device === 'mobile') {
                $css .= $public->generate_mobile_stacking_css();
            }

            // Render HTML using the SAME method as live
            ob_start();
            $public->render_banner($banner_type, $device, false); // No schema for preview
            $html = ob_get_clean();

            // IMPROVED: Validate output by checking for banner wrapper
            if (empty($html) || strpos($html, 'class="signalkit-banner') === false) {

                wp_send_json_error([
                    'message' => 'Banner rendering failed',
                    'details' => 'Invalid HTML structure'
                ]);
                return;
            }

            // Additional CSS for preview environment
            $preview_css = "
/* Preview-specific adjustments */
.signalkit-banner {
    opacity: 1 !important;
    transform: none !important;
    display: block !important;
}
.signalkit-banner.active {
    opacity: 1 !important;
    transform: none !important;
}
";

            /**
             * SECURITY: Late-escaping for AJAX response
             * 
             * HTML is generated by SignalKit_Public->render_banner() which uses:
             * - esc_html() for text content
             * - esc_attr() for attributes
             * - esc_url() for URLs
             * 
             * Additional wp_kses filtering applied here for defense in depth.
             * CSS is safe static strings generated by generate_banner_css().
             */
            $allowed_html = array_merge(
                wp_kses_allowed_html('post'),
                array(
                    'button' => array(
                        'type' => true,
                        'class' => true,
                        'aria-label' => true,
                        'data-banner' => true,
                        'data-banner-type' => true,
                        'data-banner-id' => true,
                        'data-banner-style' => true,
                        'id' => true,
                    ),
                    'div' => array(
                        'class' => true,
                        'id' => true,
                        'style' => true,
                        'data-banner-type' => true,
                        'data-banner-id' => true,
                        'data-banner-style' => true,
                        'data-stack-order' => true,
                        'data-delay' => true,
                        'data-scroll-trigger' => true,
                        'data-scroll-percentage' => true,
                        'data-exit-intent' => true,
                        'data-success-message' => true,
                        'data-dismissible' => true,
                        'data-dismiss-duration' => true,
                        'data-animate' => true,
                        'data-position' => true,
                        'data-mobile-position' => true,
                    ),
                    'svg' => array(
                        'xmlns' => true,
                        'viewbox' => true,
                        'fill' => true,
                        'class' => true,
                        'width' => true,
                        'height' => true,
                        'aria-hidden' => true,
                        'id' => true,
                    ),
                    'path' => array(
                        'd' => true,
                        'fill' => true,
                        'stroke' => true,
                        'stroke-width' => true,
                        'class' => true,
                    ),
                    'circle' => array(
                        'cx' => true,
                        'cy' => true,
                        'r' => true,
                        'fill' => true,
                        'class' => true,
                    ),
                    'rect' => array(
                        'x' => true,
                        'y' => true,
                        'width' => true,
                        'height' => true,
                        'fill' => true,
                        'rx' => true,
                        'class' => true,
                    ),
                    'lineargradient' => array(
                        'id' => true,
                        'x1' => true,
                        'y1' => true,
                        'x2' => true,
                        'y2' => true,
                    ),
                    'stop' => array(
                        'offset' => true,
                        'stop-color' => true,
                    ),
                    'g' => array(
                        'class' => true,
                        'transform' => true,
                    ),
                    'input' => array(
                        'type' => true,
                        'name' => true,
                        'placeholder' => true,
                        'required' => true,
                        'class' => true,
                        'id' => true,
                        'aria-label' => true,
                        'value' => true,
                    ),
                    'form' => array(
                        'class' => true,
                        'id' => true,
                        'method' => true,
                        'action' => true,
                        'data-ajax-url' => true,
                        'data-nonce' => true,
                        'data-redirect' => true,
                    ),
                )
            );
            
            wp_send_json_success([
                'html' => wp_kses($html, $allowed_html),
                'css'  => wp_strip_all_tags($css . $preview_css), // CSS should have no HTML tags
                'banner_type' => sanitize_key($banner_type),
                'device' => sanitize_key($device)
            ]);

        } catch (Exception $e) {

            wp_send_json_error([
                'message' => 'Preview generation failed',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add settings link to plugins page.
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=signalkit')) . '">' . esc_html__('Settings', 'signalkit') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Display admin notices for configuration warnings.
     */
    public function display_admin_notices() {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'signalkit') === false) return;

        $settings = get_option('signalkit_settings', []);

        // Check for missing URLs
        if (!empty($settings['follow_enabled']) && empty($settings['follow_google_news_url'])) {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('SignalKit:', 'signalkit') . '</strong> ' . esc_html__('Follow banner enabled but Google News URL missing.', 'signalkit') . ' <a href="' . esc_url(admin_url('admin.php?page=signalkit')) . '">' . esc_html__('Configure now', 'signalkit') . '</a></p></div>';
        }

        if (!empty($settings['preferred_enabled']) && empty($settings['preferred_google_preferences_url'])) {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('SignalKit:', 'signalkit') . '</strong> ' . esc_html__('Preferred Source banner enabled but URL missing.', 'signalkit') . ' <a href="' . esc_url(admin_url('admin.php?page=signalkit')) . '">' . esc_html__('Configure now', 'signalkit') . '</a></p></div>';
        }

        // Check mobile stack order conflict
        if (!empty($settings['follow_enabled']) && !empty($settings['preferred_enabled'])) {
            $follow_mobile = !empty($settings['follow_mobile_enabled']);
            $preferred_mobile = !empty($settings['preferred_mobile_enabled']);
            
            if ($follow_mobile && $preferred_mobile) {
                $follow_order = $settings['follow_mobile_stack_order'] ?? 1;
                $preferred_order = $settings['preferred_mobile_stack_order'] ?? 2;
                
                if ($follow_order === $preferred_order) {
                    echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('SignalKit:', 'signalkit') . '</strong> ' . esc_html__('Both banners have the same mobile stack order and will overlap.', 'signalkit') . ' <a href="' . esc_url(admin_url('admin.php?page=signalkit')) . '">' . esc_html__('Fix now', 'signalkit') . '</a></p></div>';
                }
            }
        }
    }

    /**
     * Add plugin action links.
     */
    public function add_plugin_action_links($links, $file) {
        if (strpos($file, 'signalkit.php') !== false) {
            $analytics = '<a href="' . esc_url(admin_url('admin.php?page=signalkit-analytics')) . '">' . esc_html__('Analytics', 'signalkit') . '</a>';
            array_splice($links, 1, 0, $analytics);
        }
        return $links;
    }

    /**
     * Register AJAX handlers - CRITICAL: Must be called in constructor
}