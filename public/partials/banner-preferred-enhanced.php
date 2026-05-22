<?php
/**
 * Enhanced Google Preferred Source Banner Template v2.0
 * Modern, Versatile Design with Advanced Visibility
 *
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template partial receives variables from including context

// Template partial variables - intentionally unprefixed as these are passed from including context
if (!isset($banner) || !is_array($banner)) {
    return;
}

// SECURITY FIX: No HTML allowed in user-controlled content
$allowed_html = array();

// Extract banner data with strict escaping
$site_name        = esc_html($banner['site_name'] ?? get_bloginfo('name'));
$banner_id        = 'signalkit-preferred-banner-' . uniqid();
$device           = $banner['device'] ?? 'desktop';

// Content - SECURITY: No HTML tags allowed
$headline         = str_replace('[site_name]', $site_name, wp_kses($banner['headline'] ?? '', $allowed_html));
$description      = wp_kses($banner['description'] ?? '', $allowed_html);
$button_text      = esc_html($banner['button_text'] ?? '');
$educational_text = esc_html($banner['educational_text'] ?? '');
$google_preferences_url = esc_url_raw($banner['button_url'] ?? '');
$educational_post_url   = esc_url_raw($banner['educational_url'] ?? '');
$show_educational_link  = !empty($banner['show_educational']) && !empty($educational_post_url);

// Validate URLs
if (!empty($google_preferences_url) && !filter_var($google_preferences_url, FILTER_VALIDATE_URL)) {
    $google_preferences_url = '';
}
if (!empty($educational_post_url) && !filter_var($educational_post_url, FILTER_VALIDATE_URL)) {
    $educational_post_url = '';
}

// Display Settings
$position        = $banner['position'] ?? 'bottom_right';
$mobile_position = $banner['mobile_position'] ?? 'bottom';
$animation       = $banner['animation'] ?? 'slide_in';
$dismissible     = !empty($banner['dismissible']);
$stack_order     = intval($banner['mobile_stack_order'] ?? 2);

// NEW: Enhanced Style Settings
$banner_style    = sanitize_html_class($banner['banner_style'] ?? 'modern-card');
$button_style    = sanitize_html_class($banner['button_style'] ?? 'default');
$icon_style      = sanitize_html_class($banner['icon_style'] ?? 'circle');
$enable_glow     = !empty($banner['enable_glow']);
$enable_float    = !empty($banner['enable_float']);
$visibility_mode = sanitize_html_class($banner['visibility_mode'] ?? 'auto');

// Size Settings - Enhanced
$banner_width        = max(320, min(500, absint($banner['banner_width'] ?? 380)));
$banner_padding      = max(12, min(32, absint($banner['banner_padding'] ?? 20)));
$font_size_headline  = max(14, min(28, absint($banner['font_size_headline'] ?? 16)));
$font_size_desc      = max(12, min(20, absint($banner['font_size_description'] ?? 14)));
$font_size_button    = max(12, min(20, absint($banner['font_size_button'] ?? 14)));
$border_radius       = max(0, min(40, absint($banner['border_radius'] ?? 16)));
$icon_size           = max(40, min(72, absint($banner['icon_size'] ?? 52)));

// Colors
$primary_color   = sanitize_hex_color($banner['primary_color'] ?? '#ea4335');
$secondary_color = sanitize_hex_color($banner['secondary_color'] ?? '#ffffff');
$accent_color    = sanitize_hex_color($banner['accent_color'] ?? '#fbbc04');
$text_color      = sanitize_hex_color($banner['text_color'] ?? '#202124');

// NEW: Advanced Color Settings
$gradient_start  = sanitize_hex_color($banner['gradient_start'] ?? $primary_color);
$gradient_end    = sanitize_hex_color($banner['gradient_end'] ?? $accent_color);
$gradient_angle  = max(0, min(360, absint($banner['gradient_angle'] ?? 135)));
$border_color    = sanitize_hex_color($banner['border_color'] ?? '');
$glow_intensity  = max(0, min(50, absint($banner['glow_intensity'] ?? 20)));
$backdrop_blur   = max(0, min(30, absint($banner['backdrop_blur'] ?? 12)));
$backdrop_opacity = max(50, min(100, absint($banner['backdrop_opacity'] ?? 95)));

// Helper function - Convert hex to RGB
// Function now loaded from includes/signalkit-helpers.php
$primary_rgb   = signalkit_hex_to_rgb($primary_color);
$secondary_rgb = signalkit_hex_to_rgb($secondary_color);
$accent_rgb    = signalkit_hex_to_rgb($accent_color);
$text_rgb      = signalkit_hex_to_rgb($text_color);

// Device-specific classes
$device_class = $device === 'mobile' ? 'signalkit-device-mobile' : 'signalkit-device-desktop';
$position_class = $device === 'mobile'
    ? 'signalkit-position-mobile-' . sanitize_html_class($mobile_position)
    : 'signalkit-position-' . sanitize_html_class($position);
$animation_class = 'signalkit-animation-' . sanitize_html_class($animation);
$stack_class = $device === 'mobile' ? 'signalkit-stack-order-' . $stack_order : '';
$style_class = 'signalkit-style-' . $banner_style;
$button_style_class = 'signalkit-button-style-' . $button_style;
$icon_style_class = 'signalkit-icon-style-' . $icon_style;
$visibility_class = $visibility_mode !== 'auto' ? 'signalkit-contrast-' . $visibility_mode : '';

// Width value
$width_value = $device === 'desktop' ? $banner_width . 'px' : '100%';

// Build CSS custom properties
$css_vars = array(
    '--signalkit-primary: ' . $primary_color,
    '--signalkit-primary-rgb: ' . $primary_rgb,
    '--signalkit-secondary: ' . $secondary_color,
    '--signalkit-secondary-rgb: ' . $secondary_rgb,
    '--signalkit-accent: ' . $accent_color,
    '--signalkit-accent-rgb: ' . $accent_rgb,
    '--signalkit-text: ' . $text_color,
    '--signalkit-text-rgb: ' . $text_rgb,
    '--signalkit-gradient-start: ' . $gradient_start,
    '--signalkit-gradient-end: ' . $gradient_end,
    '--signalkit-gradient-angle: ' . $gradient_angle . 'deg',
    '--signalkit-width: ' . $width_value,
    '--signalkit-padding: ' . $banner_padding . 'px',
    '--signalkit-headline-size: ' . $font_size_headline . 'px',
    '--signalkit-description-size: ' . $font_size_desc . 'px',
    '--signalkit-button-size: ' . $font_size_button . 'px',
    '--signalkit-radius: ' . $border_radius . 'px',
    '--signalkit-icon-size: ' . $icon_size . 'px',
    '--signalkit-glow-intensity: ' . ($enable_glow ? $glow_intensity . 'px' : '0px'),
    '--signalkit-backdrop-blur: ' . $backdrop_blur . 'px',
    '--signalkit-backdrop-opacity: ' . ($backdrop_opacity / 100),
);

if (!empty($border_color)) {
    $css_vars[] = '--signalkit-border-color: ' . $border_color;
}

$inline_styles = esc_attr(implode('; ', $css_vars));

// Apply filters
$extra_classes = apply_filters('signalkit_preferred_banner_classes', [], $banner);
$extra_classes = array_map('sanitize_html_class', (array)$extra_classes);

// Build class list
$base_classes = array(
    'signalkit-banner',
    'signalkit-banner-preferred',
    $position_class,
    $animation_class,
    $device_class,
    $stack_class,
    $style_class,
    $visibility_class,
);

// Add optional effect classes
if ($enable_glow) {
    $base_classes[] = 'signalkit-pulse-glow';
}
if ($enable_float) {
    $base_classes[] = 'signalkit-float-enabled';
}

$all_classes = array_merge($base_classes, $extra_classes);
$banner_classes = implode(' ', array_filter($all_classes));
?>

<div id="<?php echo esc_attr($banner_id); ?>"
     class="<?php echo esc_attr($banner_classes); ?>"
     data-banner-type="preferred"
     data-banner-id="<?php echo esc_attr($banner_id); ?>"
     data-banner-style="<?php echo esc_attr($banner_style); ?>"
     style="<?php echo esc_attr($inline_styles); ?>"
     role="alertdialog"
     aria-labelledby="<?php echo esc_attr($banner_id); ?>-headline"
     aria-describedby="<?php echo esc_attr($banner_id); ?>-description"
     aria-live="polite"
     aria-modal="false"
     lang="<?php echo esc_attr(get_bloginfo('language')); ?>">

    <div class="signalkit-banner-content">

        <div class="signalkit-icon <?php echo esc_attr($icon_style_class); ?>" role="img" aria-label="<?php esc_attr_e('Preferred Source Star Icon', 'signalkit'); ?>">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <!-- Modern Star/Preferred Icon -->
                <path d="M12 2L14.09 8.26L20.73 8.27L15.46 12.14L17.54 18.41L12 14.52L6.46 18.41L8.54 12.14L3.27 8.27L9.91 8.26L12 2Z" fill="currentColor"/>
                <!-- Sparkle effects -->
                <circle cx="19" cy="5" r="2" fill="<?php echo esc_attr($accent_color); ?>"/>
                <circle cx="5" cy="5" r="1.5" fill="<?php echo esc_attr($accent_color); ?>" opacity="0.7"/>
            </svg>
        </div>

        <div class="signalkit-text">
            <?php if (!empty($headline)): ?>
                <h3 id="<?php echo esc_attr($banner_id); ?>-headline" class="signalkit-headline">
                    <?php echo esc_html($headline); ?>
                </h3>
            <?php endif; ?>

            <?php if (!empty($description)): ?>
                <p id="<?php echo esc_attr($banner_id); ?>-description" class="signalkit-description">
                    <?php echo esc_html($description); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="signalkit-actions">
            <?php if (!empty($google_preferences_url)): ?>
                <?php /* translators: %s: button text */ ?>
                <a href="<?php echo esc_url($google_preferences_url); ?>"
                   class="signalkit-button <?php echo esc_attr($button_style_class); ?>"
                   target="_blank"
                   rel="noopener noreferrer nofollow"
                   data-banner-type="preferred"
                   <?php /* translators: %s: Button text */ ?>
                   aria-label="<?php echo esc_attr(sprintf(__('%s - Opens in new tab', 'signalkit'), $button_text)); ?>">
                    <span class="signalkit-button-text"><?php echo esc_html($button_text); ?></span>
                    <svg class="signalkit-icon-star" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8 1L9.79 5.69L14.76 5.69L10.74 8.69L12.24 13.38L8 10.38L3.76 13.38L5.26 8.69L1.24 5.69L6.21 5.69L8 1Z"/>
                    </svg>
                </a>
            <?php else: ?>
                <span class="signalkit-button-placeholder">
                    <?php esc_html_e('Configure Google Preferences URL in settings', 'signalkit'); ?>
                </span>
            <?php endif; ?>

            <?php if ($show_educational_link): ?>
                <?php /* translators: %s: Educational link text */ ?>
                <a href="<?php echo esc_url($educational_post_url); ?>"
                   class="signalkit-educational-link"
                   target="_blank"
                   rel="noopener noreferrer"
                   <?php /* translators: %s: Educational link text */ ?>
                   aria-label="<?php echo esc_attr(sprintf(__('%s - Opens in new tab', 'signalkit'), $educational_text)); ?>">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" aria-hidden="true">
                        <path d="M7 0C3.13 0 0 3.13 0 7s3.13 7 7 7 7-3.13 7-7-3.13-7-7-7zm1 10H6V6h2v4zm0-5H6V3h2v2z"/>
                    </svg>
                    <span class="signalkit-educational-text"><?php echo esc_html($educational_text); ?></span>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($dismissible): ?>
            <button type="button"
                    class="signalkit-close"
                    aria-label="<?php esc_attr_e('Dismiss notification', 'signalkit'); ?>"
                    data-banner-type="preferred"
                    title="<?php esc_attr_e('Close', 'signalkit'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>

    </div>



</div>

<?php
do_action('signalkit_after_preferred_banner', $banner, $banner_id);
?>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals ?>
