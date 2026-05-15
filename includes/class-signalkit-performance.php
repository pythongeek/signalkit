<?php
/**
 * SignalKit Performance & SEO Optimization
 * 
 * @package SignalKit
 * @version 2.0.0
 * 
 * Features:
 * - Lazy loading for banners
 * - Async/defer script loading
 * - Critical CSS inlining
 * - Google Fonts optimization
 * - Schema.org markup
 * - Meta tag optimization
 * - Preconnect & prefetch hints
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Performance optimization class
 */
class SignalKit_Performance {
    
    /**
     * Google Fonts CDN base URL
     */
    const GOOGLE_FONTS_URL = 'https://fonts.googleapis.com/css2';
    
    /**
     * Font display strategy
     */
    const FONT_DISPLAY = 'swap';
    
    /**
     * Active fonts to load
     * 
     * @var array
     */
    private $active_fonts = array();
    
    /**
     * Settings
     * 
     * @var array
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option('signalkit_settings', array());
        
        // Optimization hooks
        add_action('wp_head', array($this, 'add_preconnect_hints'), 1);
        add_action('wp_head', array($this, 'add_critical_css'), 5);
        add_action('wp_head', array($this, 'add_schema_markup'), 99);
        add_filter('script_loader_tag', array($this, 'add_async_defer'), 10, 3);
        add_filter('style_loader_tag', array($this, 'add_preload_fonts'), 10, 4);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_google_fonts'), 5);
        
        // Performance settings
        add_filter('signalkit_banner_should_lazy_load', array($this, 'should_lazy_load'), 10, 2);
    }
    
    /**
     * Add preconnect hints for external resources
     * Privacy: Only adds hints if user has explicitly enabled external fonts
     */
    public function add_preconnect_hints() {
        // Privacy: Only add preconnect if external fonts are enabled
        if (empty($this->settings['enable_google_fonts'])) {
            return;
        }

        // Only if banners are enabled
        if (!$this->has_active_banners()) {
            return;
        }

        // Preconnect to Google Fonts if using custom fonts
        if ($this->has_custom_fonts()) {
            echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
            echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        }
        
        // DNS prefetch for analytics if enabled
        if (!empty($this->settings['analytics_tracking'])) {
            echo '<link rel="dns-prefetch" href="' . esc_url(admin_url('admin-ajax.php')) . '">' . "\n";
        }
    }
    
    /**
     * Add critical CSS inline for above-the-fold content
     */
    public function add_critical_css() {
        if (!$this->has_active_banners()) {
            return;
        }
        
        // Minimal critical CSS for banner positioning
        $critical_css = '
            .signalkit-banner{position:fixed;z-index:999999;opacity:0;pointer-events:none}
            .signalkit-banner.active{opacity:1;pointer-events:auto}
            .signalkit-position-bottom_left{bottom:20px;left:20px}
            .signalkit-position-bottom_right{bottom:20px;right:20px}
            .signalkit-position-bottom_center{bottom:20px;left:50%;transform:translateX(-50%)}
            .signalkit-position-top_left{top:20px;left:20px}
            .signalkit-position-top_right{top:20px;right:20px}
            .signalkit-position-top_center{top:20px;left:50%;transform:translateX(-50%)}
            .signalkit-position-center{top:50%;left:50%;transform:translate(-50%,-50%)}
            @media(max-width:768px){.signalkit-banner{left:10px!important;right:10px!important;max-width:calc(100vw - 20px)!important;transform:none!important}}
        ';
        
        // Register a dummy handle as anchor for the inline style
        wp_register_style('signalkit-critical', false, array(), SIGNALKIT_VERSION);
        wp_enqueue_style('signalkit-critical');
        wp_add_inline_style('signalkit-critical', $this->minify_css($critical_css));
    }
    
