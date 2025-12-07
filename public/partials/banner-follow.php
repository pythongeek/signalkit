<?php
/**
 * Google Follow Banner Template
 * Version: 1.0.0
 *
 * @package SignalKit
 */

if (!defined('ABSPATH')) {
    exit;
}

// Security: Verify context
if (!isset($banner) || !is_array($banner)) {
    signalkit_log('Follow Banner: Invalid context - $banner not passed');
    return;
}

// SECURITY FIX: No HTML allowed in user-controlled content
$allowed_html = array();

// Extract banner data with strict escaping
$site_name       = esc_html($banner['site_name'] ?? get_bloginfo('name'));
$banner_id       = 'signalkit-follow-banner-' . uniqid();
$device          = $banner['device'] ?? 'desktop';

// Content - SECURITY: No HTML tags allowed
$headline        = str_replace('[site_name]', $site_name, wp_kses($banner['headline'] ?? '', $allowed_html));
$description     = wp_kses($banner['description'] ?? '', $allowed_html);
$button_text     = esc_html($banner['button_text'] ?? ''); // Added ?? '' for safety
$google_news_url = esc_url_raw($banner['button_url'] ?? ''); // Added ?? '' for safety

// Validate URL
if (!empty($google_news_url) && !filter_var($google_news_url, FILTER_VALIDATE_URL)) {
    signalkit_log('Follow Banner: Invalid Google News URL', $google_news_url);
    $google_news_url = '';
}

// Display Settings
$position        = $banner['position'] ?? 'bottom_left';
$mobile_position = $banner['mobile_position'] ?? 'bottom';
$animation       = $banner['animation'] ?? 'slide_in';
$dismissible     = !empty($banner['dismissible']);
$stack_order     = intval($banner['mobile_stack_order'] ?? 1);

// Size Settings
$banner_width        = max(280, min(500, absint($banner['banner_width'] ?? 360)));
$banner_padding      = max(8, min(32, absint($banner['banner_padding'] ?? 16)));
$font_size_headline  = max(12, min(24, absint($banner['font_size_headline'] ?? 15)));
$font_size_desc      = max(10, min(18, absint($banner['font_size_description'] ?? 13)));
$font_size_button    = max(12, min(20, absint($banner['font_size_button'] ?? 14)));
$border_radius       = max(0, min(32, absint($banner['border_radius'] ?? 8)));

// Colors
$primary_color   = sanitize_hex_color($banner['primary_color'] ?? '#4285f4');
$secondary_color = sanitize_hex_color($banner['secondary_color'] ?? '#ffffff');
$accent_color    = sanitize_hex_color($banner['accent_color'] ?? '#34a853');
$text_color      = sanitize_hex_color($banner['text_color'] ?? '#202124');

// Hover handled by JS
$primary_hover = $primary_color;

// Approximate height
$approx_height = $banner_padding * 2 + $font_size_headline + $font_size_desc + 20;

// Device-specific classes
$device_class = $device === 'mobile' ? 'signalkit-device-mobile' : 'signalkit-device-desktop';
$position_class = $device === 'mobile'
    ? 'signalkit-position-mobile-' . sanitize_html_class($mobile_position)
    : 'signalkit-position-' . sanitize_html_class($position);
$animation_class = 'signalkit-animation-' . sanitize_html_class($animation);
$stack_class = $device === 'mobile' ? 'signalkit-stack-order-' . $stack_order : '';

// Build width string separately
$width_value = $device === 'desktop' ? $banner_width . 'px' : '100%';

// Inline CSS variables
$inline_styles = sprintf(
    '--signalkit-primary: %s; --signalkit-primary-hover: %s; --signalkit-secondary: %s; ' .
    '--signalkit-accent: %s; --signalkit-text: %s; max-width: %s; padding: %dpx; ' .
    '--signalkit-headline-size: %dpx; --signalkit-description-size: %dpx; --signalkit-button-size: %dpx; ' .
    '--signalkit-radius: %dpx; --signalkit-banner-height: %dpx;',
    esc_attr($primary_color),
    esc_attr($primary_hover),
    esc_attr($secondary_color),
    esc_attr($accent_color),
    esc_attr($text_color),
    esc_attr($width_value),
    $banner_padding,
    $font_size_headline,
    $font_size_desc,
    $font_size_button,
    $border_radius,
    $approx_height
);

