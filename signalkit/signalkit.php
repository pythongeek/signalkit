<?php
/**
 * Plugin Name: SignalKit
 * Description: Display customizable Google News Follow and Preferred Source banners to boost your publication's visibility and engagement.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Nion From BDOWNEER LLC
 * Author URI: https://yoursite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: signalkit
 * Domain Path: /languages
 *
 * @package SignalKit
 *
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

// If this file is called directly, abort - Security measure
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin version and constants.
 */
define('SIGNALKIT_VERSION', '1.0.0');
define('SIGNALKIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SIGNALKIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SIGNALKIT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Debug logging function - DEFINED FIRST
 */
if (!function_exists('signalkit_log')) {
    function signalkit_log($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $timestamp = current_time('d-M-Y H:i:s T');
            $log_message = '[' . $timestamp . '] [SignalKit] ' . $message;
            if ($data !== null) {
                $log_message .= ' | Data: ' . print_r($data, true);
            }
            error_log($log_message);
        }
    }
}

/**
 * The code that runs during plugin activation.
 */
function activate_signalkit() {
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-activator.php';
    SignalKit_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_signalkit() {
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-deactivator.php';
    SignalKit_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_signalkit');
register_deactivation_hook(__FILE__, 'deactivate_signalkit');

/**
 * Begins execution of the plugin.
 */
function run_signalkit() {
    // Require the core plugin class
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-core.php';
    
    // Check if the class exists before instantiating
    if (class_exists('SignalKit_Core')) {
        try {
            $plugin = new SignalKit_Core();
            $plugin->run();
            signalkit_log('Plugin initialized successfully');
        } catch (Exception $e) {
            signalkit_log('Critical Error in run_signalkit: ' . $e->getMessage());
            
            if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>' . esc_html__('SignalKit Error:', 'signalkit') . '</strong> ' . esc_html($e->getMessage());
                    echo '</p></div>';
                });
            }
        }
    } else {
        signalkit_log('Critical Error: SignalKit_Core class not found');
    }
}

// Run the plugin after all plugins are loaded
add_action('plugins_loaded', 'run_signalkit');