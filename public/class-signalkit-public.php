<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package SignalKit_For_Google
 * @version 2.1.2 - FIXED: Changed $settings to public for admin preview access
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Public {

    private $plugin_name;
    private $version;
    public $settings;  // ← FIXED: Changed from private to public for admin preview
    private $color_cache = array();

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->settings    = get_option('signalkit_settings', array());

        // Register shortcodes
        add_shortcode('signalkit_follow', array($this, 'shortcode_follow'));
        add_shortcode('signalkit_preferred', array($this, 'shortcode_preferred'));

        // Public AJAX
        add_action('wp_ajax_nopriv_signalkit_track_click', array($this, 'ajax_track_click'));
        add_action('wp_ajax_nopriv_signalkit_track_dismissal', array($this, 'ajax_track_dismissal'));
        add_action('wp_ajax_signalkit_track_click', array($this, 'ajax_track_click'));
        add_action('wp_ajax_signalkit_track_dismissal', array($this, 'ajax_track_dismissal'));
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

        $custom_css = $this->get_custom_css();
        if (!empty($custom_css)) {
            wp_add_inline_style('signalkit-public', $custom_css);
        }

        if (!empty($this->settings['enable_csp'])) {
            add_action('send_headers', array($this, 'add_csp_header'));
        }
    }

    /**
     * Add CSP header.
     */
    public function add_csp_header() {
        if (headers_sent()) return;
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline'; "
             . "style-src 'self' 'unsafe-inline'; "
             . "img-src 'self' data: https:; "
             . "connect-src 'self' " . esc_url_raw(admin_url('admin-ajax.php'));
        header("Content-Security-Policy: {$csp}");
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

        wp_localize_script('signalkit-public', 'signalkitData', array(
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'nonce'             => wp_create_nonce('signalkit_nonce'),
            'followSettings'    => $this->get_banner_js_settings('follow'),
            'preferredSettings' => $this->get_banner_js_settings('preferred'),
            'analyticsEnabled'  => !empty($this->settings['analytics_tracking']),
            'rateLimitingEnabled' => !empty($this->settings['enable_rate_limiting']),
        ));
    }

    /**
     * JS settings.
     */
    private function get_banner_js_settings($type) {
        $prefix = $type . '_';
        return array(
            'enabled'           => !empty($this->settings[$prefix . 'enabled']),
            'dismissDuration'   => isset($this->settings[$prefix . 'dismiss_duration']) ? intval($this->settings[$prefix . 'dismiss_duration']) : 7,
            'animation'         => $this->settings[$prefix . 'animation'] ?? 'slide_in',
            'position'          => $this->settings[$prefix . 'position'] ?? ($type === 'follow' ? 'bottom_left' : 'bottom_right'),
            'mobilePosition'    => $this->settings[$prefix . 'mobile_position'] ?? 'bottom',
            'mobileStackOrder'  => isset($this->settings[$prefix . 'mobile_stack_order']) ? intval($this->settings[$prefix . 'mobile_stack_order']) : ($type === 'follow' ? 1 : 2),
            'dismissible'       => !empty($this->settings[$prefix . 'dismissible']),
            'bannerWidth'       => isset($this->settings[$prefix . 'banner_width']) ? intval($this->settings[$prefix . 'banner_width']) : 360,
            'bannerPadding'     => isset($this->settings[$prefix . 'banner_padding']) ? intval($this->settings[$prefix . 'banner_padding']) : 16,
            'fontSizeHeadline'  => isset($this->settings[$prefix . 'font_size_headline']) ? intval($this->settings[$prefix . 'font_size_headline']) : 15,
            'fontSizeDescription' => isset($this->settings[$prefix . 'font_size_description']) ? intval($this->settings[$prefix . 'font_size_description']) : 13,
            'fontSizeButton'    => isset($this->settings[$prefix . 'font_size_button']) ? intval($this->settings[$prefix . 'font_size_button']) : 14,
            'borderRadius'      => isset($this->settings[$prefix . 'border_radius']) ? intval($this->settings[$prefix . 'border_radius']) : 8,
            'showFrequency'     => $this->settings[$prefix . 'show_frequency'] ?? 'once_per_day',
            'showEducationalLink' => ($type === 'preferred') ? !empty($this->settings['preferred_show_educational_link']) : false,
        );
    }

    /**
     * Display banners.
     */
    public function display_banners() {
        $show_follow    = SignalKit_Display_Rules::should_display('follow');
        $show_preferred = SignalKit_Display_Rules::should_display('preferred');

        if (!$show_follow && !$show_preferred) return;

        $is_mobile = wp_is_mobile();

        if ($is_mobile && $show_follow && $show_preferred) {
            $this->render_mobile_stacked_banners();
        } else {
            if ($show_follow) {
                $this->render_banner('follow');
                SignalKit_Display_Rules::set_frequency_cookie($this->settings, 'follow');
                $this->track_impression_if_enabled('follow');
            }
            if ($show_preferred) {
                $this->render_banner('preferred');
                SignalKit_Display_Rules::set_frequency_cookie($this->settings, 'preferred');
                $this->track_impression_if_enabled('preferred');
            }
        }
    }

    /**
     * Render mobile stacked.
     */
    private function render_mobile_stacked_banners() {
        $follow_order    = $this->settings['follow_mobile_stack_order'] ?? 1;
        $preferred_order = $this->settings['preferred_mobile_stack_order'] ?? 2;

        $banners = array();
        if (SignalKit_Display_Rules::should_display('follow')) {
            $banners[$follow_order] = 'follow';
        }
        if (SignalKit_Display_Rules::should_display('preferred')) {
            $banners[$preferred_order] = 'preferred';
        }

        ksort($banners);

        foreach ($banners as $type) {
            $this->render_banner($type);
            SignalKit_Display_Rules::set_frequency_cookie($this->settings, $type);
            $this->track_impression_if_enabled($type);
        }
    }

    /**
     * Track impression.
     */
    private function track_impression_if_enabled($type) {
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_impression($type);
        }
    }

    /**
     * Render banner with flat $banner array.
     */
    public function render_banner($banner_type, $device = 'desktop') {
        $template_file = SIGNALKIT_PLUGIN_DIR . "public/partials/banner-{$banner_type}.php";
        if (!file_exists($template_file)) {
            signalkit_log("Banner template missing: {$template_file}");
            return;
        }

        $prefix = $banner_type . '_';
        $banner = array(
            'type' => $banner_type,
            'device' => $device,
            'site_name' => $this->settings['site_name'] ?? get_bloginfo('name'),
            'headline' => str_replace('[site_name]', $this->settings['site_name'] ?? get_bloginfo('name'), $this->settings[$prefix . 'banner_headline'] ?? ''),
            'description' => $this->settings[$prefix . 'banner_description'] ?? '',
            'button_text' => $this->settings[$prefix . 'button_text'] ?? '',
            'button_url' => $this->settings[$prefix . 'google_' . ($banner_type === 'follow' ? 'news' : 'preferences') . '_url'] ?? '',
            'primary_color' => $this->settings[$prefix . 'primary_color'] ?? '#4285f4',
            'secondary_color' => $this->settings[$prefix . 'secondary_color'] ?? '#ffffff',
            'accent_color' => $this->settings[$prefix . 'accent_color'] ?? ($banner_type === 'follow' ? '#34a853' : '#ea4335'),
            'text_color' => $this->settings[$prefix . 'text_color'] ?? '#1a1a1a',
            'position' => $this->settings[$prefix . 'position'] ?? ($banner_type === 'follow' ? 'bottom_left' : 'bottom_right'),
            'mobile_position' => $this->settings[$prefix . 'mobile_position'] ?? 'bottom',
            'animation' => $this->settings[$prefix . 'animation'] ?? 'slide_in',
            'dismissible' => !empty($this->settings[$prefix . 'dismissible']),
            'banner_width' => $this->settings[$prefix . 'banner_width'] ?? 360,
            'banner_padding' => $this->settings[$prefix . 'banner_padding'] ?? 16,
            'border_radius' => $this->settings[$prefix . 'border_radius'] ?? 8,
            'font_size_headline' => $this->settings[$prefix . 'font_size_headline'] ?? 15,
            'font_size_description' => $this->settings[$prefix . 'font_size_description'] ?? 13,
            'font_size_button' => $this->settings[$prefix . 'font_size_button'] ?? 14,
            'educational_text' => $banner_type === 'preferred' ? ($this->settings['preferred_educational_text'] ?? '') : '',
            'show_educational' => $banner_type === 'preferred' && !empty($this->settings['preferred_show_educational_link']),
            'educational_url' => $banner_type === 'preferred' ? ($this->settings['preferred_educational_post_url'] ?? '') : '',
            'mobile_stack_order' => $this->settings[$prefix . 'mobile_stack_order'] ?? ($banner_type === 'follow' ? 1 : 2),
        );

        $banner = apply_filters('signalkit_banner_data', $banner, $banner_type);

        ob_start();
        include $template_file;
        $output = ob_get_clean();

        echo $output;
    }

    /**
     * Generate custom CSS (global vars).
     */
    private function get_custom_css() {
        $css = '';

        $css .= $this->generate_global_css();
        $css .= $this->generate_mobile_stacking_css();

        return $css;
    }

    /**
     * Global CSS vars - PUBLIC so admin can call it.
     */
    public function generate_global_css() {
        $follow_primary = $this->settings['follow_primary_color'] ?? '#4285f4';
        $preferred_primary = $this->settings['preferred_primary_color'] ?? '#ea4335';

        $css = "\n/* Global Banner CSS */\n";
        $css .= ":root {\n";
        $css .= "    --signalkit-primary: {$follow_primary};\n";
        $css .= "    --signalkit-primary-hover: " . $this->darken_color($follow_primary, 10) . ";\n";
        $css .= "    --signalkit-secondary: " . ($this->settings['follow_secondary_color'] ?? '#ffffff') . ";\n";
        $css .= "    --signalkit-accent: " . ($this->settings['follow_accent_color'] ?? '#34a853') . ";\n";
        $css .= "    --signalkit-text: " . ($this->settings['follow_text_color'] ?? '#1a1a1a') . ";\n";
        $css .= "}\n";

        $css .= ".signalkit-banner-preferred {\n";
        $css .= "    --signalkit-primary: {$preferred_primary};\n";
        $css .= "    --signalkit-primary-hover: " . $this->darken_color($preferred_primary, 10) . ";\n";
        $css .= "    --signalkit-secondary: " . ($this->settings['preferred_secondary_color'] ?? '#ffffff') . ";\n";
        $css .= "    --signalkit-accent: " . ($this->settings['preferred_accent_color'] ?? '#fbbc04') . ";\n";
        $css .= "    --signalkit-text: " . ($this->settings['preferred_text_color'] ?? '#1a1a1a') . ";\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Mobile stacking CSS - PUBLIC for admin preview.
     */
    public function generate_mobile_stacking_css() {
        $css = "\n/* Mobile Stacking */\n";
        $css .= "@media (max-width: 768px) {\n";
        $css .= "    .signalkit-device-mobile .signalkit-banner-container {\n";
        $css .= "        position: fixed !important;\n";
        $css .= "        left: 0 !important;\n";
        $css .= "        right: 0 !important;\n";
        $css .= "        width: 100% !important;\n";
        $css .= "        max-width: none !important;\n";
        $css .= "        border-radius: 0 !important;\n";
        $css .= "        z-index: 9999;\n";
        $css .= "    }\n";

        $css .= "    .signalkit-stack-order-1 { bottom: 0; }\n";
        $css .= "    .signalkit-stack-order-2 { bottom: calc(var(--signalkit-banner-height, 80px) + 8px); }\n";

        $css .= "    .signalkit-position-mobile-top .signalkit-banner-container { top: 0; bottom: auto; }\n";
        $css .= "    .signalkit-position-mobile-top.signalkit-stack-order-2 { top: calc(var(--signalkit-banner-height, 80px) + 8px); }\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Generate CSS for specific banner - PUBLIC for admin preview.
     */
    public function generate_banner_css($banner_type) {
        return $this->generate_global_css();
    }

    /**
     * Darken color.
     */
    private function darken_color($hex, $percent) {
        $key = $hex . '_' . $percent;
        if (isset($this->color_cache[$key])) return $this->color_cache[$key];

        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) return '#000000';

        list($r, $g, $b) = array_map('hexdec', str_split($hex, 2));
        $r = max(0, min(255, $r * (1 - $percent / 100)));
        $g = max(0, min(255, $g * (1 - $percent / 100)));
        $b = max(0, min(255, $b * (1 - $percent / 100)));

        $result = '#' . sprintf('%02x%02x%02x', $r, $g, $b);
        $this->color_cache[$key] = $result;
        return $result;
    }

    /**
     * AJAX: Track click.
     */
    public function ajax_track_click() {
        $this->rate_limit_check('click', 10);
        check_ajax_referer('signalkit_nonce', 'nonce');
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        if (!in_array($banner_type, ['follow', 'preferred'])) {
            wp_send_json_error(['message' => 'Invalid banner type']);
        }
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_click($banner_type);
        }
        wp_send_json_success(['message' => 'Click tracked']);
    }

    /**
     * AJAX: Track dismissal.
     */
    public function ajax_track_dismissal() {
        $this->rate_limit_check('dismiss', 5);
        check_ajax_referer('signalkit_nonce', 'nonce');
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        $duration = min(365, max(1, intval($_POST['duration'] ?? 7)));
        if (!in_array($banner_type, ['follow', 'preferred'])) {
            wp_send_json_error(['message' => 'Invalid banner type']);
        }
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_dismissal($banner_type);
        }
        $cookie_name = 'signalkit_dismissed_' . $banner_type;
        setcookie($cookie_name, '1', time() + ($duration * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        wp_send_json_success(['message' => 'Dismissed']);
    }

    /**
     * Rate limit.
     */
    private function rate_limit_check($action, $limit) {
        if (empty($this->settings['enable_rate_limiting'])) return;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'signalkit_' . $action . '_' . md5($ip);
        $count = get_transient($key) ?: 0;
        if ($count >= $limit) {
            wp_send_json_error(['message' => 'Rate limit exceeded']);
        }
        set_transient($key, $count + 1, 60);
    }

    /**
     * Shortcodes.
     */
    public function shortcode_follow() {
        if (!SignalKit_Display_Rules::should_display('follow')) return '';
        ob_start();
        $this->render_banner('follow');
        return ob_get_clean();
    }

    public function shortcode_preferred() {
        if (!SignalKit_Display_Rules::should_display('preferred')) return '';
        ob_start();
        $this->render_banner('preferred');
        return ob_get_clean();
    }
}