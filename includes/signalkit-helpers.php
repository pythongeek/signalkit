<?php
/**
 * SignalKit Helper Functions
 * Centralized utility functions used across the plugin
 * 
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Convert hex color to RGB values
 * Used for CSS custom properties and color manipulation
 * 
 * @param string $hex Hex color code (with or without #)
 * @return string RGB values as comma-separated string (e.g., "255, 0, 0")
 */
if (!function_exists('signalkit_hex_to_rgb')) {
    function signalkit_hex_to_rgb($hex) {
        // Remove # if present
        $hex = ltrim($hex, '#');
        
        // Handle 3-character shorthand (e.g., #fff -> #ffffff)
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        // Validate hex length
        if (strlen($hex) !== 6) {
            // Return black as fallback for invalid input
            return '0, 0, 0';
        }
        
        // Convert hex to RGB
        $rgb = array_map('hexdec', str_split($hex, 2));
        
        return implode(', ', $rgb);
    }
}

/**
 * Sanitize and validate hex color
 * Wrapper for WordPress sanitize_hex_color with additional validation
 * 
 * @param string $color Hex color code
 * @param string $default Default color if validation fails
 * @return string Sanitized hex color
 */
if (!function_exists('signalkit_sanitize_hex_color')) {
    function signalkit_sanitize_hex_color($color, $default = '#000000') {
        $sanitized = sanitize_hex_color($color);
        return $sanitized ? $sanitized : $default;
    }
}

/**
 * Darken a hex color by a percentage
 * Used for hover states and color variations
 * 
 * @param string $hex Hex color code
 * @param int $percent Percentage to darken (0-100)
 * @return string Darkened hex color
 */
if (!function_exists('signalkit_darken_color')) {
    function signalkit_darken_color($hex, $percent) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) !== 6) {
            return '#000000';
        }
        
        // Convert to RGB
        list($r, $g, $b) = array_map('hexdec', str_split($hex, 2));
        
        // Darken each component
        $r = max(0, min(255, $r * (1 - $percent / 100)));
        $g = max(0, min(255, $g * (1 - $percent / 100)));
        $b = max(0, min(255, $b * (1 - $percent / 100)));
        
        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
}

/**
 * Lighten a hex color by a percentage
 * Used for background variations and subtle effects
 * 
 * @param string $hex Hex color code
 * @param int $percent Percentage to lighten (0-100)
 * @return string Lightened hex color
 */
if (!function_exists('signalkit_lighten_color')) {
    function signalkit_lighten_color($hex, $percent) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) !== 6) {
            return '#ffffff';
        }
        
        // Convert to RGB
        list($r, $g, $b) = array_map('hexdec', str_split($hex, 2));
        
        // Lighten each component
        $r = max(0, min(255, $r + (255 - $r) * ($percent / 100)));
        $g = max(0, min(255, $g + (255 - $g) * ($percent / 100)));
        $b = max(0, min(255, $b + (255 - $b) * ($percent / 100)));
        
        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
}

/**
 * Debug logging function
 * Only logs when WP_DEBUG is enabled
 * 
 * @param string $message Log message
 * @param mixed $data Optional data to log
 * @return void
 */
if (!function_exists('signalkit_log')) {
    function signalkit_log($message, $data = null) {
        // @codingStandardsIgnoreStart
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            if ($data !== null) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
                error_log('SignalKit: ' . $message . ' - ' . print_r($data, true));
            } else {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('SignalKit: ' . $message);
            }
        }
        // @codingStandardsIgnoreEnd
    }
}
