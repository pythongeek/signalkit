<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package SignalKit_For_Google
 * @version 2.2.0 - PRODUCTION: Preview matches live frontend exactly
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
     * @param array $input Raw input data
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // GLOBAL SETTINGS
        $sanitized['site_name'] = isset($input['site_name'])
            ? sanitize_text_field($input['site_name'])
            : get_bloginfo('name');

        // FOLLOW BANNER SETTINGS
        $sanitized['follow_enabled'] = isset($input['follow_enabled']) ? 1 : 0;
        $sanitized['follow_google_news_url'] = isset($input['follow_google_news_url'])
            ? esc_url_raw($input['follow_google_news_url'])
            : '';

        $sanitized['follow_button_text'] = isset($input['follow_button_text'])
            ? sanitize_text_field($input['follow_button_text'])
            : __('Follow Us On Google News', 'signalkit-for-google');

        $sanitized['follow_banner_headline'] = isset($input['follow_banner_headline'])
            ? sanitize_text_field($input['follow_banner_headline'])
            : __('Stay Updated with [site_name]', 'signalkit-for-google');

        $sanitized['follow_banner_description'] = isset($input['follow_banner_description'])
            ? sanitize_textarea_field($input['follow_banner_description'])
            : __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit-for-google');

        $sanitized['follow_primary_color']   = $this->sanitize_color($input, 'follow_primary_color', '#4285f4');
        $sanitized['follow_secondary_color'] = $this->sanitize_color($input, 'follow_secondary_color', '#ffffff');
        $sanitized['follow_accent_color']    = $this->sanitize_color($input, 'follow_accent_color', '#34a853');
        $sanitized['follow_text_color']      = $this->sanitize_color($input, 'follow_text_color', '#1a1a1a');

        $sanitized['follow_banner_width']     = $this->sanitize_number($input, 'follow_banner_width', 280, 600, 360);
        $sanitized['follow_banner_padding']   = $this->sanitize_number($input, 'follow_banner_padding', 8, 32, 16);
        $sanitized['follow_border_radius']    = $this->sanitize_number($input, 'follow_border_radius', 0, 32, 8);

        $sanitized['follow_font_size_headline']     = $this->sanitize_number($input, 'follow_font_size_headline', 12, 24, 15);
        $sanitized['follow_font_size_description'] = $this->sanitize_number($input, 'follow_font_size_description', 10, 18, 13);
        $sanitized['follow_font_size_button']       = $this->sanitize_number($input, 'follow_font_size_button', 11, 18, 14);

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
     */
    private function sanitize_color($input, $key, $default) {
        if (!isset($input[$key])) return $default;
        $color = sanitize_hex_color($input[$key]);
        return $color ? $color : $default;
    }

    /**
     * Sanitize number within range
     */
    private function sanitize_number($input, $key, $min, $max, $default) {
        if (!isset($input[$key])) return $default;
        $value = absint($input[$key]);
        return max($min, min($max, $value));
    }

    /**
     * Sanitize position value
     */
    private function sanitize_position($input, $key, $default) {
        $valid = ['bottom_left', 'bottom_right', 'bottom_center', 'top_left', 'top_right', 'top_center'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    /**
     * Sanitize mobile position value
     */
    private function sanitize_mobile_position($input, $key, $default) {
        $valid = ['top', 'bottom'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    /**
     * Sanitize animation value
     */
    private function sanitize_animation($input, $key, $default) {
        $valid = ['slide_in', 'fade_in', 'bounce'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    /**
     * Sanitize frequency value
     */
    private function sanitize_frequency($input, $key, $default) {
        $valid = ['always', 'once_per_session', 'once_per_day'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    /**
     * Handle AJAX reset analytics.
     */
    public function ajax_reset_analytics() {
        check_ajax_referer('signalkit_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $banner_type = sanitize_text_field($_POST['banner_type'] ?? 'all');
        $valid = ['follow', 'preferred', 'all'];

        if (!in_array($banner_type, $valid, true)) {
            wp_send_json_error(['message' => 'Invalid banner type']);
        }

        if (class_exists('SignalKit_Analytics')) {
            SignalKit_Analytics::reset_analytics($banner_type);
        }
        
        signalkit_log('Admin: Analytics reset', ['type' => $banner_type]);

        wp_send_json_success(['message' => 'Analytics reset', 'banner_type' => $banner_type]);
    }

    /**
     * AJAX: Export settings.
     */
    public function ajax_export_settings() {
        check_ajax_referer('signalkit_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $settings = get_option('signalkit_settings', []);
        signalkit_log('Admin: Settings exported', ['count' => count($settings)]);
        
        wp_send_json_success(['settings' => $settings]);
    }

    /**
     * AJAX: Import settings.
     */
    public function ajax_import_settings() {
        check_ajax_referer('signalkit_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $settings = isset($_POST['settings']) ? (array) $_POST['settings'] : [];
        
        if (empty($settings)) {
            wp_send_json_error(['message' => 'No settings provided']);
        }

        $sanitized = $this->sanitize_settings($settings);
        update_option('signalkit_settings', $sanitized);

        signalkit_log('Admin: Settings imported', ['count' => count($sanitized)]);
        wp_send_json_success(['message' => 'Settings imported']);
    }

    /**
     * AJAX: Preview Banner
     * 
     * PRODUCTION VERSION - Renders EXACTLY like live frontend
     * 
     * @version 2.2.0
     */
    public function ajax_preview_banner() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'signalkit_admin_nonce')) {
            signalkit_log('Admin Preview: Nonce verification failed');
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            signalkit_log('Admin Preview: Unauthorized access');
            wp_send_json_error(['message' => 'Unauthorized']);
            return;
        }

        // Sanitize inputs
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field($_POST['banner_type']) : '';
        $settings    = isset($_POST['settings']) ? (array) $_POST['settings'] : [];
        $device      = isset($_POST['device']) ? sanitize_text_field($_POST['device']) : 'desktop';

        // Validate banner type
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
     */
    public function register_ajax_handlers() {
        add_action('wp_ajax_signalkit_reset_analytics', array($this, 'ajax_reset_analytics'));
        add_action('wp_ajax_signalkit_export_settings', array($this, 'ajax_export_settings'));
        add_action('wp_ajax_signalkit_import_settings', array($this, 'ajax_import_settings'));
        add_action('wp_ajax_signalkit_preview_banner', array($this, 'ajax_preview_banner'));
        
        signalkit_log('Admin: AJAX handlers registered');
    }
}