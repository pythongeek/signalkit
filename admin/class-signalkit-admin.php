<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package SignalKit_For_Google
 * @version 1.0.0 - SECURITY: Added audit logging for critical admin actions
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
 *         VALUES (%s, %s) 
 *         ON DUPLICATE KEY UPDATE value = %s",
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
 *         WHERE banner_type = %s",
 *         $banner_type
 *     )
 * );
 * 
 * // ❌ UNSAFE - NEVER DO THIS
 * $banner_type = $_POST['banner_type']; // Unsanitized
 * $wpdb->query(
 *     "DELETE FROM {$wpdb->prefix}signalkit_analytics 
 *     WHERE banner_type = '{$banner_type}'"
 * );
 * // ^ This allows SQL injection attacks!
 * 
 * WordPress Database API Documentation:
 * https://developer.wordpress.org/reference/classes/wpdb/
 */

if (!defined('ABSPATH')) {
    exit;
}

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
        
        // Register AJAX handlers immediately
        $this->register_ajax_handlers();
    }

    /**
     * Enqueue admin styles.
     */
    public function enqueue_styles() {
        // Only load on plugin pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'signalkit') === false) {
            return;
        }

        wp_enqueue_style(
            'signalkit-admin',
            SIGNALKIT_PLUGIN_URL . 'admin/css/signalkit-admin.css',
            array('wp-color-picker'),
            $this->version,
            'all'
        );
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

        // Localize script with AJAX URLs and nonces - WordPress best practice
        wp_localize_script('signalkit-admin', 'signalkitAdmin', array(
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('signalkit_admin_nonce'),
            'previewNonce'  => wp_create_nonce('signalkit_admin_nonce'),
            'strings'       => array(
                'confirmReset' => __('Are you sure you want to reset analytics? This cannot be undone.', 'signalkit-for-google'),
                'saved'        => __('Settings saved successfully!', 'signalkit-for-google'),
                'error'        => __('An error occurred. Please try again.', 'signalkit-for-google'),
            ),
        ));
        
        signalkit_log('Admin: Scripts enqueued with localized data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'screen' => $screen->id
        ));
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('SignalKit for Google', 'signalkit-for-google'),
            __('SignalKit', 'signalkit-for-google'),
            'manage_options',
            'signalkit-for-google',
            array($this->settings_page, 'render_settings_page'),
            'dashicons-megaphone',
            80
        );

        add_submenu_page(
            'signalkit-for-google',
            __('Settings', 'signalkit-for-google'),
            __('Settings', 'signalkit-for-google'),
            'manage_options',
            'signalkit-for-google',
            array($this->settings_page, 'render_settings_page')
        );

        add_submenu_page(
            'signalkit-for-google',
            __('Analytics', 'signalkit-for-google'),
            __('Analytics', 'signalkit-for-google'),
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
            array($this, 'sanitize_settings')
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
            : __('Follow Us On Google News', 'signalkit-for-google');

        $sanitized['follow_banner_headline'] = isset($input['follow_banner_headline'])
            ? sanitize_text_field($input['follow_banner_headline'])
            : __('Stay Updated with [site_name]', 'signalkit-for-google');

        // SECURITY: sanitize_textarea_field for multi-line text (removes HTML)
        $sanitized['follow_banner_description'] = isset($input['follow_banner_description'])
            ? sanitize_textarea_field($input['follow_banner_description'])
            : __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit-for-google');

        // SECURITY: sanitize_hex_color validates color format
        $sanitized['follow_primary_color']   = $this->sanitize_color($input, 'follow_primary_color', '#4285f4');
        $sanitized['follow_secondary_color'] = $this->sanitize_color($input, 'follow_secondary_color', '#ffffff');
        $sanitized['follow_accent_color']    = $this->sanitize_color($input, 'follow_accent_color', '#34a853');
        $sanitized['follow_text_color']      = $this->sanitize_color($input, 'follow_text_color', '#1a1a1a');

        // SECURITY: absint() ensures integers, range validation prevents extreme values
        $sanitized['follow_banner_width']     = $this->sanitize_number($input, 'follow_banner_width', 280, 600, 360);
        $sanitized['follow_banner_padding']   = $this->sanitize_number($input, 'follow_banner_padding', 8, 32, 16);
        $sanitized['follow_border_radius']    = $this->sanitize_number($input, 'follow_border_radius', 0, 32, 8);

        $sanitized['follow_font_size_headline']     = $this->sanitize_number($input, 'follow_font_size_headline', 12, 24, 15);
        $sanitized['follow_font_size_description'] = $this->sanitize_number($input, 'follow_font_size_description', 10, 18, 13);
        $sanitized['follow_font_size_button']       = $this->sanitize_number($input, 'follow_font_size_button', 11, 18, 14);

        // SECURITY: Whitelist validation - only allows predefined values
        $sanitized['follow_position']        = $this->sanitize_position($input, 'follow_position', 'bottom_left');
        $sanitized['follow_animation']       = $this->sanitize_animation($input, 'follow_animation', 'slide_in');
        $sanitized['follow_mobile_position'] = $this->sanitize_mobile_position($input, 'follow_mobile_position', 'bottom');
        $sanitized['follow_mobile_stack_order'] = $this->sanitize_number($input, 'follow_mobile_stack_order', 1, 2, 1);

        $sanitized['follow_dismissible']       = isset($input['follow_dismissible']) ? 1 : 0;
        $sanitized['follow_dismiss_duration']  = $this->sanitize_number($input, 'follow_dismiss_duration', 1, 365, 7);
        $sanitized['follow_show_frequency']    = $this->sanitize_frequency($input, 'follow_show_frequency', 'once_per_day');

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
            : __('Add As A Preferred Source', 'signalkit-for-google');

        $sanitized['preferred_banner_headline'] = isset($input['preferred_banner_headline'])
            ? sanitize_text_field($input['preferred_banner_headline'])
            : __('Add [site_name] As A Trusted Source', 'signalkit-for-google');

        $sanitized['preferred_banner_description'] = isset($input['preferred_banner_description'])
            ? sanitize_textarea_field($input['preferred_banner_description'])
            : __('Get priority access to our news and updates in your Google News feed.', 'signalkit-for-google');

        $sanitized['preferred_educational_text'] = isset($input['preferred_educational_text'])
            ? sanitize_text_field($input['preferred_educational_text'])
            : __('Learn More', 'signalkit-for-google');

        $sanitized['preferred_show_educational_link'] = isset($input['preferred_show_educational_link']) ? 1 : 0;

        $sanitized['preferred_primary_color']   = $this->sanitize_color($input, 'preferred_primary_color', '#4285f4');
        $sanitized['preferred_secondary_color'] = $this->sanitize_color($input, 'preferred_secondary_color', '#ffffff');
        $sanitized['preferred_accent_color']    = $this->sanitize_color($input, 'preferred_accent_color', '#ea4335');
        $sanitized['preferred_text_color']      = $this->sanitize_color($input, 'preferred_text_color', '#1a1a1a');

        $sanitized['preferred_banner_width']     = $this->sanitize_number($input, 'preferred_banner_width', 280, 600, 360);
        $sanitized['preferred_banner_padding']   = $this->sanitize_number($input, 'preferred_banner_padding', 8, 32, 16);
        $sanitized['preferred_border_radius']    = $this->sanitize_number($input, 'preferred_border_radius', 0, 32, 8);

        $sanitized['preferred_font_size_headline']     = $this->sanitize_number($input, 'preferred_font_size_headline', 12, 24, 15);
        $sanitized['preferred_font_size_description'] = $this->sanitize_number($input, 'preferred_font_size_description', 10, 18, 13);
        $sanitized['preferred_font_size_button']       = $this->sanitize_number($input, 'preferred_font_size_button', 11, 18, 14);

        $sanitized['preferred_position']        = $this->sanitize_position($input, 'preferred_position', 'bottom_right');
        $sanitized['preferred_animation']       = $this->sanitize_animation($input, 'preferred_animation', 'slide_in');
        $sanitized['preferred_mobile_position'] = $this->sanitize_mobile_position($input, 'preferred_mobile_position', 'bottom');
        $sanitized['preferred_mobile_stack_order'] = $this->sanitize_number($input, 'preferred_mobile_stack_order', 1, 2, 2);

        $sanitized['preferred_dismissible']       = isset($input['preferred_dismissible']) ? 1 : 0;
        $sanitized['preferred_dismiss_duration']  = $this->sanitize_number($input, 'preferred_dismiss_duration', 1, 365, 7);
        $sanitized['preferred_show_frequency']    = $this->sanitize_frequency($input, 'preferred_show_frequency', 'once_per_day');

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
        $sanitized['import_export_key'] = isset($input['import_export_key']) ? sanitize_text_field($input['import_export_key']) : '';

        signalkit_log('Admin: Settings sanitized', array('count' => count($sanitized)));
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
        $valid = ['slide_in', 'fade_in', 'bounce'];
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

    /**
     * Handle AJAX reset analytics.
     * 
     * SECURITY: Nonce verification, capability check, input sanitization, audit logging.
     */
    public function ajax_reset_analytics() {
        // SECURITY: Verify nonce to prevent CSRF attacks
        check_ajax_referer('signalkit_admin_nonce', 'nonce');

        // SECURITY: Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // SECURITY: Sanitize and validate input
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field($_POST['banner_type']) : 'all';
        $valid = ['follow', 'preferred', 'all'];

        // SECURITY: Whitelist validation
        if (!in_array($banner_type, $valid, true)) {
            wp_send_json_error(['message' => 'Invalid banner type']);
        }

        if (class_exists('SignalKit_Analytics')) {
            SignalKit_Analytics::reset_analytics($banner_type);
        }
        
        // --- AUDIT LOGGING ---
        $user = wp_get_current_user();
        signalkit_log(
            'Analytics data reset by user: ' . $user->user_login, 
            ['type' => $banner_type]
        );

        // Email site admin as an alert for this critical action
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Alert: Analytics Data Reset', 'signalkit-for-google'),
            sprintf(
                __('User %s reset the SignalKit analytics data (%s) at %s.', 'signalkit-for-google'),
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
     */
    public function ajax_export_settings() {
        // SECURITY: Verify nonce
        check_ajax_referer('signalkit_admin_nonce', 'nonce');
        
        // SECURITY: Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // --- AUDIT LOGGING (GDPR/Security Compliance) ---
        $user = wp_get_current_user();
        signalkit_log('Settings exported by user: ' . $user->user_login);
        
        // Email site admin as a security alert
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Security Alert: Settings Exported', 'signalkit-for-google'),
            sprintf(
                __('User %s exported the SignalKit plugin settings at %s.', 'signalkit-for-google'),
                esc_html($user->user_login),
                current_time('mysql')
            )
        );
        // --- END AUDIT LOGGING ---

        $settings = get_option('signalkit_settings', []);
        
        wp_send_json_success(['settings' => $settings]);
    }

    /**
     * AJAX: Import settings.
     * 
     * SECURITY v2.5.2: Added audit logging.
     * SECURITY v2.5.1: Enhanced validation for file upload vulnerability
     * - File size limit enforced (100KB max)
     * - JSON structure validation with required keys
     * - Settings count limit (max 100 settings)
     * - Full sanitization through sanitize_settings()
     */
    public function ajax_import_settings() {
        // SECURITY: Verify nonce
        check_ajax_referer('signalkit_admin_nonce', 'nonce');
        
        // SECURITY: Check permissions
        if (!current_user_can('manage_options')) {
            signalkit_log('Import: Unauthorized access attempt');
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // SECURITY: Get raw JSON string to check size
        $raw_json = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        
        // Convert to string if it's an array (from jQuery serialization)
        if (is_array($raw_json)) {
            $raw_json = wp_json_encode($raw_json);
        }
        
        // SECURITY FIX: Enforce 100KB file size limit
        if (strlen($raw_json) > 102400) {
            signalkit_log('Import: File too large', ['size' => strlen($raw_json)]);
            wp_send_json_error(['message' => 'File too large (max 100KB)']);
        }

        // Parse settings
        $settings = is_string($raw_json) ? json_decode($raw_json, true) : (array) $raw_json;
        
        if (empty($settings) || !is_array($settings)) {
            signalkit_log('Import: Invalid or empty settings data');
            wp_send_json_error(['message' => 'No valid settings provided']);
        }

        // SECURITY FIX: Validate JSON structure with required keys
        $required_keys = ['site_name', 'follow_enabled', 'preferred_enabled'];
        $missing_keys = [];
        
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $settings)) {
                $missing_keys[] = $key;
            }
        }
        
        if (!empty($missing_keys)) {
            signalkit_log('Import: Invalid settings file structure', ['missing' => $missing_keys]);
            wp_send_json_error([
                'message' => 'Invalid settings file structure',
                'details' => 'Missing required keys: ' . implode(', ', $missing_keys)
            ]);
        }

        // SECURITY FIX: Limit number of settings to prevent resource exhaustion
        if (count($settings) > 100) {
            signalkit_log('Import: Too many settings', ['count' => count($settings)]);
            wp_send_json_error(['message' => 'Settings file contains too many entries (max 100)']);
        }

        // SECURITY: Full sanitization through sanitize_settings()
        // This ensures all imported data is validated and cleaned
        $sanitized = $this->sanitize_settings($settings);
        
        // SECURITY: update_option is SQL-injection safe (WordPress handles escaping)
        update_option('signalkit_settings', $sanitized);

        // --- AUDIT LOGGING ---
        $user = wp_get_current_user();
        signalkit_log('Settings imported by user: ' . $user->user_login, [
            'count' => count($sanitized),
            'original_count' => count($settings)
        ]);

        // Email site admin as an alert for this critical action
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Security Alert: Settings Imported', 'signalkit-for-google'),
            sprintf(
                __('User %s imported and overwrote the SignalKit plugin settings at %s.', 'signalkit-for-google'),
                esc_html($user->user_login),
                current_time('mysql')
            )
        );
        // --- END AUDIT LOGGING ---
        
        wp_send_json_success([
            'message' => 'Settings imported successfully',
            'count' => count($sanitized)
        ]);
    }

    /**
     * AJAX: Preview Banner
     * 
     * PRODUCTION VERSION - Renders EXACTLY like live frontend
     * SECURITY: Nonce verification, capability check, input sanitization
     * 
     * @version 2.5.0
     */
    public function ajax_preview_banner() {
        // SECURITY: Verify nonce to prevent CSRF
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'signalkit_admin_nonce')) {
            signalkit_log('Admin Preview: Nonce verification failed');
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        // SECURITY: Check user permissions
        if (!current_user_can('manage_options')) {
            signalkit_log('Admin Preview: Unauthorized access');
            wp_send_json_error(['message' => 'Unauthorized']);
            return;
        }

        // SECURITY: Sanitize all inputs
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field($_POST['banner_type']) : '';
        $settings    = isset($_POST['settings']) ? (array) $_POST['settings'] : [];
        $device      = isset($_POST['device']) ? sanitize_text_field($_POST['device']) : 'desktop';

        // SECURITY: Whitelist validation for banner type
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            signalkit_log('Preview: Invalid banner type', ['type' => $banner_type]);
            wp_send_json_error(['message' => 'Invalid banner type']);
            return;
        }

        signalkit_log('Preview: Request received', [
            'type' => $banner_type,
            'device' => $device,
            'settings_keys' => array_keys($settings)
        ]);

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
            $public->render_banner($banner_type, $device);
            $html = ob_get_clean();

            // Validate output
            if (empty($html) || strlen($html) < 100) {
                signalkit_log('Preview: Invalid HTML output', ['length' => strlen($html)]);
                wp_send_json_error([
                    'message' => 'Banner rendering failed',
                    'details' => 'HTML output too short: ' . strlen($html) . ' bytes'
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

            signalkit_log('Preview: Success', [
                'html_length' => strlen($html),
                'css_length' => strlen($css)
            ]);

            wp_send_json_success([
                'html' => $html,
                'css'  => $css . $preview_css,
                'banner_type' => $banner_type,
                'device' => $device
            ]);

        } catch (Exception $e) {
            signalkit_log('Preview: Exception', [
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ]);
            
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
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=signalkit-for-google')) . '">' . esc_html__('Settings', 'signalkit-for-google') . '</a>';
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
            echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('SignalKit:', 'signalkit-for-google') . '</strong> ' . esc_html__('Follow banner enabled but Google News URL missing.', 'signalkit-for-google') . ' <a href="' . esc_url(admin_url('admin.php?page=signalkit-for-google')) . '">' . esc_html__('Configure now', 'signalkit-for-google') . '</a></p></div>';
        }

        if (!empty($settings['preferred_enabled']) && empty($settings['preferred_google_preferences_url'])) {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('SignalKit:', 'signalkit-for-google') . '</strong> ' . esc_html__('Preferred Source banner enabled but URL missing.', 'signalkit-for-google') . ' <a href="' . esc_url(admin_url('admin.php?page=signalkit-for-google')) . '">' . esc_html__('Configure now', 'signalkit-for-google') . '</a></p></div>';
        }

        // Check mobile stack order conflict
        if (!empty($settings['follow_enabled']) && !empty($settings['preferred_enabled'])) {
            $follow_mobile = !empty($settings['follow_mobile_enabled']);
            $preferred_mobile = !empty($settings['preferred_mobile_enabled']);
            
            if ($follow_mobile && $preferred_mobile) {
                $follow_order = $settings['follow_mobile_stack_order'] ?? 1;
                $preferred_order = $settings['preferred_mobile_stack_order'] ?? 2;
                
                if ($follow_order === $preferred_order) {
                    echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('SignalKit:', 'signalkit-for-google') . '</strong> ' . esc_html__('Both banners have the same mobile stack order and will overlap.', 'signalkit-for-google') . ' <a href="' . esc_url(admin_url('admin.php?page=signalkit-for-google')) . '">' . esc_html__('Fix now', 'signalkit-for-google') . '</a></p></div>';
                }
            }
        }
    }

    /**
     * Add plugin action links.
     */
    public function add_plugin_action_links($links, $file) {
        if (strpos($file, 'signalkit-for-google.php') !== false) {
            $analytics = '<a href="' . esc_url(admin_url('admin.php?page=signalkit-analytics')) . '">' . esc_html__('Analytics', 'signalkit-for-google') . '</a>';
            array_splice($links, 1, 0, $analytics);
        }
        return $links;
    }

    /**
     * Register AJAX handlers - CRITICAL: Must be called in constructor
     * 
     * SECURITY: All AJAX handlers verify nonces and check capabilities
     */
    public function register_ajax_handlers() {
        add_action('wp_ajax_signalkit_reset_analytics', array($this, 'ajax_reset_analytics'));
        add_action('wp_ajax_signalkit_export_settings', array($this, 'ajax_export_settings'));
        add_action('wp_ajax_signalkit_import_settings', array($this, 'ajax_import_settings'));
        add_action('wp_ajax_signalkit_preview_banner', array($this, 'ajax_preview_banner'));
        
        signalkit_log('Admin: AJAX handlers registered');
    }

}
