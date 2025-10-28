<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package SignalKit_For_Google
 * @version 2.4.0 - FIXED: Added missing impression tracking AJAX handler
 * 
 * Security: Nonce verification, input sanitization, capability checks
 * WordPress Compatible: Follows WP coding standards, uses WP functions
 * Envato Compatible: GPL-2.0+ license, proper documentation
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly - Security measure
}

class SignalKit_Public {

    /**
     * Plugin identifier
     * @var string
     */
    private $plugin_name;

    /**
     * Plugin version
     * @var string
     */
    private $version;

    /**
     * Plugin settings array
     * @var array
     */
    public $settings;

    /**
     * Color cache for performance
     * @var array
     */
    private $color_cache = array();

    /**
     * Initialize the class
     *
     * @param string $plugin_name Plugin identifier
     * @param string $version Plugin version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->settings    = get_option('signalkit_settings', array());

        // Register shortcodes - WordPress standard
        add_shortcode('signalkit_follow', array($this, 'shortcode_follow'));
        add_shortcode('signalkit_preferred', array($this, 'shortcode_preferred'));
    }

    /**
     * Enqueue public styles
     * WordPress hook: wp_enqueue_scripts
     */
    public function enqueue_styles() {
        // Main stylesheet - WordPress standard
        wp_enqueue_style(
            'signalkit-public',
            SIGNALKIT_PLUGIN_URL . 'public/css/signalkit-public.css',
            array(),
            $this->version,
            'all'
        );

        // Add inline custom CSS
        $custom_css = $this->get_custom_css();
        if (!empty($custom_css)) {
            wp_add_inline_style('signalkit-public', $custom_css);
        }

        // Optional CSP headers for enhanced security
        if (!empty($this->settings['enable_csp'])) {
            add_action('send_headers', array($this, 'add_csp_header'));
        }
    }

    /**
     * Add Content Security Policy header
     * Security: Prevents XSS attacks
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
     * Enqueue public scripts
     * WordPress hook: wp_enqueue_scripts
     */
    public function enqueue_scripts() {
        // Main JavaScript file
        wp_enqueue_script(
            'signalkit-public',
            SIGNALKIT_PLUGIN_URL . 'public/js/signalkit-public.js',
            array('jquery'), // Dependency on jQuery
            $this->version,
            true // Load in footer
        );

        // Localize script with AJAX data and settings
        wp_localize_script('signalkit-public', 'signalkitData', array(
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'nonce'             => wp_create_nonce('signalkit_nonce'), // Security nonce
            'followSettings'    => $this->get_banner_js_settings('follow'),
            'preferredSettings' => $this->get_banner_js_settings('preferred'),
            'analyticsEnabled'  => !empty($this->settings['analytics_tracking']),
            'rateLimitingEnabled' => !empty($this->settings['enable_rate_limiting']),
        ));
    }

