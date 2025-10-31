<?php
/**
 * The core plugin class.
 *
 * @package SignalKit_For_Google
 * @version 1.0.0 - FIXED: Added missing impression AJAX handler registration
 * 
 * Security: Proper hook registration, capability checks
 * WordPress Compatible: Follows WP coding standards
 * Envato Compatible: GPL-2.0+ license, proper documentation
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly - Security measure
}

class SignalKit_Core {
    
    /**
     * The loader instance
     * @var SignalKit_Loader
     */
    protected $loader;
    
    /**
     * Plugin identifier
     * @var string
     */
    protected $plugin_name;
    
    /**
     * Plugin version
     * @var string
     */
    protected $version;
    
    /**
     * Initialize the core plugin class
     */
    public function __construct() {
        $this->version = SIGNALKIT_VERSION;
        $this->plugin_name = 'signalkit-for-google';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        
        signalkit_log('Core: Plugin initialized', array('version' => $this->version));
    }
    
    /**
     * Load required dependencies
     * Includes all necessary class files
     */
    private function load_dependencies() {
        // Loader class - handles WordPress hook registration
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-loader.php';
        
        // Core functionality classes
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-analytics.php';
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-display-rules.php';
        
        // Admin-specific classes
        require_once SIGNALKIT_PLUGIN_DIR . 'admin/class-signalkit-admin.php';
        require_once SIGNALKIT_PLUGIN_DIR . 'admin/class-signalkit-settings.php';
        
        // Public-facing classes
        require_once SIGNALKIT_PLUGIN_DIR . 'public/class-signalkit-public.php';
        
        // Initialize loader
        $this->loader = new SignalKit_Loader();
        
        signalkit_log('Core: Dependencies loaded successfully');
    }
    
    /**
     * Register admin hooks
     * All admin-area hooks and filters
     */
    private function define_admin_hooks() {
        $plugin_admin = new SignalKit_Admin($this->get_plugin_name(), $this->get_version());
        
        // Enqueue admin assets - WordPress standard hooks
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Add admin menu and register settings
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // AJAX handlers - Admin only (requires user to be logged in)
        $this->loader->add_action('wp_ajax_signalkit_reset_analytics', $plugin_admin, 'ajax_reset_analytics');
        $this->loader->add_action('wp_ajax_signalkit_preview_banner', $plugin_admin, 'ajax_preview_banner');
        $this->loader->add_action('wp_ajax_signalkit_export_settings', $plugin_admin, 'ajax_export_settings');
        $this->loader->add_action('wp_ajax_signalkit_import_settings', $plugin_admin, 'ajax_import_settings');
        
        // Settings link in plugins list page
        $this->loader->add_filter('plugin_action_links_' . SIGNALKIT_PLUGIN_BASENAME, $plugin_admin, 'add_settings_link');
        
        // Admin notices - for displaying important messages
        $this->loader->add_action('admin_notices', $plugin_admin, 'display_admin_notices');
        
        // Plugin row meta links
        $this->loader->add_filter('plugin_row_meta', $plugin_admin, 'add_plugin_action_links', 10, 2);
        
        signalkit_log('Core: Admin hooks registered successfully');
    }
    
    /**
     * Register public hooks
     * All frontend-facing hooks and filters
     */
    private function define_public_hooks() {
        $plugin_public = new SignalKit_Public($this->get_plugin_name(), $this->get_version());
        
        // Enqueue public assets - WordPress standard hooks
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Display banners in footer
        $this->loader->add_action('wp_footer', $plugin_public, 'display_banners');
        
        // AJAX handlers for tracking - FIXED: Added missing impression handler
        // Both logged in and logged out users (nopriv) can trigger these
        
        // Track impression - CRITICAL FIX: This was missing
        $this->loader->add_action('wp_ajax_signalkit_track_impression', $plugin_public, 'ajax_track_impression');
        $this->loader->add_action('wp_ajax_nopriv_signalkit_track_impression', $plugin_public, 'ajax_track_impression');
        
        // Track click
        $this->loader->add_action('wp_ajax_signalkit_track_click', $plugin_public, 'ajax_track_click');
        $this->loader->add_action('wp_ajax_nopriv_signalkit_track_click', $plugin_public, 'ajax_track_click');
        
        // Track dismissal
        $this->loader->add_action('wp_ajax_signalkit_track_dismissal', $plugin_public, 'ajax_track_dismissal');
        $this->loader->add_action('wp_ajax_nopriv_signalkit_track_dismissal', $plugin_public, 'ajax_track_dismissal');
        
        signalkit_log('Core: Public hooks registered successfully (including impression tracking)');
    }
    
    /**
     * Run the loader to execute all hooks
     * This registers all hooks with WordPress
     */
    public function run() {
        $this->loader->run();
        signalkit_log('Core: Loader executed - all hooks active');
    }
    
    /**
     * Get plugin name
     * @return string Plugin identifier
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    /**
     * Get loader reference
     * @return SignalKit_Loader Loader instance
     */
    public function get_loader() {
        return $this->loader;
    }
    
    /**
     * Get plugin version
     * @return string Plugin version number
     */
    public function get_version() {
        return $this->version;
    }

}
