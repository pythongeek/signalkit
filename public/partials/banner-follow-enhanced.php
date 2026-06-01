<?php
/**
 * Enhanced Google Follow Banner Template v2.0
 * Modern, Versatile Design with Advanced Visibility
 *
 * @package SignalKit
 * @version 2.0.0
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
$signalkit_site_name       = esc_html($signalkit_banner['site_name'] ?? get_bloginfo('name'));
$signalkit_banner_id       = 'signalkit-follow-banner-' . uniqid();
$signalkit_device          = $signalkit_banner['device'] ?? 'desktop';

// Content - SECURITY: No HTML tags allowed
$signalkit_headline        = str_replace('[site_name]', $signalkit_site_name, wp_kses($signalkit_banner['headline'] ?? '', $signalkit_allowed_html));
$signalkit_description     = wp_kses($signalkit_banner['description'] ?? '', $signalkit_allowed_html);
$signalkit_button_text     = esc_html($signalkit_banner['button_text'] ?? '');
$signalkit_google_news_url = esc_url_raw($signalkit_banner['button_url'] ?? '');

// Validate URL
if (!empty($signalkit_google_news_url) && !filter_var($signalkit_google_news_url, FILTER_VALIDATE_URL)) {
    $signalkit_google_news_url = '';
}

// Display Settings
$signalkit_position        = $signalkit_banner['position'] ?? 'bottom_left';
$signalkit_mobile_position = $signalkit_banner['mobile_position'] ?? 'bottom';
$signalkit_animation       = $signalkit_banner['animation'] ?? 'slide_in';
$signalkit_dismissible     = !empty($signalkit_banner['dismissible']);
$signalkit_stack_order     = intval($signalkit_banner['mobile_stack_order'] ?? 1);

// NEW: Enhanced Style Settings
$signalkit_banner_style    = sanitize_html_class($signalkit_banner['banner_style'] ?? 'modern-card');
$signalkit_button_style    = sanitize_html_class($signalkit_banner['button_style'] ?? 'default');
$signalkit_icon_style      = sanitize_html_class($signalkit_banner['icon_style'] ?? 'circle');
$signalkit_enable_glow     = !empty($signalkit_banner['enable_glow']);
$signalkit_enable_float    = !empty($signalkit_banner['enable_float']);
$signalkit_visibility_mode = sanitize_html_class($signalkit_banner['visibility_mode'] ?? 'auto');

// Size Settings - Enhanced
$signalkit_banner_width        = max(320, min(500, absint($signalkit_banner['banner_width'] ?? 380)));
$signalkit_banner_padding      = max(12, min(32, absint($signalkit_banner['banner_padding'] ?? 20)));
$signalkit_font_size_headline  = max(14, min(28, absint($signalkit_banner['font_size_headline'] ?? 16)));
$signalkit_font_size_desc      = max(12, min(20, absint($signalkit_banner['font_size_description'] ?? 14)));
$signalkit_font_size_button    = max(12, min(20, absint($signalkit_banner['font_size_button'] ?? 14)));
$signalkit_border_radius       = max(0, min(40, absint($signalkit_banner['border_radius'] ?? 16)));
$signalkit_icon_size           = max(40, min(72, absint($signalkit_banner['icon_size'] ?? 52)));

// Colors
$signalkit_primary_color   = sanitize_hex_color($signalkit_banner['primary_color'] ?? '#4285f4');
$signalkit_secondary_color = sanitize_hex_color($signalkit_banner['secondary_color'] ?? '#ffffff');
$signalkit_accent_color    = sanitize_hex_color($signalkit_banner['accent_color'] ?? '#34a853');
$signalkit_text_color      = sanitize_hex_color($signalkit_banner['text_color'] ?? '#202124');

// NEW: Advanced Color Settings
$signalkit_gradient_start  = sanitize_hex_color($signalkit_banner['gradient_start'] ?? $signalkit_primary_color);
$signalkit_gradient_end    = sanitize_hex_color($signalkit_banner['gradient_end'] ?? $signalkit_accent_color);
$signalkit_gradient_angle  = max(0, min(360, absint($signalkit_banner['gradient_angle'] ?? 135)));
$signalkit_border_color    = sanitize_hex_color($signalkit_banner['border_color'] ?? '');
$signalkit_glow_intensity  = max(0, min(50, absint($signalkit_banner['glow_intensity'] ?? 20)));
$signalkit_backdrop_blur   = max(0, min(30, absint($signalkit_banner['backdrop_blur'] ?? 12)));
$signalkit_backdrop_opacity = max(50, min(100, absint($signalkit_banner['backdrop_opacity'] ?? 95)));

// Convert hex to RGB for CSS variables
// Function now loaded from includes/signalkit-helpers.php
$signalkit_primary_rgb   = signalkit_hex_to_rgb($signalkit_primary_color);
$signalkit_secondary_rgb = signalkit_hex_to_rgb($signalkit_secondary_color);
$signalkit_accent_rgb    = signalkit_hex_to_rgb($signalkit_accent_color);
$signalkit_text_rgb      = signalkit_hex_to_rgb($signalkit_text_color);

// Device-specific classes
$signalkit_device_class = $signalkit_device === 'mobile' ? 'signalkit-device-mobile' : 'signalkit-device-desktop';
$signalkit_position_class = $signalkit_device === 'mobile'
    ? 'signalkit-position-mobile-' . sanitize_html_class($signalkit_mobile_position)
    : 'signalkit-position-' . sanitize_html_class($signalkit_position);
$signalkit_animation_class = 'signalkit-animation-' . sanitize_html_class($signalkit_animation);
$signalkit_stack_class = $signalkit_device === 'mobile' ? 'signalkit-stack-order-' . $signalkit_stack_order : '';
$signalkit_style_class = 'signalkit-style-' . $signalkit_banner_style;
$signalkit_button_style_class = 'signalkit-button-style-' . $signalkit_button_style;
$signalkit_icon_style_class = 'signalkit-icon-style-' . $signalkit_icon_style;
$signalkit_visibility_class = $signalkit_visibility_mode !== 'auto' ? 'signalkit-contrast-' . $signalkit_visibility_mode : '';

// Width value
$signalkit_width_value = $signalkit_device === 'desktop' ? $signalkit_banner_width . 'px' : '100%';

// Build CSS custom properties
$signalkit_css_vars = array(
    '--signalkit-primary: ' . $signalkit_primary_color,
    '--signalkit-primary-rgb: ' . $signalkit_primary_rgb,
    '--signalkit-secondary: ' . $signalkit_secondary_color,
    '--signalkit-secondary-rgb: ' . $signalkit_secondary_rgb,
    '--signalkit-accent: ' . $signalkit_accent_color,
    '--signalkit-accent-rgb: ' . $signalkit_accent_rgb,
    '--signalkit-text: ' . $signalkit_text_color,
    '--signalkit-text-rgb: ' . $signalkit_text_rgb,
    '--signalkit-gradient-start: ' . $signalkit_gradient_start,
    '--signalkit-gradient-end: ' . $signalkit_gradient_end,
    '--signalkit-gradient-angle: ' . $signalkit_gradient_angle . 'deg',
    '--signalkit-width: ' . $signalkit_width_value,
    '--signalkit-padding: ' . $signalkit_banner_padding . 'px',
    '--signalkit-headline-size: ' . $signalkit_font_size_headline . 'px',
    '--signalkit-description-size: ' . $signalkit_font_size_desc . 'px',
    '--signalkit-button-size: ' . $signalkit_font_size_button . 'px',
    '--signalkit-radius: ' . $signalkit_border_radius . 'px',
    '--signalkit-icon-size: ' . $signalkit_icon_size . 'px',
    '--signalkit-glow-intensity: ' . ($signalkit_enable_glow ? $signalkit_glow_intensity . 'px' : '0px'),
    '--signalkit-backdrop-blur: ' . $signalkit_backdrop_blur . 'px',
    '--signalkit-backdrop-opacity: ' . ($signalkit_backdrop_opacity / 100),
);

if (!empty($signalkit_border_color)) {
    $signalkit_css_vars[] = '--signalkit-border-color: ' . $signalkit_border_color;
}

$signalkit_inline_styles = esc_attr(implode('; ', $signalkit_css_vars));

// Apply filters
$signalkit_extra_classes = apply_filters('signalkit_follow_banner_classes', [], $signalkit_banner);
$signalkit_extra_classes = array_map('sanitize_html_class', (array)$signalkit_extra_classes);

// Build class list
$signalkit_base_classes = array(
    'signalkit-banner',
    'signalkit-banner-follow',
    $signalkit_position_class,
    $signalkit_animation_class,
    $signalkit_device_class,
    $signalkit_stack_class,
    $signalkit_style_class,
    $signalkit_visibility_class,
);

// Add optional effect classes
if ($signalkit_enable_glow) {
    $signalkit_base_classes[] = 'signalkit-pulse-glow';
}
if ($signalkit_enable_float) {
    $signalkit_base_classes[] = 'signalkit-float-enabled';
}

$signalkit_all_classes = array_merge($signalkit_base_classes, $signalkit_extra_classes);
$signalkit_banner_classes = implode(' ', array_filter($signalkit_all_classes));
?>

<div id="<?php echo esc_attr($signalkit_banner_id); ?>"
     class="<?php echo esc_attr($signalkit_banner_classes); ?>"
     data-banner-type="follow"
     data-banner-id="<?php echo esc_attr($signalkit_banner_id); ?>"
     data-banner-style="<?php echo esc_attr($signalkit_banner_style); ?>"
     style="<?php echo esc_attr($signalkit_inline_styles); ?>"
     role="alertdialog"
     aria-labelledby="<?php echo esc_attr($signalkit_banner_id); ?>-headline"
     aria-describedby="<?php echo esc_attr($signalkit_banner_id); ?>-description"
     aria-live="polite"
     aria-modal="false"
     lang="<?php echo esc_attr(get_bloginfo('language')); ?>">

    <div class="signalkit-banner-content">

        <div class="signalkit-icon <?php echo esc_attr($signalkit_icon_style_class); ?>" role="img" aria-label="<?php esc_attr_e('Google News Icon', 'signalkit'); ?>">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <!-- Google News styled icon with brand colors -->
                <path d="M21 7V17C21 20 19.5 21 17 21H7C4.5 21 3 20 3 17V7C3 4 4.5 3 7 3H17C19.5 3 21 4 21 7Z" stroke="<?php echo esc_attr($signalkit_primary_color); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 3V7C16 9.5 15 10 12 10H8" stroke="<?php echo esc_attr($signalkit_accent_color); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 13H12" stroke="#EA4335" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 17H15" stroke="#FABB05" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
            <?php if (!empty($signalkit_google_news_url)): ?>
                <a href="<?php echo esc_url($signalkit_google_news_url); ?>"
                   class="signalkit-button <?php echo esc_attr($signalkit_button_style_class); ?>"
                   target="_blank"
                   rel="noopener noreferrer nofollow"
                   data-banner-type="follow"
                   aria-label="<?php 
                       echo esc_attr(sprintf(
                           /* translators: %s: button text */
                           __('%s - Opens in new tab', 'signalkit'), 
                           $signalkit_button_text
                       )); 
                   ?>">
                    <span class="signalkit-button-text"><?php echo esc_html($signalkit_button_text); ?></span>
                    <svg class="signalkit-icon-arrow" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8.5 2.5L13 7H1v2h12l-4.5 4.5L10 15l6-6-6-6-1.5 1.5z"/>
                    </svg>
                </a>
            <?php else: ?>
                <span class="signalkit-button-placeholder">
                    <?php esc_html_e('Configure Google News URL in settings', 'signalkit'); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($signalkit_dismissible): ?>
            <button type="button"
                    class="signalkit-close"
                    aria-label="<?php esc_attr_e('Dismiss notification', 'signalkit'); ?>"
                    data-banner-type="follow"
                    title="<?php esc_attr_e('Close', 'signalkit'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>

    </div>



</div>

<?php
do_action('signalkit_after_follow_banner', $signalkit_banner, $signalkit_banner_id);
?>

