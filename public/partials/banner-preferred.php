<?php
/**
 * Google Preferred Source Banner Template
 * Version: 2.0.3
 *
 * @package SignalKit
 */

if (!defined('ABSPATH')) {
    exit;
}

// Template partial variables - intentionally unprefixed as these are passed from including context
if (!isset($signalkit_banner) || !is_array($signalkit_banner)) {
    return;
}

// SECURITY FIX: No HTML allowed in user-controlled content
$signalkit_allowed_html = array();

// Extract banner data with strict escaping
$signalkit_site_name        = esc_html($signalkit_banner['site_name'] ?? get_bloginfo('name'));
$signalkit_banner_id        = 'signalkit-preferred-banner-' . uniqid();
$signalkit_device           = $signalkit_banner['device'] ?? 'desktop';

// Content - SECURITY: No HTML tags allowed
$signalkit_headline         = str_replace('[site_name]', $signalkit_site_name, wp_kses($signalkit_banner['headline'] ?? '', $signalkit_allowed_html));
$signalkit_description      = wp_kses($signalkit_banner['description'] ?? '', $signalkit_allowed_html);
$signalkit_button_text      = esc_html($signalkit_banner['button_text']);
$signalkit_educational_text = esc_html($signalkit_banner['educational_text']);
$signalkit_google_preferences_url = esc_url_raw($signalkit_banner['button_url']);
$signalkit_educational_post_url   = esc_url_raw($signalkit_banner['educational_url']);
$signalkit_show_educational_link  = !empty($signalkit_banner['show_educational']) && !empty($signalkit_educational_post_url);

// Validate URLs
if (!empty($signalkit_google_preferences_url) && !filter_var($signalkit_google_preferences_url, FILTER_VALIDATE_URL)) {
    $signalkit_google_preferences_url = '';
}
if (!empty($signalkit_educational_post_url) && !filter_var($signalkit_educational_post_url, FILTER_VALIDATE_URL)) {
    $signalkit_educational_post_url = '';
}

// Display Settings
$signalkit_position        = $signalkit_banner['position'] ?? 'bottom_right';
$signalkit_mobile_position = $signalkit_banner['mobile_position'] ?? 'bottom';
$signalkit_animation       = $signalkit_banner['animation'] ?? 'slide_in';
$signalkit_dismissible     = !empty($signalkit_banner['dismissible']);
$signalkit_stack_order     = intval($signalkit_banner['mobile_stack_order'] ?? 2);

// Size Settings
$signalkit_banner_width        = max(280, min(500, absint($signalkit_banner['banner_width'] ?? 360)));
$signalkit_banner_padding      = max(8, min(32, absint($signalkit_banner['banner_padding'] ?? 16)));
$signalkit_font_size_headline  = max(12, min(24, absint($signalkit_banner['font_size_headline'] ?? 15)));
$signalkit_font_size_desc      = max(10, min(18, absint($signalkit_banner['font_size_description'] ?? 13)));
$signalkit_font_size_button    = max(12, min(20, absint($signalkit_banner['font_size_button'] ?? 14)));
$signalkit_close_button_size   = max(20, min(40, absint($signalkit_banner['close_button_size'] ?? 28)));
$signalkit_border_radius       = max(0, min(32, absint($signalkit_banner['border_radius'] ?? 8)));

// Colors
$signalkit_primary_color   = sanitize_hex_color($signalkit_banner['primary_color'] ?? '#ea4335');
$signalkit_secondary_color = sanitize_hex_color($signalkit_banner['secondary_color'] ?? '#ffffff');
$signalkit_accent_color    = sanitize_hex_color($signalkit_banner['accent_color'] ?? '#fbbc04');
$signalkit_text_color      = sanitize_hex_color($signalkit_banner['text_color'] ?? '#202124');

// Hover handled by JS
$signalkit_primary_hover = $signalkit_primary_color;

// Approximate height
$signalkit_approx_height = $signalkit_banner_padding * 2 + $signalkit_font_size_headline + $signalkit_font_size_desc + 20;

// Device-specific classes
$signalkit_device_class = $signalkit_device === 'mobile' ? 'signalkit-device-mobile' : 'signalkit-device-desktop';
$signalkit_position_class = $signalkit_device === 'mobile'
    ? 'signalkit-position-mobile-' . sanitize_html_class($signalkit_mobile_position)
    : 'signalkit-position-' . sanitize_html_class($signalkit_position);
$signalkit_animation_class = 'signalkit-animation-' . sanitize_html_class($signalkit_animation);
$signalkit_stack_class = $signalkit_device === 'mobile' ? 'signalkit-stack-order-' . $signalkit_stack_order : '';

// Build width string separately
$signalkit_width_value = $signalkit_device === 'desktop' ? $signalkit_banner_width . 'px' : '100%';

// Inline CSS variables
$signalkit_inline_styles = sprintf(
    '--signalkit-primary: %s; --signalkit-primary-hover: %s; --signalkit-secondary: %s; ' .
    '--signalkit-accent: %s; --signalkit-text: %s; max-width: %s; padding: %dpx; ' .
    '--signalkit-headline-size: %dpx; --signalkit-description-size: %dpx; --signalkit-button-size: %dpx; ' .
    '--signalkit-radius: %dpx; --signalkit-banner-height: %dpx; --signalkit-close-size: %dpx;',
    esc_attr($signalkit_primary_color),
    esc_attr($signalkit_primary_hover),
    esc_attr($signalkit_secondary_color),
    esc_attr($signalkit_accent_color),
    esc_attr($signalkit_text_color),
    esc_attr($signalkit_width_value),
    $signalkit_banner_padding,
    $signalkit_font_size_headline,
    $signalkit_font_size_desc,
    $signalkit_font_size_button,
    $signalkit_border_radius,
    $signalkit_approx_height,
    $signalkit_close_button_size
);

