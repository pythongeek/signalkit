<?php
/**
 * Custom Banner Template - Lead Capture, Newsletter, CTA
 * 
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Extract settings with defaults
$signalkit_banner_type = isset($signalkit_settings['custom_banner_type']) ? $signalkit_settings['custom_banner_type'] : 'newsletter';

// Type-specific presets - each type has unique styling and content defaults
$signalkit_type_presets = array(
    'newsletter' => array(
        'headline' => __('📧 Subscribe to Our Newsletter', 'signalkit'),
        'description' => __('Get the latest news and updates delivered to your inbox.', 'signalkit'),
        'button_text' => __('Subscribe', 'signalkit'),
        'primary_color' => '#6366f1',
        'accent_color' => '#8b5cf6',
        'show_form' => true,
    ),
    'lead' => array(
        'headline' => __('🎯 Get Your Free Guide', 'signalkit'),
        'description' => __('Enter your details below to get instant access.', 'signalkit'),
        'button_text' => __('Get Access', 'signalkit'),
        'primary_color' => '#10b981',
        'accent_color' => '#059669',
        'show_form' => true,
    ),
    'cta' => array(
        'headline' => __('🚀 Ready to Get Started?', 'signalkit'),
        'description' => __('Take action now and transform your experience.', 'signalkit'),
        'button_text' => __('Get Started', 'signalkit'),
        'primary_color' => '#f59e0b',
        'accent_color' => '#d97706',
        'show_form' => false,
    ),
    'announcement' => array(
        'headline' => __('📢 Important Update', 'signalkit'),
        'description' => __('We have some exciting news to share with you!', 'signalkit'),
        'button_text' => __('Learn More', 'signalkit'),
        'primary_color' => '#3b82f6',
        'accent_color' => '#2563eb',
        'show_form' => false,
    ),
    'promo' => array(
        'headline' => __('🏷️ Limited Time Offer!', 'signalkit'),
        'description' => __("Don't miss out on this deal. Save big today!", 'signalkit'),
        'button_text' => __('Claim Offer', 'signalkit'),
        'primary_color' => '#ef4444',
        'accent_color' => '#dc2626',
        'show_form' => false,
    ),
);

$signalkit_preset = isset($signalkit_type_presets[$signalkit_banner_type]) ? $signalkit_type_presets[$signalkit_banner_type] : $signalkit_type_presets['newsletter'];

// Use user values if set, otherwise use type-specific defaults
$signalkit_headline = !empty($signalkit_settings['custom_headline']) ? $signalkit_settings['custom_headline'] : $signalkit_preset['headline'];
$signalkit_description = !empty($signalkit_settings['custom_description']) ? $signalkit_settings['custom_description'] : $signalkit_preset['description'];
$signalkit_button_text = !empty($signalkit_settings['custom_button_text']) ? $signalkit_settings['custom_button_text'] : $signalkit_preset['button_text'];
$signalkit_placeholder_email = isset($signalkit_settings['custom_placeholder_email']) ? $signalkit_settings['custom_placeholder_email'] : __('Enter your email', 'signalkit');
$signalkit_placeholder_name = isset($signalkit_settings['custom_placeholder_name']) ? $signalkit_settings['custom_placeholder_name'] : __('Your name', 'signalkit');
$signalkit_show_name = !empty($signalkit_settings['custom_show_name_field']);
$signalkit_require_name = !empty($signalkit_settings['custom_require_name']);
$signalkit_show_privacy = !empty($signalkit_settings['custom_show_privacy']);
$signalkit_privacy_text = isset($signalkit_settings['custom_privacy_text']) ? $signalkit_settings['custom_privacy_text'] : '';
$signalkit_success_message = !empty($signalkit_settings['custom_success_message']) ? $signalkit_settings['custom_success_message'] : __('Thank you!', 'signalkit');
$signalkit_dismissible = !empty($signalkit_settings['custom_dismissible']);

// Style settings
$signalkit_banner_style = isset($signalkit_settings['custom_banner_style']) ? $signalkit_settings['custom_banner_style'] : 'modern-card';
$signalkit_button_style = isset($signalkit_settings['custom_button_style']) ? $signalkit_settings['custom_button_style'] : 'default';
$signalkit_icon_style = isset($signalkit_settings['custom_icon_style']) ? $signalkit_settings['custom_icon_style'] : 'circle';
$signalkit_visibility_mode = isset($signalkit_settings['custom_visibility_mode']) ? $signalkit_settings['custom_visibility_mode'] : 'auto';
$signalkit_enable_glow = !empty($signalkit_settings['custom_enable_glow']);
$signalkit_enable_float = !empty($signalkit_settings['custom_enable_float']);
$signalkit_position = isset($signalkit_settings['custom_position']) ? $signalkit_settings['custom_position'] : 'bottom_right';
$signalkit_animation = isset($signalkit_settings['custom_animation']) ? $signalkit_settings['custom_animation'] : 'slide_in';

// Typography
$signalkit_font_family = isset($signalkit_settings['custom_font_family']) ? $signalkit_settings['custom_font_family'] : 'system';
$signalkit_font_weight_headline = isset($signalkit_settings['custom_font_weight_headline']) ? $signalkit_settings['custom_font_weight_headline'] : '700';
$signalkit_font_weight_body = isset($signalkit_settings['custom_font_weight_body']) ? $signalkit_settings['custom_font_weight_body'] : '400';
$signalkit_letter_spacing = isset($signalkit_settings['custom_letter_spacing']) ? $signalkit_settings['custom_letter_spacing'] : 0;
$signalkit_line_height = isset($signalkit_settings['custom_line_height']) ? $signalkit_settings['custom_line_height'] : 1.5;

// Colors - use preset defaults if user hasn't customized
$signalkit_primary_color = !empty($signalkit_settings['custom_primary_color']) ? $signalkit_settings['custom_primary_color'] : $signalkit_preset['primary_color'];
$signalkit_secondary_color = isset($signalkit_settings['custom_secondary_color']) ? $signalkit_settings['custom_secondary_color'] : '#ffffff';
$signalkit_accent_color = !empty($signalkit_settings['custom_accent_color']) ? $signalkit_settings['custom_accent_color'] : $signalkit_preset['accent_color'];
$signalkit_text_color = isset($signalkit_settings['custom_text_color']) ? $signalkit_settings['custom_text_color'] : '#1e1e1e';

// Size/Spacing
$signalkit_banner_width = isset($signalkit_settings['custom_banner_width']) ? absint($signalkit_settings['custom_banner_width']) : 400;
$signalkit_banner_padding = isset($signalkit_settings['custom_banner_padding']) ? absint($signalkit_settings['custom_banner_padding']) : 24;
$signalkit_border_radius = isset($signalkit_settings['custom_border_radius']) ? absint($signalkit_settings['custom_border_radius']) : 16;
$signalkit_backdrop_blur = isset($signalkit_settings['custom_backdrop_blur']) ? absint($signalkit_settings['custom_backdrop_blur']) : 12;
$signalkit_backdrop_opacity = isset($signalkit_settings['custom_backdrop_opacity']) ? absint($signalkit_settings['custom_backdrop_opacity']) : 95;
$signalkit_glow_intensity = isset($signalkit_settings['custom_glow_intensity']) ? absint($signalkit_settings['custom_glow_intensity']) : 20;
$signalkit_icon_size = isset($signalkit_settings['custom_icon_size']) ? absint($signalkit_settings['custom_icon_size']) : 52;
$signalkit_close_button_size = isset($signalkit_settings['custom_close_button_size']) ? absint($signalkit_settings['custom_close_button_size']) : 28;

// Trigger settings
$signalkit_delay = isset($signalkit_settings['custom_delay']) ? absint($signalkit_settings['custom_delay']) : 3;
$signalkit_scroll_trigger = !empty($signalkit_settings['custom_scroll_trigger']);
$signalkit_scroll_percentage = isset($signalkit_settings['custom_scroll_percentage']) ? absint($signalkit_settings['custom_scroll_percentage']) : 50;
$signalkit_exit_intent = !empty($signalkit_settings['custom_exit_intent']);

// Build CSS classes
$signalkit_classes = array(
    'signalkit-banner',
    'signalkit-banner-custom',
    'signalkit-banner-type-' . sanitize_html_class($signalkit_banner_type),
    'signalkit-style-' . sanitize_html_class($signalkit_banner_style),
    'signalkit-position-' . sanitize_html_class($signalkit_position),
    'signalkit-animation-' . sanitize_html_class($signalkit_animation),
    'signalkit-button-style-' . sanitize_html_class($signalkit_button_style),
);

if ($signalkit_visibility_mode === 'light') {
    $signalkit_classes[] = 'signalkit-contrast-light';
} elseif ($signalkit_visibility_mode === 'dark') {
    $signalkit_classes[] = 'signalkit-contrast-dark';
}

if ($signalkit_enable_glow) {
    $signalkit_classes[] = 'signalkit-pulse-glow';
}

if ($signalkit_enable_float) {
    $signalkit_classes[] = 'signalkit-float-enabled';
}

if ($signalkit_position === 'center') {
    $signalkit_classes[] = 'signalkit-modal';
}

// Apply filter for shortcode style classes
$signalkit_extra_classes = apply_filters('signalkit_custom_banner_classes', array(), $signalkit_settings);
$signalkit_classes = array_merge($signalkit_classes, $signalkit_extra_classes);

// Font family map
$signalkit_font_map = array(
    'system' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
    'inter' => '"Inter", sans-serif',
    'roboto' => '"Roboto", sans-serif',
    'open-sans' => '"Open Sans", sans-serif',
    'lato' => '"Lato", sans-serif',
    'montserrat' => '"Montserrat", sans-serif',
    'poppins' => '"Poppins", sans-serif',
    'nunito' => '"Nunito", sans-serif',
    'raleway' => '"Raleway", sans-serif',
    'ubuntu' => '"Ubuntu", sans-serif',
    'playfair' => '"Playfair Display", serif',
    'merriweather' => '"Merriweather", serif',
    'source-sans' => '"Source Sans Pro", sans-serif',
    'oswald' => '"Oswald", sans-serif',
    'rubik' => '"Rubik", sans-serif',
);

$signalkit_font_stack = isset($signalkit_font_map[$signalkit_font_family]) ? $signalkit_font_map[$signalkit_font_family] : $signalkit_font_map['system'];

// Convert colors to RGB
$signalkit_primary_rgb = signalkit_hex_to_rgb($signalkit_primary_color);
$signalkit_secondary_rgb = signalkit_hex_to_rgb($signalkit_secondary_color);
$signalkit_accent_rgb = signalkit_hex_to_rgb($signalkit_accent_color);

// Build inline styles
$signalkit_inline_styles = sprintf(
    '--signalkit-primary: %1$signalkit_s; --signalkit-primary-rgb: %2$signalkit_s; --signalkit-secondary: %3$signalkit_s; --signalkit-secondary-rgb: %4$signalkit_s; --signalkit-accent: %5$signalkit_s; --signalkit-accent-rgb: %6$signalkit_s; --signalkit-text: %7$signalkit_s; --signalkit-width: %8$signalkit_spx; --signalkit-padding: %9$signalkit_spx; --signalkit-radius: %10$signalkit_spx; --signalkit-backdrop-blur: %11$signalkit_spx; --signalkit-backdrop-opacity: %12$signalkit_s; --signalkit-icon-size: %13$signalkit_spx; --signalkit-glow-intensity: %14$signalkit_spx; --signalkit-font-family: %15$signalkit_s; --signalkit-font-weight-headline: %16$signalkit_s; --signalkit-font-weight-body: %17$signalkit_s; --signalkit-letter-spacing: %18$signalkit_spx; --signalkit-line-height: %19$signalkit_s; --signalkit-close-size: %20$signalkit_spx;',
    esc_attr($signalkit_primary_color),
    esc_attr($signalkit_primary_rgb),
    esc_attr($signalkit_secondary_color),
    esc_attr($signalkit_secondary_rgb),
    esc_attr($signalkit_accent_color),
    esc_attr($signalkit_accent_rgb),
    esc_attr($signalkit_text_color),
    esc_attr($signalkit_banner_width),
    esc_attr($signalkit_banner_padding),
    esc_attr($signalkit_border_radius),
    esc_attr($signalkit_backdrop_blur),
    esc_attr($signalkit_backdrop_opacity / 100),
    esc_attr($signalkit_icon_size),
    esc_attr($signalkit_glow_intensity),
    esc_attr($signalkit_font_stack),
    esc_attr($signalkit_font_weight_headline),
    esc_attr($signalkit_font_weight_body),
    esc_attr($signalkit_letter_spacing),
    esc_attr($signalkit_line_height),
    esc_attr($signalkit_close_button_size)
);

// Icon based on banner type
$signalkit_icons = array(
    'newsletter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
    'lead' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>',
    'cta' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
    'announcement' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M18 11v2h4v-2h-4zm-2 6.61c.96.71 2.21 1.65 3.2 2.39.4-.53.8-1.07 1.2-1.6-.99-.74-2.24-1.68-3.2-2.4-.4.54-.8 1.08-1.2 1.61zM20.4 5.6c-.4-.53-.8-1.07-1.2-1.6-.99.74-2.24 1.68-3.2 2.4.4.53.8 1.07 1.2 1.6.96-.72 2.21-1.65 3.2-2.4zM4 9c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2h1v4h2v-4h1l5 3V6L8 9H4zm11.5 3c0-1.33-.58-2.53-1.5-3.35v6.69c.92-.81 1.5-2.01 1.5-3.34z"/></svg>',
    'promo' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>',
);

$signalkit_icon_svg = isset($signalkit_icons[$signalkit_banner_type]) ? $signalkit_icons[$signalkit_banner_type] : $signalkit_icons['newsletter'];

// Determine if form fields are needed (based on type preset)
$signalkit_has_form = $signalkit_preset['show_form'];

// Data attributes for JavaScript
$signalkit_data_attrs = array(
    'data-banner-type'       => $signalkit_banner_type,
    'data-delay'             => $signalkit_delay,
    'data-scroll-trigger'    => $signalkit_scroll_trigger ? 'true' : 'false',
    'data-scroll-percentage' => $signalkit_scroll_percentage,
    'data-exit-intent'       => $signalkit_exit_intent ? 'true' : 'false',
    'data-success-message'   => $signalkit_success_message,
    'data-dismissible'       => $signalkit_dismissible ? 'true' : 'false',
);
?>

<aside id="signalkit-banner-custom" 
       class="<?php echo esc_attr(implode(' ', $signalkit_classes)); ?>"
       style="<?php echo esc_attr($signalkit_inline_styles); ?>"
       <?php foreach ($signalkit_data_attrs as $signalkit_key => $signalkit_val) : ?>
           <?php echo esc_attr($signalkit_key); ?>="<?php echo esc_attr($signalkit_val); ?>"
       <?php endforeach; ?>
       role="complementary"
       aria-label="<?php echo esc_attr($signalkit_headline); ?>"
       aria-hidden="true">
    
    <?php if ($signalkit_position === 'center'): ?>
    <div class="signalkit-modal-overlay" aria-hidden="true"></div>
    <?php endif; ?>
    
    <div class="signalkit-banner-content">
        
        <!-- Icon -->
        <div class="signalkit-icon signalkit-icon-style-<?php echo esc_attr($signalkit_icon_style); ?>" aria-hidden="true">
            <?php 
            // Late escaping: Allow only safe SVG elements for the icon
            $signalkit_allowed_svg = array(
                'svg' => array('xmlns' => array(), 'viewBox' => array(), 'fill' => array(), 'class' => array(), 'width' => array(), 'height' => array(), 'aria-hidden' => array()),
                'path' => array('d' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array()),
                'circle' => array('cx' => array(), 'cy' => array(), 'r' => array(), 'fill' => array(), 'stroke' => array()),
                'rect' => array('x' => array(), 'y' => array(), 'width' => array(), 'height' => array(), 'rx' => array(), 'ry' => array(), 'fill' => array()),
                'g' => array('fill' => array(), 'transform' => array()),
            );
            echo wp_kses($signalkit_icon_svg, $signalkit_allowed_svg);
            ?>
        </div>
        
        <!-- Text Content -->
        <div class="signalkit-text">
            <?php if ($signalkit_headline): ?>
                <h3 class="signalkit-headline">
                    <?php echo wp_kses($signalkit_headline, array('strong' => array(), 'em' => array(), 'br' => array())); ?>
                </h3>
            <?php endif; ?>
            
            <?php if ($signalkit_description): ?>
                <p class="signalkit-description">
                    <?php echo wp_kses($signalkit_description, array('strong' => array(), 'em' => array(), 'br' => array(), 'a' => array('href' => array(), 'target' => array()))); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Actions / Form - Type Specific Layouts -->
        <div class="signalkit-actions signalkit-actions-<?php echo esc_attr($signalkit_banner_type); ?>">
            
            <?php if ($signalkit_banner_type === 'newsletter'): ?>
                <!-- NEWSLETTER: Email focused with optional name -->
                <form class="signalkit-custom-form signalkit-form-newsletter" method="post" action="">
                    <?php wp_nonce_field('signalkit_custom_submit', 'signalkit_custom_nonce'); ?>
                    <input type="hidden" name="action" value="signalkit_custom_submit">
                    <input type="hidden" name="banner_type" value="newsletter">
                    <input type="hidden" name="signalkit_redirect_url" value="<?php echo esc_url($signalkit_settings['custom_redirect_url'] ?? ''); ?>">
                    
                    <?php if ($signalkit_show_name): ?>
                        <input type="text" 
                               name="signalkit_name" 
                               class="signalkit-input signalkit-input-name"
                               placeholder="<?php echo esc_attr($signalkit_placeholder_name); ?>"
                               aria-label="<?php esc_attr_e('Your name', 'signalkit'); ?>">
                    <?php endif; ?>
                    
                    <div class="signalkit-form-row">
                        <input type="email" 
                               name="signalkit_email" 
                               class="signalkit-input signalkit-input-email"
                               placeholder="<?php echo esc_attr($signalkit_placeholder_email); ?>"
                               required
                               aria-label="<?php esc_attr_e('Email address', 'signalkit'); ?>">
                        
                        <button type="submit" class="signalkit-button signalkit-button-style-<?php echo esc_attr($signalkit_button_style); ?>">
                            <span class="signalkit-button-icon">📧</span>
                            <span class="signalkit-button-text"><?php echo esc_html($signalkit_button_text); ?></span>
                            <span class="signalkit-button-loading" aria-hidden="true">
                                <svg class="signalkit-spinner" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"></circle>
                                </svg>
                            </span>
                        </button>
                    </div>
                    
                    <?php if ($signalkit_show_privacy && $signalkit_privacy_text): ?>
                        <p class="signalkit-privacy-text">
                            <?php echo wp_kses($signalkit_privacy_text, array('a' => array('href' => array(), 'target' => array()))); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="signalkit-form-message" aria-live="polite"></div>
                </form>
                
            <?php elseif ($signalkit_banner_type === 'lead'): ?>
                <!-- LEAD CAPTURE: Name (required) + Email + Optional Phone -->
                <form class="signalkit-custom-form signalkit-form-lead" method="post" action="">
                    <?php wp_nonce_field('signalkit_custom_submit', 'signalkit_custom_nonce'); ?>
                    <input type="hidden" name="action" value="signalkit_custom_submit">
                    <input type="hidden" name="banner_type" value="lead">
                    <input type="hidden" name="signalkit_redirect_url" value="<?php echo esc_url($signalkit_settings['custom_redirect_url'] ?? ''); ?>">
                    
                    <div class="signalkit-form-fields">
                        <input type="text" 
                               name="signalkit_name" 
                               class="signalkit-input signalkit-input-name"
                               placeholder="<?php esc_attr_e('Full Name *', 'signalkit'); ?>"
                               required
                               aria-label="<?php esc_attr_e('Full name', 'signalkit'); ?>">
                        
                        <input type="email" 
                               name="signalkit_email" 
                               class="signalkit-input signalkit-input-email"
                               placeholder="<?php esc_attr_e('Email Address *', 'signalkit'); ?>"
                               required
                               aria-label="<?php esc_attr_e('Email address', 'signalkit'); ?>">
                        
                        <input type="tel" 
                               name="signalkit_phone" 
                               class="signalkit-input signalkit-input-phone"
                               placeholder="<?php esc_attr_e('Phone (optional)', 'signalkit'); ?>"
                               aria-label="<?php esc_attr_e('Phone number', 'signalkit'); ?>">
                    </div>
                    
                    <button type="submit" class="signalkit-button signalkit-button-full signalkit-button-style-<?php echo esc_attr($signalkit_button_style); ?>">
                        <span class="signalkit-button-icon">🎯</span>
                        <span class="signalkit-button-text"><?php echo esc_html($signalkit_button_text); ?></span>
                        <span class="signalkit-button-loading" aria-hidden="true">
                            <svg class="signalkit-spinner" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"></circle>
                            </svg>
                        </span>
                    </button>
                    
                    <?php if ($signalkit_show_privacy && $signalkit_privacy_text): ?>
                        <p class="signalkit-privacy-text">
                            <?php echo wp_kses($signalkit_privacy_text, array('a' => array('href' => array(), 'target' => array()))); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="signalkit-form-message" aria-live="polite"></div>
                </form>
                
            <?php elseif ($signalkit_banner_type === 'cta'): ?>
                <!-- CTA: Bold action button -->
                <div class="signalkit-cta-actions">
                    <a href="<?php echo esc_url($signalkit_settings['custom_redirect_url'] ?? '#'); ?>" 
                       class="signalkit-button signalkit-button-cta signalkit-button-style-<?php echo esc_attr($signalkit_button_style); ?>"
                       target="_blank"
                       rel="noopener">
                        <span class="signalkit-button-icon">🚀</span>
                        <span class="signalkit-button-text"><?php echo esc_html($signalkit_button_text); ?></span>
                        <svg class="signalkit-icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                
            <?php elseif ($signalkit_banner_type === 'announcement'): ?>
                <!-- ANNOUNCEMENT: Info display with action -->
                <div class="signalkit-announcement-actions">
                    <div class="signalkit-announcement-meta">
                        <span class="signalkit-announcement-badge"><?php esc_html_e('NEW', 'signalkit'); ?></span>
                        <span class="signalkit-announcement-date"><?php echo esc_html(wp_date(get_option('date_format'))); ?></span>
                    </div>
                    <a href="<?php echo esc_url($signalkit_settings['custom_redirect_url'] ?? '#'); ?>" 
                       class="signalkit-button signalkit-button-announcement signalkit-button-style-<?php echo esc_attr($signalkit_button_style); ?>"
                       target="_blank"
                       rel="noopener">
                        <span class="signalkit-button-text"><?php echo esc_html($signalkit_button_text); ?></span>
                        <svg class="signalkit-icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                
            <?php elseif ($signalkit_banner_type === 'promo'): ?>
                <!-- PROMO: Discount/offer focused -->
                <div class="signalkit-promo-actions">
                    <?php 
                    $signalkit_promo_code = isset($signalkit_settings['custom_promo_code']) ? $signalkit_settings['custom_promo_code'] : '';
                    if ($signalkit_promo_code): ?>
                        <div class="signalkit-promo-code">
                            <span class="signalkit-promo-label"><?php esc_html_e('Use Code:', 'signalkit'); ?></span>
                            <span class="signalkit-promo-value"><?php echo esc_html($signalkit_promo_code); ?></span>
                            <button type="button" class="signalkit-copy-code" data-code="<?php echo esc_attr($signalkit_promo_code); ?>" title="<?php esc_attr_e('Copy code', 'signalkit'); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo esc_url($signalkit_settings['custom_redirect_url'] ?? '#'); ?>" 
                       class="signalkit-button signalkit-button-promo signalkit-button-style-<?php echo esc_attr($signalkit_button_style); ?>"
                       target="_blank"
                       rel="noopener">
                        <span class="signalkit-button-icon">🏷️</span>
                        <span class="signalkit-button-text"><?php echo esc_html($signalkit_button_text); ?></span>
                    </a>
                    <p class="signalkit-promo-urgency"><?php esc_html_e('⏰ Limited time offer!', 'signalkit'); ?></p>
                </div>
                
            <?php else: ?>
                <!-- Fallback: Simple button -->
                <a href="<?php echo esc_url($signalkit_settings['custom_redirect_url'] ?? '#'); ?>" 
                   class="signalkit-button signalkit-button-style-<?php echo esc_attr($signalkit_button_style); ?>"
                   target="_blank"
                   rel="noopener">
                    <?php echo esc_html($signalkit_button_text); ?>
                </a>
            <?php endif; ?>
            
        </div>
        
        <!-- Close Button -->
        <?php if ($signalkit_dismissible): ?>
            <button type="button" 
                    class="signalkit-close" 
                    aria-label="<?php esc_attr_e('Close banner', 'signalkit'); ?>"
                    data-banner="custom">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>
        
    </div>
    
    <!-- Success State -->
    <div class="signalkit-success-state" aria-hidden="true">
        <div class="signalkit-success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <p class="signalkit-success-message"><?php echo esc_html($signalkit_success_message); ?></p>
    </div>
    
</aside>

