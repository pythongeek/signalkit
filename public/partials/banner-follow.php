<?php
/**
 * Google News Follow Banner Template
 * Version: 2.1.1 - LIVE PREVIEW + MOBILE STACKING FIXED
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

// Security: Verify context
if (!isset($banner) || !is_array($banner)) {
    signalkit_log('Follow Banner: Invalid context - $banner not passed');
    return;
}

// Extract banner data
$site_name       = esc_html($banner['site_name'] ?? get_bloginfo('name'));
$banner_id       = 'signalkit-follow-banner-' . uniqid();
$device          = $banner['device'] ?? 'desktop';

// Content
$headline        = str_replace('[site_name]', $site_name, esc_html($banner['headline']));
$description     = esc_html($banner['description']);
$button_text     = esc_html($banner['button_text']);
$google_news_url = esc_url_raw($banner['button_url']);

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

// Hover color (JS will handle, but provide fallback)
$primary_hover = $primary_color; // JS handles hover

// Approximate height for stacking
$approx_height = $banner_padding * 2 + $font_size_headline + $font_size_desc + 20;

// Device-specific classes
$device_class = $device === 'mobile' ? 'signalkit-device-mobile' : 'signalkit-device-desktop';
$position_class = $device === 'mobile'
    ? 'signalkit-position-mobile-' . sanitize_html_class($mobile_position)
    : 'signalkit-position-' . sanitize_html_class($position);
$animation_class = 'signalkit-animation-' . sanitize_html_class($animation);
$stack_class = $device === 'mobile' ? 'signalkit-stack-order-' . $stack_order : '';

// Inline CSS variables
$inline_styles = sprintf(
    '--signalkit-primary: %s; --signalkit-primary-hover: %s; --signalkit-secondary: %s; ' .
    '--signalkit-accent: %s; --signalkit-text: %s; --signalkit-width: %d%; --signalkit-padding: %dpx; ' .
    '--signalkit-headline-size: %dpx; --signalkit-description-size: %dpx; --signalkit-button-size: %dpx; ' .
    '--signalkit-radius: %dpx; --signalkit-banner-height: %dpx;',
    esc_attr($primary_color),
    esc_attr($primary_hover),
    esc_attr($secondary_color),
    esc_attr($accent_color),
    esc_attr($text_color),
    $device === 'desktop' ? $banner_width : 100,
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

$banner_classes = implode(' ', array_filter([
    'signalkit-banner',
    'signalkit-banner-follow',
    $position_class,
    $animation_class,
    $device_class,
    $stack_class,
    ...$extra_classes
]));
?>

<!-- Follow Banner -->
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

    <div class="signalkit-banner-container">

        <!-- Icon -->
        <div class="signalkit-icon" role="img" aria-label="<?php esc_attr_e('Google News Icon', 'signalkit-for-google'); ?>">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <circle cx="24" cy="24" r="20" fill="var(--signalkit-primary)"/>
                <path d="M24 14C18.48 14 14 18.48 14 24C14 29.52 18.48 34 24 34C29.52 34 34 29.52 34 24C34 18.48 29.52 14 24 14ZM24 30C20.69 30 18 27.31 18 24C18 20.69 20.69 18 24 18C27.31 18 30 20.69 30 24C30 27.31 27.31 30 24 30Z" fill="var(--signalkit-secondary)"/>
                <circle cx="24" cy="24" r="4" fill="var(--signalkit-secondary)"/>
            </svg>
        </div>

        <!-- Text Content -->
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

        <!-- CTA Button -->
        <div class="signalkit-actions">
            <?php if (!empty($google_news_url)): ?>
                <a href="<?php echo esc_url($google_news_url); ?>"
                   class="signalkit-button"
                   target="_blank"
                   rel="noopener noreferrer nofollow"
                   data-banner-type="follow"
                   aria-label="<?php echo esc_attr(sprintf(__('%s - Opens in new tab', 'signalkit-for-google'), $button_text)); ?>">
                    <span class="signalkit-button-text"><?php echo esc_html($button_text); ?></span>
                    <svg class="signalkit-icon-external" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M14 2H10V4H12.59L6.29 10.29L7.71 11.71L14 5.41V8H16V2H14ZM12 14H4V6H2V14C2 15.1 2.9 16 4 16H12C13.1 16 14 15.1 14 14V12H12V14Z"/>
                    </svg>
                </a>
            <?php else: ?>
                <span class="signalkit-button-placeholder">
                    <?php esc_html_e('Configure Google News URL in settings', 'signalkit-for-google'); ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Dismiss Button -->
        <?php if ($dismissible): ?>
            <button type="button"
                    class="signalkit-close"
                    aria-label="<?php esc_attr_e('Dismiss banner', 'signalkit-for-google'); ?>"
                    data-banner-type="follow"
                    title="<?php esc_attr_e('Close', 'signalkit-for-google'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>

    </div>

    <!-- Schema.org JSON-LD -->
    <?php if (!empty($google_news_url)): ?>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BroadcastEvent",
            "name": <?php echo wp_json_encode($headline); ?>,
            "description": <?php echo wp_json_encode($description); ?>,
            "url": <?php echo wp_json_encode($google_news_url); ?>,
            "startDate": <?php echo wp_json_encode(current_time('c', 1)); ?>,
            "eventStatus": "https://schema.org/EventScheduled",
            "eventAttendanceMode": "https://schema.org/OnlineEventAttendanceMode",
            "publisher": {
                "@type": "NewsMediaOrganization",
                "name": <?php echo wp_json_encode($site_name); ?>,
                "url": <?php echo wp_json_encode(home_url('/')); ?>,
                "logo": {
                    "@type": "ImageObject",
                    "url": <?php echo wp_json_encode(get_site_icon_url(512) ?: home_url('/wp-admin/images/wordpress-logo.svg')); ?>
                }
            }
        }
        </script>
    <?php endif; ?>

</div>

<?php
do_action('signalkit_after_follow_banner', $banner, $banner_id);
?>