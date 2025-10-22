<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Public {
    
    private $plugin_name;
    private $version;
    private $settings;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = get_option('signalkit_settings', array());
    }
    
    /**
     * Enqueue public styles.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'signalkit-public',
            SIGNALKIT_PLUGIN_URL . 'public/css/signalkit-public.css',
            array(),
            $this->version,
            'all'
        );
        
        // Add inline custom colors for BOTH banners
        $custom_css = $this->get_custom_css();
        wp_add_inline_style('signalkit-public', $custom_css);
    }
    
    /**
     * Enqueue public scripts.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'signalkit-public',
            SIGNALKIT_PLUGIN_URL . 'public/js/signalkit-public.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Localize script with settings for BOTH banners
        wp_localize_script('signalkit-public', 'signalkitData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('signalkit_nonce'),
            'followSettings' => array(
                'dismissDuration' => isset($this->settings['follow_dismiss_duration']) ? intval($this->settings['follow_dismiss_duration']) : 7,
                'animation' => isset($this->settings['follow_animation']) ? $this->settings['follow_animation'] : 'slide_in',
                'position' => isset($this->settings['follow_position']) ? $this->settings['follow_position'] : 'bottom_left',
                'dismissible' => isset($this->settings['follow_dismissible']) ? $this->settings['follow_dismissible'] : true,
            ),
            'preferredSettings' => array(
                'dismissDuration' => isset($this->settings['preferred_dismiss_duration']) ? intval($this->settings['preferred_dismiss_duration']) : 7,
                'animation' => isset($this->settings['preferred_animation']) ? $this->settings['preferred_animation'] : 'slide_in',
                'position' => isset($this->settings['preferred_position']) ? $this->settings['preferred_position'] : 'bottom_right',
                'dismissible' => isset($this->settings['preferred_dismissible']) ? $this->settings['preferred_dismissible'] : true,
            ),
        ));
    }
    
    /**
     * Display banners in footer.
     */
    public function display_banners() {
        // Display Follow Banner
        if (SignalKit_Display_Rules::should_display('follow')) {
            $this->render_banner('follow');
            SignalKit_Display_Rules::set_frequency_cookie($this->settings, 'follow');
            SignalKit_Analytics::track_impression('follow');
        }
        
        // Display Preferred Source Banner
        if (SignalKit_Display_Rules::should_display('preferred')) {
            $this->render_banner('preferred');
            SignalKit_Display_Rules::set_frequency_cookie($this->settings, 'preferred');
            SignalKit_Analytics::track_impression('preferred');
        }
    }
    
    /**
     * Render specific banner.
     */
    private function render_banner($banner_type) {
        $template_file = SIGNALKIT_PLUGIN_DIR . "public/partials/banner-{$banner_type}.php";
        
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            signalkit_log("Public: Banner template not found for {$banner_type}", $template_file);
        }
    }
    
    /**
     * Generate custom CSS for both banners.
     */
    private function get_custom_css() {
        $css = '';
        
        // Follow Banner Colors
        if (!empty($this->settings['follow_primary_color'])) {
            $follow_primary = sanitize_hex_color($this->settings['follow_primary_color']);
            $follow_secondary = sanitize_hex_color($this->settings['follow_secondary_color']);
            $follow_accent = sanitize_hex_color($this->settings['follow_accent_color']);
            
            $css .= "
                .signalkit-banner-follow {
                    background: linear-gradient(135deg, {$follow_primary} 0%, {$follow_accent} 100%);
                }
                .signalkit-banner-follow .signalkit-button {
                    background-color: {$follow_secondary};
                    color: {$follow_primary};
                }
                .signalkit-banner-follow .signalkit-button:hover {
                    background-color: {$follow_accent};
                    color: {$follow_secondary};
                }
            ";
        }
        
        // Preferred Banner Colors
        if (!empty($this->settings['preferred_primary_color'])) {
            $preferred_primary = sanitize_hex_color($this->settings['preferred_primary_color']);
            $preferred_secondary = sanitize_hex_color($this->settings['preferred_secondary_color']);
            $preferred_accent = sanitize_hex_color($this->settings['preferred_accent_color']);
            
            $css .= "
                .signalkit-banner-preferred {
                    background: linear-gradient(135deg, {$preferred_primary} 0%, {$preferred_accent} 100%);
                }
                .signalkit-banner-preferred .signalkit-button {
                    background-color: {$preferred_secondary};
                    color: {$preferred_primary};
                }
                .signalkit-banner-preferred .signalkit-button:hover {
                    background-color: {$preferred_accent};
                    color: {$preferred_secondary};
                }
                .signalkit-banner-preferred .signalkit-educational-link {
                    color: {$preferred_secondary};
                }
            ";
        }
        
        return $css;
    }
    
    /**
     * Handle AJAX click tracking.
     */
    public function ajax_track_click() {
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field($_POST['banner_type']) : 'follow';
        
        SignalKit_Analytics::track_click($banner_type);
        
        wp_send_json_success(array(
            'message' => 'Click tracked successfully',
            'banner_type' => $banner_type,
        ));
    }
    
    /**
     * Handle AJAX dismissal tracking.
     */
    public function ajax_track_dismissal() {
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        $banner_type = isset($_POST['banner_type']) ? sanitize_text_field($_POST['banner_type']) : 'follow';
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 7;
        
        SignalKit_Analytics::track_dismissal($banner_type);
        
        // Set dismissal cookie
        setcookie(
            'signalkit_dismissed_' . $banner_type,
            '1',
            time() + ($duration * DAY_IN_SECONDS),
            COOKIEPATH,
            COOKIE_DOMAIN
        );
        
        wp_send_json_success(array(
            'message' => 'Dismissal tracked successfully',
            'banner_type' => $banner_type,
        ));
    }
}