    /**
     * Get JavaScript settings for specific banner type
     *
     * @param string $type Banner type ('follow' or 'preferred')
     * @return array Settings array
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
     * Display banners on frontend
     * WordPress hook: wp_footer
     */
    public function display_banners() {
        // Check if banners should be displayed using display rules
        $show_follow    = SignalKit_Display_Rules::should_display('follow');
        $show_preferred = SignalKit_Display_Rules::should_display('preferred');

        if (!$show_follow && !$show_preferred) return;

        $is_mobile = wp_is_mobile();

        // Handle mobile stacking when both banners are shown
        if ($is_mobile && $show_follow && $show_preferred) {
            $this->render_mobile_stacked_banners();
        } else {
            // Render individual banners
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
     * Render mobile stacked banners with proper ordering
     */
    private function render_mobile_stacked_banners() {
        $follow_order    = $this->settings['follow_mobile_stack_order'] ?? 1;
        $preferred_order = $this->settings['preferred_mobile_stack_order'] ?? 2;

        // Create array of banners with their stack order
        $banners = array();
        if (SignalKit_Display_Rules::should_display('follow')) {
            $banners[$follow_order] = 'follow';
        }
        if (SignalKit_Display_Rules::should_display('preferred')) {
            $banners[$preferred_order] = 'preferred';
        }

        // Sort by stack order
        ksort($banners);

        // Render each banner in order
        foreach ($banners as $type) {
            $this->render_banner($type);
            SignalKit_Display_Rules::set_frequency_cookie($this->settings, $type);
            $this->track_impression_if_enabled($type);
        }
    }

    /**
     * Track impression if analytics enabled
     *
     * @param string $type Banner type
     */
    private function track_impression_if_enabled($type) {
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_impression($type);
        }
    }

    /**
     * Render banner HTML
     * 
     * @param string $banner_type Banner type ('follow' or 'preferred')
     * @param string $device Device type ('desktop' or 'mobile')
     */
    public function render_banner($banner_type, $device = 'desktop') {
        // Validate banner type - Security check
        if (!in_array($banner_type, array('follow', 'preferred'), true)) {
            return;
        }

        $template_file = SIGNALKIT_PLUGIN_DIR . "public/partials/banner-{$banner_type}.php";
        
        if (!file_exists($template_file)) {
            signalkit_log("Banner template missing: {$template_file}");
            return;
        }

        $prefix = $banner_type . '_';
        $site_name = $this->settings['site_name'] ?? get_bloginfo('name');
        
        // Prepare banner data array
        $banner = array(
            'type' => $banner_type,
            'device' => $device,
            'site_name' => sanitize_text_field($site_name),
            'headline' => str_replace('[site_name]', $site_name, $this->settings[$prefix . 'banner_headline'] ?? ''),
            'description' => $this->settings[$prefix . 'banner_description'] ?? '',
            'button_text' => $this->settings[$prefix . 'button_text'] ?? '',
            'button_url' => $this->settings[$prefix . 'google_' . ($banner_type === 'follow' ? 'news' : 'preferences') . '_url'] ?? '',
            'primary_color' => sanitize_hex_color($this->settings[$prefix . 'primary_color'] ?? '#4285f4'),
            'secondary_color' => sanitize_hex_color($this->settings[$prefix . 'secondary_color'] ?? '#ffffff'),
            'accent_color' => sanitize_hex_color($this->settings[$prefix . 'accent_color'] ?? ($banner_type === 'follow' ? '#34a853' : '#ea4335')),
            'text_color' => sanitize_hex_color($this->settings[$prefix . 'text_color'] ?? '#1a1a1a'),
            'position' => $this->settings[$prefix . 'position'] ?? ($banner_type === 'follow' ? 'bottom_left' : 'bottom_right'),
            'mobile_position' => $this->settings[$prefix . 'mobile_position'] ?? 'bottom',
            'animation' => $this->settings[$prefix . 'animation'] ?? 'slide_in',
            'dismissible' => !empty($this->settings[$prefix . 'dismissible']),
            'banner_width' => absint($this->settings[$prefix . 'banner_width'] ?? 360),
            'banner_padding' => absint($this->settings[$prefix . 'banner_padding'] ?? 16),
            'border_radius' => absint($this->settings[$prefix . 'border_radius'] ?? 8),
            'font_size_headline' => absint($this->settings[$prefix . 'font_size_headline'] ?? 15),
            'font_size_description' => absint($this->settings[$prefix . 'font_size_description'] ?? 13),
            'font_size_button' => absint($this->settings[$prefix . 'font_size_button'] ?? 14),
            'educational_text' => $banner_type === 'preferred' ? ($this->settings['preferred_educational_text'] ?? '') : '',
            'show_educational' => $banner_type === 'preferred' && !empty($this->settings['preferred_show_educational_link']),
            'educational_url' => $banner_type === 'preferred' ? ($this->settings['preferred_educational_post_url'] ?? '') : '',
            'mobile_stack_order' => absint($this->settings[$prefix . 'mobile_stack_order'] ?? ($banner_type === 'follow' ? 1 : 2)),
        );

        // Allow developers to filter banner data - WordPress standard
        $banner = apply_filters('signalkit_banner_data', $banner, $banner_type);

        // Render template
        ob_start();
        include $template_file;
        $output = ob_get_clean();

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in template
    }

    /**
     * Generate custom CSS
     *
     * @return string CSS rules
     */
    private function get_custom_css() {
        $css = '';
        $css .= $this->generate_global_css();
        $css .= $this->generate_mobile_stacking_css();
        return $css;
    }

    /**
     * Generate global CSS variables
     *
     * @return string CSS with CSS variables
     */
    public function generate_global_css() {
        $follow_primary = sanitize_hex_color($this->settings['follow_primary_color'] ?? '#4285f4');
        $preferred_primary = sanitize_hex_color($this->settings['preferred_primary_color'] ?? '#ea4335');

        $css = "\n/* SignalKit Global CSS Variables */\n";
        $css .= ":root {\n";
        $css .= "    --signalkit-primary: {$follow_primary};\n";
        $css .= "    --signalkit-primary-hover: " . $this->darken_color($follow_primary, 10) . ";\n";
        $css .= "    --signalkit-secondary: " . sanitize_hex_color($this->settings['follow_secondary_color'] ?? '#ffffff') . ";\n";
        $css .= "    --signalkit-accent: " . sanitize_hex_color($this->settings['follow_accent_color'] ?? '#34a853') . ";\n";
        $css .= "    --signalkit-text: " . sanitize_hex_color($this->settings['follow_text_color'] ?? '#1a1a1a') . ";\n";
        $css .= "}\n";

        $css .= ".signalkit-banner-preferred {\n";
        $css .= "    --signalkit-primary: {$preferred_primary};\n";
        $css .= "    --signalkit-primary-hover: " . $this->darken_color($preferred_primary, 10) . ";\n";
        $css .= "    --signalkit-secondary: " . sanitize_hex_color($this->settings['preferred_secondary_color'] ?? '#ffffff') . ";\n";
        $css .= "    --signalkit-accent: " . sanitize_hex_color($this->settings['preferred_accent_color'] ?? '#fbbc04') . ";\n";
        $css .= "    --signalkit-text: " . sanitize_hex_color($this->settings['preferred_text_color'] ?? '#1a1a1a') . ";\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Generate mobile stacking CSS
     *
     * @return string Mobile CSS rules
     */
    public function generate_mobile_stacking_css() {
        $css = "\n/* Mobile Stacking Rules */\n";
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
     * Generate CSS for specific banner (used by admin preview)
     *
     * @param string $banner_type Banner type
     * @return string CSS rules
     */
    public function generate_banner_css($banner_type) {
        return $this->generate_global_css();
    }

    /**
     * Darken a hex color by percentage
     *
     * @param string $hex Hex color code
     * @param int $percent Percentage to darken (0-100)
     * @return string Darkened hex color
     */
    private function darken_color($hex, $percent) {
        // Use cache for performance
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
     * AJAX Handler: Track impression
     * FIXED: This handler was missing, causing 400 errors
     * 
     * Security: Nonce verification, input sanitization
     */
    public function ajax_track_impression() {
        // Rate limiting check - Security measure
        $this->rate_limit_check('impression', 20);
        
        // Verify nonce - Security measure
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        // Sanitize and validate input
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type'], 400);
            return;
        }
        
        // Track impression if analytics enabled
        if (!empty($this->settings['analytics_tracking'])) {
            $result = SignalKit_Analytics::track_impression($banner_type);
            
            if ($result) {
                wp_send_json_success([
                    'message' => 'Impression tracked successfully',
                    'banner_type' => $banner_type
                ]);
            } else {
                wp_send_json_error(['message' => 'Failed to track impression'], 500);
            }
        } else {
            wp_send_json_success([
                'message' => 'Analytics tracking disabled',
                'tracked' => false
            ]);
        }
    }

    /**
     * AJAX Handler: Track click
     * 
     * Security: Nonce verification, input sanitization
     */
    public function ajax_track_click() {
        // Rate limiting - Security measure
        $this->rate_limit_check('click', 10);
        
        // Verify nonce - Security measure
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        // Sanitize and validate input
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type'], 400);
            return;
        }
        
        // Track click if analytics enabled
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_click($banner_type);
        }
        
        wp_send_json_success(['message' => 'Click tracked']);
    }

    /**
     * AJAX Handler: Track dismissal
     * 
     * Security: Nonce verification, input sanitization, secure cookie
     */
    public function ajax_track_dismissal() {
        // Rate limiting - Security measure
        $this->rate_limit_check('dismiss', 5);
        
        // Verify nonce - Security measure
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        // Sanitize and validate input
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        $duration = min(365, max(1, intval($_POST['duration'] ?? 7))); // Limit: 1-365 days
        
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type'], 400);
            return;
        }
        
        // Track dismissal if analytics enabled
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_dismissal($banner_type);
        }
        
