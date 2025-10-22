<?php
/**
 * Google Preferred Source Banner Template
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('signalkit_settings', array());
$site_name = get_bloginfo('name');

// Get banner content
$headline = isset($settings['preferred_banner_headline']) ? $settings['preferred_banner_headline'] : '';
$description = isset($settings['preferred_banner_description']) ? $settings['preferred_banner_description'] : '';
$button_text = isset($settings['preferred_button_text']) ? $settings['preferred_button_text'] : __('Add As A Preferred Source', 'signalkit-for-google');
$educational_text = isset($settings['preferred_educational_text']) ? $settings['preferred_educational_text'] : '';
$google_preferences_url = isset($settings['preferred_google_preferences_url']) ? $settings['preferred_google_preferences_url'] : '';
$educational_post_url = isset($settings['preferred_educational_post_url']) ? $settings['preferred_educational_post_url'] : '';
$show_educational_link = isset($settings['preferred_show_educational_link']) ? $settings['preferred_show_educational_link'] : true;
$position = isset($settings['preferred_position']) ? $settings['preferred_position'] : 'bottom_right';
$animation = isset($settings['preferred_animation']) ? $settings['preferred_animation'] : 'slide_in';
$dismissible = isset($settings['preferred_dismissible']) ? $settings['preferred_dismissible'] : true;

// Replace [site_name] placeholder
$headline = str_replace('[site_name]', $site_name, $headline);
$description = str_replace('[site_name]', $site_name, $description);

// Generate position classes
$position_class = 'signalkit-position-' . esc_attr($position);
$animation_class = 'signalkit-animation-' . esc_attr($animation);
?>

<div class="signalkit-banner signalkit-banner-preferred <?php echo esc_attr($position_class); ?> <?php echo esc_attr($animation_class); ?>" 
     data-banner-type="preferred" 
     style="display: none;">
    
    <div class="signalkit-banner-content">
        
        <!-- Star Icon for Preferred Source -->
        <div class="signalkit-icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="20" fill="#EA4335"/>
                <path d="M24 10L27.09 19.26L37 19.27L28.96 25.14L32.04 34.41L24 28.52L15.96 34.41L19.04 25.14L11 19.27L20.91 19.26L24 10Z" fill="white"/>
            </svg>
        </div>
        
        <!-- Banner Text -->
        <div class="signalkit-text">
            <?php if (!empty($headline)): ?>
                <h3 class="signalkit-headline"><?php echo esc_html($headline); ?></h3>
            <?php endif; ?>
            
            <?php if (!empty($description)): ?>
                <p class="signalkit-description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- CTA Button -->
        <div class="signalkit-actions">
            <?php if (!empty($google_preferences_url)): ?>
                <a href="<?php echo esc_url($google_preferences_url); ?>" 
                   class="signalkit-button signalkit-preferred-button"
                   target="_blank" 
                   rel="noopener noreferrer"
                   data-banner-type="preferred">
                    <?php echo esc_html($button_text); ?>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="signalkit-icon-star">
                        <path d="M8 1L10.09 6.26L16 6.27L11.18 9.98L13.26 15.23L8 11.51L2.74 15.23L4.82 9.98L0 6.27L5.91 6.26L8 1Z"/>
                    </svg>
                </a>
            <?php endif; ?>
            
            <!-- Educational Link -->
            <?php if ($show_educational_link && !empty($educational_post_url) && !empty($educational_text)): ?>
                <a href="<?php echo esc_url($educational_post_url); ?>" 
                   class="signalkit-educational-link"
                   target="_blank" 
                   rel="noopener noreferrer">
                    <?php echo esc_html($educational_text); ?>
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" style="vertical-align: middle; margin-left: 4px;">
                        <path d="M7 0C3.13 0 0 3.13 0 7s3.13 7 7 7 7-3.13 7-7-3.13-7-7-7zm1 10H6V6h2v4zm0-5H6V3h2v2z"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Close Button -->
        <?php if ($dismissible): ?>
            <button class="signalkit-close" 
                    aria-label="<?php esc_attr_e('Close banner', 'signalkit-for-google'); ?>"
                    data-banner-type="preferred">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        <?php endif; ?>
        
    </div>
</div>