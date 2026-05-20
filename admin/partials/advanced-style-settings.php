<?php
/**
 * Advanced Banner Customization Settings Partial
 * Enhanced Design Controls for SignalKit v2.0
 *
 * @package SignalKit
 * @version 2.0.0
 * 
 * This file should be included in the settings-page.php right after the
 * typography settings for each banner type.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Template partial variables - intentionally unprefixed as these are passed from including context
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template partial
/**
 * Render the advanced style settings for a banner type
 * 
 * @param array  $settings Plugin settings array
 * @param string $type     Banner type ('follow' or 'preferred')
 */
function signalkit_render_advanced_style_settings($settings, $type) {
    $prefix = $type . '_';
    
    // Default values for new settings
    $defaults = array(
        'banner_style' => 'glassmorphism',
        'button_style' => 'default',
        'icon_style' => 'circle',
        'visibility_mode' => 'auto',
        'enable_glow' => 0,
        'enable_float' => 0,
        'glow_intensity' => 20,
        'backdrop_blur' => 12,
        'backdrop_opacity' => 95,
        'gradient_start' => $type === 'follow' ? '#4285f4' : '#ea4335',
        'gradient_end' => $type === 'follow' ? '#34a853' : '#fbbc04',
        'gradient_angle' => 135,
        'border_color' => '',
        'icon_size' => 52,
    );
    
    // Merge with actual settings
    foreach ($defaults as $key => $default) {
        $full_key = $prefix . $key;
        if (!isset($settings[$full_key])) {
            $settings[$full_key] = $default;
        }
    }
    ?>
    
    <div class="signalkit-section-header signalkit-section-advanced">
        <h3><span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span> <?php esc_html_e('Advanced Style Options', 'signalkit'); ?></h3>
        <p class="description"><?php esc_html_e('Banner design customization', 'signalkit'); ?></p>
    </div>
    
    <!-- Banner Style Presets -->
    <div class="signalkit-setting-row">
        <h4><?php esc_html_e('Banner Style Preset', 'signalkit'); ?></h4>
        <div class="signalkit-style-presets">
            <?php
            $presets = array(
                'glassmorphism' => array(
                    'label' => __('Glassmorphism', 'signalkit'),
                    'desc' => __('Native Google News style with gradient accent', 'signalkit'),
                    'icon' => '◈'
                ),
                'preferred-star' => array(
                    'label' => __('Preferred Star', 'signalkit'),
                    'desc' => __('Educational card with star icon and accent border', 'signalkit'),
                    'icon' => '★'
                ),
                'lead-gradient' => array(
                    'label' => __('Lead Gradient', 'signalkit'),
                    'desc' => __('Dark gradient with gradient text - ideal for forms', 'signalkit'),
                    'icon' => '▨'
                ),
                'modern-card' => array(
                    'label' => __('Modern Card', 'signalkit'),
                    'desc' => __('Clean, elevated card with subtle shadow', 'signalkit'),
                    'icon' => '▢'
                ),
                'glass' => array(
                    'label' => __('Frosted Glass', 'signalkit'),
                    'desc' => __('Simple frosted glass with blur', 'signalkit'),
                    'icon' => '◇'
                ),
                'solid' => array(
                    'label' => __('Solid Classic', 'signalkit'),
                    'desc' => __('Traditional solid background with accent', 'signalkit'),
                    'icon' => '■'
                ),
                'gradient' => array(
                    'label' => __('Gradient', 'signalkit'),
                    'desc' => __('Vibrant gradient background', 'signalkit'),
                    'icon' => '◐'
                ),
                'dark' => array(
                    'label' => __('Dark Mode', 'signalkit'),
                    'desc' => __('Dark theme for light backgrounds', 'signalkit'),
                    'icon' => '◐'
                ),
                'toast' => array(
                    'label' => __('Minimal Toast', 'signalkit'),
                    'desc' => __('Compact notification style', 'signalkit'),
                    'icon' => '─'
                ),
                'bubble' => array(
                    'label' => __('Floating Bubble', 'signalkit'),
                    'desc' => __('Rounded with accent border glow', 'signalkit'),
                    'icon' => '○'
                ),
                'neon' => array(
                    'label' => __('Neon Glow', 'signalkit'),
                    'desc' => __('Dark with glowing border effect', 'signalkit'),
                    'icon' => '✦'
                ),
            );
            
            foreach ($presets as $value => $preset): ?>
                <label class="signalkit-preset-option <?php echo esc_attr($settings[$prefix . 'banner_style'] === $value ? 'active' : ''); ?>">
                    <input type="radio" 
                           name="signalkit_settings[<?php echo esc_attr($prefix); ?>banner_style]" 
                           value="<?php echo esc_attr($value); ?>"
                           <?php checked($settings[$prefix . 'banner_style'], $value); ?>
                           class="signalkit-preview-trigger"
                           data-banner="<?php echo esc_attr($type); ?>">
                    <span class="signalkit-preset-icon" aria-hidden="true"><?php echo esc_html($preset['icon']); ?></span>
                    <span class="signalkit-preset-info">
                        <strong><?php echo esc_html($preset['label']); ?></strong>
                        <small><?php echo esc_html($preset['desc']); ?></small>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Visibility Mode -->
    <div class="signalkit-setting-row">
        <label for="<?php echo esc_attr($prefix); ?>visibility_mode"><?php esc_html_e('Visibility Mode', 'signalkit'); ?></label>
        <select id="<?php echo esc_attr($prefix); ?>visibility_mode" 
                name="signalkit_settings[<?php echo esc_attr($prefix); ?>visibility_mode]"
                class="signalkit-preview-trigger"
                data-banner="<?php echo esc_attr($type); ?>">
            <option value="auto" <?php selected($settings[$prefix . 'visibility_mode'], 'auto'); ?>><?php esc_html_e('Auto (Adapts to Theme)', 'signalkit'); ?></option>
            <option value="light" <?php selected($settings[$prefix . 'visibility_mode'], 'light'); ?>><?php esc_html_e('Force Light (For Dark Backgrounds)', 'signalkit'); ?></option>
            <option value="dark" <?php selected($settings[$prefix . 'visibility_mode'], 'dark'); ?>><?php esc_html_e('Force Dark (For Light Backgrounds)', 'signalkit'); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Choose banner contrast mode for optimal visibility on any background', 'signalkit'); ?></p>
    </div>
    
    <!-- Button Style -->
    <div class="signalkit-setting-row">
        <label for="<?php echo esc_attr($prefix); ?>button_style"><?php esc_html_e('Button Style', 'signalkit'); ?></label>
        <select id="<?php echo esc_attr($prefix); ?>button_style" 
                name="signalkit_settings[<?php echo esc_attr($prefix); ?>button_style]"
                class="signalkit-preview-trigger"
                data-banner="<?php echo esc_attr($type); ?>">
            <option value="default" <?php selected($settings[$prefix . 'button_style'], 'default'); ?>><?php esc_html_e('Rounded (Default)', 'signalkit'); ?></option>
            <option value="pill" <?php selected($settings[$prefix . 'button_style'], 'pill'); ?>><?php esc_html_e('Pill Shape', 'signalkit'); ?></option>
            <option value="sharp" <?php selected($settings[$prefix . 'button_style'], 'sharp'); ?>><?php esc_html_e('Sharp Corners', 'signalkit'); ?></option>
            <option value="outline" <?php selected($settings[$prefix . 'button_style'], 'outline'); ?>><?php esc_html_e('Outline Only', 'signalkit'); ?></option>
        </select>
    </div>
    
    <!-- Icon Style -->
    <div class="signalkit-setting-row">
        <label for="<?php echo esc_attr($prefix); ?>icon_style"><?php esc_html_e('Icon Style', 'signalkit'); ?></label>
        <div class="signalkit-style-grid">
            <select id="<?php echo esc_attr($prefix); ?>icon_style" 
                    name="signalkit_settings[<?php echo esc_attr($prefix); ?>icon_style]"
                    class="signalkit-preview-trigger"
                    data-banner="<?php echo esc_attr($type); ?>">
                <option value="circle" <?php selected($settings[$prefix . 'icon_style'], 'circle'); ?>><?php esc_html_e('Circle', 'signalkit'); ?></option>
                <option value="rounded" <?php selected($settings[$prefix . 'icon_style'], 'rounded'); ?>><?php esc_html_e('Rounded Square', 'signalkit'); ?></option>
                <option value="square" <?php selected($settings[$prefix . 'icon_style'], 'square'); ?>><?php esc_html_e('Square', 'signalkit'); ?></option>
            </select>
            
            <div class="signalkit-size-input inline">
                <label for="<?php echo esc_attr($prefix); ?>icon_size"><?php esc_html_e('Size', 'signalkit'); ?></label>
                <input type="number" 
                       id="<?php echo esc_attr($prefix); ?>icon_size"
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>icon_size]" 
                       value="<?php echo esc_attr($settings[$prefix . 'icon_size']); ?>" 
                       min="40" 
                       max="72" 
                       class="small-text signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
                <span class="signalkit-range-value"><?php echo esc_html($settings[$prefix . 'icon_size']); ?>px</span>
            </div>
        </div>
    </div>

    <!-- Gradient Colors (for gradient style) -->
    <div class="signalkit-setting-row signalkit-gradient-settings" data-show-for-style="gradient">
        <h4><?php esc_html_e('Gradient Colors', 'signalkit'); ?></h4>
        <div class="signalkit-color-grid compact">
            <div class="signalkit-color-input">
                <label for="<?php echo esc_attr($prefix); ?>gradient_start"><?php esc_html_e('Start Color', 'signalkit'); ?></label>
                <input type="text" 
                       id="<?php echo esc_attr($prefix); ?>gradient_start"
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>gradient_start]" 
                       value="<?php echo esc_attr($settings[$prefix . 'gradient_start']); ?>" 
                       class="signalkit-color-picker signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
            </div>
            <div class="signalkit-color-input">
                <label for="<?php echo esc_attr($prefix); ?>gradient_end"><?php esc_html_e('End Color', 'signalkit'); ?></label>
                <input type="text" 
                       id="<?php echo esc_attr($prefix); ?>gradient_end"
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>gradient_end]" 
                       value="<?php echo esc_attr($settings[$prefix . 'gradient_end']); ?>" 
                       class="signalkit-color-picker signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
            </div>
            <div class="signalkit-size-input">
                <label for="<?php echo esc_attr($prefix); ?>gradient_angle"><?php esc_html_e('Angle (°)', 'signalkit'); ?></label>
                <input type="number" 
                       id="<?php echo esc_attr($prefix); ?>gradient_angle"
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>gradient_angle]" 
                       value="<?php echo esc_attr($settings[$prefix . 'gradient_angle']); ?>" 
                       min="0" 
                       max="360" 
                       class="small-text signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
            </div>
        </div>
    </div>
    
    <!-- Backdrop & Effects -->
    <div class="signalkit-setting-row">
        <h4><?php esc_html_e('Background Effects', 'signalkit'); ?></h4>
        <div class="signalkit-effects-grid">
            <div class="signalkit-size-input">
                <label for="<?php echo esc_attr($prefix); ?>backdrop_blur"><?php esc_html_e('Blur Intensity', 'signalkit'); ?></label>
                <input type="range" 
                       id="<?php echo esc_attr($prefix); ?>backdrop_blur"
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>backdrop_blur]" 
                       value="<?php echo esc_attr($settings[$prefix . 'backdrop_blur']); ?>" 
                       min="0" 
                       max="30"
                       class="signalkit-range signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
                <span class="signalkit-range-value"><?php echo esc_html($settings[$prefix . 'backdrop_blur']); ?>px</span>
            </div>
            
            <div class="signalkit-size-input">
                <label for="<?php echo esc_attr($prefix); ?>backdrop_opacity"><?php esc_html_e('Background Opacity', 'signalkit'); ?></label>
                <input type="range" 
                       id="<?php echo esc_attr($prefix); ?>backdrop_opacity"
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>backdrop_opacity]" 
                       value="<?php echo esc_attr($settings[$prefix . 'backdrop_opacity']); ?>" 
                       min="50" 
                       max="100"
                       class="signalkit-range signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
                <span class="signalkit-range-value"><?php echo esc_html($settings[$prefix . 'backdrop_opacity']); ?>%</span>
            </div>
        </div>
    </div>
    
    <!-- Special Effects -->
    <div class="signalkit-setting-row">
        <h4><?php esc_html_e('Special Effects', 'signalkit'); ?></h4>
        <div class="signalkit-effects-toggles">
            <label class="signalkit-toggle-inline">
                <input type="checkbox" 
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>enable_glow]" 
                       value="1" 
                       <?php checked($settings[$prefix . 'enable_glow'] ?? 0, 1); ?>
                       class="signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
                <span class="signalkit-toggle-slider-mini" aria-hidden="true"></span>
                <span class="signalkit-toggle-label">
                    <strong><?php esc_html_e('Pulse Glow', 'signalkit'); ?></strong>
                    <small><?php esc_html_e('Subtle pulsing glow effect', 'signalkit'); ?></small>
                </span>
            </label>
            
            <label class="signalkit-toggle-inline">
                <input type="checkbox" 
                       name="signalkit_settings[<?php echo esc_attr($prefix); ?>enable_float]" 
                       value="1" 
                       <?php checked($settings[$prefix . 'enable_float'] ?? 0, 1); ?>
                       class="signalkit-preview-trigger"
                       data-banner="<?php echo esc_attr($type); ?>">
                <span class="signalkit-toggle-slider-mini" aria-hidden="true"></span>
                <span class="signalkit-toggle-label">
                    <strong><?php esc_html_e('Floating Animation', 'signalkit'); ?></strong>
                    <small><?php esc_html_e('Gentle floating motion', 'signalkit'); ?></small>
                </span>
            </label>
        </div>
        
        <div class="signalkit-glow-settings" style="margin-top: 12px; <?php echo esc_attr(empty($settings[$prefix . 'enable_glow']) ? 'display:none;' : ''); ?>">
            <label for="<?php echo esc_attr($prefix); ?>glow_intensity"><?php esc_html_e('Glow Intensity', 'signalkit'); ?></label>
            <input type="range" 
                   id="<?php echo esc_attr($prefix); ?>glow_intensity"
                   name="signalkit_settings[<?php echo esc_attr($prefix); ?>glow_intensity]" 
                   value="<?php echo esc_attr($settings[$prefix . 'glow_intensity']); ?>" 
                   min="0" 
                   max="50"
                   class="signalkit-range signalkit-preview-trigger"
                   data-banner="<?php echo esc_attr($type); ?>">
            <span class="signalkit-range-value"><?php echo esc_html($settings[$prefix . 'glow_intensity']); ?>px</span>
        </div>
    </div>
    
    <!-- Border Color (Optional) -->
    <div class="signalkit-setting-row">
        <div class="signalkit-color-input single">
            <label for="<?php echo esc_attr($prefix); ?>border_color"><?php esc_html_e('Custom Border Color (Optional)', 'signalkit'); ?></label>
            <input type="text" 
                   id="<?php echo esc_attr($prefix); ?>border_color"
                   name="signalkit_settings[<?php echo esc_attr($prefix); ?>border_color]" 
                   value="<?php echo esc_attr($settings[$prefix . 'border_color']); ?>" 
class="signalkit-color-picker signalkit-preview-trigger"
                    data-banner="<?php echo esc_attr($type); ?>"
                    data-default-color="">
            <p class="description"><?php esc_html_e('Leave empty to use default based on style', 'signalkit'); ?></p>
        </div>
    </div>
    <!-- Animation settings moved to main Display Settings section to avoid duplicate IDs -->
    
    <?php
}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals
?>
