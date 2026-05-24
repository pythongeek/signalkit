<?php
if (!defined('ABSPATH')) { exit; }
/**
 * Plugin Name: SignalKit
 * Description: News publisher notification plugin with Follow, Preferred Source, and Lead Capture banners to help grow your audience and collect leads.
 * Version: 2.0.1
 * Requires at least: 5.0
 * Author: N4Nion
 * Author URI: https://n4nion.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: signalkit
 * Domain Path: /languages
 *
 * @package SignalKit
 * @version 2.0.1
 * WordPress & Envato Compatible
 * - GPL-2.0+ licensed (compatible with WordPress.org and Envato)
 * - No encoded/obfuscated code
 * - Complete source code included
 * - Follows WordPress coding standards
 * - Comprehensive documentation included
 * - Security hardened with best practices
 *
 * Security Features:
 * - Nonce verification on all AJAX requests
 * - Session token validation
 * - Rate limiting (client + server)
 * - Input sanitization and output escaping
 * - SQL injection prevention via WordPress APIs
 * - XSS prevention via proper escaping
 * - CSRF protection via nonces
 * - Cookie security with SameSite attribute
 *
 * Performance:
 * - Optimized database queries
 * - Efficient caching strategies
 * - Minimal HTTP requests
 * - Lightweight assets (<50KB total)
 * - Race condition protection
 *
 * Compatibility:
 * - WordPress 5.0+ tested
 * - PHP 7.2 - 8.2 compatible
 * - Multisite compatible
 * - Theme agnostic
 * - Translation ready
 * - RTL language support
 * - WCAG 2.1 AA compliant
 */

// Security measure is now at the top of the file

/**
 * Plugin version and constants.
 */
define('SIGNALKIT_VERSION', '2.0.1');
define('SIGNALKIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SIGNALKIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SIGNALKIT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 * Uses unique prefix to prevent conflicts with other plugins.
 */
function signalkit_activate_plugin() {
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-activator.php';
    SignalKit_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * Uses unique prefix to prevent conflicts with other plugins.
 */
function signalkit_deactivate_plugin() {
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-deactivator.php';
    SignalKit_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'signalkit_activate_plugin');
register_deactivation_hook(__FILE__, 'signalkit_deactivate_plugin');

/**
 * Begins execution of the plugin.
 * Uses unique, descriptive name to prevent conflicts.
 */
function signalkit_initialize_plugin_core() {
    // Include helper functions FIRST (prevents redeclaration errors)
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/signalkit-helpers.php';
    
    // Require the core plugin class
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-core.php';
    
    // Include performance and SEO optimization
    if (file_exists(SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-performance.php')) {
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-performance.php';
    }
    
    // Include custom banner handler
    if (file_exists(SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-custom-handler.php')) {
        require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-custom-handler.php';
        
        if (class_exists('SignalKit_Custom_Handler')) {
            new SignalKit_Custom_Handler();
        }
    }
    
    // Check if the class exists before instantiating
    if (class_exists('SignalKit_Core')) {
        try {
            $plugin = new SignalKit_Core();
            $plugin->run();
        } catch (Exception $e) {
            if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>' . esc_html__('SignalKit Error:', 'signalkit') . '</strong> ' . esc_html($e->getMessage());
                    echo '</p></div>';
                });
            }
        }
    }
}

// Run the plugin after all plugins are loaded
add_action('plugins_loaded', 'signalkit_initialize_plugin_core');