// Apply filters
$extra_classes = apply_filters('signalkit_follow_banner_classes', [], $banner);
$extra_classes = array_map('sanitize_html_class', (array)$extra_classes);

// *** BUG FIX: Replaced PHP 7.4+ array spread operator (...) with PHP 7.2 compatible array_merge() ***
$base_classes = [
    'signalkit-banner',
    'signalkit-banner-follow',
    $position_class,
    $animation_class,
    $device_class,
    $stack_class,
];
$all_classes = array_merge($base_classes, $extra_classes);
$banner_classes = implode(' ', array_filter($all_classes));
?>

<div id="<?php echo esc_attr($banner_id); ?>"
     class="<?php echo esc_attr($banner_classes); ?>"
     data-banner-type="follow"
     data-banner-id="<?php echo esc_attr($banner_id); ?>"
     style="<?php echo esc_attr($inline_styles); ?>"
     role="alertdialog"
     aria-labelledby="<?php echo esc_attr($banner_id); ?>-headline"
     aria-describedby="<?php echo esc_attr($banner_id); ?>-description"
     aria-live="polite"
     aria-modal="false"
     lang="<?php echo esc_attr(get_bloginfo('language')); ?>">

    <div class="signalkit-banner-content">

        <div class="signalkit-icon" role="img" aria-label="<?php esc_attr_e('Google News Icon', 'signalkit'); ?>">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <circle cx="24" cy="24" r="20" fill="<?php echo esc_attr($primary_color); ?>"/>
                <path d="M24 13C18.48 13 14 17.48 14 23C14 28.52 18.48 33 24 33C29.52 33 34 28.52 34 23C34 17.48 29.52 13 24 13ZM27 28H21V26H27V28ZM27 24H21V18H27V24Z" fill="<?php echo esc_attr($secondary_color); ?>"/>
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
            <?php if (!empty($google_news_url)): ?>
                <a href="<?php echo esc_url($google_news_url); ?>"
                   class="signalkit-button"
                   target="_blank"
                   rel="noopener noreferrer nofollow"
                   data-banner-type="follow"
                   aria-label="<?php echo esc_attr(sprintf(__('%s - Opens in new tab', 'signalkit'), $button_text)); ?>">
                    <span class="signalkit-button-text"><?php echo esc_html($button_text); ?></span>
                    <svg class="signalkit-icon-arrow" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8 0L6.59 1.41L12.17 7H0V9H12.17L6.59 14.59L8 16L16 8L8 0Z"/>
                    </svg>
                </a>
            <?php else: ?>
                <span class="signalkit-button-placeholder">
                    <?php esc_html_e('Configure Google News URL in settings', 'signalkit'); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($dismissible): ?>
            <button type="button"
                    class="signalkit-close"
                    aria-label="<?php esc_attr_e('Dismiss notification', 'signalkit'); ?>"
                    data-banner-type="follow"
                    title="<?php esc_attr_e('Close', 'signalkit'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>

    </div>

    <?php if (!empty($google_news_url)): ?>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FollowAction",
            "@id": "FollowOnGoogleNews",
            "name": <?php echo wp_json_encode($headline); ?>,
            "description": <?php echo wp_json_encode($description); ?>,
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": <?php echo wp_json_encode($google_news_url); ?>,
                "actionPlatform": ["http://schema.org/DesktopWebPlatform", "http://schema.org/MobileWebPlatform"]
            },
            "object": {
                "@type": "NewsMediaOrganization",
                "name": <?php echo wp_json_encode($site_name); ?>,
                "url": <?php echo wp_json_encode(home_url()); ?>,
                "logo": {
                    "@type": "ImageObject",
                    "url": <?php echo wp_json_encode(get_site_icon_url()); ?>
                }
            }
        }
        </script>
    <?php endif; ?>

</div>

<?php
do_action('signalkit_after_follow_banner', $banner, $banner_id);
?>