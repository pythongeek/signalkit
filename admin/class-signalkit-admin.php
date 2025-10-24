<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package SignalKit_For_Google
 * @version 2.1.1 - LIVE PREVIEW AJAX HANDLER FIXED + NONCE + CSS
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Admin {

    private $plugin_name;
    private $version;
    private $settings_page;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        // Initialize settings page
        $this->settings_page = new SignalKit_Settings($plugin_name, $version);
    }

    /**
     * Enqueue admin styles.
     */
    public function enqueue_styles() {
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
        wp_enqueue_script(
            'signalkit-admin',
            SIGNALKIT_PLUGIN_URL . 'admin/js/signalkit-admin.js',
            array('jquery', 'wp-color-picker'),
            $this->version,
            true
        );

        // Localize script with AJAX URLs and nonces
        wp_localize_script('signalkit-admin', 'signalkitAdmin', array(
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('signalkit_admin_nonce'),
            'previewNonce'  => wp_create_nonce('signalkit_admin_nonce'), // ← FIXED: Use same nonce
            'strings'       => array(
                'confirmReset' => __('Are you sure you want to reset analytics? This cannot be undone.', 'signalkit-for-google'),
                'saved'        => __('Settings saved successfully!', 'signalkit-for-google'),
                'error'        => __('An error occurred. Please try again.', 'signalkit-for-google'),
            ),
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
        $sanitized['follow_mobile_stack_order'] = $this->sanitize_number($input, 'follow423_mobile_stack_order', 1, 2, 1);

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

        signalkit_log('Admin: Settings sanitized', array('count' => count($sanitized)));
        return $sanitized;
    }

    // === SANITIZATION HELPERS ===
    private function sanitize_color($input, $key, $default) {
        return isset($input[$key]) ? sanitize_hex_color($input[$key]) ?: $default : $default;
    }

    private function sanitize_number($input, $key, $min, $max, $default) {
        if (!isset($input[$key])) return $default;
        $value = absint($input[$key]);
        return $value < $min ? $min : ($value > $max ? $max : $value);
    }

    private function sanitize_position($input, $key, $default) {
        $valid = ['bottom_left', 'bottom_right', 'bottom_center', 'top_left', 'top_right', 'top_center'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    private function sanitize_mobile_position($input, $key, $default) {
        $valid = ['top', 'bottom'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

    private function sanitize_animation($input, $key, $default) {
        $valid = ['slide_in', 'fade_in', 'bounce'];
        return isset($input[$key]) && in_array($input[$key], $valid, true) ? $input[$key] : $default;
    }

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

        SignalKit_Analytics::reset_analytics($banner_type);
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
     * AJAX: Preview Banner - RENDER HTML + CSS FOR LIVE PREVIEW
     */
    public function ajax_preview_banner() {
        check_ajax_referer('signalkit_admin_nonce', 'nonce'); // ← FIXED: Use same nonce

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        $settings    = isset($_POST['settings']) ? (array) $_POST['settings'] : [];
        $device      = sanitize_text_field($_POST['device'] ?? 'desktop');

        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type']);
        }

        // Merge with current settings to avoid missing keys
        $current = get_option('signalkit_settings', []);
        $preview_settings = array_merge($current, $settings);
        $sanitized = $this->sanitize_settings($preview_settings);

        // Use public class to generate CSS
        $public = new SignalKit_Public('', '');
        $public->settings = $sanitized; // Inject sanitized settings

        // Generate CSS for this banner only
        $css = $public->generate_banner_css($banner_type);
        if ($device === 'mobile') {
            $css .= $public->generate_mobile_stacking_css();
        }

        // Build banner data
        $prefix = $banner_type . '_';
        $banner = [
            'type' => $banner_type,
            'enabled' => !empty($sanitized[$prefix . 'enabled']),
            'headline' => str_replace('[site_name]', $sanitized['site_name'], $sanitized[$prefix . 'banner_headline']),
            'description' => $sanitized[$prefix . 'banner_description'],
            'button_text' => $sanitized[$prefix . 'button_text'],
            'button_url' => $banner_type === 'follow' ? $sanitized['follow_google_news_url'] : $sanitized['preferred_google_preferences_url'],
            'primary_color' => $sanitized[$prefix . 'primary_color'],
            'secondary_color' => $sanitized[$prefix . 'secondary_color'],
            'accent_color' => $sanitized[$prefix . 'accent_color'],
            'text_color' => $sanitized[$prefix . 'text_color'],
            'position' => $sanitized[$prefix . 'position'],
            'mobile_position' => $sanitized[$prefix . 'mobile_position'],
            'animation' => $sanitized[$prefix . 'animation'],
            'dismissible' => !empty($sanitized[$prefix . 'dismissible']),
            'banner_width' => $sanitized[$prefix . 'banner_width'],
            'banner_padding' => $sanitized[$prefix . 'banner_padding'],
            'border_radius' => $sanitized[$prefix . 'border_radius'],
            'font_size_headline' => $sanitized[$prefix . 'font_size_headline'],
            'font_size_description' => $sanitized[$prefix . 'font_size_description'],
            'font_size_button' => $sanitized[$prefix . 'font_size_button'],
            'educational_text' => $banner_type === 'preferred' ? $sanitized['preferred_educational_text'] : '',
            'show_educational' => $banner_type === 'preferred' && !empty($sanitized['preferred_show_educational_link']),
            'educational_url' => $banner_type === 'preferred' ? $sanitized['preferred_educational_post_url'] : '#',
            'device' => $device,
            'mobile_stack_order' => $sanitized[$prefix . 'mobile_stack_order']
        ];

        // Render HTML
        $template = SIGNALKIT_PLUGIN_DIR . 'public/partials/banner-' . $banner_type . '.php'; // ← FIXED: Use DIR
        if (!file_exists($template)) {
            wp_send_json_error(['message' => 'Template missing']);
        }

        ob_start();
        include $template;
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'css'  => $css // ← ADDED: Return CSS
        ]);
    }

    /**
     * Add settings link.
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=signalkit-for-google') . '">' . __('Settings', 'signalkit-for-google') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Admin notices.
     */
    public function display_admin_notices() {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'signalkit') === false) return;

        $settings = get_option('signalkit_settings', []);

        if (!empty($settings['follow_enabled']) && empty($settings['follow_google_news_url'])) {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>SignalKit:</strong> Follow banner enabled but Google News URL missing. <a href="' . admin_url('admin.php?page=signalkit-for-google') . '">Configure now</a></p></div>';
        }

        if (!empty($settings['preferred_enabled']) && empty($settings['preferred_google_preferences_url'])) {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>SignalKit:</strong> Preferred Source banner enabled but URL missing. <a href="' . admin_url('admin.php?page=signalkit-for-google') . '">Configure now</a></p></div>';
        }

        if (!empty($settings['follow_enabled']) && !empty($settings['preferred_enabled'])) {
            $f = !empty($settings['follow_mobile_enabled']);
            $p = !empty($settings['preferred_mobile_enabled']);
            if ($f && $p && ($settings['follow_mobile_stack_order'] ?? 1) === ($settings['preferred_mobile_stack_order'] ?? 2)) {
                echo '<div class="notice notice-info is-dismissible"><p><strong>SignalKit:</strong> Both banners have same mobile stack order. May overlap. <a href="' . admin_url('admin.php?page=signalkit-for-google') . '">Fix now</a></p></div>';
            }
        }
    }

    /**
     * Plugin row meta links.
     */
    public function add_plugin_action_links($links, $file) {
        if (strpos($file, 'signalkit-for-google.php') !== false) {
            $analytics = '<a href="' . admin_url('admin.php?page=signalkit-analytics') . '">' . __('Analytics', 'signalkit-for-google') . '</a>';
            array_splice($links, 1, 0, $analytics);
        }
        return $links;
    }

    /**
     * Register AJAX handlers.
     */
    public function register_ajax_handlers() {
        add_action('wp_ajax_signalkit_reset_analytics', array($this, 'ajax_reset_analytics'));
        add_action('wp_ajax_signalkit_export_settings', array($this, 'ajax_export_settings'));
        add_action('wp_ajax_signalkit_import_settings', array($this, 'ajax_import_settings'));
        add_action('wp_ajax_signalkit_preview_banner', array($this, 'ajax_preview_banner'));
    }
}