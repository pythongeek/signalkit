<?php
/**
 * Custom Banner Settings Partial
 * Lead Capture, Newsletter, CTA Banner Configuration
 *
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Template partial variables - intentionally unprefixed as these are passed from including context
/**
 * Render custom banner settings
 * 
 * @param array $settings Plugin settings array
 */
function signalkit_render_custom_banner_settings($settings) {
    // Default values
    $defaults = array(
        'custom_enabled' => 0,
        'custom_banner_type' => 'newsletter',
        'custom_headline' => __('Subscribe to Our Newsletter', 'signalkit'),
        'custom_description' => __('Get the latest news and updates delivered to your inbox.', 'signalkit'),
        'custom_button_text' => __('Subscribe', 'signalkit'),
        'custom_success_message' => __('Thank you for subscribing!', 'signalkit'),
        'custom_error_message' => __('Something went wrong. Please try again.', 'signalkit'),
        'custom_placeholder_email' => __('Enter your email', 'signalkit'),
        'custom_placeholder_name' => __('Your name (optional)', 'signalkit'),
        'custom_show_name_field' => 0,
        'custom_require_name' => 0,
        'custom_privacy_text' => __('We respect your privacy. Unsubscribe anytime.', 'signalkit'),
        'custom_show_privacy' => 1,
        'custom_redirect_url' => '',
        'custom_webhook_url' => '',
        'custom_webhook_consent' => 0, // GDPR: Default to no consent for external data transmission
        'custom_store_locally' => 1,
        // Style settings
        'custom_primary_color' => '#6366f1',
        'custom_secondary_color' => '#ffffff',
        'custom_accent_color' => '#8b5cf6',
        'custom_text_color' => '#1e1e1e',
        'custom_position' => 'bottom_right',
        'custom_mobile_position' => 'bottom',
        'custom_animation' => 'slide_in',
        'custom_dismissible' => 1,
        'custom_dismiss_duration' => 7,
        'custom_show_frequency' => 'once_per_session',
        'custom_delay' => 3,
        'custom_scroll_trigger' => 0,
        'custom_scroll_percentage' => 50,
        'custom_exit_intent' => 0,
        'custom_mobile_enabled' => 1,
        'custom_desktop_enabled' => 1,
        'custom_show_on_posts' => 1,
        'custom_show_on_pages' => 1,
        'custom_show_on_homepage' => 1,
        'custom_show_on_archive' => 0,
        // Enhanced styling
        'custom_banner_style' => 'lead-gradient',
        'custom_button_style' => 'default',
        'custom_icon_style' => 'circle',
        'custom_visibility_mode' => 'auto',
        'custom_enable_glow' => 0,
        'custom_enable_float' => 0,
        'custom_backdrop_blur' => 12,
        'custom_backdrop_opacity' => 95,
        'custom_banner_width' => 400,
        'custom_banner_padding' => 30,
        'custom_border_radius' => 16,
        // Font settings
        'custom_font_family' => 'system',
        'custom_font_weight_headline' => '700',
        'custom_font_weight_body' => '400',
        'custom_letter_spacing' => 0,
        'custom_line_height' => 1.5,
    );
    
    // Merge with actual settings
    foreach ($defaults as $key => $default) {
        if (!isset($settings[$key])) {
            $settings[$key] = $default;
        }
    }
    
    // Available font families
    $font_families = array(
        'system' => __('System Default', 'signalkit'),
        'inter' => 'Inter',
        'roboto' => 'Roboto',
        'open-sans' => 'Open Sans',
        'lato' => 'Lato',
        'montserrat' => 'Montserrat',
        'poppins' => 'Poppins',
        'nunito' => 'Nunito',
        'raleway' => 'Raleway',
        'ubuntu' => 'Ubuntu',
        'playfair' => 'Playfair Display',
        'merriweather' => 'Merriweather',
        'source-sans' => 'Source Sans Pro',
        'oswald' => 'Oswald',
        'rubik' => 'Rubik',
    );
    
    $font_weights = array(
        '300' => __('Light (300)', 'signalkit'),
        '400' => __('Regular (400)', 'signalkit'),
        '500' => __('Medium (500)', 'signalkit'),
        '600' => __('Semi-Bold (600)', 'signalkit'),
        '700' => __('Bold (700)', 'signalkit'),
        '800' => __('Extra Bold (800)', 'signalkit'),
    );
    
    $banner_types = array(
        'newsletter' => array(
            'label' => __('Newsletter Signup', 'signalkit'),
            'icon' => '📧',
            'desc' => __('Collect email subscribers', 'signalkit'),
        ),
        'lead' => array(
            'label' => __('Lead Capture', 'signalkit'),
            'icon' => '🎯',
            'desc' => __('Capture leads with name & email', 'signalkit'),
        ),
        'cta' => array(
            'label' => __('Call to Action', 'signalkit'),
            'icon' => 'dashicons dashicons-rocket',
            'desc' => __('Drive action with a button', 'signalkit'),
        ),
        'announcement' => array(
            'label' => __('Announcement', 'signalkit'),
            'icon' => '📢',
            'desc' => __('Share important updates', 'signalkit'),
        ),
        'promo' => array(
            'label' => __('Promotional', 'signalkit'),
            'icon' => '🏷️',
            'desc' => __('Promote offers or discounts', 'signalkit'),
        ),
    );
    ?>
    
    <div class="signalkit-tab-content" id="tab-custom" data-content="custom" role="tabpanel">
        <div class="signalkit-settings-grid">
            
            <!-- Enable Toggle -->
            <div class="signalkit-setting-row">
                <label class="signalkit-toggle">
                    <input type="checkbox" 
                           name="signalkit_settings[custom_enabled]" 
                           value="1" 
                           <?php checked($settings['custom_enabled'], 1); ?>
                           class="signalkit-preview-trigger"
                           data-banner="custom"
                           aria-describedby="custom-enabled-desc">
                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                </label>
                <div class="signalkit-setting-info">
                    <strong><?php esc_html_e('Enable Custom Banner', 'signalkit'); ?></strong>
                    <p id="custom-enabled-desc"><?php esc_html_e('Create a custom banner for lead capture, newsletters, or promotions', 'signalkit'); ?></p>
                </div>
            </div>
            
            <!-- Banner Type Selection -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-welcome-widgets-menus" aria-hidden="true"></span> <?php esc_html_e('Banner Type', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row">
                <div class="signalkit-banner-type-grid">
                    <?php foreach ($banner_types as $value => $type): ?>
                        <label class="signalkit-banner-type-option <?php echo esc_attr($settings['custom_banner_type'] === $value ? 'active' : ''); ?>">
                            <input type="radio" 
                                   name="signalkit_settings[custom_banner_type]" 
                                   value="<?php echo esc_attr($value); ?>"
                                   <?php checked($settings['custom_banner_type'], $value); ?>
                                   class="signalkit-preview-trigger"
                                   data-banner="custom">
                            <span class="signalkit-type-icon" aria-hidden="true"><?php echo esc_html($type['icon']); ?></span>
                            <span class="signalkit-type-info">
                                <strong><?php echo esc_html($type['label']); ?></strong>
                                <small><?php echo esc_html($type['desc']); ?></small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-edit" aria-hidden="true"></span> <?php esc_html_e('Content', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_headline"><?php esc_html_e('Banner Headline', 'signalkit'); ?></label>
                <input type="text" 
                       id="custom_headline"
                       name="signalkit_settings[custom_headline]" 
                       value="<?php echo esc_attr($settings['custom_headline']); ?>" 
                       class="regular-text signalkit-preview-trigger"
                       data-banner="custom"
                       placeholder="<?php echo esc_attr__('Subscribe to Our Newsletter', 'signalkit'); ?>">
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_description"><?php esc_html_e('Banner Description', 'signalkit'); ?></label>
                <textarea id="custom_description"
                          name="signalkit_settings[custom_description]" 
                          rows="3" 
                          class="large-text signalkit-preview-trigger"
                          data-banner="custom"><?php echo esc_textarea($settings['custom_description']); ?></textarea>
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_button_text"><?php esc_html_e('Button Text', 'signalkit'); ?></label>
                <input type="text" 
                       id="custom_button_text"
                       name="signalkit_settings[custom_button_text]" 
                       value="<?php echo esc_attr($settings['custom_button_text']); ?>" 
                       class="regular-text signalkit-preview-trigger"
                       data-banner="custom"
                       placeholder="<?php echo esc_attr__('Subscribe', 'signalkit'); ?>">
            </div>
            
            <!-- Form Fields (for newsletter/lead types) -->
            <div class="signalkit-form-fields-section" data-show-for-type="newsletter,lead">
                <div class="signalkit-section-header">
                    <h3><span class="dashicons dashicons-forms" aria-hidden="true"></span> <?php esc_html_e('Form Fields', 'signalkit'); ?></h3>
                </div>
                
                <div class="signalkit-setting-row">
                    <label for="custom_placeholder_email"><?php esc_html_e('Email Placeholder', 'signalkit'); ?></label>
                    <input type="text" 
                           id="custom_placeholder_email"
                           name="signalkit_settings[custom_placeholder_email]" 
                           value="<?php echo esc_attr($settings['custom_placeholder_email']); ?>" 
                           class="regular-text"
                           placeholder="<?php echo esc_attr__('Enter your email', 'signalkit'); ?>">
                </div>
                
                <div class="signalkit-setting-row">
                    <label class="signalkit-toggle">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_show_name_field]" 
                               value="1" 
                               <?php checked($settings['custom_show_name_field'], 1); ?>>
                        <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                    </label>
                    <div class="signalkit-setting-info">
                        <strong><?php esc_html_e('Show Name Field', 'signalkit'); ?></strong>
                        <p><?php esc_html_e('Add a name input field to the form', 'signalkit'); ?></p>
                    </div>
                </div>
                
                <div class="signalkit-setting-row signalkit-name-field-options" style="<?php echo esc_attr(empty($settings['custom_show_name_field']) ? 'display:none;' : ''); ?>">
                    <label for="custom_placeholder_name"><?php esc_html_e('Name Placeholder', 'signalkit'); ?></label>
                    <input type="text" 
                           id="custom_placeholder_name"
                           name="signalkit_settings[custom_placeholder_name]" 
                           value="<?php echo esc_attr($settings['custom_placeholder_name']); ?>" 
                           class="regular-text"
                           placeholder="<?php echo esc_attr__('Your name (optional)', 'signalkit'); ?>">
                    
                    <label class="signalkit-checkbox-inline" style="margin-top: 8px;">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_require_name]" 
                               value="1" 
                               <?php checked($settings['custom_require_name'], 1); ?>>
                        <?php esc_html_e('Require name field', 'signalkit'); ?>
                    </label>
                </div>
                
                <div class="signalkit-setting-row">
                    <label class="signalkit-toggle">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_show_privacy]" 
                               value="1" 
                               <?php checked($settings['custom_show_privacy'], 1); ?>>
                        <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                    </label>
                    <div class="signalkit-setting-info">
                        <strong><?php esc_html_e('Show Privacy Notice', 'signalkit'); ?></strong>
                    </div>
                </div>
                
                <div class="signalkit-setting-row" style="<?php echo esc_attr(empty($settings['custom_show_privacy']) ? 'display:none;' : ''); ?>">
                    <label for="custom_privacy_text"><?php esc_html_e('Privacy Text', 'signalkit'); ?></label>
                    <input type="text" 
                           id="custom_privacy_text"
                           name="signalkit_settings[custom_privacy_text]" 
                           value="<?php echo esc_attr($settings['custom_privacy_text']); ?>" 
                           class="regular-text"
                           placeholder="<?php echo esc_attr__('We respect your privacy.', 'signalkit'); ?>">
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-megaphone" aria-hidden="true"></span> <?php esc_html_e('Messages', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_success_message"><?php esc_html_e('Success Message', 'signalkit'); ?></label>
                <input type="text" 
                       id="custom_success_message"
                       name="signalkit_settings[custom_success_message]" 
                       value="<?php echo esc_attr($settings['custom_success_message']); ?>" 
                       class="regular-text"
                       placeholder="<?php echo esc_attr__('Thank you for subscribing!', 'signalkit'); ?>">
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_error_message"><?php esc_html_e('Error Message', 'signalkit'); ?></label>
                <input type="text" 
                       id="custom_error_message"
                       name="signalkit_settings[custom_error_message]" 
                       value="<?php echo esc_attr($settings['custom_error_message']); ?>" 
                       class="regular-text"
                       placeholder="<?php echo esc_attr__('Something went wrong. Please try again.', 'signalkit'); ?>">
            </div>
            
            <!-- Integration Section -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span> <?php esc_html_e('Integration', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row">
                <label class="signalkit-toggle">
                    <input type="checkbox" 
                           name="signalkit_settings[custom_store_locally]" 
                           value="1" 
                           <?php checked($settings['custom_store_locally'], 1); ?>>
                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                </label>
                <div class="signalkit-setting-info">
                    <strong><?php esc_html_e('Store Submissions Locally', 'signalkit'); ?></strong>
                    <p><?php esc_html_e('Save submissions in the WordPress database', 'signalkit'); ?></p>
                </div>
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_webhook_url"><?php esc_html_e('Webhook URL (Optional)', 'signalkit'); ?></label>
                <input type="url" 
                       id="custom_webhook_url"
                       name="signalkit_settings[custom_webhook_url]" 
                       value="<?php echo esc_url($settings['custom_webhook_url']); ?>" 
                       class="regular-text"
                       placeholder="https://hooks.zapier.com/...">
                <p class="description"><?php esc_html_e('Send submissions to Zapier, Make, or any webhook endpoint', 'signalkit'); ?></p>
            </div>
            
            <!-- Privacy Disclosure for Webhook -->
            <div class="notice notice-warning inline" style="margin: 10px 0 20px 0; padding: 12px;">
                <p style="margin: 0;">
                    <span class="dashicons dashicons-warning" style="color: #f0b849; margin-right: 4px;" aria-hidden="true"></span>
                    <strong><?php esc_html_e('Privacy Notice:', 'signalkit'); ?></strong> 
                    <?php esc_html_e('If you configure a webhook URL above, user data (email addresses and names) will be sent to that external service. Please ensure you have appropriate privacy policies and user consent mechanisms in place before enabling webhook integration.', 'signalkit'); ?>
                </p>
            </div>
            
            <!-- GDPR Webhook Consent Checkbox (Required for Envato/CodeCanyon Compliance) -->
            <div class="signalkit-setting-row">
                <label class="signalkit-toggle">
                    <input type="checkbox" 
                           name="signalkit_settings[custom_webhook_consent]" 
                           value="1" 
                           <?php checked(!empty($settings['custom_webhook_consent']), true); ?>
                           aria-describedby="webhook-consent-desc">
                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                </label>
                <div class="signalkit-setting-info">
                    <strong><?php esc_html_e('I Consent to Sending Data to External Webhook', 'signalkit'); ?></strong>
                    <p id="webhook-consent-desc"><?php esc_html_e('Required: You must explicitly consent to sending user data (email addresses and names) to the webhook URL configured above. Without this consent, no data will be transmitted to external services.', 'signalkit'); ?></p>
                </div>
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_redirect_url"><?php esc_html_e('Redirect URL (Optional)', 'signalkit'); ?></label>
                <input type="url" 
                       id="custom_redirect_url"
                       name="signalkit_settings[custom_redirect_url]" 
                       value="<?php echo esc_url($settings['custom_redirect_url']); ?>" 
                       class="regular-text"
                       placeholder="https://yoursite.com/thank-you/">
                <p class="description"><?php esc_html_e('Redirect users after successful submission', 'signalkit'); ?></p>
            </div>
            
            <!-- Promo Code (for Promotional type) -->
            <div class="signalkit-setting-row signalkit-promo-field" data-show-for-type="promo">
                <label for="custom_promo_code"><?php esc_html_e('Promo Code', 'signalkit'); ?></label>
                <input type="text" 
                       id="custom_promo_code"
                       name="signalkit_settings[custom_promo_code]" 
                       value="<?php echo esc_attr($settings['custom_promo_code'] ?? ''); ?>" 
                       class="regular-text"
                       placeholder="<?php esc_attr_e('SAVE20', 'signalkit'); ?>"
                       style="font-family: monospace; font-weight: bold; letter-spacing: 2px;">
                <p class="description"><?php esc_html_e('Discount code that visitors can copy (displays on promotional banners)', 'signalkit'); ?></p>
            </div>
            
            <!-- Typography Section -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-editor-textcolor" aria-hidden="true"></span> <?php esc_html_e('Typography', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_font_family"><?php esc_html_e('Font Family', 'signalkit'); ?></label>
                <select id="custom_font_family" 
                        name="signalkit_settings[custom_font_family]"
                        class="signalkit-preview-trigger signalkit-font-selector"
                        data-banner="custom">
                    <?php foreach ($font_families as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['custom_font_family'], $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Google Fonts are loaded only when this banner is active', 'signalkit'); ?></p>
            </div>
            
            <div class="signalkit-setting-row">
                <div class="signalkit-typography-grid">
                    <div class="signalkit-typography-input">
                        <label for="custom_font_weight_headline"><?php esc_html_e('Headline Weight', 'signalkit'); ?></label>
                        <select id="custom_font_weight_headline" 
                                name="signalkit_settings[custom_font_weight_headline]"
                                class="signalkit-preview-trigger"
                                data-banner="custom">
                            <?php foreach ($font_weights as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['custom_font_weight_headline'], $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="signalkit-typography-input">
                        <label for="custom_font_weight_body"><?php esc_html_e('Body Weight', 'signalkit'); ?></label>
                        <select id="custom_font_weight_body" 
                                name="signalkit_settings[custom_font_weight_body]"
                                class="signalkit-preview-trigger"
                                data-banner="custom">
                            <?php foreach ($font_weights as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['custom_font_weight_body'], $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="signalkit-typography-input">
                        <label for="custom_letter_spacing"><?php esc_html_e('Letter Spacing', 'signalkit'); ?></label>
                        <input type="number" 
                               id="custom_letter_spacing"
                               name="signalkit_settings[custom_letter_spacing]" 
                               value="<?php echo esc_attr($settings['custom_letter_spacing']); ?>" 
                               min="-2" 
                               max="5"
                               step="0.1"
                               class="small-text signalkit-preview-trigger"
                               data-banner="custom">
                        <span class="signalkit-range-value">px</span>
                    </div>
                    
                    <div class="signalkit-typography-input">
                        <label for="custom_line_height"><?php esc_html_e('Line Height', 'signalkit'); ?></label>
                        <input type="number" 
                               id="custom_line_height"
                               name="signalkit_settings[custom_line_height]" 
                               value="<?php echo esc_attr($settings['custom_line_height']); ?>" 
                               min="1" 
                               max="2.5"
                               step="0.1"
                               class="small-text signalkit-preview-trigger"
                               data-banner="custom">
                    </div>
                </div>
            </div>
            
            <!-- Color Settings -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-art" aria-hidden="true"></span> <?php esc_html_e('Colors', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row signalkit-color-row">
                <div class="signalkit-color-grid">
                    <div class="signalkit-color-input">
                        <label for="custom_primary_color"><?php esc_html_e('Primary Color', 'signalkit'); ?></label>
                        <input type="text" 
                               id="custom_primary_color"
                               name="signalkit_settings[custom_primary_color]" 
                               value="<?php echo esc_attr($settings['custom_primary_color']); ?>" 
                               class="signalkit-color-picker signalkit-preview-trigger"
                               data-banner="custom">
                    </div>
                    <div class="signalkit-color-input">
                        <label for="custom_secondary_color"><?php esc_html_e('Secondary Color', 'signalkit'); ?></label>
                        <input type="text" 
                               id="custom_secondary_color"
                               name="signalkit_settings[custom_secondary_color]" 
                               value="<?php echo esc_attr($settings['custom_secondary_color']); ?>" 
                               class="signalkit-color-picker signalkit-preview-trigger"
                               data-banner="custom">
                    </div>
                    <div class="signalkit-color-input">
                        <label for="custom_accent_color"><?php esc_html_e('Accent Color', 'signalkit'); ?></label>
                        <input type="text" 
                               id="custom_accent_color"
                               name="signalkit_settings[custom_accent_color]" 
                               value="<?php echo esc_attr($settings['custom_accent_color']); ?>" 
                               class="signalkit-color-picker signalkit-preview-trigger"
                               data-banner="custom">
                    </div>
                    <div class="signalkit-color-input">
                        <label for="custom_text_color"><?php esc_html_e('Text Color', 'signalkit'); ?></label>
                        <input type="text" 
                               id="custom_text_color"
                               name="signalkit_settings[custom_text_color]" 
                               value="<?php echo esc_attr($settings['custom_text_color']); ?>" 
                               class="signalkit-color-picker signalkit-preview-trigger"
                               data-banner="custom">
                    </div>
                </div>
            </div>
            
            <!-- Advanced Style Options -->
            <?php 
            if (function_exists('signalkit_render_advanced_style_settings')) {
                signalkit_render_advanced_style_settings($settings, 'custom');
            }
            ?>
            
            <!-- Trigger Settings -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-clock" aria-hidden="true"></span> <?php esc_html_e('Display Triggers', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row">
                <label for="custom_delay"><?php esc_html_e('Show After Delay', 'signalkit'); ?></label>
                <input type="number" 
                       id="custom_delay"
                       name="signalkit_settings[custom_delay]" 
                       value="<?php echo esc_attr($settings['custom_delay']); ?>" 
                       min="0" 
                       max="60"
                       class="small-text">
                <span class="signalkit-range-value"><?php esc_html_e('seconds', 'signalkit'); ?></span>
            </div>
            
            <div class="signalkit-setting-row">
                <label class="signalkit-toggle">
                    <input type="checkbox" 
                           name="signalkit_settings[custom_scroll_trigger]" 
                           value="1" 
                           <?php checked($settings['custom_scroll_trigger'], 1); ?>>
                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                </label>
                <div class="signalkit-setting-info">
                    <strong><?php esc_html_e('Scroll Trigger', 'signalkit'); ?></strong>
                    <p><?php esc_html_e('Show banner after user scrolls a percentage of the page', 'signalkit'); ?></p>
                </div>
            </div>
            
            <div class="signalkit-setting-row signalkit-scroll-options" style="<?php echo esc_attr(empty($settings['custom_scroll_trigger']) ? 'display:none;' : ''); ?>">
                <label for="custom_scroll_percentage"><?php esc_html_e('Scroll Percentage', 'signalkit'); ?></label>
                <input type="range" 
                       id="custom_scroll_percentage"
                       name="signalkit_settings[custom_scroll_percentage]" 
                       value="<?php echo esc_attr($settings['custom_scroll_percentage']); ?>" 
                       min="10" 
                       max="100"
                       class="signalkit-range">
                <span class="signalkit-range-value"><?php echo esc_html($settings['custom_scroll_percentage']); ?>%</span>
            </div>
            
            <div class="signalkit-setting-row">
                <label class="signalkit-toggle">
                    <input type="checkbox" 
                           name="signalkit_settings[custom_exit_intent]" 
                           value="1" 
                           <?php checked($settings['custom_exit_intent'], 1); ?>>
                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                </label>
                <div class="signalkit-setting-info">
                    <strong><?php esc_html_e('Exit Intent (Desktop)', 'signalkit'); ?></strong>
                    <p><?php esc_html_e('Show banner when user is about to leave the page', 'signalkit'); ?></p>
                </div>
            </div>
            
            <!-- Display Settings (position, visibility) -->
            <div class="signalkit-section-header">
                <h3><span class="dashicons dashicons-desktop" aria-hidden="true"></span> <?php esc_html_e('Display Settings', 'signalkit'); ?></h3>
            </div>
            
            <div class="signalkit-setting-row">
                <div class="signalkit-display-grid">
                    <div class="signalkit-select-group">
                        <label for="custom_position"><?php esc_html_e('Desktop Position', 'signalkit'); ?></label>
                        <select id="custom_position" 
                                name="signalkit_settings[custom_position]" 
                                class="signalkit-preview-trigger"
                                data-banner="custom">
                            <option value="bottom_left" <?php selected($settings['custom_position'], 'bottom_left'); ?>><?php esc_html_e('Bottom Left', 'signalkit'); ?></option>
                            <option value="bottom_right" <?php selected($settings['custom_position'], 'bottom_right'); ?>><?php esc_html_e('Bottom Right', 'signalkit'); ?></option>
                            <option value="bottom_center" <?php selected($settings['custom_position'], 'bottom_center'); ?>><?php esc_html_e('Bottom Center', 'signalkit'); ?></option>
                            <option value="top_left" <?php selected($settings['custom_position'], 'top_left'); ?>><?php esc_html_e('Top Left', 'signalkit'); ?></option>
                            <option value="top_right" <?php selected($settings['custom_position'], 'top_right'); ?>><?php esc_html_e('Top Right', 'signalkit'); ?></option>
                            <option value="top_center" <?php selected($settings['custom_position'], 'top_center'); ?>><?php esc_html_e('Top Center', 'signalkit'); ?></option>
                            <option value="center" <?php selected($settings['custom_position'], 'center'); ?>><?php esc_html_e('Center (Modal)', 'signalkit'); ?></option>
                        </select>
                    </div>
                    
                    <div class="signalkit-select-group">
                        <label for="custom_animation"><?php esc_html_e('Animation', 'signalkit'); ?></label>
                        <select id="custom_animation" 
                                name="signalkit_settings[custom_animation]" 
                                class="signalkit-preview-trigger"
                                data-banner="custom">
                            <option value="slide_in" <?php selected($settings['custom_animation'], 'slide_in'); ?>><?php esc_html_e('Slide In', 'signalkit'); ?></option>
                            <option value="fade_in" <?php selected($settings['custom_animation'], 'fade_in'); ?>><?php esc_html_e('Fade In', 'signalkit'); ?></option>
                            <option value="bounce" <?php selected($settings['custom_animation'], 'bounce'); ?>><?php esc_html_e('Bounce', 'signalkit'); ?></option>
                            <option value="elastic" <?php selected($settings['custom_animation'], 'elastic'); ?>><?php esc_html_e('Elastic', 'signalkit'); ?></option>
                            <option value="swing" <?php selected($settings['custom_animation'], 'swing'); ?>><?php esc_html_e('Swing In', 'signalkit'); ?></option>
                            <option value="zoom" <?php selected($settings['custom_animation'], 'zoom'); ?>><?php esc_html_e('Zoom In', 'signalkit'); ?></option>
                        </select>
                    </div>
                    
                    <div class="signalkit-select-group">
                        <label for="custom_show_frequency"><?php esc_html_e('Show Frequency', 'signalkit'); ?></label>
                        <select id="custom_show_frequency" 
                                name="signalkit_settings[custom_show_frequency]">
                            <option value="always" <?php selected($settings['custom_show_frequency'], 'always'); ?>><?php esc_html_e('Every Page Visit', 'signalkit'); ?></option>
                            <option value="once_per_session" <?php selected($settings['custom_show_frequency'], 'once_per_session'); ?>><?php esc_html_e('Once Per Session', 'signalkit'); ?></option>
                            <option value="once_per_day" <?php selected($settings['custom_show_frequency'], 'once_per_day'); ?>><?php esc_html_e('Once Per Day', 'signalkit'); ?></option>
                            <option value="once" <?php selected($settings['custom_show_frequency'], 'once'); ?>><?php esc_html_e('Once (Until Submitted)', 'signalkit'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Device & Page Visibility -->
            <div class="signalkit-setting-row">
                <h4><?php esc_html_e('Device Visibility', 'signalkit'); ?></h4>
                <div class="signalkit-checkbox-grid">
                    <label class="signalkit-checkbox">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_desktop_enabled]" 
                               value="1" 
                               <?php checked($settings['custom_desktop_enabled'], 1); ?>>
                        <span class="dashicons dashicons-desktop" aria-hidden="true"></span>
                        <?php esc_html_e('Desktop', 'signalkit'); ?>
                    </label>
                    <label class="signalkit-checkbox">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_mobile_enabled]" 
                               value="1" 
                               <?php checked($settings['custom_mobile_enabled'], 1); ?>>
                        <span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
                        <?php esc_html_e('Mobile', 'signalkit'); ?>
                    </label>
                </div>
            </div>
            
            <div class="signalkit-setting-row">
                <h4><?php esc_html_e('Show On', 'signalkit'); ?></h4>
                <div class="signalkit-checkbox-grid">
                    <label class="signalkit-checkbox">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_show_on_homepage]" 
                               value="1" 
                               <?php checked($settings['custom_show_on_homepage'], 1); ?>>
                        <?php esc_html_e('Homepage', 'signalkit'); ?>
                    </label>
                    <label class="signalkit-checkbox">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_show_on_posts]" 
                               value="1" 
                               <?php checked($settings['custom_show_on_posts'], 1); ?>>
                        <?php esc_html_e('Posts', 'signalkit'); ?>
                    </label>
                    <label class="signalkit-checkbox">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_show_on_pages]" 
                               value="1" 
                               <?php checked($settings['custom_show_on_pages'], 1); ?>>
                        <?php esc_html_e('Pages', 'signalkit'); ?>
                    </label>
                    <label class="signalkit-checkbox">
                        <input type="checkbox" 
                               name="signalkit_settings[custom_show_on_archive]" 
                               value="1" 
                               <?php checked($settings['custom_show_on_archive'], 1); ?>>
                        <?php esc_html_e('Archives', 'signalkit'); ?>
                    </label>
                </div>
            </div>
            
            <!-- Dismiss Settings -->
            <div class="signalkit-setting-row">
                <label class="signalkit-toggle">
                    <input type="checkbox" 
                           name="signalkit_settings[custom_dismissible]" 
                           value="1" 
                           <?php checked($settings['custom_dismissible'], 1); ?>>
                    <span class="signalkit-toggle-slider" aria-hidden="true"></span>
                </label>
                <div class="signalkit-setting-info">
                    <strong><?php esc_html_e('Allow Dismiss', 'signalkit'); ?></strong>
                    <p><?php esc_html_e('Users can close the banner with a button', 'signalkit'); ?></p>
                </div>
            </div>

            <div class="signalkit-setting-row" style="<?php echo esc_attr(empty($settings['custom_dismissible']) ? 'display:none;' : ''); ?>">
                <label for="custom_close_button_size"><?php esc_html_e('Close Button Size (px)', 'signalkit'); ?></label>
                <div class="signalkit-size-input inline">
                    <input type="range" 
                           id="custom_close_button_size"
                           name="signalkit_settings[custom_close_button_size]" 
                           value="<?php echo esc_attr(isset($settings['custom_close_button_size']) ? $settings['custom_close_button_size'] : 28); ?>" 
                           min="20" 
                           max="40" 
                           step="1"
                           class="signalkit-range signalkit-preview-trigger"
                           data-banner="custom">
                    <span class="signalkit-range-value"><?php echo esc_html(isset($settings['custom_close_button_size']) ? $settings['custom_close_button_size'] : 28); ?>px</span>
                </div>
            </div>
            
            <div class="signalkit-setting-row" style="<?php echo esc_attr(empty($settings['custom_dismissible']) ? 'display:none;' : ''); ?>">
                <label for="custom_dismiss_duration"><?php esc_html_e('Remember Dismiss For', 'signalkit'); ?></label>
                <input type="number" 
                       id="custom_dismiss_duration"
                       name="signalkit_settings[custom_dismiss_duration]" 
                       value="<?php echo esc_attr($settings['custom_dismiss_duration']); ?>" 
                       min="1" 
                       max="365"
                       class="small-text">
                <span class="signalkit-range-value"><?php esc_html_e('days', 'signalkit'); ?></span>
            </div>
            
        </div>
    </div>
    
    <?php
}

?>
