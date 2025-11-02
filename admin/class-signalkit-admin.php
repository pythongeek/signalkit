<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package SignalKit
 * @version 1.0.0
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
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('signalkit_admin_nonce'),
            'strings'    => array(
                'confirmReset' => __('Are you sure you want to reset analytics? This cannot be undone.', 'signalkit'),
                'saved'        => __('Settings saved successfully!', 'signalkit'),
                'error'        => __('An error occurred. Please try again.', 'signalkit'),
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

    // === SECURITY & AJAX HANDLERS ===

    /**
     * Get user IP address for rate limiting.
     *
     * @return string
     */
    private function get_user_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field(wp_unslash($ip));
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
            signalkit_log('Rate limit exceeded', ['ip' => $ip, 'action' => $action_name]);
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
                signalkit_log('Encryption failed: openssl_encrypt returned false');
                return false;
            }
            
            return base64_encode($iv . $ciphertext);
        } catch (Exception $e) {
            signalkit_log('Encryption Exception', ['message' => $e->getMessage()]);
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
                signalkit_log('Decryption failed: openssl_decrypt returned false (likely wrong key)');
                return false;
            }
            
            return $decrypted;
        } catch (Exception $e) {
            signalkit_log('Decryption Exception', ['message' => $e->getMessage()]);
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
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // SECURITY: Enforce rate limiting (10 seconds for this destructive action)
        $this->check_rate_limit('reset_analytics', 10);

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
            __('SignalKit Alert: Analytics Data Reset', 'signalkit'),
            sprintf(
                __('User %s reset the SignalKit analytics data (%s) at %s.', 'signalkit'),
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
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // SECURITY: Enforce rate limiting (5 seconds)
        $this->check_rate_limit('export_settings', 5);

        // --- AUDIT LOGGING (GDPR/Security Compliance) ---
        $user = wp_get_current_user();
        signalkit_log('Settings exported by user: ' . $user->user_login);
        
        // Email site admin as a security alert
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Security Alert: Settings Exported', 'signalkit'),
            sprintf(
                __('User %s exported the SignalKit plugin settings at %s.', 'signalkit'),
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
                signalkit_log('Settings export encrypted successfully');
            } else {
                signalkit_log('Settings export encryption failed');
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
        
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'signalkit_admin_nonce')) {
            $nonce_verified = true;
        }
        
        if (!$nonce_verified && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'signalkit_import_settings')) {
            $nonce_verified = true;
        }
        
        if (!$nonce_verified) {
            signalkit_log('Import: Nonce verification failed');
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        // SECURITY: Check permissions
        if (!current_user_can('manage_options')) {
            signalkit_log('Import: Unauthorized access attempt');
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // SECURITY: Enforce rate limiting (10 seconds for this destructive action)
        $this->check_rate_limit('import_settings', 10);

        $is_encrypted = isset($_POST['encrypted']) && $_POST['encrypted'] == 1;
        $raw_data = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        
        // SECURITY: Enforce 100KB file size limit (for both text and JSON)
        if (strlen($raw_data) > 102400) {
            signalkit_log('Import: File too large', ['size' => strlen($raw_data)]);
            wp_send_json_error(['message' => 'File too large (max 100KB)']);
        }

        $settings_json = $raw_data;

        if ($is_encrypted) {
            signalkit_log('Import: Attempting to decrypt settings');
            $current_settings = get_option('signalkit_settings', []);
            $key = $current_settings['import_export_key'] ?? '';

            if (empty($key)) {
                signalkit_log('Import: Decryption failed - No key set');
                wp_send_json_error(['message' => 'Decryption failed: Please set your Import/Export Key before importing.']);
            }

            $settings_json = $this->decrypt_settings($raw_data, $key);

            if ($settings_json === false) {
                signalkit_log('Import: Decryption failed - Invalid key or corrupt data');
                wp_send_json_error(['message' => 'Decryption failed: Invalid key or corrupt data.']);
            }
        }

        // Parse settings
        $settings = json_decode($settings_json, true);
        
        if (empty($settings) || !is_array($settings)) {
            signalkit_log('Import: Invalid or empty settings data after parse');
            wp_send_json_error(['message' => 'No valid settings provided or JSON is corrupt.']);
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
            'import_export_key' => 'string'
        );

        // Validate against schema and remove unknown keys
        $validated_settings = array();
        $validation_errors = array();
        
        foreach ($settings as $key => $value) {
            // Remove keys not in schema
            if (!isset($schema[$key])) {
                signalkit_log('Import: Unknown key removed', ['key' => $key]);
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
                signalkit_log('Import: Type validation failed', [
                    'key' => $key,
                    'expected' => $expected_type,
                    'received' => gettype($value)
                ]);
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
            signalkit_log('Import: Missing required keys', ['missing' => $missing_keys]);
            wp_send_json_error([
                'message' => 'Invalid settings file structure',
                'details' => 'Missing required keys: ' . implode(', ', $missing_keys)
            ]);
        }

        // Log validation errors if any (non-fatal)
        if (!empty($validation_errors)) {
            signalkit_log('Import: Validation warnings', ['errors' => $validation_errors]);
        }

        // SECURITY: Limit number of settings to prevent resource exhaustion
        if (count($validated_settings) > 100) {
            signalkit_log('Import: Too many settings', ['count' => count($validated_settings)]);
            wp_send_json_error(['message' => 'Settings file contains too many entries (max 100)']);
        }

        // SECURITY: Full sanitization through sanitize_settings()
        $sanitized = $this->sanitize_settings($validated_settings);
        
        // SECURITY: update_option is SQL-injection safe (WordPress handles escaping)
        update_option('signalkit_settings', $sanitized);

        // --- AUDIT LOGGING ---
        $user = wp_get_current_user();
        signalkit_log('Settings imported by user: ' . $user->user_login, [
            'count' => count($sanitized),
            'original_count' => count($settings),
            'validated_count' => count($validated_settings),
            'validation_errors' => count($validation_errors),
            'was_encrypted' => $is_encrypted
        ]);

        // Email site admin as an alert for this critical action
        wp_mail(
            get_option('admin_email'),
            __('SignalKit Security Alert: Settings Imported', 'signalkit'),
            sprintf(
                __('User %s imported and overwrote the SignalKit plugin settings at %s. (Encrypted: %s)', 'signalkit'),
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

        // SECURITY: Enforce rate limiting (1 second for rapid preview)
        $this->check_rate_limit('preview_banner', 1);

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

            // IMPROVED: Validate output by checking for banner wrapper
            if (empty($html) || strpos($html, 'class="signalkit-banner') === false) {
                signalkit_log('Preview: Invalid HTML output', [
                    'length' => strlen($html),
                    'details' => 'Main banner wrapper not found in output'
                ]);
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