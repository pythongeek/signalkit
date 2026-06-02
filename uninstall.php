<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://github.com/pythongeek/signalkit
 * @since      1.0.0
 *
 * @package    SignalKit
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
delete_option( 'signalkit_settings' );
delete_option( 'signalkit_analytics' );

global $wpdb;

// Drop custom table.
$signalkit_table_name = esc_sql( $wpdb->prefix . 'signalkit_submissions' );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS `{$signalkit_table_name}`" );

// Delete plugin transients.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_signalkit_%' OR option_name LIKE '_transient_timeout_signalkit_%'" );
