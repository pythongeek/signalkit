<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package SignalKit
 * @version 1.0.0
 *
 * Security Enhancements:
 * - Server-side session tracking for impressions
 * - Enhanced rate limiting with IP fingerprinting
 * - Request signature validation
 * - Cookie manipulation prevention
 * - Strict input validation
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Public {

    private $plugin_name;
    private $version;
    public $settings;
    private $color_cache = array();

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->settings    = get_option('signalkit_settings', array());

        add_shortcode('signalkit_follow', array($this, 'shortcode_follow'));
        add_shortcode('signalkit_preferred', array($this, 'shortcode_preferred'));
    }

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

    public function add_csp_header() {
        if (headers_sent()) return;
        
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline'; "
             . "style-src 'self' 'unsafe-inline'; "
             . "img-src 'self' data: https:; "
             . "connect-src 'self' " . esc_url_raw(admin_url('admin-ajax.php'));
        
        header("Content-Security-Policy: {$csp}");
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'signalkit-public',
            SIGNALKIT_PLUGIN_URL . 'public/js/signalkit-public.js',
            array('jquery'),
            $this->version,
            true
        );

        // Generate secure session token for this page load
        $session_token = $this->generate_session_token();

        wp_localize_script('signalkit-public', 'signalkitData', array(
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'nonce'             => wp_create_nonce('signalkit_nonce'),
            'sessionToken'      => $session_token, // NEW: Session-specific token
            'followSettings'    => $this->get_banner_js_settings('follow'),
            'preferredSettings' => $this->get_banner_js_settings('preferred'),
            'analyticsEnabled'  => !empty($this->settings['analytics_tracking']),
            'rateLimitingEnabled' => !empty($this->settings['enable_rate_limiting']),
        ));
    }

    /**
     * Generate secure session token
     * Prevents replay attacks and session manipulation
     */
    private function generate_session_token() {
        $user_ip = $this->get_client_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        $timestamp = time();
        
        // Create fingerprint
        $fingerprint = hash('sha256', $user_ip . $user_agent . wp_salt() . $timestamp);
        
        // Store in transient (expires in 30 minutes)
        set_transient('signalkit_session_' . $fingerprint, array(
            'ip' => $user_ip,
            'agent' => $user_agent,
            'time' => $timestamp
        ), 1800);
        
        return $fingerprint;
    }

    /**
     * Validate session token
     * Returns false if invalid or expired
     */
    private function validate_session_token($token) {
        if (empty($token) || strlen($token) !== 64) {
            return false;
        }

        $session = get_transient('signalkit_session_' . $token);
        
        if ($session === false) {
            return false; // Expired or doesn't exist
        }

        // Verify IP and user agent match
        $current_ip = $this->get_client_ip();
        $current_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        if ($session['ip'] !== $current_ip || $session['agent'] !== $current_agent) {
            delete_transient('signalkit_session_' . $token);
            return false; // Fingerprint mismatch
        }

        // Check if token is too old (30 minutes)
        if ((time() - $session['time']) > 1800) {
            delete_transient('signalkit_session_' . $token);
            return false;
        }

        return true;
    }

    /**
     * Get client IP with proxy support
     * Security: Properly sanitized and validated
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = sanitize_text_field($_SERVER[$key]);
                
                // Handle comma-separated IPs (proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0'; // Fallback
    }

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

    private function track_impression_if_enabled($type) {
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_impression($type);
        }
    }

    public function render_banner($banner_type, $device = 'desktop') {
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

        $banner = apply_filters('signalkit_banner_data', $banner, $banner_type);

        ob_start();
        include $template_file;
        $output = ob_get_clean();

        echo $output;
    }

    private function get_custom_css() {
        $css = '';
        $css .= $this->generate_global_css();
        return $css;
    }

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

    public function generate_banner_css($banner_type) {
        return $this->generate_global_css();
    }

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
     * Generate mobile stacking CSS
     * Used for preview mode to ensure proper mobile banner stacking
     *
     * @return string Mobile-specific CSS
     */
    public function generate_mobile_stacking_css() {
        return "
/* Mobile Banner Stacking */
@media (max-width: 768px) {
    .signalkit-banner {
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: none !important;
        border-radius: 0 !important;
    }
    
    .signalkit-position-mobile-bottom {
        bottom: 0 !important;
        top: auto !important;
    }
    
    .signalkit-position-mobile-top {
        top: 0 !important;
        bottom: auto !important;
    }
}
";
    }

    /**
     * AJAX Handler: Track impression
     * SECURITY HARDENED: Session token validation, enhanced rate limiting, anti-spam
     */
    public function ajax_track_impression() {
        // Verify nonce
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        // NEW: Validate session token
        $session_token = isset($_POST['sessionToken']) ? sanitize_text_field($_POST['sessionToken']) : '';
        if (!$this->validate_session_token($session_token)) {
            signalkit_log('Impression tracking: Invalid session token', array(
                'ip' => $this->get_client_ip(),
                'token' => substr($session_token, 0, 8) . '...'
            ));
            wp_send_json_error(array('message' => 'Invalid session'), 403);
            return;
        }
        
        // Enhanced rate limiting
        $this->rate_limit_check('impression', 10, 60); // 10 per minute
        
        // Sanitize and validate input
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type'], 400);
            return;
        }
        
        // NEW: Check if impression already tracked in this session
        $impression_key = 'signalkit_impression_' . $session_token . '_' . $banner_type;
        if (get_transient($impression_key)) {
            // Already tracked in this session
            wp_send_json_success([
                'message' => 'Already tracked in session',
                'tracked' => false
            ]);
            return;
        }
        
        // Track impression if analytics enabled
        if (!empty($this->settings['analytics_tracking'])) {
            $result = SignalKit_Analytics::track_impression($banner_type);
            
            if ($result) {
                // Mark as tracked for this session (30 minutes)
                set_transient($impression_key, 1, 1800);
                
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
     * SECURITY HARDENED: Session token validation, stricter rate limiting
     */
    public function ajax_track_click() {
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        // Validate session token
        $session_token = isset($_POST['sessionToken']) ? sanitize_text_field($_POST['sessionToken']) : '';
        if (!$this->validate_session_token($session_token)) {
            wp_send_json_error(array('message' => 'Invalid session'), 403);
            return;
        }
        
        // Stricter rate limiting for clicks
        $this->rate_limit_check('click', 5, 60); // 5 per minute
        
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type'], 400);
            return;
        }
        
        // Check if click already tracked in this session
        $click_key = 'signalkit_click_' . $session_token . '_' . $banner_type;
        if (get_transient($click_key)) {
            wp_send_json_success(['message' => 'Already tracked', 'duplicate' => true]);
            return;
        }
        
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_click($banner_type);
            set_transient($click_key, 1, 1800); // Mark for 30 minutes
        }
        
        wp_send_json_success(['message' => 'Click tracked']);
    }

    /**
     * AJAX Handler: Track dismissal
     * SECURITY HARDENED: Session validation, cookie manipulation prevention
     */
    public function ajax_track_dismissal() {
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        // Validate session token
        $session_token = isset($_POST['sessionToken']) ? sanitize_text_field($_POST['sessionToken']) : '';
        if (!$this->validate_session_token($session_token)) {
            wp_send_json_error(array('message' => 'Invalid session'), 403);
            return;
        }
        
        $this->rate_limit_check('dismiss', 3, 60); // 3 per minute
        
        $banner_type = sanitize_text_field($_POST['banner_type'] ?? '');
        $duration = min(365, max(1, intval($_POST['duration'] ?? 7)));
        
        if (!in_array($banner_type, ['follow', 'preferred'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type'], 400);
            return;
        }
        
        // NEW: Verify user hasn't dismissed/undismissed multiple times
        $dismiss_history_key = 'signalkit_dismiss_history_' . md5($this->get_client_ip() . $banner_type);
        $dismiss_count = get_transient($dismiss_history_key) ?: 0;
        
        if ($dismiss_count >= 5) {
            signalkit_log('Dismissal abuse detected', array(
                'ip' => $this->get_client_ip(),
                'banner' => $banner_type,
                'count' => $dismiss_count
            ));
            wp_send_json_error(['message' => 'Too many dismissals'], 429);
            return;
        }
        
        // Track dismissal
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_dismissal($banner_type);
        }
        
        // Set secure cookie
        $cookie_name = 'signalkit_dismissed_' . $banner_type;
        $expiry = time() + ($duration * DAY_IN_SECONDS);
        
        // NEW: Add signature to cookie to prevent manipulation
        $cookie_signature = hash_hmac('sha256', $banner_type . $expiry, wp_salt());
        $cookie_value = base64_encode(json_encode(array(
            'dismissed' => 1,
            'expiry' => $expiry,
            'signature' => $cookie_signature
        )));
        
        setcookie(
            $cookie_name, 
            $cookie_value, 
            $expiry, 
            COOKIEPATH, 
            COOKIE_DOMAIN, 
            is_ssl(),
            true
        );
        
        // Increment dismiss history counter (expires after 1 hour)
        set_transient($dismiss_history_key, $dismiss_count + 1, 3600);
        
        wp_send_json_success([
            'message' => 'Dismissal tracked',
            'duration' => $duration
        ]);
    }

    /**
     * Enhanced rate limit check
     * SECURITY: IP-based with configurable time window
     */
    private function rate_limit_check($action, $limit, $window = 60) {
        if (empty($this->settings['enable_rate_limiting'])) return;
        
        $ip = $this->get_client_ip();
        
        // Create unique key with IP and action
        $key = 'signalkit_rl_' . $action . '_' . md5($ip);
        $attempts = get_transient($key) ?: array();
        $current_time = time();
        
        // Clean old attempts outside window
        $attempts = array_filter($attempts, function($timestamp) use ($current_time, $window) {
            return ($current_time - $timestamp) < $window;
        });
        
        // Check limit
        if (count($attempts) >= $limit) {
            signalkit_log('Rate limit exceeded', array(
                'action' => $action,
                'ip' => $ip,
                'attempts' => count($attempts),
                'limit' => $limit
            ));
            
            wp_send_json_error([
                'message' => 'Rate limit exceeded. Please try again later.'
            ], 429);
            exit;
        }
        
        // Add current attempt
        $attempts[] = $current_time;
        set_transient($key, $attempts, $window);
    }

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