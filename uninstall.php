<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package SignalKit_For_Google
 */

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete plugin options.
 */
delete_option('signalkit_settings');
delete_option('signalkit_analytics');
delete_option('signalkit_version');

/**
 * For multisite installations.
 */
if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    $original_blog_id = get_current_blog_id();
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        delete_option('signalkit_settings');
        delete_option('signalkit_analytics');
        delete_option('signalkit_version');
    }
    
    switch_to_blog($original_blog_id);
}

/**
 * Clean up any transients.
 */
delete_transient('signalkit_cache');