    /**
     * Add schema.org markup for organization
     */
    public function add_schema_markup() {
        if (!$this->has_active_banners()) {
            return;
        }
        
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        
        // Only add if Google News banners are enabled
        if (!empty($this->settings['follow_enabled']) || !empty($this->settings['preferred_enabled'])) {
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $site_name,
                'url' => $site_url,
                'sameAs' => array(),
            );
            
            // Add Google News publication URL if available
            if (!empty($this->settings['follow_google_news_url'])) {
                $schema['sameAs'][] = $this->settings['follow_google_news_url'];
            }
            
            // Add logo if available
            $custom_logo_id = get_theme_mod('custom_logo');
            if ($custom_logo_id) {
                $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                if ($logo_url) {
                    $schema['logo'] = array(
                        '@type' => 'ImageObject',
                        'url' => $logo_url,
                    );
                }
            }
            
            // Remove empty sameAs
            if (empty($schema['sameAs'])) {
                unset($schema['sameAs']);
            }
            
            // Use WordPress script API instead of raw echo.
            wp_register_script('signalkit-schema-org', '', array(), SIGNALKIT_VERSION, false);
            wp_enqueue_script('signalkit-schema-org');
            wp_add_inline_script(
                'signalkit-schema-org',
                wp_json_encode($schema, JSON_UNESCAPED_SLASHES),
                'before'
            );
        }
    }
    
    /**
     * Add async/defer to script tags
     * 
     * @param string $tag Script tag HTML
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return string Modified script tag
     */
    public function add_async_defer($tag, $handle, $src) {
        // Only modify SignalKit scripts
        if (strpos($handle, 'signalkit') === false) {
            return $tag;
        }
        
        // Use defer for our scripts (doesn't block parsing, maintains order)
        if (strpos($tag, 'defer') === false) {
            $tag = str_replace(' src', ' defer src', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Add preload for font stylesheets
     * 
     * @param string $html Link tag HTML
     * @param string $handle Stylesheet handle
     * @param string $href Stylesheet URL
     * @param string $media Media attribute
     * @return string Modified link tag
     */
    public function add_preload_fonts($html, $handle, $href, $media) {
        // Only for Google Fonts
        if (strpos($handle, 'signalkit-font') !== false) {
            // Add preload with font-display: swap
            $preload = '<link rel="preload" as="style" href="' . esc_url($href) . '">' . "\n";
            return $preload . $html;
        }
        
        return $html;
    }
    
    /**
     * Enqueue Google Fonts based on settings
     * Privacy: Only loads fonts if user has explicitly enabled external fonts
     */
    public function enqueue_google_fonts() {
        // Privacy: Only load external fonts if user has opted in
        if (empty($this->settings['enable_google_fonts'])) {
            return;
        }

        if (!$this->has_active_banners()) {
            return;
        }

        $fonts_needed = $this->get_required_fonts();
        
        if (empty($fonts_needed)) {
            return;
        }
        
        // Build Google Fonts URL
        $font_families = array();
        foreach ($fonts_needed as $font) {
            // Map font names to Google Fonts format
            $google_font_name = $this->get_google_font_name($font);
            if ($google_font_name) {
                $font_families[] = 'family=' . urlencode($google_font_name) . ':wght@300;400;500;600;700;800';
            }
        }
        
        if (!empty($font_families)) {
            $fonts_url = self::GOOGLE_FONTS_URL . '?' . implode('&', $font_families) . '&display=' . self::FONT_DISPLAY;
            
            wp_enqueue_style(
                'signalkit-fonts',
                $fonts_url,
                array(),
                SIGNALKIT_VERSION
            );
        }
    }
    
    /**
     * Get required fonts from settings
     * 
     * @return array
     */
    private function get_required_fonts() {
        $fonts = array();
        
        // Check custom banner font
        if (!empty($this->settings['custom_enabled']) && !empty($this->settings['custom_font_family'])) {
            $font = $this->settings['custom_font_family'];
            if ($font !== 'system' && !in_array($font, $fonts)) {
                $fonts[] = $font;
            }
        }
        
        // Check follow banner font if added
        if (!empty($this->settings['follow_enabled']) && !empty($this->settings['follow_font_family'])) {
            $font = $this->settings['follow_font_family'];
            if ($font !== 'system' && !in_array($font, $fonts)) {
                $fonts[] = $font;
            }
        }
        
        // Check preferred banner font if added
        if (!empty($this->settings['preferred_enabled']) && !empty($this->settings['preferred_font_family'])) {
            $font = $this->settings['preferred_font_family'];
            if ($font !== 'system' && !in_array($font, $fonts)) {
                $fonts[] = $font;
            }
        }
        
        return $fonts;
    }
    
    /**
     * Convert font setting to Google Fonts name
     * 
     * @param string $font Font setting value
     * @return string|null Google Fonts name
     */
    private function get_google_font_name($font) {
        $map = array(
            'inter' => 'Inter',
            'roboto' => 'Roboto',
            'open-sans' => 'Open Sans',
            'lato' => 'Lato',
            'montserrat' => 'Montserrat',
            'poppins' => 'Poppins',
            'nunito' => 'Nunito',
            'raleway' => 'Raleway',
            'ubuntu' => 'Ubuntu',
            'playfair' => 'Playfair Display',
            'merriweather' => 'Merriweather',
            'source-sans' => 'Source Sans Pro',
            'oswald' => 'Oswald',
            'rubik' => 'Rubik',
        );
        
        return isset($map[$font]) ? $map[$font] : null;
    }
    
    /**
     * Check if custom fonts are being used
     * 
     * @return bool
     */
    private function has_custom_fonts() {
        $fonts = $this->get_required_fonts();
        return !empty($fonts);
    }
    
    /**
     * Check if any banners are active
     * 
     * @return bool
     */
    private function has_active_banners() {
        return !empty($this->settings['follow_enabled']) 
            || !empty($this->settings['preferred_enabled'])
            || !empty($this->settings['custom_enabled']);
    }
    
    /**
     * Determine if banner should lazy load
     * 
     * @param bool $should_lazy Current value
     * @param string $banner_type Banner type
     * @return bool
     */
    public function should_lazy_load($should_lazy, $banner_type) {
        // Always lazy load custom banner if delay or scroll trigger is set
        if ($banner_type === 'custom') {
            $delay = !empty($this->settings['custom_delay']);
            $scroll = !empty($this->settings['custom_scroll_trigger']);
            $exit = !empty($this->settings['custom_exit_intent']);
            
            return $delay || $scroll || $exit;
        }
        
        return $should_lazy;
    }
    
    /**
     * Minify CSS
     * 
     * @param string $css CSS content
     * @return string Minified CSS
     */
    private function minify_css($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
        // Remove extra spaces
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([\{\}\:\;\,])\s*/', '$1', $css);
        
        return trim($css);
    }
}

/**
 * SEO optimization class
 */
class SignalKit_SEO {
    
    /**
     * Settings
     * 
     * @var array
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option('signalkit_settings', array());
        
        // SEO hooks
        add_action('wp_head', array($this, 'add_meta_tags'), 1);
        add_filter('document_title_parts', array($this, 'optimize_title'), 10);
        add_action('wp_head', array($this, 'add_canonical_hints'), 5);
    }
    
    /**
     * Add SEO meta tags
     */
    public function add_meta_tags() {
        // Only on pages with active banners
        if (!$this->has_google_news_banners()) {
            return;
        }
        
        $site_name = get_bloginfo('name');
        
        // Add news publication meta if not already present
        if (!empty($this->settings['follow_google_news_url'])) {
            echo '<meta name="news_keywords" content="' . esc_attr($site_name) . ', news, updates">' . "\n";
        }
        
        // Add Google News specific meta
        if (is_single() || is_page()) {
            // Syndication source - helps Google understand original source
            echo '<meta name="syndication-source" content="' . esc_url(get_permalink()) . '">' . "\n";
            // Original source
            echo '<meta name="original-source" content="' . esc_url(home_url()) . '">' . "\n";
        }
    }
    
    /**
     * Optimize document title
     * 
     * @param array $title_parts Title parts
     * @return array
     */
    public function optimize_title($title_parts) {
        // No modification needed for SignalKit
        // This is a placeholder for future SEO enhancements
        return $title_parts;
    }
    
    /**
     * Add canonical URL hints
     */
    public function add_canonical_hints() {
        // Ensure canonical is set (most themes/plugins handle this)
        // This is just a safety check
    }
    
    /**
     * Check if Google News banners are enabled
     * 
     * @return bool
     */
    private function has_google_news_banners() {
        return !empty($this->settings['follow_enabled']) 
            || !empty($this->settings['preferred_enabled']);
    }
}

/**
 * Initialize performance and SEO classes
 */
function signalkit_init_performance() {
    new SignalKit_Performance();
    new SignalKit_SEO();
}
add_action('init', 'signalkit_init_performance');