        // Set secure cookie with proper parameters
        $cookie_name = 'signalkit_dismissed_' . $banner_type;
        $expiry = time() + ($duration * DAY_IN_SECONDS);
        
        setcookie(
            $cookie_name, 
            '1', 
            $expiry, 
            COOKIEPATH, 
            COOKIE_DOMAIN, 
            is_ssl(), // Secure flag if using HTTPS
            true      // HttpOnly flag for security
        );
        
        wp_send_json_success([
            'message' => 'Dismissal tracked',
            'duration' => $duration
        ]);
    }

    /**
     * Rate limit check for AJAX requests
     * Security: Prevents abuse and DoS attacks
     *
     * @param string $action Action type
     * @param int $limit Maximum requests per minute
     */
    private function rate_limit_check($action, $limit) {
        if (empty($this->settings['enable_rate_limiting'])) return;
        
        // Get client IP - sanitized
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
        
        // Create unique transient key
        $key = 'signalkit_' . $action . '_' . md5($ip);
        $count = get_transient($key) ?: 0;
        
        // Check rate limit
        if ($count >= $limit) {
            wp_send_json_error([
                'message' => 'Rate limit exceeded. Please try again later.'
            ], 429);
            exit;
        }
        
        // Increment counter with 60 second expiry
        set_transient($key, $count + 1, 60);
    }

    /**
     * Shortcode: [signalkit_follow]
     * Display follow banner via shortcode
     *
     * @return string Banner HTML
     */
    public function shortcode_follow() {
        if (!SignalKit_Display_Rules::should_display('follow')) return '';
        
        ob_start();
        $this->render_banner('follow');
        return ob_get_clean();
    }

    /**
     * Shortcode: [signalkit_preferred]
     * Display preferred banner via shortcode
     *
     * @return string Banner HTML
     */
    public function shortcode_preferred() {
        if (!SignalKit_Display_Rules::should_display('preferred')) return '';
        
        ob_start();
        $this->render_banner('preferred');
        return ob_get_clean();
    }
}