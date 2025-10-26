<?php
/**
 * Google Preferred Source Banner Template
 * Version: 2.2.0 - FIXED: Colors, Responsive Layout, SVG
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

// Security: Verify context
if (!isset($banner) || !is_array($banner)) {
    signalkit_log('Preferred Banner: Invalid context - $banner not passed');
    return;
}

// Extract banner data
$site_name       = esc_html($banner['site_name'] ?? get_bloginfo('name'));
$banner_id       = 'signalkit-preferred-banner-' . uniqid();
$device          = $banner['device'] ?? 'desktop';

// Content
$headline        = str_replace('[site_name]', $site_name, esc_html($banner['headline']));
$description     = esc_html($banner['description']);
$button_text     = esc_html($banner['button_text']);
$educational_text = esc_html($banner['educational_text']);
$google_preferences_url = esc_url_raw($banner['button_url']);
$educational_post_url   = esc_url_raw($banner['educational_url']);
$show_educational_link  = !empty($banner['show_educational']) && !empty($educational_post_url);

// Validate URLs
if (!empty($google_preferences_url) && !filter_var($google_preferences_url, FILTER_VALIDATE_URL)) {
    signalkit_log('Preferred Banner: Invalid Google Preferences URL', $google_preferences_url);
    $google_preferences_url = '';
}
if (!empty($educational_post_url) && !filter_var($educational_post_url, FILTER_VALIDATE_URL)) {
    signalkit_log('Preferred Banner: Invalid Educational Post URL', $educational_post_url);
    $educational_post_url = '';
}

// Display Settings
$position        = $banner['position'] ?? 'bottom_right';
$mobile_position = $banner['mobile_position'] ?? 'bottom';
$animation       = $banner['animation'] ?? 'slide_in';
$dismissible     = !empty($banner['dismissible']);
$stack_order     = intval($banner['mobile_stack_order'] ?? 2);

// Size Settings
$banner_width        = max(280, min(500, absint($banner['banner_width'] ?? 360)));
$banner_padding      = max(8, min(32, absint($banner['banner_padding'] ?? 16)));
$font_size_headline  = max(12, min(24, absint($banner['font_size_headline'] ?? 15)));
$font_size_desc      = max(10, min(18, absint($banner['font_size_description'] ?? 13)));
$font_size_button    = max(12, min(20, absint($banner['font_size_button'] ?? 14)));
$border_radius       = max(0, min(32, absint($banner['border_radius'] ?? 8)));

// Colors
$primary_color   = sanitize_hex_color($banner['primary_color'] ?? '#ea4335');
$secondary_color = sanitize_hex_color($banner['secondary_color'] ?? '#ffffff');
$accent_color    = sanitize_hex_color($banner['accent_color'] ?? '#fbbc04');
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

// FIXED: Build width string separately
$width_value = $device === 'desktop' ? $banner_width . 'px' : '100%';

// Inline CSS variables - FIXED
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
$extra_classes = apply_filters('signalkit_preferred_banner_classes', [], $banner);
$extra_classes = array_map('sanitize_html_class', (array)$extra_classes);

$banner_classes = implode(' ', array_filter([
    'signalkit-banner',
    'signalkit-banner-preferred',
    $position_class,
    $animation_class,
    $device_class,
    $stack_class,
    ...$extra_classes
]));
?>

<!-- Preferred Source Banner -->
<div id="<?php echo esc_attr($banner_id); ?>"
     class="<?php echo esc_attr($banner_classes); ?>"
     data-banner-type="preferred"
     data-banner-id="<?php echo esc_attr($banner_id); ?>"
     style="<?php echo esc_attr($inline_styles); ?>"
     role="alertdialog"
     aria-labelledby="<?php echo esc_attr($banner_id); ?>-headline"
     aria-describedby="<?php echo esc_attr($banner_id); ?>-description"
     aria-live="polite"
     aria-modal="false"
     lang="<?php echo esc_attr(get_bloginfo('language')); ?>">

    <div class="signalkit-banner-content">

        <!-- Star Icon - FIXED: Properly closed SVG -->
        <div class="signalkit-icon" role="img" aria-label="<?php esc_attr_e('Preferred Source Star Icon', 'signalkit-for-google'); ?>">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <circle cx="24" cy="24" r="20" fill="<?php echo esc_attr($primary_color); ?>"/>
                <path d="M24 10L27.09 19.26L37 19.27L28.96 25.14L32.04 34.41L24 28.52L15.96 34.41L19.04 25.14L11 19.27L20.91 19.26L24 10Z" fill="<?php echo esc_attr($secondary_color); ?>"/>
            </svg>
        </div>

        <!-- Text Content - FIXED: Use CSS variables -->
        <div class="signalkit-text">
            <?php if (!empty($headline)): ?>
                <h3 id="<?php echo esc_attr($banner_id); ?>-headline" class="signalkit-headline">
                    <?php echo wp_kses_post($headline); ?>
                </h3>
            <?php endif; ?>

            <?php if (!empty($description)): ?>
                <p id="<?php echo esc_attr($banner_id); ?>-description" class="signalkit-description">
                    <?php echo wp_kses_post($description); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="signalkit-actions">
            <?php if (!empty($google_preferences_url)): ?>
                <a href="<?php echo esc_url($google_preferences_url); ?>"
                   class="signalkit-button"
                   target="_blank"
                   rel="noopener noreferrer nofollow"
                   data-banner-type="preferred"
                   aria-label="<?php echo esc_attr(sprintf(__('%s - Opens in new tab', 'signalkit-for-google'), $button_text)); ?>">
                    <span class="signalkit-button-text"><?php echo esc_html($button_text); ?></span>
                    <svg class="signalkit-icon-star" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8 1L10.09 6.26L16 6.27L11.18 9.98L13.26 15.23L8 11.51L2.74 15.23L4.82 9.98L0 6.27L5.91 6.26L8 1Z"/>
                    </svg>
                </a>
            <?php else: ?>
                <span class="signalkit-button-placeholder">
                    <?php esc_html_e('Configure Google Preferences URL in settings', 'signalkit-for-google'); ?>
                </span>
            <?php endif; ?>

            <?php if ($show_educational_link): ?>
                <a href="<?php echo esc_url($educational_post_url); ?>"
                   class="signalkit-educational-link"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="<?php echo esc_attr(sprintf(__('%s - Opens in new tab', 'signalkit-for-google'), $educational_text)); ?>">
                    <span class="signalkit-educational-text"><?php echo esc_html($educational_text); ?></span>
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" aria-hidden="true">
                        <path d="M7 0C3.13 0 0 3.13 0 7s3.13 7 7 7 7-3.13 7-7-3.13-7-7-7zm1 10H6V6h2v4zm0-5H6V3h2v2z"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <!-- Close Button -->
        <?php if ($dismissible): ?>
            <button type="button"
                    class="signalkit-close"
                    aria-label="<?php esc_attr_e('Dismiss notification', 'signalkit-for-google'); ?>"
                    data-banner-type="preferred"
                    title="<?php esc_attr_e('Close', 'signalkit-for-google'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>

    </div>

    <!-- Schema.org -->
    <?php if (!empty($google_preferences_url)): ?>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Action",
            "@id": "AddPreferredSource",
            "name": <?php echo wp_json_encode($headline); ?>,
            "description": <?php echo wp_json_encode($description); ?>,
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": <?php echo wp_json_encode($google_preferences_url); ?>,
                "actionPlatform": ["http://schema.org/DesktopWebPlatform", "http://schema.org/MobileWebPlatform"]
            },
            "agent": {
                "@type": "NewsMediaOrganization",
                "name": <?php echo wp_json_encode($site_name); ?>,
                "url": <?php echo wp_json_encode(home_url()); ?>,
                "logo": {
                    "@type": "ImageObject",
                    "url": <?php echo wp_json_encode(get_site_icon_url()); ?>
                }
            }
            <?php if ($show_educational_link): ?>,
            "potentialAction": {
                "@type": "LearnAction",
                "name": <?php echo wp_json_encode($educational_text); ?>,
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": <?php echo wp_json_encode($educational_post_url); ?>
                }
            }
            <?php endif; ?>
        }
        </script>
    <?php endif; ?>

</div>

<?php
do_action('signalkit_after_preferred_banner', $banner, $banner_id);
?>