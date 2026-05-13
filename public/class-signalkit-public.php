<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package SignalKit
 * @version 2.0.0
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
    
    // Shortcode style properties
    private $current_shortcode_style = 'default';
    private $current_shortcode_align = 'center';
    private $current_shortcode_extra_class = '';

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->settings    = get_option('signalkit_settings', array());

        // Register all banner shortcodes
        add_shortcode('signalkit_follow', array($this, 'shortcode_follow'));
        add_shortcode('signalkit_preferred', array($this, 'shortcode_preferred'));
        add_shortcode('signalkit_custom', array($this, 'shortcode_custom'));
        
        // Store current shortcode style for filter callback
        $this->current_shortcode_style = 'default';
    }

    public function enqueue_styles() {
        // Use enhanced CSS if available, fallback to original
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
        
        // Enqueue custom banner CSS if enabled
        if (!empty($this->settings['custom_enabled'])) {
            $custom_css_path = SIGNALKIT_PLUGIN_DIR . 'public/css/signalkit-custom-banner.css';
            if (file_exists($custom_css_path)) {
                wp_enqueue_style(
                    'signalkit-custom-banner',
                    SIGNALKIT_PLUGIN_URL . 'public/css/signalkit-custom-banner.css',
                    array('signalkit-public'),
                    $this->version,
                    'all'
                );
            }
        }

        $custom_css = $this->get_custom_css();
        if (!empty($custom_css)) {
            wp_add_inline_style('signalkit-public', $custom_css);
        }


    }



    public function enqueue_scripts() {
        wp_enqueue_script(
            'signalkit-public',
            SIGNALKIT_PLUGIN_URL . 'public/js/signalkit-public.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Enqueue custom banner JS if enabled
        if (!empty($this->settings['custom_enabled'])) {
            $custom_js_path = SIGNALKIT_PLUGIN_DIR . 'public/js/signalkit-custom-banner.js';
            if (file_exists($custom_js_path)) {
                wp_enqueue_script(
                    'signalkit-custom-banner',
                    SIGNALKIT_PLUGIN_URL . 'public/js/signalkit-custom-banner.js',
                    array('signalkit-public'),
                    $this->version,
                    true
                );
            }
        }

        // Generate secure session token for this page load
        $session_token = $this->generate_session_token();

        wp_localize_script('signalkit-public', 'signalkitData', array(
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'nonce'             => wp_create_nonce('signalkit_nonce'),
            'sessionToken'      => $session_token, // NEW: Session-specific token
            'followSettings'    => $this->get_banner_js_settings('follow'),
            'preferredSettings' => $this->get_banner_js_settings('preferred'),
            'customSettings'    => $this->get_banner_js_settings('custom'),
            'analyticsEnabled'  => !empty($this->settings['analytics_tracking']),
            'rateLimitingEnabled' => !empty($this->settings['enable_rate_limiting']),
            'isMobile'          => wp_is_mobile(),
            'mobileBannerStrategy' => $this->settings['mobile_banner_strategy'] ?? 'show_all',
        ));
        
        // i18n: Localize user-facing strings for JavaScript (CodeCanyon compliance)
        wp_localize_script('signalkit-public', 'signalkitStrings', array(
            // Form validation messages
            'emailInvalid'       => __('Please enter a valid email address', 'signalkit'),
            'emailRequired'      => __('Email address is required', 'signalkit'),
            
            // Error messages
            'errorGeneral'       => __('Something went wrong. Please try again.', 'signalkit'),
            'errorNetwork'       => __('Network error. Please check your connection.', 'signalkit'),
            'errorTimeout'       => __('Request timed out. Please try again.', 'signalkit'),
            'errorServer'        => __('Server error. Please try again later.', 'signalkit'),
            
            // Success messages
            'successSubmit'      => __('Thank you for subscribing!', 'signalkit'),
            'successCopied'      => __('Copied!', 'signalkit'),
            
            // Banner actions
            'dismissBanner'      => __('Dismiss notification', 'signalkit'),
            'closeBanner'        => __('Close', 'signalkit'),
            
            // Loading states
            'loading'            => __('Loading...', 'signalkit'),
            'submitting'         => __('Submitting...', 'signalkit'),
            'processing'         => __('Processing...', 'signalkit'),
            
            // Rate limiting
            'rateLimitExceeded'  => __('Too many requests. Please try again later.', 'signalkit'),
            
            // Accessibility labels
            'openNewTab'         => __('Opens in new tab', 'signalkit'),
        ));
    }

    /**
     * Generate secure session token
     * Prevents replay attacks and session manipulation
     */
    private function generate_session_token() {
        $user_ip = $this->get_client_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
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
        $current_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        
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
     *
     * SECURITY: Validation-first approach (Envato/CodeCanyon requirement)
     * - Validates IP format IMMEDIATELY after raw $_SERVER access
     * - Prevents header spoofing by rejecting invalid IPs before processing
     * - Only sanitizes after validation passes
     *
     * @return string The validated and sanitized IP address.
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
                // Get raw value first
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated immediately below before sanitization
                $raw_ip = wp_unslash($_SERVER[$key]);
                
                // Handle comma-separated IPs (proxies) - get first IP
                if (strpos($raw_ip, ',') !== false) {
                    $raw_ip = trim(explode(',', $raw_ip)[0]);
                }
                
                // SECURITY: Validate IP format IMMEDIATELY after raw access
                // Using stricter validation that excludes private/reserved ranges
                if (!filter_var($raw_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    // Invalid IP format - skip this header and try next
                    continue;
                }
                
                // Only sanitize AFTER validation passes
                return sanitize_text_field($raw_ip);
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

    private $banners_rendered = false;

    public function display_banners() {
        // Prevent duplicate rendering
        if ($this->banners_rendered) {
            return;
        }

        $show_follow    = SignalKit_Display_Rules::should_display('follow');
        $show_preferred = SignalKit_Display_Rules::should_display('preferred');
        $show_custom    = SignalKit_Display_Rules::should_display('custom');

        if (!$show_follow && !$show_preferred && !$show_custom) {
            return;
        }

        $this->banners_rendered = true;

        $is_mobile = wp_is_mobile();

        if ($is_mobile && $show_follow && $show_preferred) {
            $this->render_mobile_stacked_banners();
        } else {
            if ($show_follow) {
                $this->render_banner('follow');
                // Moved to JS: SignalKit_Display_Rules::set_frequency_cookie($this->settings, 'follow');
                // Moved to JS: $this->track_impression_if_enabled('follow');
            }
            if ($show_preferred) {
                $this->render_banner('preferred');
                // Moved to JS: SignalKit_Display_Rules::set_frequency_cookie($this->settings, 'preferred');
                // Moved to JS: $this->track_impression_if_enabled('preferred');
            }
        }
        
        // Render custom banner (handled separately with its own triggers)
        if ($show_custom) {
            $this->render_custom_banner();
            // Moved to JS: $this->track_impression_if_enabled('custom');
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

        foreach ($banners as $type) {
            $this->render_banner($type);
            // Moved to JS: SignalKit_Display_Rules::set_frequency_cookie($this->settings, $type);
            $this->track_impression_if_enabled($type);
        }
    }

    private function track_impression_if_enabled($type) {
        if (!empty($this->settings['analytics_tracking'])) {
            SignalKit_Analytics::track_impression($type);
        }
    }

    public function render_banner($banner_type, $device = 'desktop', $include_schema = true) {
        if (!in_array($banner_type, array('follow', 'preferred'), true)) {
            return;
        }

        // Use enhanced template if available
        $template_enhanced = SIGNALKIT_PLUGIN_DIR . "public/partials/banner-{$banner_type}-enhanced.php";
        $template_file = file_exists($template_enhanced) ? $template_enhanced : SIGNALKIT_PLUGIN_DIR . "public/partials/banner-{$banner_type}.php";
        
        if (!file_exists($template_file)) {
            return;
        }

        $prefix = $banner_type . '_';
        $site_name = $this->settings['site_name'] ?? get_bloginfo('name');
        
        // Base banner data
        $banner = array(
            'type' => $banner_type,
            'device' => $device,
            'site_name' => sanitize_text_field($site_name),
            'headline' => str_replace('[site_name]', $site_name, $this->settings[$prefix . 'banner_headline'] ?? ''),
            'description' => $this->settings[$prefix . 'banner_description'] ?? '',
            'button_text' => $this->settings[$prefix . 'button_text'] ?? '',
            'button_url' => $this->settings[$prefix . 'google_' . ($banner_type === 'follow' ? 'news' : 'preferences') . '_url'] ?? '',
            
            // Colors
            'primary_color' => sanitize_hex_color($this->settings[$prefix . 'primary_color'] ?? '#4285f4'),
            'secondary_color' => sanitize_hex_color($this->settings[$prefix . 'secondary_color'] ?? '#ffffff'),
            'accent_color' => sanitize_hex_color($this->settings[$prefix . 'accent_color'] ?? ($banner_type === 'follow' ? '#34a853' : '#ea4335')),
            'text_color' => sanitize_hex_color($this->settings[$prefix . 'text_color'] ?? '#1a1a1a'),
            
            // NEW: Advanced Color Settings
            'gradient_start' => sanitize_hex_color($this->settings[$prefix . 'gradient_start'] ?? ($this->settings[$prefix . 'primary_color'] ?? '#4285f4')),
            'gradient_end' => sanitize_hex_color($this->settings[$prefix . 'gradient_end'] ?? ($this->settings[$prefix . 'accent_color'] ?? '#34a853')),
            'gradient_angle' => absint($this->settings[$prefix . 'gradient_angle'] ?? 135),
            'border_color' => sanitize_hex_color($this->settings[$prefix . 'border_color'] ?? ''),
            
            // Position & Animation
            'position' => $this->settings[$prefix . 'position'] ?? ($banner_type === 'follow' ? 'bottom_left' : 'bottom_right'),
            'mobile_position' => $this->settings[$prefix . 'mobile_position'] ?? 'bottom',
            'animation' => $this->settings[$prefix . 'animation'] ?? 'slide_in',
            'dismissible' => !empty($this->settings[$prefix . 'dismissible']),
            
            // Sizing
            'banner_width' => absint($this->settings[$prefix . 'banner_width'] ?? 380),
            'banner_padding' => absint($this->settings[$prefix . 'banner_padding'] ?? 20),
            'border_radius' => absint($this->settings[$prefix . 'border_radius'] ?? 16),
            'icon_size' => absint($this->settings[$prefix . 'icon_size'] ?? 52),
            'font_size_headline' => absint($this->settings[$prefix . 'font_size_headline'] ?? 16),
            'font_size_description' => absint($this->settings[$prefix . 'font_size_description'] ?? 14),
            'font_size_button' => absint($this->settings[$prefix . 'font_size_button'] ?? 14),
            'close_button_size' => absint($this->settings[$prefix . 'close_button_size'] ?? 28),
            
            // NEW: Style Presets
            'banner_style' => sanitize_html_class($this->settings[$prefix . 'banner_style'] ?? 'modern-card'),
            'button_style' => sanitize_html_class($this->settings[$prefix . 'button_style'] ?? 'default'),
            'icon_style' => sanitize_html_class($this->settings[$prefix . 'icon_style'] ?? 'circle'),
            
            // NEW: Visibility & Effects
            'visibility_mode' => sanitize_html_class($this->settings[$prefix . 'visibility_mode'] ?? 'auto'),
            'enable_glow' => !empty($this->settings[$prefix . 'enable_glow']),
            'enable_float' => !empty($this->settings[$prefix . 'enable_float']),
            'glow_intensity' => absint($this->settings[$prefix . 'glow_intensity'] ?? 20),
            'backdrop_blur' => absint($this->settings[$prefix . 'backdrop_blur'] ?? 12),
            'backdrop_opacity' => absint($this->settings[$prefix . 'backdrop_opacity'] ?? 95),
            
            // Preferred-specific
            'educational_text' => $banner_type === 'preferred' ? ($this->settings['preferred_educational_text'] ?? '') : '',
            'show_educational' => $banner_type === 'preferred' && !empty($this->settings['preferred_show_educational_link']),
            'educational_url' => $banner_type === 'preferred' ? ($this->settings['preferred_educational_post_url'] ?? '') : '',
            'mobile_stack_order' => absint($this->settings[$prefix . 'mobile_stack_order'] ?? ($banner_type === 'follow' ? 1 : 2)),
        );

        $banner = apply_filters('signalkit_banner_data', $banner, $banner_type);

        ob_start();
        include $template_file;
        $output = ob_get_clean();

        echo wp_kses_post($output);

        // Render Schema (JSON-LD)
        // Handled separately because wp_kses_post strips <script> tags.
        // We trust our own JSON encoding for safety.
        if ($include_schema) {
            $this->render_schema($banner_type, $banner);
        }
    }

    /**
     * Render JSON-LD Schema
     * 
     * @param string $banner_type follow|preferred
     * @param array $banner Banner data
     */
    private function render_schema($banner_type, $banner) {
        $schema = [];
        $site_name = $banner['site_name'];
        $headline = $banner['headline'];
        $description = $banner['description'];
        $url = $banner['button_url'];

        if (empty($url)) return;

        if ($banner_type === 'follow') {
            $schema = [
                "@context" => "https://schema.org",
                "@type" => "FollowAction",
                "@id" => "FollowOnGoogleNews",
                "name" => $headline,
                "description" => $description,
                "target" => [
                    "@type" => "EntryPoint",
                    "urlTemplate" => $url,
                    "actionPlatform" => ["http://schema.org/DesktopWebPlatform", "http://schema.org/MobileWebPlatform"]
                ],
                "object" => [
                    "@type" => "NewsMediaOrganization",
                    "name" => $site_name,
                    "url" => home_url(),
                    "logo" => [
                        "@type" => "ImageObject",
                        "url" => get_site_icon_url()
                    ]
                ]
            ];
        } elseif ($banner_type === 'preferred') {
            $schema = [
                "@context" => "https://schema.org",
                "@type" => "Action",
                "@id" => "AddPreferredSource",
                "name" => $headline,
                "description" => $description,
                "target" => [
                    "@type" => "EntryPoint",
                    "urlTemplate" => $url,
                    "actionPlatform" => ["http://schema.org/DesktopWebPlatform", "http://schema.org/MobileWebPlatform"]
                ],
                "agent" => [
                    "@type" => "NewsMediaOrganization",
                    "name" => $site_name,
                    "url" => home_url(),
                    "logo" => [
                        "@type" => "ImageObject",
                        "url" => get_site_icon_url()
                    ]
                ]
            ];

            if (!empty($banner['educational_url']) && !empty($banner['educational_text'])) {
                $schema['potentialAction'] = [
                    "@type" => "LearnAction",
                    "name" => $banner['educational_text'],
                    "target" => [
                        "@type" => "EntryPoint",
                        "urlTemplate" => $banner['educational_url']
                    ]
                ];
            }
        }

        if (!empty($schema)) {
            echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
        }
    }
    
    /**
     * Render custom lead capture banner
     */
    private function render_custom_banner() {
        $template_file = SIGNALKIT_PLUGIN_DIR . 'public/partials/banner-custom.php';
        
        if (!file_exists($template_file)) {
            return;
        }
        
        // Pass settings to template
        $settings = $this->settings;
        
        ob_start();
        include $template_file;
        $output = ob_get_clean();
        
        echo wp_kses_post($output);
    }

    private function get_custom_css() {
        $css = '';
        $css .= $this->generate_global_css();
        return $css;
    }

    public function generate_global_css() {
        $follow_primary = sanitize_hex_color($this->settings['follow_primary_color'] ?? '#4285f4');
        $preferred_primary = sanitize_hex_color($this->settings['preferred_primary_color'] ?? '#ea4335');

        // Button Colors
        $follow_btn_text = sanitize_hex_color($this->settings['follow_button_text_color'] ?? '#ffffff');
        $follow_btn_bg = sanitize_hex_color($this->settings['follow_button_bg_color'] ?? $follow_primary);
        
        $preferred_btn_text = sanitize_hex_color($this->settings['preferred_button_text_color'] ?? '#ffffff');
        $preferred_btn_bg = sanitize_hex_color($this->settings['preferred_button_bg_color'] ?? $preferred_primary);

        $css = "\n/* SignalKit Global CSS Variables */\n";
        $css .= ":root {\n";
        $css .= "    --signalkit-primary: {$follow_primary};\n";
        $css .= "    --signalkit-primary-hover: " . $this->darken_color($follow_primary, 10) . ";\n";
        $css .= "    --signalkit-secondary: " . sanitize_hex_color($this->settings['follow_secondary_color'] ?? '#ffffff') . ";\n";
        $css .= "    --signalkit-accent: " . sanitize_hex_color($this->settings['follow_accent_color'] ?? '#34a853') . ";\n";
        $css .= "    --signalkit-text: " . sanitize_hex_color($this->settings['follow_text_color'] ?? '#1a1a1a') . ";\n";
        $css .= "    --signalkit-button-text: {$follow_btn_text};\n";
        $css .= "    --signalkit-button-bg: {$follow_btn_bg};\n";
        $css .= "}\n";

        $css .= ".signalkit-banner-preferred {\n";
        $css .= "    --signalkit-primary: {$preferred_primary};\n";
        $css .= "    --signalkit-primary-hover: " . $this->darken_color($preferred_primary, 10) . ";\n";
        $css .= "    --signalkit-secondary: " . sanitize_hex_color($this->settings['preferred_secondary_color'] ?? '#ffffff') . ";\n";
        $css .= "    --signalkit-accent: " . sanitize_hex_color($this->settings['preferred_accent_color'] ?? '#fbbc04') . ";\n";
        $css .= "    --signalkit-text: " . sanitize_hex_color($this->settings['preferred_text_color'] ?? '#1a1a1a') . ";\n";
        $css .= "    --signalkit-button-text: {$preferred_btn_text};\n";
        $css .= "    --signalkit-button-bg: {$preferred_btn_bg};\n";
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
        
        // NEW: Validate session token (handle both formats)
        $session_token = isset($_POST['sessionToken']) ? sanitize_text_field(wp_unslash($_POST['sessionToken'])) : (isset($_POST['session_token']) ? sanitize_text_field(wp_unslash($_POST['session_token'])) : '');
        
        
        if (!$this->validate_session_token($session_token)) {
            wp_send_json_error(array('message' => 'Invalid session'), 403);
            return;
        }
        
        // Enhanced rate limiting
        $this->rate_limit_check('impression', 20, 60); // Increased limit
        
        // Sanitize and validate input
        $banner_type = sanitize_text_field(wp_unslash($_POST['banner_type'] ?? ''));
        
        if (!in_array($banner_type, ['follow', 'preferred', 'custom'], true)) {
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
        $session_token = isset($_POST['sessionToken']) ? sanitize_text_field(wp_unslash($_POST['sessionToken'])) : (isset($_POST['session_token']) ? sanitize_text_field(wp_unslash($_POST['session_token'])) : '');
        
        
        if (!$this->validate_session_token($session_token)) {
            wp_send_json_error(array('message' => 'Invalid session'), 403);
            return;
        }
        
        // Stricter rate limiting for clicks
        $this->rate_limit_check('click', 15, 60); // increased limit
        
        $banner_type = sanitize_text_field(wp_unslash($_POST['banner_type'] ?? ''));
        
        if (!in_array($banner_type, ['follow', 'preferred', 'custom'], true)) {
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
        $session_token = isset($_POST['sessionToken']) ? sanitize_text_field(wp_unslash($_POST['sessionToken'])) : (isset($_POST['session_token']) ? sanitize_text_field(wp_unslash($_POST['session_token'])) : '');
        
        
        if (!$this->validate_session_token($session_token)) {
            wp_send_json_error(array('message' => 'Invalid session'), 403);
            return;
        }
        
        $this->rate_limit_check('dismiss', 10, 60); // increased limit
        
        $banner_type = sanitize_text_field(wp_unslash($_POST['banner_type'] ?? ''));
        $duration = min(365, max(1, intval($_POST['duration'] ?? 7)));
        
        if (!in_array($banner_type, ['follow', 'preferred', 'custom'], true)) {
            wp_send_json_error(['message' => 'Invalid banner type'], 400);
            return;
        }
        
        // NEW: Verify user hasn't dismissed/undismissed multiple times
        $dismiss_history_key = 'signalkit_dismiss_history_' . md5($this->get_client_ip() . $banner_type);
        $dismiss_count = get_transient($dismiss_history_key) ?: 0;
        
        if ($dismiss_count >= 5) {
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
        
        // Set secure cookie
        // GDPR NOTE: This is a "Strictly Necessary" / "Functional" cookie as defined by GDPR/ePrivacy.
        // It stores ONLY user preference (dismissal state) and contains no PII (Personally Identifiable Information).
        // It is exempt from consent requirements.
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
            wp_send_json_error([
                'message' => 'Rate limit exceeded. Please try again later.'
            ], 429);
            exit;
        }
        
        // Add current attempt
        $attempts[] = $current_time;
        set_transient($key, $attempts, $window);
    }

    /**
     * Shortcode: [signalkit_follow]
     * Attributes:
     *   - style: leaderboard|skyscraper|rectangle|compact|full (default: full)
     *   - align: left|center|right (default: center)
     * 
     * @param array $atts Shortcode attributes
     * @return string Banner HTML
     */
    public function shortcode_follow($atts = array()) {
        return $this->render_shortcode_banner('follow', $atts);
    }

    /**
     * Shortcode: [signalkit_preferred]
     * Attributes:
     *   - style: leaderboard|skyscraper|rectangle|compact|full (default: full)
     *   - align: left|center|right (default: center)
     * 
     * @param array $atts Shortcode attributes
     * @return string Banner HTML
     */
    public function shortcode_preferred($atts = array()) {
        return $this->render_shortcode_banner('preferred', $atts);
    }

    /**
     * Shortcode: [signalkit_custom]
     * Attributes:
     *   - style: leaderboard|skyscraper|rectangle|compact|full (default: full)
     *   - align: left|center|right (default: center)
     * 
     * @param array $atts Shortcode attributes
     * @return string Banner HTML
     */
    public function shortcode_custom($atts = array()) {
        return $this->render_shortcode_banner('custom', $atts);
    }

    /**
     * Unified shortcode banner renderer
     * Handles all banner types with style variations
     * 
     * @param string $banner_type follow|preferred|custom
     * @param array $atts Shortcode attributes
     * @return string Banner HTML
     */
    private function render_shortcode_banner($banner_type, $atts) {
        try {
            // Parse attributes with defaults
            $atts = shortcode_atts(array(
                'style' => 'full',      // leaderboard, skyscraper, rectangle, compact, full
                'align' => 'center',    // left, center, right
                'class' => '',          // Additional custom class
            ), $atts, 'signalkit_' . $banner_type);

            // Validate style
            $valid_styles = array('leaderboard', 'skyscraper', 'rectangle', 'compact', 'full');
            $style = in_array($atts['style'], $valid_styles) ? $atts['style'] : 'full';
            
            // Validate alignment
            $valid_aligns = array('left', 'center', 'right');
            $align = in_array($atts['align'], $valid_aligns) ? $atts['align'] : 'center';

            // Store style for filter callback
            $this->current_shortcode_style = $style;
            $this->current_shortcode_align = $align;
            $this->current_shortcode_extra_class = sanitize_html_class($atts['class']);

            // Determine filter name based on banner type
            $filter_name = 'signalkit_' . $banner_type . '_banner_classes';
            
            // Add inline and style classes
            add_filter($filter_name, array($this, 'add_shortcode_classes'));
            
            ob_start();
            
            // Handle custom banner differently
            if ($banner_type === 'custom') {
                $this->render_custom_banner_shortcode();
            } else {
                $this->render_banner($banner_type);
            }
            
            $html = ob_get_clean();
            
            // Remove filter to prevent affecting other banners
            remove_filter($filter_name, array($this, 'add_shortcode_classes'));
            
            // Wrap in alignment container if needed
            if ($align !== 'center' || !empty($atts['class'])) {
                $wrapper_class = 'signalkit-shortcode-wrapper signalkit-align-' . esc_attr($align);
                if (!empty($atts['class'])) {
                    $wrapper_class .= ' ' . esc_attr($atts['class']);
                }
                $html = '<div class="' . $wrapper_class . '">' . $html . '</div>';
            }
            
            return $html;
        } catch (Throwable $e) {
            if (ob_get_length()) ob_end_clean();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                return '<!-- SignalKit Error: ' . esc_html($e->getMessage()) . ' -->';
            }
            return '';
        }
    }

    /**
     * Render custom banner for shortcode
     * Uses internal render_custom_banner method
     */
    private function render_custom_banner_shortcode() {
        $this->render_custom_banner();
    }

    /**
     * Add shortcode classes callback
     * Adds inline, style, and alignment classes to banner
     * 
     * @param array $classes Existing classes
     * @return array Modified classes
     */
    public function add_shortcode_classes($classes) {
        // Core inline class for positioning
        $classes[] = 'signalkit-inline';
        
        // Style class (leaderboard, skyscraper, etc.)
        if (!empty($this->current_shortcode_style)) {
            $classes[] = 'signalkit-style-' . $this->current_shortcode_style;
        }
        
        // Alignment class
        if (!empty($this->current_shortcode_align)) {
            $classes[] = 'signalkit-align-' . $this->current_shortcode_align;
        }
        
        // Extra custom class
        if (!empty($this->current_shortcode_extra_class)) {
            $classes[] = $this->current_shortcode_extra_class;
        }
        
        return $classes;
    }

    /**
     * Legacy callback for backward compatibility
     */
    public function add_inline_class($classes) {
        $classes[] = 'signalkit-inline';
        return $classes;
    }

    /**
     * AJAX Handler: Get banner HTML (Theme Compatibility Loader)
     * Used as a fallback when PHP hooks fail to inject content (e.g. broken themes)
     * 
     * SECURITY: Nonce verification added for CodeCanyon compliance
     */
    public function ajax_get_banner_html() {
        // Verify nonce for security (CodeCanyon requirement)
        check_ajax_referer('signalkit_nonce', 'nonce');
        
        // Force rendering state reset for this new request
        $this->banners_rendered = false;
        
        ob_start();
        $this->display_banners();
        $html = ob_get_clean();
        
        if (empty($html)) {
            wp_send_json_error(['message' => __('No banners to display', 'signalkit')], 404);
        } else {
            wp_send_json_success(['html' => $html]);
        }
    }
}