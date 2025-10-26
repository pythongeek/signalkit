<?php
/**
 * Banner Preview Template for Admin Live Preview
 */
if (!defined('ABSPATH')) exit;

// Use $banner array passed from AJAX
?>
<div class="signalkit-banner signalkit-banner-<?php echo esc_attr($banner['type']); ?> signalkit-animation-<?php echo esc_attr($banner['animation']); ?> signalkit-position-<?php echo $banner['device'] === 'mobile' ? 'mobile-' . esc_attr($banner['mobile_position']) : esc_attr($banner['position']); ?> <?php echo $banner['device'] === 'mobile' ? 'signalkit-stack-order-' . esc_attr($banner['mobile_stack_order']) : ''; ?>" style="display:block !important;">
    <div class="signalkit-banner-inner">
        <div class="signalkit-content">
            <h3 class="signalkit-headline"><?php echo esc_html($banner['headline']); ?></h3>
            <p class="signalkit-description"><?php echo esc_html($banner['description']); ?></p>
            <div class="signalkit-actions">
                <a href="<?php echo esc_url($banner['button_url']); ?>" class="signalkit-button" target="_blank">
                    <?php echo esc_html($banner['button_text']); ?>
                </a>
                <?php if ($banner['show_educational'] && $banner['type'] === 'preferred'): ?>
                    <a href="<?php echo esc_url($banner['educational_url']); ?>" class="signalkit-educational-link" target="_blank">
                        <?php echo esc_html($banner['educational_text']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($banner['dismissible']): ?>
            <button class="signalkit-close" aria-label="Dismiss">&times;</button>
        <?php endif; ?>
        <div class="signalkit-icon">
            <svg width="24" height="24" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
    </div>
</div>