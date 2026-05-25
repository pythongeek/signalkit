<?php
/**
 * SignalKit Uninstall
 *
 * Fired when the plugin is uninstalled.
 *
 * @package SignalKit
 * @version 2.0.1
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user has permission to uninstall
if (!current_user_can('activate_plugins')) {
    return;
}

// Get settings to check if data cleanup is enabled
$settings = get_option('signalkit_settings', array());

// Only delete data if user explicitly opted in
if (empty($settings['delete_data_on_uninstall'])) {
    return;
}

// Delete plugin options
delete_option('signalkit_settings');
delete_option('signalkit_analytics');
delete_option('signalkit_version');
delete_option('signalkit_activated_at');

// Delete transients (cleanup any remaining)
global $wpdb;
$like = $wpdb->esc_like('_transient_signalkit_') . '%';
$wpdb->query($wpdb->prepare(
    "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE %s",
    $like
));

$like_timeout = $wpdb->esc_like('_transient_timeout_signalkit_') . '%';
$wpdb->query($wpdb->prepare(
    "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE %s",
    $like_timeout
));

// Drop custom submissions table if it exists
$table_name = $wpdb->prefix . 'signalkit_submissions';
$wpdb->query($wpdb->prepare(
    "DROP TABLE IF EXISTS `%s`",
    $table_name
));
