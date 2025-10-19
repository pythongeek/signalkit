<?php
/**
 * Plugin Name: SignalKit for Google
 * Plugin URI: https://yoursite.com/signalkit-for-google
 * Description: Display customizable Google News Follow and Preferred Source banners to boost your publication's visibility and engagement.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: signalkit-for-google
 * Domain Path: /languages
 *
 * @package SignalKit_For_Google
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
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
            $log_message = '[SignalKit] ' . $message;
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
 * 
 * CRITICAL: This runs AFTER WordPress is fully loaded to ensure
 * all dependencies and functions are available.
 */
function run_signalkit() {
    // Require the core plugin class
    require_once SIGNALKIT_PLUGIN_DIR . 'includes/class-signalkit-core.php';
    
    // Check if the class exists before instantiating
    if (class_exists('SignalKit_Core')) {
        try {
            $plugin = new SignalKit_Core();
            $plugin->run();
        } catch (Exception $e) {
            // Log the error for debugging
            signalkit_log('Critical Error in run_signalkit: ' . $e->getMessage());
            
            // Display admin notice if in admin area
            if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>SignalKit for Google Error:</strong> ' . esc_html($e->getMessage());
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