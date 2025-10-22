<?php
/**
 * Google News Follow Banner Template
 *
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('signalkit_settings', array());
$site_name = get_bloginfo('name');

// Get banner content
$headline = isset($settings['follow_banner_headline']) ? $settings['follow_banner_headline'] : '';
$description = isset($settings['follow_banner_description']) ? $settings['follow_banner_description'] : '';
$button_text = isset($settings['follow_button_text']) ? $settings['follow_button_text'] : __('Follow Us On Google News', 'signalkit-for-google');
$google_news_url = isset($settings['follow_google_news_url']) ? $settings['follow_google_news_url'] : '';
$position = isset($settings['follow_position']) ? $settings['follow_position'] : 'bottom_left';
$animation = isset($settings['follow_animation']) ? $settings['follow_animation'] : 'slide_in';
$dismissible = isset($settings['follow_dismissible']) ? $settings['follow_dismissible'] : true;

// Replace [site_name] placeholder
$headline = str_replace('[site_name]', $site_name, $headline);
$description = str_replace('[site_name]', $site_name, $description);

// Generate position classes
$position_class = 'signalkit-position-' . esc_attr($position);
$animation_class = 'signalkit-animation-' . esc_attr($animation);
?>

<div class="signalkit-banner signalkit-banner-follow <?php echo esc_attr($position_class); ?> <?php echo esc_attr($animation_class); ?>" 
     data-banner-type="follow" 
     style="display: none;">
    
    <div class="signalkit-banner-content">
        
        <!-- Google News Icon -->
        <div class="signalkit-icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24 4C12.95 4 4 12.95 4 24C4 35.05 12.95 44 24 44C35.05 44 44 35.05 44 24C44 12.95 35.05 4 24 4Z" fill="#4285F4"/>
                <path d="M24 14C18.48 14 14 18.48 14 24C14 29.52 18.48 34 24 34C29.52 34 34 29.52 34 24C34 18.48 29.52 14 24 14ZM24 30C20.69 30 18 27.31 18 24C18 20.69 20.69 18 24 18C27.31 18 30 20.69 30 24C30 27.31 27.31 30 24 30Z" fill="white"/>
                <circle cx="24" cy="24" r="4" fill="white"/>
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
            <?php if (!empty($google_news_url)): ?>
                <a href="<?php echo esc_url($google_news_url); ?>" 
                   class="signalkit-button signalkit-follow-button"
                   target="_blank" 
                   rel="noopener noreferrer"
                   data-banner-type="follow">
                    <?php echo esc_html($button_text); ?>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="signalkit-icon-external">
                        <path d="M14 2H10V4H12.59L6.29 10.29L7.71 11.71L14 5.41V8H16V2H14ZM12 14H4V6H2V14C2 15.1 2.9 16 4 16H12C13.1 16 14 15.1 14 14V12H12V14Z"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Close Button -->
        <?php if ($dismissible): ?>
            <button class="signalkit-close" 
                    aria-label="<?php esc_attr_e('Close banner', 'signalkit-for-google'); ?>"
                    data-banner-type="follow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        <?php endif; ?>
        
    </div>
</div>