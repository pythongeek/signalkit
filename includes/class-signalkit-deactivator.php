<?php
/**
 * Fired during plugin deactivation.
 *
 * @package SignalKit
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Deactivator {
    
    /**
     * Deactivate the plugin.
     *
     * This method is called when the plugin is deactivated.
     * It's the right place to clean up scheduled tasks (cron jobs)
     * but NOT for deleting data (options, posts, etc.).
     * Data deletion belongs in uninstall.php.
     */
    public static function deactivate() {
        // Clear any scheduled events to prevent orphaned cron jobs
        wp_clear_scheduled_hook('signalkit_daily_cleanup');
        
        // Flush object cache
        wp_cache_flush();
        
        signalkit_log('Deactivator: Plugin deactivated, cron hooks cleared.');
    }
}