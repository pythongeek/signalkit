<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package SignalKit
 * @version 1.0.0
 *
 * ENVATO/WORDPRESS.ORG COMPLIANCE:
 * =================================
 * - Follows WordPress Plugin Handbook guidelines for uninstall
 * - Deletes ALL plugin data only when explicitly uninstalled
 * - Does NOT delete data on deactivation (see class-signalkit-deactivator.php)
 * - Multisite compatible
 * - User confirmation required via WordPress uninstall process
 *
 * WHAT THIS FILE DELETES:
 * =======================
 * 1. Plugin options (signalkit_settings, etc.)
 * 2. Plugin transients (rate limiting, caching)
 * 3. Analytics custom post type data
 * 4. Post meta associated with analytics
 * 5. Term relationships for analytics CPT
 * 
 * WHAT THIS FILE CANNOT DELETE:
 * =============================
 * - User cookies (browser-side, expire automatically)
 * - User localStorage (browser-side, cleared by users)
 * 
 * NOTE: Cookies are dismissed after X days (user setting) and
 * automatically expire. This is GDPR/privacy compliant.
 */

// If uninstall not called from WordPress, exit - SECURITY
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Helper function to clean up all plugin data for a single site.
 * 
 * This function deletes:
 * 1. All plugin options
 * 2. All plugin transients (rate limiting, sessions)
 * 3. All analytics data (custom post type)
 * 4. All post meta for analytics
 * 5. All term relationships
 *
 * @return void
 */
function signalkit_uninstall_cleanup() {
    global $wpdb;

    // ===================================
    // 1. DELETE ALL PLUGIN OPTIONS
    // ===================================
    delete_option('signalkit_settings');
    delete_option('signalkit_analytics'); // Legacy analytics option
    delete_option('signalkit_version');
    delete_option('signalkit_db_version'); // If used for migrations

    // ===================================
    // 2. DELETE ALL PLUGIN TRANSIENTS
    // ===================================
    // This includes:
    // - Rate limiting transients (signalkit_rl_*)
    // - Session transients (signalkit_session_*)
    // - Cache transients (signalkit_cache_*)
    
    // Delete transient keys
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '\_transient\_signalkit\_%' 
         OR option_name LIKE '\_transient\_timeout\_signalkit\_%'"
    );

    // For multisite: also delete from sitemeta
    if (is_multisite()) {
        $wpdb->query(
            "DELETE FROM {$wpdb->sitemeta} 
             WHERE meta_key LIKE '\_site\_transient\_signalkit\_%' 
             OR meta_key LIKE '\_site\_transient\_timeout\_signalkit\_%'"
        );
    }

    // ===================================
    // 3. DELETE ANALYTICS CUSTOM POST TYPE
    // ===================================
    $cpt_name = 'signalkit_analytics';
    
    // Get all analytics posts
    $analytics_posts = get_posts(array(
        'post_type'   => $cpt_name,
        'numberposts' => -1,
        'post_status' => 'any',
        'fields'      => 'ids'
    ));

    if (!empty($analytics_posts)) {
        foreach ($analytics_posts as $post_id) {
            // Delete post meta
            $wpdb->delete($wpdb->postmeta, array('post_id' => $post_id), array('%d'));
            
            // Delete term relationships
            $wpdb->delete($wpdb->term_relationships, array('object_id' => $post_id), array('%d'));
            
            // Delete the post itself (bypass trash)
            wp_delete_post($post_id, true);
        }
    }

    // ===================================
    // 4. DELETE ORPHANED POST META
    // ===================================
    // Remove any orphaned meta that may have been left behind
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} 
             WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})"
        )
    );

    // ===================================
    // 5. CLEANUP CUSTOM TABLES (IF ANY)
    // ===================================
    // If you added custom database tables, delete them here:
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}signalkit_custom_table");

    // ===================================
    // 6. CLEAR USER META (IF STORING USER-SPECIFIC DATA)
    // ===================================
    // If you stored user preferences in user meta:
    // delete_metadata('user', 0, 'signalkit_user_preference', '', true);

    // ===================================
    // 7. FLUSH REWRITE RULES
    // ===================================
    // Clean up any custom rewrite rules
    flush_rewrite_rules();

    // ===================================
    // NOTE: COOKIES CANNOT BE DELETED SERVER-SIDE
    // ===================================
    // Cookies like 'signalkit_dismissed_follow' are stored in the
    // user's browser and will expire based on their duration setting.
    // This is proper GDPR/privacy behavior - users control their cookies.
    // 
    // The plugin respects cookie expiry and dismissal preferences.
}

/**
 * Main uninstall execution
 * Handles both single-site and multisite installations
 */
if (is_multisite()) {
    // ===================================
    // MULTISITE CLEANUP
    // ===================================
    global $wpdb;
    
    // Get all blog IDs
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    $original_blog_id = get_current_blog_id();
    
    // Loop through all sites
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        signalkit_uninstall_cleanup();
    }
    
    // Restore original blog
    switch_to_blog($original_blog_id);
    
    // Delete network-wide options (if any)
    delete_site_option('signalkit_network_settings');
    
} else {
    // ===================================
    // SINGLE SITE CLEANUP
    // ===================================
    signalkit_uninstall_cleanup();
}

/**
 * UNINSTALL COMPLETE
 * 
 * All plugin data has been removed from the database.
 * User cookies will expire naturally based on dismissal duration.
 * 
 * This complies with:
 * - WordPress Plugin Handbook
 * - Envato Quality Standards
 * - GDPR data deletion requirements
 * - WordPress.org plugin guidelines
 */