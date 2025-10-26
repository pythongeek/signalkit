<?php
/**
 * The core plugin class.
 *
 * @package SignalKit_For_Google
 * @version 2.1.2 - FIXED: Added all missing AJAX handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Core {
    
    protected $loader;
    protected $plugin_name;
    protected $version;
    
    public function __construct() {
        $this->version = SIGNALKIT_VERSION;
        $this->plugin_name = 'signalkit-for-google';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        
        signalkit_log('Core: Plugin initialized', array('version' => $this->version));
    }
    
    /**
     * Load required dependencies.
     */
    private function load_dependencies() {
        // Loader
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-loader.php';
        
        // Core classes
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-analytics.php';
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-display-rules.php';
        
        // Admin classes
        require_once SIGNALKIT_PLUGIN_DIR . 'admin/class-signalkit-admin.php';
        require_once SIGNALKIT_PLUGIN_DIR . 'admin/class-signalkit-settings.php';
        
        // Public classes
        require_once SIGNALKIT_PLUGIN_DIR . 'public/class-signalkit-public.php';
        
        $this->loader = new SignalKit_Loader();
        
        signalkit_log('Core: Dependencies loaded');
    }
    
    /**
     * Register admin hooks.
     */
    private function define_admin_hooks() {
        // Pass plugin_name and version to admin class
        $plugin_admin = new SignalKit_Admin($this->get_plugin_name(), $this->get_version());
        
        // Enqueue styles and scripts
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Add menu and settings
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // ALL AJAX handlers - FIXED: Added missing preview, export, import handlers
        $this->loader->add_action('wp_ajax_signalkit_reset_analytics', $plugin_admin, 'ajax_reset_analytics');
        $this->loader->add_action('wp_ajax_signalkit_preview_banner', $plugin_admin, 'ajax_preview_banner');
        $this->loader->add_action('wp_ajax_signalkit_export_settings', $plugin_admin, 'ajax_export_settings');
        $this->loader->add_action('wp_ajax_signalkit_import_settings', $plugin_admin, 'ajax_import_settings');
        
        // Settings link
        $this->loader->add_filter('plugin_action_links_' . SIGNALKIT_PLUGIN_BASENAME, $plugin_admin, 'add_settings_link');
        
        // Admin notices
        $this->loader->add_action('admin_notices', $plugin_admin, 'display_admin_notices');
        
        // Plugin row meta (for analytics link in plugin list)
        $this->loader->add_filter('plugin_row_meta', $plugin_admin, 'add_plugin_action_links', 10, 2);
        
        signalkit_log('Core: Admin hooks registered (including preview AJAX)');
    }
    
    /**
     * Register public hooks.
     */
    private function define_public_hooks() {
        // Pass plugin_name and version to public class
        $plugin_public = new SignalKit_Public($this->get_plugin_name(), $this->get_version());
        
        // Enqueue styles and scripts
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Display banners
        $this->loader->add_action('wp_footer', $plugin_public, 'display_banners');
        
        // AJAX handlers for tracking (logged in and logged out users)
        $this->loader->add_action('wp_ajax_signalkit_track_click', $plugin_public, 'ajax_track_click');
        $this->loader->add_action('wp_ajax_nopriv_signalkit_track_click', $plugin_public, 'ajax_track_click');
        
        $this->loader->add_action('wp_ajax_signalkit_track_dismissal', $plugin_public, 'ajax_track_dismissal');
        $this->loader->add_action('wp_ajax_nopriv_signalkit_track_dismissal', $plugin_public, 'ajax_track_dismissal');
        
        $this->loader->add_action('wp_ajax_signalkit_track_view', $plugin_public, 'ajax_track_view');
        $this->loader->add_action('wp_ajax_nopriv_signalkit_track_view', $plugin_public, 'ajax_track_view');
        
        signalkit_log('Core: Public hooks registered');
    }
    
    /**
     * Run the loader.
     */
    public function run() {
        $this->loader->run();
        signalkit_log('Core: Loader executed');
    }
    
    /**
     * Get plugin name.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    /**
     * Get loader.
     */
    public function get_loader() {
        return $this->loader;
    }
    
    /**
     * Get version.
     */
    public function get_version() {
        return $this->version;
    }
}