<?php
/**
 * Settings Page Template - ENHANCED WITH LIVE PREVIEW
 *
 * @package SignalKit_For_Google
 * @version 2.0.0
 * @link https://developer.wordpress.org/plugins/settings/
 * @link https://developer.wordpress.org/reference/functions/settings_fields/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly - WordPress security best practice
}

// Get current settings with proper sanitization
$settings = get_option('signalkit_settings', array());

// Enhanced defaults with new customization options - WordPress and Envato compatible
$defaults = array(
    'site_name' => get_bloginfo('name'),
    
    // Follow Banner Settings
    'follow_enabled' => 0,
    'follow_google_news_url' => '',
    'follow_button_text' => __('Follow Us On Google News', 'signalkit-for-google'),
    'follow_banner_headline' => __('Stay Updated with [site_name]', 'signalkit-for-google'),
    'follow_banner_description' => __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit-for-google'),
    'follow_primary_color' => '#4285f4',
    'follow_secondary_color' => '#ffffff',
    'follow_accent_color' => '#34a853',
    'follow_text_color' => '#1a1a1a',
    'follow_position' => 'bottom_left',
    'follow_animation' => 'slide_in',
    'follow_dismissible' => 1,
    'follow_dismiss_duration' => 7,
    'follow_show_frequency' => 'once_per_day',
    'follow_mobile_enabled' => 1,
    'follow_desktop_enabled' => 1,
    'follow_show_on_posts' => 1,
    'follow_show_on_pages' => 1,
    'follow_show_on_homepage' => 1,
    'follow_show_on_archive' => 0,
    'follow_banner_width' => 360,
    'follow_banner_padding' => 16,
    'follow_font_size_headline' => 15,
    'follow_font_size_description' => 13,
    'follow_font_size_button' => 14,
    'follow_border_radius' => 8,
    'follow_mobile_position' => 'bottom',
    'follow_mobile_stack_order' => 1,
    
    // Preferred Source Banner Settings
    'preferred_enabled' => 0,
    'preferred_google_preferences_url' => '',
    'preferred_educational_post_url' => '',
    'preferred_button_text' => __('Add As A Preferred Source', 'signalkit-for-google'),
    'preferred_banner_headline' => __('Add [site_name] As A Trusted Source', 'signalkit-for-google'),
    'preferred_banner_description' => __('Get priority access to our news and updates in your Google News feed.', 'signalkit-for-google'),
    'preferred_educational_text' => __('Learn More', 'signalkit-for-google'),
    'preferred_show_educational_link' => 1,
    'preferred_primary_color' => '#4285f4',
    'preferred_secondary_color' => '#ffffff',
    'preferred_accent_color' => '#ea4335',
    'preferred_text_color' => '#1a1a1a',
    'preferred_position' => 'bottom_right',
    'preferred_animation' => 'slide_in',
    'preferred_dismissible' => 1,
    'preferred_dismiss_duration' => 7,
    'preferred_show_frequency' => 'once_per_day',
    'preferred_mobile_enabled' => 1,
    'preferred_desktop_enabled' => 1,
    'preferred_show_on_posts' => 1,
    'preferred_show_on_pages' => 1,
    'preferred_show_on_homepage' => 1,
    'preferred_show_on_archive' => 0,
    'preferred_banner_width' => 360,
    'preferred_banner_padding' => 16,
    'preferred_font_size_headline' => 15,
    'preferred_font_size_description' => 13,
    'preferred_font_size_button' => 14,
    'preferred_border_radius' => 8,
    'preferred_mobile_position' => 'bottom',
    'preferred_mobile_stack_order' => 2,
    
    // Security & Advanced Features - Envato/WordPress.org compliance
    'enable_csp' => 1,
    'enable_rate_limiting' => 1,
    'analytics_tracking' => 1,
    'import_export_key' => '',
);

// Merge settings with defaults - WordPress best practice
$settings = wp_parse_args($settings, $defaults);
?>

<div class="wrap signalkit-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('signalkit_settings'); ?>
    
    <!-- Layout with Preview Panel -->
    <div class="signalkit-layout-container">
        
        <!-- LEFT: Settings Panel -->
        <div class="signalkit-settings-panel">
            
            <div class="signalkit-tabs-container">
                <nav class="signalkit-tabs" role="tablist">
                    <button type="button" class="signalkit-tab active" data-tab="follow" role="tab" aria-selected="true" aria-controls="tab-follow">
                        <span class="dashicons dashicons-bell" aria-hidden="true"></span>
                        <?php esc_html_e('Follow Banner', 'signalkit-for-google'); ?>
                    </button>
                    <button type="button" class="signalkit-tab" data-tab="preferred" role="tab" aria-selected="false" aria-controls="tab-preferred">
                        <span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
                        <?php esc_html_e('Preferred Source', 'signalkit-for-google'); ?>
                    </button>
                    <button type="button" class="signalkit-tab" data-tab="global" role="tab" aria-selected="false" aria-controls="tab-global">
                        <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
                        <?php esc_html_e('Global Settings', 'signalkit-for-google'); ?>
                    </button>
                    <button type="button" class="signalkit-tab" data-tab="advanced" role="tab" aria-selected="false" aria-controls="tab-advanced">
                        <span class="dashicons dashicons-shield-alt" aria-hidden="true"></span>
                        <?php esc_html_e('Advanced & Security', 'signalkit-for-google'); ?>
                    </button>
                </nav>
                
                <form method="post" action="options.php" class="signalkit-form" novalidate>
                    <?php 
                    // WordPress Settings API - Required for proper form handling
                    settings_fields('signalkit_settings_group'); 
                    // Security nonce for AJAX preview
                    wp_nonce_field('signalkit_settings_nonce', 'signalkit_nonce'); 
                    ?>
                    
                    <!-- FOLLOW BANNER TAB -->
                    <div class="signalkit-tab-content active" id="tab-follow" data-content="follow" role="tabpanel">
                        <div class="signalkit-settings-grid">
                            
                            <!-- Enable/Disable -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[follow_enabled]" 
                                           value="1" 
                                           <?php checked($settings['follow_enabled'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="follow"
                                           aria-describedby="follow-enabled-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Enable Follow Banner', 'signalkit-for-google'); ?></strong>
                                    <p id="follow-enabled-desc"><?php esc_html_e('Display the Google News Follow banner on your site', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Google News URL -->
                            <div class="signalkit-setting-row">
                                <label for="follow_google_news_url"><?php esc_html_e('Google News URL', 'signalkit-for-google'); ?> <span class="required" aria-label="<?php esc_attr_e('required', 'signalkit-for-google'); ?>">*</span></label>
                                <input type="url" 
                                       id="follow_google_news_url"
                                       name="signalkit_settings[follow_google_news_url]" 
                                       value="<?php echo esc_url($settings['follow_google_news_url']); ?>" 
                                       class="regular-text signalkit-preview-trigger" 
                                       data-banner="follow"
                                       placeholder="https://news.google.com/publications/..."
                                       aria-describedby="follow-url-desc"
                                       required>
                                <p class="description" id="follow-url-desc"><?php esc_html_e('Your Google News publication URL', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Content Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-edit" aria-hidden="true"></span> <?php esc_html_e('Content', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Banner Headline -->
                            <div class="signalkit-setting-row">
                                <label for="follow_banner_headline"><?php esc_html_e('Banner Headline', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       id="follow_banner_headline"
                                       name="signalkit_settings[follow_banner_headline]" 
                                       value="<?php echo esc_attr($settings['follow_banner_headline']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="follow"
                                       placeholder="<?php echo esc_attr__('Stay Updated with [site_name]', 'signalkit-for-google'); ?>"
                                       aria-describedby="follow-headline-desc">
                                <p class="description" id="follow-headline-desc"><?php esc_html_e('Use [site_name] as placeholder for your site name', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Banner Description -->
                            <div class="signalkit-setting-row">
                                <label for="follow_banner_description"><?php esc_html_e('Banner Description', 'signalkit-for-google'); ?></label>
                                <textarea id="follow_banner_description"
                                          name="signalkit_settings[follow_banner_description]" 
                                          rows="3" 
                                          class="large-text signalkit-preview-trigger"
                                          data-banner="follow"><?php echo esc_textarea($settings['follow_banner_description']); ?></textarea>
                            </div>
                            
                            <!-- Button Text -->
                            <div class="signalkit-setting-row">
                                <label for="follow_button_text"><?php esc_html_e('Button Text', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       id="follow_button_text"
                                       name="signalkit_settings[follow_button_text]" 
                                       value="<?php echo esc_attr($settings['follow_button_text']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="follow"
                                       placeholder="<?php echo esc_attr__('Follow Us On Google News', 'signalkit-for-google'); ?>">
                            </div>
                            
                            <!-- Design Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-art" aria-hidden="true"></span> <?php esc_html_e('Design & Appearance', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Color Settings -->
                            <div class="signalkit-setting-row signalkit-color-row">
                                <h4><?php esc_html_e('Colors', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-color-grid">
                                    <div class="signalkit-color-input">
                                        <label for="follow_primary_color"><?php esc_html_e('Primary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="follow_primary_color"
                                               name="signalkit_settings[follow_primary_color]" 
                                               value="<?php echo esc_attr($settings['follow_primary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label for="follow_secondary_color"><?php esc_html_e('Secondary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="follow_secondary_color"
                                               name="signalkit_settings[follow_secondary_color]" 
                                               value="<?php echo esc_attr($settings['follow_secondary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label for="follow_accent_color"><?php esc_html_e('Accent Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="follow_accent_color"
                                               name="signalkit_settings[follow_accent_color]" 
                                               value="<?php echo esc_attr($settings['follow_accent_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label for="follow_text_color"><?php esc_html_e('Text Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="follow_text_color"
                                               name="signalkit_settings[follow_text_color]" 
                                               value="<?php echo esc_attr($settings['follow_text_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Size & Spacing -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Size & Spacing', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-size-grid">
                                    <div class="signalkit-size-input">
                                        <label for="follow_banner_width"><?php esc_html_e('Banner Width (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="follow_banner_width"
                                               name="signalkit_settings[follow_banner_width]" 
                                               value="<?php echo esc_attr($settings['follow_banner_width']); ?>" 
                                               min="280" 
                                               max="600" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_banner_width']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label for="follow_banner_padding"><?php esc_html_e('Padding (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="follow_banner_padding"
                                               name="signalkit_settings[follow_banner_padding]" 
                                               value="<?php echo esc_attr($settings['follow_banner_padding']); ?>" 
                                               min="8" 
                                               max="32" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_banner_padding']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label for="follow_border_radius"><?php esc_html_e('Border Radius (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="follow_border_radius"
                                               name="signalkit_settings[follow_border_radius]" 
                                               value="<?php echo esc_attr($settings['follow_border_radius']); ?>" 
                                               min="0" 
                                               max="32" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_border_radius']); ?>px</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Typography -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Typography', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-typography-grid">
                                    <div class="signalkit-typography-input">
                                        <label for="follow_font_size_headline"><?php esc_html_e('Headline Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="follow_font_size_headline"
                                               name="signalkit_settings[follow_font_size_headline]" 
                                               value="<?php echo esc_attr($settings['follow_font_size_headline']); ?>" 
                                               min="12" 
                                               max="24" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_font_size_headline']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label for="follow_font_size_description"><?php esc_html_e('Description Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="follow_font_size_description"
                                               name="signalkit_settings[follow_font_size_description]" 
                                               value="<?php echo esc_attr($settings['follow_font_size_description']); ?>" 
                                               min="10" 
                                               max="18" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_font_size_description']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label for="follow_font_size_button"><?php esc_html_e('Button Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="follow_font_size_button"
                                               name="signalkit_settings[follow_font_size_button]" 
                                               value="<?php echo esc_attr($settings['follow_font_size_button']); ?>" 
                                               min="11" 
                                               max="18" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_font_size_button']); ?>px</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Display Settings Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-desktop" aria-hidden="true"></span> <?php esc_html_e('Display Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Position Settings -->
                            <div class="signalkit-setting-row">
                                <div class="signalkit-display-grid">
                                    <div class="signalkit-select-group">
                                        <label for="follow_position"><?php esc_html_e('Desktop Position', 'signalkit-for-google'); ?></label>
                                        <select id="follow_position" 
                                                name="signalkit_settings[follow_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="follow">
                                            <option value="bottom_left" <?php selected($settings['follow_position'], 'bottom_left'); ?>><?php esc_html_e('Bottom Left', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_right" <?php selected($settings['follow_position'], 'bottom_right'); ?>><?php esc_html_e('Bottom Right', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_center" <?php selected($settings['follow_position'], 'bottom_center'); ?>><?php esc_html_e('Bottom Center', 'signalkit-for-google'); ?></option>
                                            <option value="top_left" <?php selected($settings['follow_position'], 'top_left'); ?>><?php esc_html_e('Top Left', 'signalkit-for-google'); ?></option>
                                            <option value="top_right" <?php selected($settings['follow_position'], 'top_right'); ?>><?php esc_html_e('Top Right', 'signalkit-for-google'); ?></option>
                                            <option value="top_center" <?php selected($settings['follow_position'], 'top_center'); ?>><?php esc_html_e('Top Center', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="follow_mobile_position"><?php esc_html_e('Mobile Position', 'signalkit-for-google'); ?></label>
                                        <select id="follow_mobile_position" 
                                                name="signalkit_settings[follow_mobile_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="follow">
                                            <option value="top" <?php selected($settings['follow_mobile_position'], 'top'); ?>><?php esc_html_e('Top of Screen', 'signalkit-for-google'); ?></option>
                                            <option value="bottom" <?php selected($settings['follow_mobile_position'], 'bottom'); ?>><?php esc_html_e('Bottom of Screen', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="follow_mobile_stack_order"><?php esc_html_e('Mobile Stack Order', 'signalkit-for-google'); ?></label>
                                        <select id="follow_mobile_stack_order" 
                                                name="signalkit_settings[follow_mobile_stack_order]">
                                            <option value="1" <?php selected($settings['follow_mobile_stack_order'], 1); ?>><?php esc_html_e('First (Top/Bottom)', 'signalkit-for-google'); ?></option>
                                            <option value="2" <?php selected($settings['follow_mobile_stack_order'], 2); ?>><?php esc_html_e('Second', 'signalkit-for-google'); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e('Order when both banners are active on mobile', 'signalkit-for-google'); ?></p>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="follow_animation"><?php esc_html_e('Animation', 'signalkit-for-google'); ?></label>
                                        <select id="follow_animation" 
                                                name="signalkit_settings[follow_animation]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="follow">
                                            <option value="slide_in" <?php selected($settings['follow_animation'], 'slide_in'); ?>><?php esc_html_e('Slide In', 'signalkit-for-google'); ?></option>
                                            <option value="fade_in" <?php selected($settings['follow_animation'], 'fade_in'); ?>><?php esc_html_e('Fade In', 'signalkit-for-google'); ?></option>
                                            <option value="bounce" <?php selected($settings['follow_animation'], 'bounce'); ?>><?php esc_html_e('Bounce', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="follow_show_frequency"><?php esc_html_e('Show Frequency', 'signalkit-for-google'); ?></label>
                                        <select id="follow_show_frequency" 
                                                name="signalkit_settings[follow_show_frequency]">
                                            <option value="always" <?php selected($settings['follow_show_frequency'], 'always'); ?>><?php esc_html_e('Always', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_session" <?php selected($settings['follow_show_frequency'], 'once_per_session'); ?>><?php esc_html_e('Once Per Session', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_day" <?php selected($settings['follow_show_frequency'], 'once_per_day'); ?>><?php esc_html_e('Once Per Day', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dismissal Settings -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[follow_dismissible]" 
                                           value="1" 
                                           <?php checked($settings['follow_dismissible'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="follow"
                                           aria-describedby="follow-dismissible-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Allow Dismissal', 'signalkit-for-google'); ?></strong>
                                    <p id="follow-dismissible-desc"><?php esc_html_e('Users can close the banner', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label for="follow_dismiss_duration"><?php esc_html_e('Dismiss Duration (days)', 'signalkit-for-google'); ?></label>
                                <input type="number" 
                                       id="follow_dismiss_duration"
                                       name="signalkit_settings[follow_dismiss_duration]" 
                                       value="<?php echo esc_attr($settings['follow_dismiss_duration']); ?>" 
                                       min="1" 
                                       max="365" 
                                       class="small-text"
                                       aria-describedby="follow-dismiss-desc">
                                <p class="description" id="follow-dismiss-desc"><?php esc_html_e('How long to hide the banner after dismissal', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Device Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Device Settings', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_mobile_enabled]" 
                                               value="1" 
                                               <?php checked($settings['follow_mobile_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
                                        <?php esc_html_e('Show on Mobile', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_desktop_enabled]" 
                                               value="1" 
                                               <?php checked($settings['follow_desktop_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="dashicons dashicons-desktop" aria-hidden="true"></span>
                                        <?php esc_html_e('Show on Desktop', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Page Type Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Page Types', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_posts]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_posts'], 1); ?>>
                                        <?php esc_html_e('Show on Posts', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_pages]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_pages'], 1); ?>>
                                        <?php esc_html_e('Show on Pages', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_homepage]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_homepage'], 1); ?>>
                                        <?php esc_html_e('Show on Homepage', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_archive]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_archive'], 1); ?>>
                                        <?php esc_html_e('Show on Archive Pages', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- PREFERRED SOURCE BANNER TAB -->
                    <div class="signalkit-tab-content" id="tab-preferred" data-content="preferred" role="tabpanel">
                        <div class="signalkit-settings-grid">
                            
                            <!-- Enable/Disable -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[preferred_enabled]" 
                                           value="1" 
                                           <?php checked($settings['preferred_enabled'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="preferred"
                                           aria-describedby="preferred-enabled-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Enable Preferred Source Banner', 'signalkit-for-google'); ?></strong>
                                    <p id="preferred-enabled-desc"><?php esc_html_e('Display the Preferred Source banner on your site', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Google Preferences URL -->
                            <div class="signalkit-setting-row">
                                <label for="preferred_google_preferences_url"><?php esc_html_e('Google Preferences URL', 'signalkit-for-google'); ?> <span class="required" aria-label="<?php esc_attr_e('required', 'signalkit-for-google'); ?>">*</span></label>
                                <input type="url" 
                                       id="preferred_google_preferences_url"
                                       name="signalkit_settings[preferred_google_preferences_url]" 
                                       value="<?php echo esc_url($settings['preferred_google_preferences_url']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="https://news.google.com/preferences"
                                       aria-describedby="preferred-url-desc"
                                       required>
                                <p class="description" id="preferred-url-desc"><?php esc_html_e('Google News preferences URL where users can add your site as preferred', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Educational Post URL -->
                            <div class="signalkit-setting-row">
                                <label for="preferred_educational_post_url"><?php esc_html_e('Educational Post URL', 'signalkit-for-google'); ?></label>
                                <input type="url" 
                                       id="preferred_educational_post_url"
                                       name="signalkit_settings[preferred_educational_post_url]" 
                                       value="<?php echo esc_url($settings['preferred_educational_post_url']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="https://yoursite.com/how-to-add-preferred-sources"
                                       aria-describedby="preferred-edu-desc">
                                <p class="description" id="preferred-edu-desc"><?php esc_html_e('Link to a post explaining how to add preferred sources', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Show Educational Link -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[preferred_show_educational_link]" 
                                           value="1" 
                                           <?php checked($settings['preferred_show_educational_link'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="preferred"
                                           aria-describedby="preferred-show-edu-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Show Educational Link', 'signalkit-for-google'); ?></strong>
                                    <p id="preferred-show-edu-desc"><?php esc_html_e('Display a link to learn more about preferred sources', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Educational Link Text -->
                            <div class="signalkit-setting-row">
                                <label for="preferred_educational_text"><?php esc_html_e('Educational Link Text', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       id="preferred_educational_text"
                                       name="signalkit_settings[preferred_educational_text]" 
                                       value="<?php echo esc_attr($settings['preferred_educational_text']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="<?php echo esc_attr__('Learn More', 'signalkit-for-google'); ?>">
                            </div>
                            
                            <!-- Content Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-edit" aria-hidden="true"></span> <?php esc_html_e('Content', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Banner Headline -->
                            <div class="signalkit-setting-row">
                                <label for="preferred_banner_headline"><?php esc_html_e('Banner Headline', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       id="preferred_banner_headline"
                                       name="signalkit_settings[preferred_banner_headline]" 
                                       value="<?php echo esc_attr($settings['preferred_banner_headline']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="<?php echo esc_attr__('Add [site_name] As A Trusted Source', 'signalkit-for-google'); ?>"
                                       aria-describedby="preferred-headline-desc">
                                <p class="description" id="preferred-headline-desc"><?php esc_html_e('Use [site_name] as placeholder for your site name', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Banner Description -->
                            <div class="signalkit-setting-row">
                                <label for="preferred_banner_description"><?php esc_html_e('Banner Description', 'signalkit-for-google'); ?></label>
                                <textarea id="preferred_banner_description"
                                          name="signalkit_settings[preferred_banner_description]" 
                                          rows="3" 
                                          class="large-text signalkit-preview-trigger"
                                          data-banner="preferred"><?php echo esc_textarea($settings['preferred_banner_description']); ?></textarea>
                            </div>
                            
                            <!-- Button Text -->
                            <div class="signalkit-setting-row">
                                <label for="preferred_button_text"><?php esc_html_e('Button Text', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       id="preferred_button_text"
                                       name="signalkit_settings[preferred_button_text]" 
                                       value="<?php echo esc_attr($settings['preferred_button_text']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="<?php echo esc_attr__('Add As A Preferred Source', 'signalkit-for-google'); ?>">
                            </div>
                            
                            <!-- Design Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-art" aria-hidden="true"></span> <?php esc_html_e('Design & Appearance', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Color Settings -->
                            <div class="signalkit-setting-row signalkit-color-row">
                                <h4><?php esc_html_e('Colors', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-color-grid">
                                    <div class="signalkit-color-input">
                                        <label for="preferred_primary_color"><?php esc_html_e('Primary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="preferred_primary_color"
                                               name="signalkit_settings[preferred_primary_color]" 
                                               value="<?php echo esc_attr($settings['preferred_primary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label for="preferred_secondary_color"><?php esc_html_e('Secondary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="preferred_secondary_color"
                                               name="signalkit_settings[preferred_secondary_color]" 
                                               value="<?php echo esc_attr($settings['preferred_secondary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label for="preferred_accent_color"><?php esc_html_e('Accent Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="preferred_accent_color"
                                               name="signalkit_settings[preferred_accent_color]" 
                                               value="<?php echo esc_attr($settings['preferred_accent_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label for="preferred_text_color"><?php esc_html_e('Text Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               id="preferred_text_color"
                                               name="signalkit_settings[preferred_text_color]" 
                                               value="<?php echo esc_attr($settings['preferred_text_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Size & Spacing -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Size & Spacing', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-size-grid">
                                    <div class="signalkit-size-input">
                                        <label for="preferred_banner_width"><?php esc_html_e('Banner Width (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="preferred_banner_width"
                                               name="signalkit_settings[preferred_banner_width]" 
                                               value="<?php echo esc_attr($settings['preferred_banner_width']); ?>" 
                                               min="280" 
                                               max="600" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_banner_width']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label for="preferred_banner_padding"><?php esc_html_e('Padding (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="preferred_banner_padding"
                                               name="signalkit_settings[preferred_banner_padding]" 
                                               value="<?php echo esc_attr($settings['preferred_banner_padding']); ?>" 
                                               min="8" 
                                               max="32" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_banner_padding']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label for="preferred_border_radius"><?php esc_html_e('Border Radius (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="preferred_border_radius"
                                               name="signalkit_settings[preferred_border_radius]" 
                                               value="<?php echo esc_attr($settings['preferred_border_radius']); ?>" 
                                               min="0" 
                                               max="32" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_border_radius']); ?>px</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Typography -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Typography', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-typography-grid">
                                    <div class="signalkit-typography-input">
                                        <label for="preferred_font_size_headline"><?php esc_html_e('Headline Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="preferred_font_size_headline"
                                               name="signalkit_settings[preferred_font_size_headline]" 
                                               value="<?php echo esc_attr($settings['preferred_font_size_headline']); ?>" 
                                               min="12" 
                                               max="24" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_font_size_headline']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label for="preferred_font_size_description"><?php esc_html_e('Description Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="preferred_font_size_description"
                                               name="signalkit_settings[preferred_font_size_description]" 
                                               value="<?php echo esc_attr($settings['preferred_font_size_description']); ?>" 
                                               min="10" 
                                               max="18" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_font_size_description']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label for="preferred_font_size_button"><?php esc_html_e('Button Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               id="preferred_font_size_button"
                                               name="signalkit_settings[preferred_font_size_button]" 
                                               value="<?php echo esc_attr($settings['preferred_font_size_button']); ?>" 
                                               min="11" 
                                               max="18" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_font_size_button']); ?>px</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Display Settings Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-desktop" aria-hidden="true"></span> <?php esc_html_e('Display Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Position Settings -->
                            <div class="signalkit-setting-row">
                                <div class="signalkit-display-grid">
                                    <div class="signalkit-select-group">
                                        <label for="preferred_position"><?php esc_html_e('Desktop Position', 'signalkit-for-google'); ?></label>
                                        <select id="preferred_position" 
                                                name="signalkit_settings[preferred_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="preferred">
                                            <option value="bottom_left" <?php selected($settings['preferred_position'], 'bottom_left'); ?>><?php esc_html_e('Bottom Left', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_right" <?php selected($settings['preferred_position'], 'bottom_right'); ?>><?php esc_html_e('Bottom Right', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_center" <?php selected($settings['preferred_position'], 'bottom_center'); ?>><?php esc_html_e('Bottom Center', 'signalkit-for-google'); ?></option>
                                            <option value="top_left" <?php selected($settings['preferred_position'], 'top_left'); ?>><?php esc_html_e('Top Left', 'signalkit-for-google'); ?></option>
                                            <option value="top_right" <?php selected($settings['preferred_position'], 'top_right'); ?>><?php esc_html_e('Top Right', 'signalkit-for-google'); ?></option>
                                            <option value="top_center" <?php selected($settings['preferred_position'], 'top_center'); ?>><?php esc_html_e('Top Center', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="preferred_mobile_position"><?php esc_html_e('Mobile Position', 'signalkit-for-google'); ?></label>
                                        <select id="preferred_mobile_position" 
                                                name="signalkit_settings[preferred_mobile_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="preferred">
                                            <option value="top" <?php selected($settings['preferred_mobile_position'], 'top'); ?>><?php esc_html_e('Top of Screen', 'signalkit-for-google'); ?></option>
                                            <option value="bottom" <?php selected($settings['preferred_mobile_position'], 'bottom'); ?>><?php esc_html_e('Bottom of Screen', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="preferred_mobile_stack_order"><?php esc_html_e('Mobile Stack Order', 'signalkit-for-google'); ?></label>
                                        <select id="preferred_mobile_stack_order" 
                                                name="signalkit_settings[preferred_mobile_stack_order]">
                                            <option value="1" <?php selected($settings['preferred_mobile_stack_order'], 1); ?>><?php esc_html_e('First (Top/Bottom)', 'signalkit-for-google'); ?></option>
                                            <option value="2" <?php selected($settings['preferred_mobile_stack_order'], 2); ?>><?php esc_html_e('Second', 'signalkit-for-google'); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e('Order when both banners are active on mobile', 'signalkit-for-google'); ?></p>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="preferred_animation"><?php esc_html_e('Animation', 'signalkit-for-google'); ?></label>
                                        <select id="preferred_animation" 
                                                name="signalkit_settings[preferred_animation]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="preferred">
                                            <option value="slide_in" <?php selected($settings['preferred_animation'], 'slide_in'); ?>><?php esc_html_e('Slide In', 'signalkit-for-google'); ?></option>
                                            <option value="fade_in" <?php selected($settings['preferred_animation'], 'fade_in'); ?>><?php esc_html_e('Fade In', 'signalkit-for-google'); ?></option>
                                            <option value="bounce" <?php selected($settings['preferred_animation'], 'bounce'); ?>><?php esc_html_e('Bounce', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label for="preferred_show_frequency"><?php esc_html_e('Show Frequency', 'signalkit-for-google'); ?></label>
                                        <select id="preferred_show_frequency" 
                                                name="signalkit_settings[preferred_show_frequency]">
                                            <option value="always" <?php selected($settings['preferred_show_frequency'], 'always'); ?>><?php esc_html_e('Always', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_session" <?php selected($settings['preferred_show_frequency'], 'once_per_session'); ?>><?php esc_html_e('Once Per Session', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_day" <?php selected($settings['preferred_show_frequency'], 'once_per_day'); ?>><?php esc_html_e('Once Per Day', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dismissal Settings -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[preferred_dismissible]" 
                                           value="1" 
                                           <?php checked($settings['preferred_dismissible'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="preferred"
                                           aria-describedby="preferred-dismissible-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Allow Dismissal', 'signalkit-for-google'); ?></strong>
                                    <p id="preferred-dismissible-desc"><?php esc_html_e('Users can close the banner', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label for="preferred_dismiss_duration"><?php esc_html_e('Dismiss Duration (days)', 'signalkit-for-google'); ?></label>
                                <input type="number" 
                                       id="preferred_dismiss_duration"
                                       name="signalkit_settings[preferred_dismiss_duration]" 
                                       value="<?php echo esc_attr($settings['preferred_dismiss_duration']); ?>" 
                                       min="1" 
                                       max="365" 
                                       class="small-text"
                                       aria-describedby="preferred-dismiss-desc">
                                <p class="description" id="preferred-dismiss-desc"><?php esc_html_e('How long to hide the banner after dismissal', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Device Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Device Settings', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_mobile_enabled]" 
                                               value="1" 
                                               <?php checked($settings['preferred_mobile_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
                                        <?php esc_html_e('Show on Mobile', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_desktop_enabled]" 
                                               value="1" 
                                               <?php checked($settings['preferred_desktop_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="dashicons dashicons-desktop" aria-hidden="true"></span>
                                        <?php esc_html_e('Show on Desktop', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Page Type Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php esc_html_e('Page Types', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_posts]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_posts'], 1); ?>>
                                        <?php esc_html_e('Show on Posts', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_pages]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_pages'], 1); ?>>
                                        <?php esc_html_e('Show on Pages', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_homepage]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_homepage'], 1); ?>>
                                        <?php esc_html_e('Show on Homepage', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_archive]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_archive'], 1); ?>>
                                        <?php esc_html_e('Show on Archive Pages', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- GLOBAL SETTINGS TAB -->
                    <div class="signalkit-tab-content" id="tab-global" data-content="global" role="tabpanel">
                        <div class="signalkit-settings-grid">
                            
                            <div class="signalkit-setting-row">
                                <label for="site_name"><?php esc_html_e('Site Name', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       id="site_name"
                                       name="signalkit_settings[site_name]" 
                                       value="<?php echo esc_attr($settings['site_name']); ?>"

<div class="regular-text signalkit-preview-trigger"
                                       data-banner="both"
                                       placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>"
                                       aria-describedby="site-name-desc">
                                <p class="description" id="site-name-desc"><?php esc_html_e('Used in banner text when [site_name] placeholder is used', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <div class="signalkit-info-box">
                                <h3><?php esc_html_e('Plugin Information', 'signalkit-for-google'); ?></h3>
                                <p><strong><?php esc_html_e('Version:', 'signalkit-for-google'); ?></strong> <?php echo esc_html(SIGNALKIT_VERSION); ?></p>
                                <p><strong><?php esc_html_e('Status:', 'signalkit-for-google'); ?></strong> 
                                    <?php 
                                    $follow_enabled = !empty($settings['follow_enabled']);
                                    $preferred_enabled = !empty($settings['preferred_enabled']);
                                    if ($follow_enabled && $preferred_enabled) {
                                        echo '<span style="color: #46b450;">✓ ' . esc_html__('Both banners enabled', 'signalkit-for-google') . '</span>';
                                    } elseif ($follow_enabled) {
                                        echo '<span style="color: #46b450;">✓ ' . esc_html__('Follow banner enabled', 'signalkit-for-google') . '</span>';
                                    } elseif ($preferred_enabled) {
                                        echo '<span style="color: #46b450;">✓ ' . esc_html__('Preferred banner enabled', 'signalkit-for-google') . '</span>';
                                    } else {
                                        echo '<span style="color: #dc3232;">✗ ' . esc_html__('No banners enabled', 'signalkit-for-google') . '</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <!-- Mobile Stacking Information -->
                            <div class="signalkit-info-box" style="background: #fff3cd; border-left-color: #ffc107;">
                                <h3><span class="dashicons dashicons-smartphone" aria-hidden="true"></span> <?php esc_html_e('Mobile Display Information', 'signalkit-for-google'); ?></h3>
                                <p><?php esc_html_e('When both banners are enabled on mobile devices:', 'signalkit-for-google'); ?></p>
                                <ul style="margin-left: 20px; list-style: disc;">
                                    <li><?php esc_html_e('Banners will stack vertically based on their Mobile Stack Order', 'signalkit-for-google'); ?></li>
                                    <li><?php esc_html_e('Order 1 appears closest to the edge (top/bottom)', 'signalkit-for-google'); ?></li>
                                    <li><?php esc_html_e('Order 2 appears second with appropriate spacing', 'signalkit-for-google'); ?></li>
                                    <li><?php esc_html_e('Full-width display ensures optimal mobile experience', 'signalkit-for-google'); ?></li>
                                </ul>
                            </div>
                            
                            <!-- Import/Export Settings -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-download" aria-hidden="true"></span> <?php esc_html_e('Import/Export Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <p class="description"><?php esc_html_e('Backup and restore your settings easily', 'signalkit-for-google'); ?></p>
                                <div class="signalkit-import-export">
                                    <button type="button" class="button button-secondary signalkit-export-settings">
                                        <span class="dashicons dashicons-download" aria-hidden="true"></span>
                                        <?php esc_html_e('Export Settings', 'signalkit-for-google'); ?>
                                    </button>
                                    <input type="file" id="signalkit-import-file" accept=".json" style="display: none;" aria-label="<?php esc_attr_e('Choose settings file to import', 'signalkit-for-google'); ?>">
                                    <button type="button" class="button button-secondary signalkit-import-settings">
                                        <span class="dashicons dashicons-upload" aria-hidden="true"></span>
                                        <?php esc_html_e('Import Settings', 'signalkit-for-google'); ?>
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- ADVANCED & SECURITY TAB -->
                    <div class="signalkit-tab-content" id="tab-advanced" data-content="advanced" role="tabpanel">
                        <div class="signalkit-settings-grid">
                            
                            <!-- Security Settings -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-shield-alt" aria-hidden="true"></span> <?php esc_html_e('Security Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[enable_csp]" 
                                           value="1" 
                                           <?php checked($settings['enable_csp'], 1); ?>
                                           aria-describedby="csp-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Enable Content Security Policy (CSP)', 'signalkit-for-google'); ?></strong>
                                    <p id="csp-desc"><?php esc_html_e('Add CSP headers to prevent XSS attacks on banners', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[enable_rate_limiting]" 
                                           value="1" 
                                           <?php checked($settings['enable_rate_limiting'], 1); ?>
                                           aria-describedby="rate-limit-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Enable Rate Limiting for Analytics', 'signalkit-for-google'); ?></strong>
                                    <p id="rate-limit-desc"><?php esc_html_e('Limit AJAX requests to prevent abuse', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Analytics Settings -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-chart-line" aria-hidden="true"></span> <?php esc_html_e('Analytics Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[analytics_tracking]" 
                                           value="1" 
                                           <?php checked($settings['analytics_tracking'], 1); ?>
                                           aria-describedby="analytics-desc">
                                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php esc_html_e('Enable Analytics Tracking', 'signalkit-for-google'); ?></strong>
                                    <p id="analytics-desc"><?php esc_html_e('Track impressions, clicks, and dismissals for both banners', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Import/Export Key -->
                            <div class="signalkit-setting-row">
                                <label for="import_export_key"><?php esc_html_e('Import/Export Encryption Key', 'signalkit-for-google'); ?></label>
                                <input type="password" 
                                       id="import_export_key"
                                       name="signalkit_settings[import_export_key]" 
                                       value="<?php echo esc_attr($settings['import_export_key']); ?>" 
                                       class="regular-text"
                                       placeholder="<?php esc_attr_e('Leave empty for auto-generated', 'signalkit-for-google'); ?>"
                                       aria-describedby="export-key-desc"
                                       autocomplete="off">
                                <p class="description" id="export-key-desc"><?php esc_html_e('Optional key for encrypting exported settings (for Envato/WordPress.org compatibility)', 'signalkit-for-google'); ?></p>
                            </div>
                            
                        </div>
                    </div>
                    
                    <?php submit_button(__('Save Settings', 'signalkit-for-google'), 'primary signalkit-submit-button'); ?>
                    
                </form>
            </div>
        </div>
        
        <!-- RIGHT: Live Preview Panel -->
        <div class="signalkit-preview-panel">
            <div class="signalkit-preview-header">
                <h3><span class="dashicons dashicons-visibility" aria-hidden="true"></span> <?php esc_html_e('Live Preview', 'signalkit-for-google'); ?></h3>
                <div class="signalkit-preview-controls" role="group" aria-label="<?php esc_attr_e('Preview device switcher', 'signalkit-for-google'); ?>">
                    <button type="button" class="button signalkit-preview-device active" data-device="desktop" aria-pressed="true">
                        <span class="dashicons dashicons-desktop" aria-hidden="true"></span>
                        <?php esc_html_e('Desktop', 'signalkit-for-google'); ?>
                    </button>
                    <button type="button" class="button signalkit-preview-device" data-device="mobile" aria-pressed="false">
                        <span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
                        <?php esc_html_e('Mobile', 'signalkit-for-google'); ?>
                    </button>
                </div>
            </div>
            
            <div class="signalkit-preview-viewport" data-device="desktop">
                <div class="signalkit-preview-screen">
                    <div class="signalkit-preview-content">
                        <div class="signalkit-preview-placeholder">
                            <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                            <p><?php esc_html_e('Your website content appears here', 'signalkit-for-google'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Preview Banners -->
                    <div id="signalkit-preview-follow" class="signalkit-preview-banner" style="display: none;" role="region" aria-label="<?php esc_attr_e('Follow banner preview', 'signalkit-for-google'); ?>">
                        <!-- Follow banner preview will be dynamically generated via JavaScript -->
                    </div>
                    
                    <div id="signalkit-preview-preferred" class="signalkit-preview-banner" style="display: none;" role="region" aria-label="<?php esc_attr_e('Preferred source banner preview', 'signalkit-for-google'); ?>">
                        <!-- Preferred banner preview will be dynamically generated via JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="signalkit-preview-info">
                <p class="description">
                    <span class="dashicons dashicons-info" aria-hidden="true"></span>
                    <?php esc_html_e('Changes appear instantly in the preview. Click Save Settings to apply changes to your live site.', 'signalkit-for-google'); ?>
                </p>
            </div>
        </div>
        
    </div>
    
    <!-- Hidden data for preview JavaScript - WordPress best practice for passing PHP data to JS -->
    <script type="text/javascript">
    /* <![CDATA[ */
    var signalkitPreviewData = <?php echo wp_json_encode($settings); ?>;
    var signalkitNonce = <?php echo wp_json_encode(wp_create_nonce('signalkit_preview_nonce')); ?>;
    var signalkitAjaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
    /* ]]> */
    </script>
    
</div>