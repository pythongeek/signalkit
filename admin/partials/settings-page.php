<?php
/**
 * Settings Page Template - ENHANCED WITH LIVE PREVIEW
 *
 * @package SignalKit_For_Google
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = get_option('signalkit_settings', array());

// Enhanced defaults with new customization options
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
    // NEW: Size & Typography
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
    // NEW: Size & Typography
    'preferred_banner_width' => 360,
    'preferred_banner_padding' => 16,
    'preferred_font_size_headline' => 15,
    'preferred_font_size_description' => 13,
    'preferred_font_size_button' => 14,
    'preferred_border_radius' => 8,
    'preferred_mobile_position' => 'bottom',
    'preferred_mobile_stack_order' => 2,
    
    // NEW: Security & Advanced Features
    'enable_csp' => 1,
    'enable_rate_limiting' => 1,
    'analytics_tracking' => 1,
    'import_export_key' => '',
);

$settings = wp_parse_args($settings, $defaults);

// Security: Nonce for form
wp_nonce_field('signalkit_settings_nonce', 'signalkit_nonce');
?>

<div class="wrap signalkit-settings-page">
    <h1><?php _e('SignalKit for Google - Settings', 'signalkit-for-google'); ?></h1>
    
    <?php settings_errors('signalkit_settings'); ?>
    
    <!-- NEW: Layout with Preview Panel -->
    <div class="signalkit-layout-container">
        
        <!-- LEFT: Settings Panel -->
        <div class="signalkit-settings-panel">
            
            <div class="signalkit-tabs-container">
                <nav class="signalkit-tabs">
                    <button class="signalkit-tab active" data-tab="follow">
                        <span class="dashicons dashicons-bell"></span>
                        <?php _e('Follow Banner', 'signalkit-for-google'); ?>
                    </button>
                    <button class="signalkit-tab" data-tab="preferred">
                        <span class="dashicons dashicons-star-filled"></span>
                        <?php _e('Preferred Source', 'signalkit-for-google'); ?>
                    </button>
                    <button class="signalkit-tab" data-tab="global">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php _e('Global Settings', 'signalkit-for-google'); ?>
                    </button>
                    <button class="signalkit-tab" data-tab="advanced">
                        <span class="dashicons dashicons-shield-alt"></span>
                        <?php _e('Advanced & Security', 'signalkit-for-google'); ?>
                    </button>
                </nav>
                
                <form method="post" action="options.php" class="signalkit-form" novalidate>
                    <?php settings_fields('signalkit_settings_group'); ?>
                    
                    <!-- FOLLOW BANNER TAB -->
                    <div class="signalkit-tab-content active" data-content="follow">
                        <div class="signalkit-settings-grid">
                            
                            <!-- Enable/Disable -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[follow_enabled]" 
                                           value="1" 
                                           <?php checked($settings['follow_enabled'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="follow">
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Enable Follow Banner', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Display the Google News Follow banner on your site', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Google News URL -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Google News URL', 'signalkit-for-google'); ?> <span class="required">*</span></label>
                                <input type="url" 
                                       name="signalkit_settings[follow_google_news_url]" 
                                       value="<?php echo esc_url($settings['follow_google_news_url']); ?>" 
                                       class="regular-text signalkit-preview-trigger" 
                                       data-banner="follow"
                                       placeholder="https://news.google.com/publications/..."
                                       required>
                                <p class="description"><?php _e('Your Google News publication URL', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Content Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-edit"></span> <?php _e('Content', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Banner Headline -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Banner Headline', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       name="signalkit_settings[follow_banner_headline]" 
                                       value="<?php echo esc_attr($settings['follow_banner_headline']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="follow"
                                       placeholder="<?php echo esc_attr__('Stay Updated with [site_name]', 'signalkit-for-google'); ?>">
                                <p class="description"><?php _e('Use [site_name] as placeholder for your site name', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Banner Description -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Banner Description', 'signalkit-for-google'); ?></label>
                                <textarea name="signalkit_settings[follow_banner_description]" 
                                          rows="3" 
                                          class="large-text signalkit-preview-trigger"
                                          data-banner="follow"><?php echo esc_textarea($settings['follow_banner_description']); ?></textarea>
                            </div>
                            
                            <!-- Button Text -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Button Text', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       name="signalkit_settings[follow_button_text]" 
                                       value="<?php echo esc_attr($settings['follow_button_text']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="follow"
                                       placeholder="<?php echo esc_attr__('Follow Us On Google News', 'signalkit-for-google'); ?>">
                            </div>
                            
                            <!-- Design Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-art"></span> <?php _e('Design & Appearance', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Color Settings -->
                            <div class="signalkit-setting-row signalkit-color-row">
                                <h4><?php _e('Colors', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-color-grid">
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Primary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[follow_primary_color]" 
                                               value="<?php echo esc_attr($settings['follow_primary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Secondary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[follow_secondary_color]" 
                                               value="<?php echo esc_attr($settings['follow_secondary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Accent Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[follow_accent_color]" 
                                               value="<?php echo esc_attr($settings['follow_accent_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Text Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[follow_text_color]" 
                                               value="<?php echo esc_attr($settings['follow_text_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="follow">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- NEW: Size & Spacing -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Size & Spacing', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-size-grid">
                                    <div class="signalkit-size-input">
                                        <label><?php _e('Banner Width (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[follow_banner_width]" 
                                               value="<?php echo esc_attr($settings['follow_banner_width']); ?>" 
                                               min="280" 
                                               max="600" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_banner_width']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label><?php _e('Padding (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[follow_banner_padding]" 
                                               value="<?php echo esc_attr($settings['follow_banner_padding']); ?>" 
                                               min="8" 
                                               max="32" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_banner_padding']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label><?php _e('Border Radius (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
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
                            
                            <!-- NEW: Typography -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Typography', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-typography-grid">
                                    <div class="signalkit-typography-input">
                                        <label><?php _e('Headline Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[follow_font_size_headline]" 
                                               value="<?php echo esc_attr($settings['follow_font_size_headline']); ?>" 
                                               min="12" 
                                               max="24" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_font_size_headline']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label><?php _e('Description Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[follow_font_size_description]" 
                                               value="<?php echo esc_attr($settings['follow_font_size_description']); ?>" 
                                               min="10" 
                                               max="18" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['follow_font_size_description']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label><?php _e('Button Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
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
                                <h3><span class="dashicons dashicons-desktop"></span> <?php _e('Display Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Position Settings -->
                            <div class="signalkit-setting-row">
                                <div class="signalkit-display-grid">
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Desktop Position', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[follow_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="follow">
                                            <option value="bottom_left" <?php selected($settings['follow_position'], 'bottom_left'); ?>><?php _e('Bottom Left', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_right" <?php selected($settings['follow_position'], 'bottom_right'); ?>><?php _e('Bottom Right', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_center" <?php selected($settings['follow_position'], 'bottom_center'); ?>><?php _e('Bottom Center', 'signalkit-for-google'); ?></option>
                                            <option value="top_left" <?php selected($settings['follow_position'], 'top_left'); ?>><?php _e('Top Left', 'signalkit-for-google'); ?></option>
                                            <option value="top_right" <?php selected($settings['follow_position'], 'top_right'); ?>><?php _e('Top Right', 'signalkit-for-google'); ?></option>
                                            <option value="top_center" <?php selected($settings['follow_position'], 'top_center'); ?>><?php _e('Top Center', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <!-- NEW: Mobile Position -->
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Mobile Position', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[follow_mobile_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="follow">
                                            <option value="top" <?php selected($settings['follow_mobile_position'], 'top'); ?>><?php _e('Top of Screen', 'signalkit-for-google'); ?></option>
                                            <option value="bottom" <?php selected($settings['follow_mobile_position'], 'bottom'); ?>><?php _e('Bottom of Screen', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <!-- NEW: Mobile Stack Order -->
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Mobile Stack Order', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[follow_mobile_stack_order]">
                                            <option value="1" <?php selected($settings['follow_mobile_stack_order'], 1); ?>><?php _e('First (Top/Bottom)', 'signalkit-for-google'); ?></option>
                                            <option value="2" <?php selected($settings['follow_mobile_stack_order'], 2); ?>><?php _e('Second', 'signalkit-for-google'); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Order when both banners are active on mobile', 'signalkit-for-google'); ?></p>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Animation', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[follow_animation]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="follow">
                                            <option value="slide_in" <?php selected($settings['follow_animation'], 'slide_in'); ?>><?php _e('Slide In', 'signalkit-for-google'); ?></option>
                                            <option value="fade_in" <?php selected($settings['follow_animation'], 'fade_in'); ?>><?php _e('Fade In', 'signalkit-for-google'); ?></option>
                                            <option value="bounce" <?php selected($settings['follow_animation'], 'bounce'); ?>><?php _e('Bounce', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Show Frequency', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[follow_show_frequency]">
                                            <option value="always" <?php selected($settings['follow_show_frequency'], 'always'); ?>><?php _e('Always', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_session" <?php selected($settings['follow_show_frequency'], 'once_per_session'); ?>><?php _e('Once Per Session', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_day" <?php selected($settings['follow_show_frequency'], 'once_per_day'); ?>><?php _e('Once Per Day', 'signalkit-for-google'); ?></option>
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
                                           data-banner="follow">
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Allow Dismissal', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Users can close the banner', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label><?php _e('Dismiss Duration (days)', 'signalkit-for-google'); ?></label>
                                <input type="number" 
                                       name="signalkit_settings[follow_dismiss_duration]" 
                                       value="<?php echo esc_attr($settings['follow_dismiss_duration']); ?>" 
                                       min="1" 
                                       max="365" 
                                       class="small-text">
                                <p class="description"><?php _e('How long to hide the banner after dismissal', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Device Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Device Settings', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_mobile_enabled]" 
                                               value="1" 
                                               <?php checked($settings['follow_mobile_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="dashicons dashicons-smartphone"></span>
                                        <?php _e('Show on Mobile', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_desktop_enabled]" 
                                               value="1" 
                                               <?php checked($settings['follow_desktop_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="follow">
                                        <span class="dashicons dashicons-desktop"></span>
                                        <?php _e('Show on Desktop', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Page Type Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Page Types', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_posts]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_posts'], 1); ?>>
                                        <?php _e('Show on Posts', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_pages]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_pages'], 1); ?>>
                                        <?php _e('Show on Pages', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_homepage]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_homepage'], 1); ?>>
                                        <?php _e('Show on Homepage', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[follow_show_on_archive]" 
                                               value="1" 
                                               <?php checked($settings['follow_show_on_archive'], 1); ?>>
                                        <?php _e('Show on Archive Pages', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- PREFERRED SOURCE BANNER TAB -->
                    <div class="signalkit-tab-content" data-content="preferred">
                        <div class="signalkit-settings-grid">
                            
                            <!-- Enable/Disable -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[preferred_enabled]" 
                                           value="1" 
                                           <?php checked($settings['preferred_enabled'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="preferred">
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Enable Preferred Source Banner', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Display the Preferred Source banner on your site', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Google Preferences URL -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Google Preferences URL', 'signalkit-for-google'); ?> <span class="required">*</span></label>
                                <input type="url" 
                                       name="signalkit_settings[preferred_google_preferences_url]" 
                                       value="<?php echo esc_url($settings['preferred_google_preferences_url']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="https://news.google.com/preferences"
                                       required>
                                <p class="description"><?php _e('Google News preferences URL where users can add your site as preferred', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Educational Post URL -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Educational Post URL', 'signalkit-for-google'); ?></label>
                                <input type="url" 
                                       name="signalkit_settings[preferred_educational_post_url]" 
                                       value="<?php echo esc_url($settings['preferred_educational_post_url']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="https://yoursite.com/how-to-add-preferred-sources">
                                <p class="description"><?php _e('Link to a post explaining how to add preferred sources', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Show Educational Link -->
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[preferred_show_educational_link]" 
                                           value="1" 
                                           <?php checked($settings['preferred_show_educational_link'], 1); ?>
                                           class="signalkit-preview-trigger"
                                           data-banner="preferred">
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Show Educational Link', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Display a link to learn more about preferred sources', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Educational Link Text -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Educational Link Text', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       name="signalkit_settings[preferred_educational_text]" 
                                       value="<?php echo esc_attr($settings['preferred_educational_text']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="<?php echo esc_attr__('Learn More', 'signalkit-for-google'); ?>">
                            </div>
                            
                            <!-- Content Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-edit"></span> <?php _e('Content', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Banner Headline -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Banner Headline', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       name="signalkit_settings[preferred_banner_headline]" 
                                       value="<?php echo esc_attr($settings['preferred_banner_headline']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="<?php echo esc_attr__('Add [site_name] As A Trusted Source', 'signalkit-for-google'); ?>">
                                <p class="description"><?php _e('Use [site_name] as placeholder for your site name', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Banner Description -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Banner Description', 'signalkit-for-google'); ?></label>
                                <textarea name="signalkit_settings[preferred_banner_description]" 
                                          rows="3" 
                                          class="large-text signalkit-preview-trigger"
                                          data-banner="preferred"><?php echo esc_textarea($settings['preferred_banner_description']); ?></textarea>
                            </div>
                            
                            <!-- Button Text -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Button Text', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       name="signalkit_settings[preferred_button_text]" 
                                       value="<?php echo esc_attr($settings['preferred_button_text']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="preferred"
                                       placeholder="<?php echo esc_attr__('Add As A Preferred Source', 'signalkit-for-google'); ?>">
                            </div>
                            
                            <!-- Design Section -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-art"></span> <?php _e('Design & Appearance', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Color Settings -->
                            <div class="signalkit-setting-row signalkit-color-row">
                                <h4><?php _e('Colors', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-color-grid">
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Primary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[preferred_primary_color]" 
                                               value="<?php echo esc_attr($settings['preferred_primary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Secondary Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[preferred_secondary_color]" 
                                               value="<?php echo esc_attr($settings['preferred_secondary_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Accent Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[preferred_accent_color]" 
                                               value="<?php echo esc_attr($settings['preferred_accent_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                    <div class="signalkit-color-input">
                                        <label><?php _e('Text Color', 'signalkit-for-google'); ?></label>
                                        <input type="text" 
                                               name="signalkit_settings[preferred_text_color]" 
                                               value="<?php echo esc_attr($settings['preferred_text_color']); ?>" 
                                               class="signalkit-color-picker signalkit-preview-trigger"
                                               data-banner="preferred">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- NEW: Size & Spacing -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Size & Spacing', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-size-grid">
                                    <div class="signalkit-size-input">
                                        <label><?php _e('Banner Width (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[preferred_banner_width]" 
                                               value="<?php echo esc_attr($settings['preferred_banner_width']); ?>" 
                                               min="280" 
                                               max="600" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_banner_width']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label><?php _e('Padding (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[preferred_banner_padding]" 
                                               value="<?php echo esc_attr($settings['preferred_banner_padding']); ?>" 
                                               min="8" 
                                               max="32" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_banner_padding']); ?>px</span>
                                    </div>
                                    <div class="signalkit-size-input">
                                        <label><?php _e('Border Radius (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
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
                            
                            <!-- NEW: Typography -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Typography', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-typography-grid">
                                    <div class="signalkit-typography-input">
                                        <label><?php _e('Headline Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[preferred_font_size_headline]" 
                                               value="<?php echo esc_attr($settings['preferred_font_size_headline']); ?>" 
                                               min="12" 
                                               max="24" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_font_size_headline']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label><?php _e('Description Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
                                               name="signalkit_settings[preferred_font_size_description]" 
                                               value="<?php echo esc_attr($settings['preferred_font_size_description']); ?>" 
                                               min="10" 
                                               max="18" 
                                               class="small-text signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="signalkit-range-value"><?php echo esc_html($settings['preferred_font_size_description']); ?>px</span>
                                    </div>
                                    <div class="signalkit-typography-input">
                                        <label><?php _e('Button Size (px)', 'signalkit-for-google'); ?></label>
                                        <input type="number" 
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
                                <h3><span class="dashicons dashicons-desktop"></span> <?php _e('Display Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <!-- Position Settings -->
                            <div class="signalkit-setting-row">
                                <div class="signalkit-display-grid">
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Desktop Position', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[preferred_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="preferred">
                                            <option value="bottom_left" <?php selected($settings['preferred_position'], 'bottom_left'); ?>><?php _e('Bottom Left', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_right" <?php selected($settings['preferred_position'], 'bottom_right'); ?>><?php _e('Bottom Right', 'signalkit-for-google'); ?></option>
                                            <option value="bottom_center" <?php selected($settings['preferred_position'], 'bottom_center'); ?>><?php _e('Bottom Center', 'signalkit-for-google'); ?></option>
                                            <option value="top_left" <?php selected($settings['preferred_position'], 'top_left'); ?>><?php _e('Top Left', 'signalkit-for-google'); ?></option>
                                            <option value="top_right" <?php selected($settings['preferred_position'], 'top_right'); ?>><?php _e('Top Right', 'signalkit-for-google'); ?></option>
                                            <option value="top_center" <?php selected($settings['preferred_position'], 'top_center'); ?>><?php _e('Top Center', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <!-- NEW: Mobile Position -->
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Mobile Position', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[preferred_mobile_position]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="preferred">
                                            <option value="top" <?php selected($settings['preferred_mobile_position'], 'top'); ?>><?php _e('Top of Screen', 'signalkit-for-google'); ?></option>
                                            <option value="bottom" <?php selected($settings['preferred_mobile_position'], 'bottom'); ?>><?php _e('Bottom of Screen', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <!-- NEW: Mobile Stack Order -->
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Mobile Stack Order', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[preferred_mobile_stack_order]">
                                            <option value="1" <?php selected($settings['preferred_mobile_stack_order'], 1); ?>><?php _e('First (Top/Bottom)', 'signalkit-for-google'); ?></option>
                                            <option value="2" <?php selected($settings['preferred_mobile_stack_order'], 2); ?>><?php _e('Second', 'signalkit-for-google'); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Order when both banners are active on mobile', 'signalkit-for-google'); ?></p>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Animation', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[preferred_animation]" 
                                                class="signalkit-preview-trigger"
                                                data-banner="preferred">
                                            <option value="slide_in" <?php selected($settings['preferred_animation'], 'slide_in'); ?>><?php _e('Slide In', 'signalkit-for-google'); ?></option>
                                            <option value="fade_in" <?php selected($settings['preferred_animation'], 'fade_in'); ?>><?php _e('Fade In', 'signalkit-for-google'); ?></option>
                                            <option value="bounce" <?php selected($settings['preferred_animation'], 'bounce'); ?>><?php _e('Bounce', 'signalkit-for-google'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="signalkit-select-group">
                                        <label><?php _e('Show Frequency', 'signalkit-for-google'); ?></label>
                                        <select name="signalkit_settings[preferred_show_frequency]">
                                            <option value="always" <?php selected($settings['preferred_show_frequency'], 'always'); ?>><?php _e('Always', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_session" <?php selected($settings['preferred_show_frequency'], 'once_per_session'); ?>><?php _e('Once Per Session', 'signalkit-for-google'); ?></option>
                                            <option value="once_per_day" <?php selected($settings['preferred_show_frequency'], 'once_per_day'); ?>><?php _e('Once Per Day', 'signalkit-for-google'); ?></option>
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
                                           data-banner="preferred">
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Allow Dismissal', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Users can close the banner', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label><?php _e('Dismiss Duration (days)', 'signalkit-for-google'); ?></label>
                                <input type="number" 
                                       name="signalkit_settings[preferred_dismiss_duration]" 
                                       value="<?php echo esc_attr($settings['preferred_dismiss_duration']); ?>" 
                                       min="1" 
                                       max="365" 
                                       class="small-text">
                                <p class="description"><?php _e('How long to hide the banner after dismissal', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <!-- Device Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Device Settings', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_mobile_enabled]" 
                                               value="1" 
                                               <?php checked($settings['preferred_mobile_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="dashicons dashicons-smartphone"></span>
                                        <?php _e('Show on Mobile', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_desktop_enabled]" 
                                               value="1" 
                                               <?php checked($settings['preferred_desktop_enabled'], 1); ?>
                                               class="signalkit-preview-trigger"
                                               data-banner="preferred">
                                        <span class="dashicons dashicons-desktop"></span>
                                        <?php _e('Show on Desktop', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Page Type Settings -->
                            <div class="signalkit-setting-row">
                                <h4><?php _e('Page Types', 'signalkit-for-google'); ?></h4>
                                <div class="signalkit-checkbox-group">
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_posts]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_posts'], 1); ?>>
                                        <?php _e('Show on Posts', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_pages]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_pages'], 1); ?>>
                                        <?php _e('Show on Pages', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_homepage]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_homepage'], 1); ?>>
                                        <?php _e('Show on Homepage', 'signalkit-for-google'); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" 
                                               name="signalkit_settings[preferred_show_on_archive]" 
                                               value="1" 
                                               <?php checked($settings['preferred_show_on_archive'], 1); ?>>
                                        <?php _e('Show on Archive Pages', 'signalkit-for-google'); ?>
                                    </label>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- GLOBAL SETTINGS TAB -->
                    <div class="signalkit-tab-content" data-content="global">
                        <div class="signalkit-settings-grid">
                            
                            <div class="signalkit-setting-row">
                                <label><?php _e('Site Name', 'signalkit-for-google'); ?></label>
                                <input type="text" 
                                       name="signalkit_settings[site_name]" 
                                       value="<?php echo esc_attr($settings['site_name']); ?>" 
                                       class="regular-text signalkit-preview-trigger"
                                       data-banner="both"
                                       placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>">
                                <p class="description"><?php _e('Used in banner text when [site_name] placeholder is used', 'signalkit-for-google'); ?></p>
                            </div>
                            
                            <div class="signalkit-info-box">
                                <h3><?php _e('Plugin Information', 'signalkit-for-google'); ?></h3>
                                <p><strong><?php _e('Version:', 'signalkit-for-google'); ?></strong> <?php echo SIGNALKIT_VERSION; ?></p>
                                <p><strong><?php _e('Status:', 'signalkit-for-google'); ?></strong> 
                                    <?php 
                                    $follow_enabled = !empty($settings['follow_enabled']);
                                    $preferred_enabled = !empty($settings['preferred_enabled']);
                                    if ($follow_enabled && $preferred_enabled) {
                                        echo '<span style="color: #46b450;">✓ Both banners enabled</span>';
                                    } elseif ($follow_enabled) {
                                        echo '<span style="color: #46b450;">✓ Follow banner enabled</span>';
                                    } elseif ($preferred_enabled) {
                                        echo '<span style="color: #46b450;">✓ Preferred banner enabled</span>';
                                    } else {
                                        echo '<span style="color: #dc3232;">✗ No banners enabled</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <!-- NEW: Mobile Stacking Information -->
                            <div class="signalkit-info-box" style="background: #fff3cd; border-left-color: #ffc107;">
                                <h3><span class="dashicons dashicons-smartphone"></span> <?php _e('Mobile Display Information', 'signalkit-for-google'); ?></h3>
                                <p><?php _e('When both banners are enabled on mobile devices:', 'signalkit-for-google'); ?></p>
                                <ul style="margin-left: 20px; list-style: disc;">
                                    <li><?php _e('Banners will stack vertically based on their Mobile Stack Order', 'signalkit-for-google'); ?></li>
                                    <li><?php _e('Order 1 appears closest to the edge (top/bottom)', 'signalkit-for-google'); ?></li>
                                    <li><?php _e('Order 2 appears second with appropriate spacing', 'signalkit-for-google'); ?></li>
                                    <li><?php _e('Full-width display ensures optimal mobile experience', 'signalkit-for-google'); ?></li>
                                </ul>
                            </div>
                            
                            <!-- NEW: Import/Export Settings -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-download"></span> <?php _e('Import/Export Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <p class="description"><?php _e('Backup and restore your settings easily', 'signalkit-for-google'); ?></p>
                                <div class="signalkit-import-export">
                                    <button type="button" class="button button-secondary signalkit-export-settings"><?php _e('Export Settings', 'signalkit-for-google'); ?></button>
                                    <input type="file" id="signalkit-import-file" accept=".json" style="display: none;">
                                    <button type="button" class="button button-secondary signalkit-import-settings"><?php _e('Import Settings', 'signalkit-for-google'); ?></button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- ADVANCED & SECURITY TAB -->
                    <div class="signalkit-tab-content" data-content="advanced">
                        <div class="signalkit-settings-grid">
                            
                            <!-- Security Settings -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-shield-alt"></span> <?php _e('Security Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[enable_csp]" 
                                           value="1" 
                                           <?php checked($settings['enable_csp'], 1); ?>>
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Enable Content Security Policy (CSP)', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Add CSP headers to prevent XSS attacks on banners', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[enable_rate_limiting]" 
                                           value="1" 
                                           <?php checked($settings['enable_rate_limiting'], 1); ?>>
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Enable Rate Limiting for Analytics', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Limit AJAX requests to prevent abuse', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Analytics Settings -->
                            <div class="signalkit-section-header">
                                <h3><span class="dashicons dashicons-chart-line"></span> <?php _e('Analytics Settings', 'signalkit-for-google'); ?></h3>
                            </div>
                            
                            <div class="signalkit-setting-row">
                                <label class="signalkit-toggle">
                                    <input type="checkbox" 
                                           name="signalkit_settings[analytics_tracking]" 
                                           value="1" 
                                           <?php checked($settings['analytics_tracking'], 1); ?>>
                                    <span class="signalkit-toggle-slider"></span>
                                </label>
                                <div class="signalkit-setting-info">
                                    <strong><?php _e('Enable Analytics Tracking', 'signalkit-for-google'); ?></strong>
                                    <p><?php _e('Track impressions, clicks, and dismissals for both banners', 'signalkit-for-google'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Import/Export Key -->
                            <div class="signalkit-setting-row">
                                <label><?php _e('Import/Export Encryption Key', 'signalkit-for-google'); ?></label>
                                <input type="password" 
                                       name="signalkit_settings[import_export_key]" 
                                       value="<?php echo esc_attr($settings['import_export_key']); ?>" 
                                       class="regular-text"
                                       placeholder="<?php _e('Leave empty for auto-generated', 'signalkit-for-google'); ?>">
                                <p class="description"><?php _e('Optional key for encrypting exported settings (for Envato/WordPress.org compatibility)', 'signalkit-for-google'); ?></p>
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
                <h3><span class="dashicons dashicons-visibility"></span> <?php _e('Live Preview', 'signalkit-for-google'); ?></h3>
                <div class="signalkit-preview-controls">
                    <button type="button" class="button signalkit-preview-device active" data-device="desktop">
                        <span class="dashicons dashicons-desktop"></span>
                        <?php _e('Desktop', 'signalkit-for-google'); ?>
                    </button>
                    <button type="button" class="button signalkit-preview-device" data-device="mobile">
                        <span class="dashicons dashicons-smartphone"></span>
                        <?php _e('Mobile', 'signalkit-for-google'); ?>
                    </button>
                </div>
            </div>
            
            <div class="signalkit-preview-viewport" data-device="desktop">
                <div class="signalkit-preview-screen">
                    <div class="signalkit-preview-content">
                        <div class="signalkit-preview-placeholder">
                            <span class="dashicons dashicons-admin-page"></span>
                            <p><?php _e('Your website content appears here', 'signalkit-for-google'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Preview Banners -->
                    <div id="signalkit-preview-follow" class="signalkit-preview-banner" style="display: none;">
                        <!-- Follow banner preview will be dynamically generated -->
                    </div>
                    
                    <div id="signalkit-preview-preferred" class="signalkit-preview-banner" style="display: none;">
                        <!-- Preferred banner preview will be dynamically generated -->
                    </div>
                </div>
            </div>
            
            <div class="signalkit-preview-info">
                <p class="description">
                    <span class="dashicons dashicons-info"></span>
                    <?php _e('Changes appear instantly in the preview. Click Save Settings to apply changes to your live site.', 'signalkit-for-google'); ?>
                </p>
            </div>
        </div>
        
    </div>
    
    <!-- Hidden data for preview JavaScript -->
    <script type="text/javascript">
    var signalkitPreviewData = <?php echo wp_json_encode($settings); ?>;
    var signalkitNonce = '<?php echo wp_create_nonce('signalkit_preview_nonce'); ?>';
    </script>
    
</div>