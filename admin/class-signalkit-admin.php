<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package SignalKit_For_Google
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
        $this->version = $version;
        
        // Pass plugin_name and version to Settings class
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
        
        wp_localize_script('signalkit-admin', 'signalkitAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('signalkit_admin_nonce'),
            'strings' => array(
                'confirmReset' => __('Are you sure you want to reset analytics? This cannot be undone.', 'signalkit-for-google'),
                'saved' => __('Settings saved successfully!', 'signalkit-for-google'),
                'error' => __('An error occurred. Please try again.', 'signalkit-for-google'),
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
     * Sanitize settings.
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Global settings
        $sanitized['site_name'] = sanitize_text_field($input['site_name']);
        
        // Follow Banner Settings
        $sanitized['follow_enabled'] = isset($input['follow_enabled']) ? 1 : 0;
        $sanitized['follow_google_news_url'] = esc_url_raw($input['follow_google_news_url']);
        $sanitized['follow_button_text'] = sanitize_text_field($input['follow_button_text']);
        $sanitized['follow_banner_headline'] = sanitize_text_field($input['follow_banner_headline']);
        $sanitized['follow_banner_description'] = sanitize_textarea_field($input['follow_banner_description']);
        $sanitized['follow_primary_color'] = sanitize_hex_color($input['follow_primary_color']);
        $sanitized['follow_secondary_color'] = sanitize_hex_color($input['follow_secondary_color']);
        $sanitized['follow_accent_color'] = sanitize_hex_color($input['follow_accent_color']);
        $sanitized['follow_position'] = sanitize_text_field($input['follow_position']);
        $sanitized['follow_animation'] = sanitize_text_field($input['follow_animation']);
        $sanitized['follow_dismissible'] = isset($input['follow_dismissible']) ? 1 : 0;
        $sanitized['follow_dismiss_duration'] = absint($input['follow_dismiss_duration']);
        $sanitized['follow_show_frequency'] = sanitize_text_field($input['follow_show_frequency']);
        $sanitized['follow_mobile_enabled'] = isset($input['follow_mobile_enabled']) ? 1 : 0;
        $sanitized['follow_desktop_enabled'] = isset($input['follow_desktop_enabled']) ? 1 : 0;
        $sanitized['follow_show_on_posts'] = isset($input['follow_show_on_posts']) ? 1 : 0;
        $sanitized['follow_show_on_pages'] = isset($input['follow_show_on_pages']) ? 1 : 0;
        $sanitized['follow_show_on_homepage'] = isset($input['follow_show_on_homepage']) ? 1 : 0;
        $sanitized['follow_show_on_archive'] = isset($input['follow_show_on_archive']) ? 1 : 0;
        
        // Preferred Source Banner Settings
        $sanitized['preferred_enabled'] = isset($input['preferred_enabled']) ? 1 : 0;
        $sanitized['preferred_google_preferences_url'] = esc_url_raw($input['preferred_google_preferences_url']);
        $sanitized['preferred_educational_post_url'] = esc_url_raw($input['preferred_educational_post_url']);
        $sanitized['preferred_button_text'] = sanitize_text_field($input['preferred_button_text']);
        $sanitized['preferred_banner_headline'] = sanitize_text_field($input['preferred_banner_headline']);
        $sanitized['preferred_banner_description'] = sanitize_textarea_field($input['preferred_banner_description']);
        $sanitized['preferred_educational_text'] = sanitize_text_field($input['preferred_educational_text']);
        $sanitized['preferred_show_educational_link'] = isset($input['preferred_show_educational_link']) ? 1 : 0;
        $sanitized['preferred_primary_color'] = sanitize_hex_color($input['preferred_primary_color']);
        $sanitized['preferred_secondary_color'] = sanitize_hex_color($input['preferred_secondary_color']);
        $sanitized['preferred_accent_color'] = sanitize_hex_color($input['preferred_accent_color']);
        $sanitized['preferred_position'] = sanitize_text_field($input['preferred_position']);
        $sanitized['preferred_animation'] = sanitize_text_field($input['preferred_animation']);
        $sanitized['preferred_dismissible'] = isset($input['preferred_dismissible']) ? 1 : 0;
        $sanitized['preferred_dismiss_duration'] = absint($input['preferred_dismiss_duration']);
        $sanitized['preferred_show_frequency'] = sanitize_text_field($input['preferred_show_frequency']);
        $sanitized['preferred_mobile_enabled'] = isset($input['preferred_mobile_enabled']) ? 1 : 0;
        $sanitized['preferred_desktop_enabled'] = isset($input['preferred_desktop_enabled']) ? 1 : 0;
        $sanitized['preferred_show_on_posts'] = isset($input['preferred_show_on_posts']) ? 1 : 0;
        $sanitized['preferred_show_on_pages'] = isset($input['preferred_show_on_pages']) ? 1 : 0;
        $sanitized['preferred_show_on_homepage'] = isset($input['preferred_show_on_homepage']) ? 1 : 0;
        $sanitized['preferred_show_on_archive'] = isset($input['preferred_show_on_archive']) ? 1 : 0;
        
        signalkit_log('Admin: Settings sanitized', $sanitized);
        
        return $sanitized;
    }
    
    /**
     * Handle AJAX reset analytics.
     */
    public function ajax_reset_analytics() {
        check_ajax_referer('signalkit_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field($_POST['banner_type']) : 'all';
        
        SignalKit_Analytics::reset_analytics($banner_type);
        
        wp_send_json_success(array(
            'message' => 'Analytics reset successfully',
            'banner_type' => $banner_type,
        ));
    }
    
    /**
     * Add settings link to plugins page.
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=signalkit-for-google') . '">' . __('Settings', 'signalkit-for-google') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Display admin notices.
     */
    public function display_admin_notices() {
        $settings = get_option('signalkit_settings', array());
        
        // Check if Follow banner is enabled but URL is missing
        if (!empty($settings['follow_enabled']) && empty($settings['follow_google_news_url'])) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php _e('SignalKit for Google:', 'signalkit-for-google'); ?></strong>
                    <?php _e('Follow banner is enabled but Google News URL is missing.', 'signalkit-for-google'); ?>
                    <a href="<?php echo admin_url('admin.php?page=signalkit-for-google'); ?>"><?php _e('Configure now', 'signalkit-for-google'); ?></a>
                </p>
            </div>
            <?php
        }
        
        // Check if Preferred banner is enabled but URL is missing
        if (!empty($settings['preferred_enabled']) && empty($settings['preferred_google_preferences_url'])) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php _e('SignalKit for Google:', 'signalkit-for-google'); ?></strong>
                    <?php _e('Preferred Source banner is enabled but Google Preferences URL is missing.', 'signalkit-for-google'); ?>
                    <a href="<?php echo admin_url('admin.php?page=signalkit-for-google'); ?>"><?php _e('Configure now', 'signalkit-for-google'); ?></a>
                </p>
            </div>
            <?php
        }
    }
}