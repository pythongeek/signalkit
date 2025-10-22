<?php
/**
 * Settings Page Template - COMPLETE FIXED VERSION
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = get_option('signalkit_settings', array());

// Ensure we have defaults
$defaults = array(
    'site_name' => get_bloginfo('name'),
    'follow_enabled' => 0,
    'follow_google_news_url' => '',
    'follow_button_text' => __('Follow Us On Google News', 'signalkit-for-google'),
    'follow_banner_headline' => __('Stay Updated with [site_name]', 'signalkit-for-google'),
    'follow_banner_description' => __('Follow us on Google News to get the latest stories directly in your feed.', 'signalkit-for-google'),
    'follow_primary_color' => '#4285f4',
    'follow_secondary_color' => '#ffffff',
    'follow_accent_color' => '#34a853',
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
);

$settings = wp_parse_args($settings, $defaults);
?>

<div class="wrap signalkit-settings-page">
    <h1><?php _e('SignalKit for Google - Settings', 'signalkit-for-google'); ?></h1>
    
    <?php settings_errors('signalkit_settings'); ?>
    
    <div class="signalkit-tabs-container">
        <nav class="signalkit-tabs">
            <button class="signalkit-tab active" data-tab="follow">
                <?php _e('Follow Banner', 'signalkit-for-google'); ?>
            </button>
            <button class="signalkit-tab" data-tab="preferred">
                <?php _e('Preferred Source Banner', 'signalkit-for-google'); ?>
            </button>
            <button class="signalkit-tab" data-tab="global">
                <?php _e('Global Settings', 'signalkit-for-google'); ?>
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
                            <input type="checkbox" name="signalkit_settings[follow_enabled]" value="1" 
                                   <?php checked($settings['follow_enabled'], 1); ?>>
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
                        <input type="url" name="signalkit_settings[follow_google_news_url]" 
                               value="<?php echo esc_url($settings['follow_google_news_url']); ?>" 
                               class="regular-text" 
                               placeholder="https://news.google.com/publications/...">
                        <p class="description"><?php _e('Your Google News publication URL', 'signalkit-for-google'); ?></p>
                    </div>
                    
                    <!-- Banner Headline -->
                    <div class="signalkit-setting-row">
                        <label><?php _e('Banner Headline', 'signalkit-for-google'); ?></label>
                        <input type="text" name="signalkit_settings[follow_banner_headline]" 
                               value="<?php echo esc_attr($settings['follow_banner_headline']); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('Stay Updated with [site_name]', 'signalkit-for-google'); ?>">
                        <p class="description"><?php _e('Use [site_name] as placeholder for your site name', 'signalkit-for-google'); ?></p>
                    </div>
                    
                    <!-- Banner Description -->
                    <div class="signalkit-setting-row">
                        <label><?php _e('Banner Description', 'signalkit-for-google'); ?></label>
                        <textarea name="signalkit_settings[follow_banner_description]" 
                                  rows="3" class="large-text"><?php echo esc_textarea($settings['follow_banner_description']); ?></textarea>
                    </div>
                    
                    <!-- Button Text -->
                    <div class="signalkit-setting-row">
                        <label><?php _e('Button Text', 'signalkit-for-google'); ?></label>
                        <input type="text" name="signalkit_settings[follow_button_text]" 
                               value="<?php echo esc_attr($settings['follow_button_text']); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('Follow Us On Google News', 'signalkit-for-google'); ?>">
                    </div>
                    
                    <!-- Color Settings -->
                    <div class="signalkit-setting-row signalkit-color-row">
                        <h3><?php _e('Colors', 'signalkit-for-google'); ?></h3>
                        <div class="signalkit-color-grid">
                            <div class="signalkit-color-input">
                                <label><?php _e('Primary Color', 'signalkit-for-google'); ?></label>
                                <input type="text" name="signalkit_settings[follow_primary_color]" 
                                       value="<?php echo esc_attr($settings['follow_primary_color']); ?>" 
                                       class="signalkit-color-picker">
                            </div>
                            <div class="signalkit-color-input">
                                <label><?php _e('Secondary Color', 'signalkit-for-google'); ?></label>
                                <input type="text" name="signalkit_settings[follow_secondary_color]" 
                                       value="<?php echo esc_attr($settings['follow_secondary_color']); ?>" 
                                       class="signalkit-color-picker">
                            </div>
                            <div class="signalkit-color-input">
                                <label><?php _e('Accent Color', 'signalkit-for-google'); ?></label>
                                <input type="text" name="signalkit_settings[follow_accent_color]" 
                                       value="<?php echo esc_attr($settings['follow_accent_color']); ?>" 
                                       class="signalkit-color-picker">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Display Settings -->
                    <div class="signalkit-setting-row">
                        <h3><?php _e('Display Settings', 'signalkit-for-google'); ?></h3>
                        
                        <div class="signalkit-display-grid">
                            <div class="signalkit-select-group">
                                <label><?php _e('Position', 'signalkit-for-google'); ?></label>
                                <select name="signalkit_settings[follow_position]">
                                    <option value="bottom_left" <?php selected($settings['follow_position'], 'bottom_left'); ?>><?php _e('Bottom Left', 'signalkit-for-google'); ?></option>
                                    <option value="bottom_right" <?php selected($settings['follow_position'], 'bottom_right'); ?>><?php _e('Bottom Right', 'signalkit-for-google'); ?></option>
                                    <option value="bottom_center" <?php selected($settings['follow_position'], 'bottom_center'); ?>><?php _e('Bottom Center', 'signalkit-for-google'); ?></option>
                                    <option value="top_left" <?php selected($settings['follow_position'], 'top_left'); ?>><?php _e('Top Left', 'signalkit-for-google'); ?></option>
                                    <option value="top_right" <?php selected($settings['follow_position'], 'top_right'); ?>><?php _e('Top Right', 'signalkit-for-google'); ?></option>
                                    <option value="top_center" <?php selected($settings['follow_position'], 'top_center'); ?>><?php _e('Top Center', 'signalkit-for-google'); ?></option>
                                </select>
                            </div>
                            
                            <div class="signalkit-select-group">
                                <label><?php _e('Animation', 'signalkit-for-google'); ?></label>
                                <select name="signalkit_settings[follow_animation]">
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
                            <input type="checkbox" name="signalkit_settings[follow_dismissible]" value="1" 
                                   <?php checked($settings['follow_dismissible'], 1); ?>>
                            <span class="signalkit-toggle-slider"></span>
                        </label>
                        <div class="signalkit-setting-info">
                            <strong><?php _e('Allow Dismissal', 'signalkit-for-google'); ?></strong>
                            <p><?php _e('Users can close the banner', 'signalkit-for-google'); ?></p>
                        </div>
                    </div>
                    
                    <div class="signalkit-setting-row">
                        <label><?php _e('Dismiss Duration (days)', 'signalkit-for-google'); ?></label>
                        <input type="number" name="signalkit_settings[follow_dismiss_duration]" 
                               value="<?php echo esc_attr($settings['follow_dismiss_duration']); ?>" 
                               min="1" max="365" class="small-text">
                        <p class="description"><?php _e('How long to hide the banner after dismissal', 'signalkit-for-google'); ?></p>
                    </div>
                    
                    <!-- Device Settings -->
                    <div class="signalkit-setting-row">
                        <h3><?php _e('Device Settings', 'signalkit-for-google'); ?></h3>
                        <div class="signalkit-checkbox-group">
                            <label>
                                <input type="checkbox" name="signalkit_settings[follow_mobile_enabled]" value="1" 
                                       <?php checked($settings['follow_mobile_enabled'], 1); ?>>
                                <?php _e('Show on Mobile', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[follow_desktop_enabled]" value="1" 
                                       <?php checked($settings['follow_desktop_enabled'], 1); ?>>
                                <?php _e('Show on Desktop', 'signalkit-for-google'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Page Type Settings -->
                    <div class="signalkit-setting-row">
                        <h3><?php _e('Page Types', 'signalkit-for-google'); ?></h3>
                        <div class="signalkit-checkbox-group">
                            <label>
                                <input type="checkbox" name="signalkit_settings[follow_show_on_posts]" value="1" 
                                       <?php checked($settings['follow_show_on_posts'], 1); ?>>
                                <?php _e('Show on Posts', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[follow_show_on_pages]" value="1" 
                                       <?php checked($settings['follow_show_on_pages'], 1); ?>>
                                <?php _e('Show on Pages', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[follow_show_on_homepage]" value="1" 
                                       <?php checked($settings['follow_show_on_homepage'], 1); ?>>
                                <?php _e('Show on Homepage', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[follow_show_on_archive]" value="1" 
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
                            <input type="checkbox" name="signalkit_settings[preferred_enabled]" value="1" 
                                   <?php checked($settings['preferred_enabled'], 1); ?>>
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
                        <input type="url" name="signalkit_settings[preferred_google_preferences_url]" 
                               value="<?php echo esc_url($settings['preferred_google_preferences_url']); ?>" 
                               class="regular-text"
                               placeholder="https://news.google.com/preferences">
                        <p class="description"><?php _e('Google News preferences URL where users can add your site as preferred', 'signalkit-for-google'); ?></p>
                    </div>
                    
                    <!-- Educational Post URL -->
                    <div class="signalkit-setting-row">
                        <label><?php _e('Educational Post URL', 'signalkit-for-google'); ?></label>
                        <input type="url" name="signalkit_settings[preferred_educational_post_url]" 
                               value="<?php echo esc_url($settings['preferred_educational_post_url']); ?>" 
                               class="regular-text"
                               placeholder="https://yoursite.com/how-to-add-preferred-sources">
                        <p class="description"><?php _e('Link to a post explaining how to add preferred sources', 'signalkit-for-google'); ?></p>
                    </div>
                    
                    <!-- Show Educational Link -->
                    <div class="signalkit-setting-row">
                        <label class="signalkit-toggle">
                            <input type="checkbox" name="signalkit_settings[preferred_show_educational_link]" value="1" 
                                   <?php checked($settings['preferred_show_educational_link'], 1); ?>>
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
                        <input type="text" name="signalkit_settings[preferred_educational_text]" 
                               value="<?php echo esc_attr($settings['preferred_educational_text']); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('Learn More', 'signalkit-for-google'); ?>">
                    </div>
                    
                    <!-- Banner Headline -->
                    <div class="signalkit-setting-row">
                        <label><?php _e('Banner Headline', 'signalkit-for-google'); ?></label>
                        <input type="text" name="signalkit_settings[preferred_banner_headline]" 
                               value="<?php echo esc_attr($settings['preferred_banner_headline']); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('Add [site_name] As A Trusted Source', 'signalkit-for-google'); ?>">
                        <p class="description"><?php _e('Use [site_name] as placeholder for your site name', 'signalkit-for-google'); ?></p>
                    </div>
                    
                    <!-- Banner Description -->
                    <div class="signalkit-setting-row">
                        <label><?php _e('Banner Description', 'signalkit-for-google'); ?></label>
                        <textarea name="signalkit_settings[preferred_banner_description]" 
                                  rows="3" class="large-text"><?php echo esc_textarea($settings['preferred_banner_description']); ?></textarea>
                    </div>
                    
                    <!-- Button Text -->
                    <div class="signalkit-setting-row">
                        <label><?php _e('Button Text', 'signalkit-for-google'); ?></label>
                        <input type="text" name="signalkit_settings[preferred_button_text]" 
                               value="<?php echo esc_attr($settings['preferred_button_text']); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('Add As A Preferred Source', 'signalkit-for-google'); ?>">
                    </div>
                    
                    <!-- Color Settings -->
                    <div class="signalkit-setting-row signalkit-color-row">
                        <h3><?php _e('Colors', 'signalkit-for-google'); ?></h3>
                        <div class="signalkit-color-grid">
                            <div class="signalkit-color-input">
                                <label><?php _e('Primary Color', 'signalkit-for-google'); ?></label>
                                <input type="text" name="signalkit_settings[preferred_primary_color]" 
                                       value="<?php echo esc_attr($settings['preferred_primary_color']); ?>" 
                                       class="signalkit-color-picker">
                            </div>
                            <div class="signalkit-color-input">
                                <label><?php _e('Secondary Color', 'signalkit-for-google'); ?></label>
                                <input type="text" name="signalkit_settings[preferred_secondary_color]" 
                                       value="<?php echo esc_attr($settings['preferred_secondary_color']); ?>" 
                                       class="signalkit-color-picker">
                            </div>
                            <div class="signalkit-color-input">
                                <label><?php _e('Accent Color', 'signalkit-for-google'); ?></label>
                                <input type="text" name="signalkit_settings[preferred_accent_color]" 
                                       value="<?php echo esc_attr($settings['preferred_accent_color']); ?>" 
                                       class="signalkit-color-picker">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Display Settings -->
                    <div class="signalkit-setting-row">
                        <h3><?php _e('Display Settings', 'signalkit-for-google'); ?></h3>
                        
                        <div class="signalkit-display-grid">
                            <div class="signalkit-select-group">
                                <label><?php _e('Position', 'signalkit-for-google'); ?></label>
                                <select name="signalkit_settings[preferred_position]">
                                    <option value="bottom_left" <?php selected($settings['preferred_position'], 'bottom_left'); ?>><?php _e('Bottom Left', 'signalkit-for-google'); ?></option>
                                    <option value="bottom_right" <?php selected($settings['preferred_position'], 'bottom_right'); ?>><?php _e('Bottom Right', 'signalkit-for-google'); ?></option>
                                    <option value="bottom_center" <?php selected($settings['preferred_position'], 'bottom_center'); ?>><?php _e('Bottom Center', 'signalkit-for-google'); ?></option>
                                    <option value="top_left" <?php selected($settings['preferred_position'], 'top_left'); ?>><?php _e('Top Left', 'signalkit-for-google'); ?></option>
                                    <option value="top_right" <?php selected($settings['preferred_position'], 'top_right'); ?>><?php _e('Top Right', 'signalkit-for-google'); ?></option>
                                    <option value="top_center" <?php selected($settings['preferred_position'], 'top_center'); ?>><?php _e('Top Center', 'signalkit-for-google'); ?></option>
                                </select>
                            </div>
                            
                            <div class="signalkit-select-group">
                                <label><?php _e('Animation', 'signalkit-for-google'); ?></label>
                                <select name="signalkit_settings[preferred_animation]">
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
                            <input type="checkbox" name="signalkit_settings[preferred_dismissible]" value="1" 
                                   <?php checked($settings['preferred_dismissible'], 1); ?>>
                            <span class="signalkit-toggle-slider"></span>
                        </label>
                        <div class="signalkit-setting-info">
                            <strong><?php _e('Allow Dismissal', 'signalkit-for-google'); ?></strong>
                            <p><?php _e('Users can close the banner', 'signalkit-for-google'); ?></p>
                        </div>
                    </div>
                    
                    <div class="signalkit-setting-row">
                        <label><?php _e('Dismiss Duration (days)', 'signalkit-for-google'); ?></label>
                        <input type="number" name="signalkit_settings[preferred_dismiss_duration]" 
                               value="<?php echo esc_attr($settings['preferred_dismiss_duration']); ?>" 
                               min="1" max="365" class="small-text">
                        <p class="description"><?php _e('How long to hide the banner after dismissal', 'signalkit-for-google'); ?></p>
                    </div>
                    
                    <!-- Device Settings -->
                    <div class="signalkit-setting-row">
                        <h3><?php _e('Device Settings', 'signalkit-for-google'); ?></h3>
                        <div class="signalkit-checkbox-group">
                            <label>
                                <input type="checkbox" name="signalkit_settings[preferred_mobile_enabled]" value="1" 
                                       <?php checked($settings['preferred_mobile_enabled'], 1); ?>>
                                <?php _e('Show on Mobile', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[preferred_desktop_enabled]" value="1" 
                                       <?php checked($settings['preferred_desktop_enabled'], 1); ?>>
                                <?php _e('Show on Desktop', 'signalkit-for-google'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Page Type Settings -->
                    <div class="signalkit-setting-row">
                        <h3><?php _e('Page Types', 'signalkit-for-google'); ?></h3>
                        <div class="signalkit-checkbox-group">
                            <label>
                                <input type="checkbox" name="signalkit_settings[preferred_show_on_posts]" value="1" 
                                       <?php checked($settings['preferred_show_on_posts'], 1); ?>>
                                <?php _e('Show on Posts', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[preferred_show_on_pages]" value="1" 
                                       <?php checked($settings['preferred_show_on_pages'], 1); ?>>
                                <?php _e('Show on Pages', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[preferred_show_on_homepage]" value="1" 
                                       <?php checked($settings['preferred_show_on_homepage'], 1); ?>>
                                <?php _e('Show on Homepage', 'signalkit-for-google'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="signalkit_settings[preferred_show_on_archive]" value="1" 
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
                        <input type="text" name="signalkit_settings[site_name]" 
                               value="<?php echo esc_attr($settings['site_name']); ?>" 
                               class="regular-text"
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
                    
                </div>
            </div>
            
            <?php submit_button(__('Save Settings', 'signalkit-for-google'), 'primary signalkit-submit-button'); ?>
            
        </form>
    </div>
</div>