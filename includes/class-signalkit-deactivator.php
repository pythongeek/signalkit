<?php
/**
 * Fired during plugin deactivation.
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Deactivator {
    
    /**
     * Deactivate the plugin.
     */
    public static function deactivate() {
        // Clear any scheduled events if you add them later
        wp_clear_scheduled_hook('signalkit_daily_cleanup');
        
        // Flush cache
        wp_cache_flush();
        
        signalkit_log('Deactivator: Plugin deactivated');
    }
}