// Apply filters
$signalkit_extra_classes = apply_filters('signalkit_preferred_banner_classes', [], $signalkit_banner);
$signalkit_extra_classes = array_map('sanitize_html_class', (array)$signalkit_extra_classes);

// *** BUG FIX: Replaced PHP 7.4+ array spread operator with PHP 7.2 compatible array_merge() ***
$signalkit_base_classes = [
    'signalkit-banner',
    'signalkit-banner-preferred',
    $signalkit_position_class,
    $signalkit_animation_class,
    $signalkit_device_class,
    $signalkit_stack_class,
];
$signalkit_all_classes = array_merge($signalkit_base_classes, $signalkit_extra_classes);
$signalkit_banner_classes = implode(' ', array_filter($signalkit_all_classes));
?>

<div id="<?php echo esc_attr($signalkit_banner_id); ?>"
     class="<?php echo esc_attr($signalkit_banner_classes); ?>"
     data-banner-type="preferred"
     data-banner-id="<?php echo esc_attr($signalkit_banner_id); ?>"
     style="<?php echo esc_attr($signalkit_inline_styles); ?>"
     role="alertdialog"
     aria-labelledby="<?php echo esc_attr($signalkit_banner_id); ?>-headline"
     aria-describedby="<?php echo esc_attr($signalkit_banner_id); ?>-description"
     aria-live="polite"
     aria-modal="false"
     lang="<?php echo esc_attr(get_bloginfo('language')); ?>">

    <div class="signalkit-banner-content">

        <div class="signalkit-icon" role="img" aria-label="<?php esc_attr_e('Preferred Source Star Icon', 'signalkit'); ?>">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <circle cx="24" cy="24" r="20" fill="<?php echo esc_attr($signalkit_primary_color); ?>"/>
                <path d="M24 10L27.09 19.26L37 19.27L28.96 25.14L32.04 34.41L24 28.52L15.96 34.41L19.04 25.14L11 19.27L20.91 19.26L24 10Z" fill="<?php echo esc_attr($signalkit_secondary_color); ?>"/>
            </svg>
        </div>

        <div class="signalkit-text">
            <?php if (!empty($signalkit_headline)): ?>
                <h3 id="<?php echo esc_attr($signalkit_banner_id); ?>-headline" class="signalkit-headline">
                    <?php echo esc_html($signalkit_headline); ?>
                </h3>
            <?php endif; ?>

            <?php if (!empty($signalkit_description)): ?>
                <p id="<?php echo esc_attr($signalkit_banner_id); ?>-description" class="signalkit-description">
                    <?php echo esc_html($signalkit_description); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="signalkit-actions">
            <?php if (!empty($signalkit_google_preferences_url)): ?>
                <a href="<?php echo esc_url($signalkit_google_preferences_url); ?>"
                   class="signalkit-button"
                   target="_blank"
                   rel="noopener noreferrer nofollow"
                   data-banner-type="preferred"
                   aria-label="<?php 
                       echo esc_attr(sprintf(
                           /* translators: %s: button text */
                           __('%s - Opens in new tab', 'signalkit'), 
                           $signalkit_button_text
                       )); 
                   ?>">
                    <span class="signalkit-button-text"><?php echo esc_html($signalkit_button_text); ?></span>
                    <svg class="signalkit-icon-star" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8 1L10.09 6.26L16 6.27L11.18 9.98L13.26 15.23L8 11.51L2.74 15.23L4.82 9.98L0 6.27L5.91 6.26L8 1Z"/>
                    </svg>
                </a>
            <?php else: ?>
                <span class="signalkit-button-placeholder">
                    <?php esc_html_e('Configure Google Preferences URL in settings', 'signalkit'); ?>
                </span>
            <?php endif; ?>

            <?php if ($signalkit_show_educational_link): ?>
                <a href="<?php echo esc_url($signalkit_educational_post_url); ?>"
                   class="signalkit-educational-link"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="<?php 
                       echo esc_attr(sprintf(
                           /* translators: %s: educational link text */
                           __('%s - Opens in new tab', 'signalkit'), 
                           $signalkit_educational_text
                       )); 
                   ?>">
                    <span class="signalkit-educational-text"><?php echo esc_html($signalkit_educational_text); ?></span>
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" aria-hidden="true">
                        <path d="M7 0C3.13 0 0 3.13 0 7s3.13 7 7 7 7-3.13 7-7-3.13-7-7-7zm1 10H6V6h2v4zm0-5H6V3h2v2z"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($signalkit_dismissible): ?>
            <button type="button"
                    class="signalkit-close"
                    aria-label="<?php esc_attr_e('Dismiss notification', 'signalkit'); ?>"
                    data-banner-type="preferred"
                    title="<?php esc_attr_e('Close', 'signalkit'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>

    </div>



</div>

<?php
do_action('signalkit_after_preferred_banner', $signalkit_banner, $signalkit_banner_id);
?>