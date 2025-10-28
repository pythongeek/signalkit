<?php
/**
 * Banner Preview Template for Admin Live Preview
 * Version: 2.0.0 - IMPROVED: Better styling, responsive design, visual enhancements
 * 
 * Security: All output is properly escaped
 * WordPress Compatible: Uses WP escaping functions
 * Envato Compatible: GPL-2.0+ license
 * 
 * @package SignalKit_For_Google
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Use $banner array passed from AJAX
?>
<div class="signalkit-preview-wrapper" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; border-radius: 12px; margin-bottom: 20px;">
    <div class="signalkit-preview-header" style="text-align: center; margin-bottom: 30px;">
        <h3 style="color: #fff; font-size: 18px; font-weight: 600; margin: 0 0 8px 0;">
            <?php 
            echo esc_html($banner['type'] === 'follow' ? '🔔 Follow Banner Preview' : '⭐ Preferred Source Banner Preview'); 
            ?>
        </h3>
        <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 0;">
            This is how your banner will appear to visitors on <?php echo esc_html($banner['device'] === 'mobile' ? 'Mobile' : 'Desktop'); ?> devices
        </p>
    </div>

    <div class="signalkit-preview-container" style="background: #f5f7fa; border-radius: 8px; padding: 30px; position: relative; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
        <!-- Device Frame Mockup -->
        <div class="signalkit-device-frame" style="max-width: <?php echo $banner['device'] === 'mobile' ? '375px' : '800px'; ?>; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); position: relative; overflow: hidden;">
            
            <!-- Browser/Phone Header -->
            <div class="signalkit-device-header" style="background: #e8eaed; padding: 12px 16px; border-bottom: 1px solid #dadce0; display: flex; align-items: center; gap: 8px;">
                <?php if ($banner['device'] === 'mobile'): ?>
                    <div style="font-size: 11px; color: #5f6368;">9:41</div>
                    <div style="margin-left: auto; display: flex; gap: 4px;">
                        <div style="width: 16px; height: 10px; background: #5f6368; border-radius: 2px;"></div>
                        <div style="width: 16px; height: 10px; background: #5f6368; border-radius: 2px;"></div>
                    </div>
                <?php else: ?>
                    <div style="display: flex; gap: 6px;">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #ff5f56;"></div>
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #ffbd2e;"></div>
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #27c93f;"></div>
                    </div>
                    <div style="flex: 1; text-align: center; font-size: 12px; color: #5f6368;">yoursite.com</div>
                <?php endif; ?>
            </div>

            <!-- Content Area -->
            <div class="signalkit-device-content" style="padding: 20px; min-height: 200px; background: #fafbfc;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <div style="height: 8px; background: #e8eaed; border-radius: 4px; margin-bottom: 12px; width: 70%;"></div>
                    <div style="height: 8px; background: #e8eaed; border-radius: 4px; margin-bottom: 12px; width: 90%;"></div>
                    <div style="height: 8px; background: #e8eaed; border-radius: 4px; margin-bottom: 12px; width: 60%;"></div>
                    <div style="height: 80px; background: #e8eaed; border-radius: 8px; margin-top: 20px;"></div>
                </div>
            </div>

            <!-- Actual Banner Preview -->
            <div class="signalkit-banner signalkit-banner-<?php echo esc_attr($banner['type']); ?> signalkit-animation-<?php echo esc_attr($banner['animation']); ?> signalkit-position-<?php echo $banner['device'] === 'mobile' ? 'mobile-' . esc_attr($banner['mobile_position']) : esc_attr($banner['position']); ?> <?php echo $banner['device'] === 'mobile' ? 'signalkit-stack-order-' . esc_attr($banner['mobile_stack_order']) : ''; ?>" 
                 style="display:block !important; position: absolute !important; opacity: 1 !important; animation: none !important; <?php 
                 // Position styles based on device and settings
                 if ($banner['device'] === 'mobile') {
                     echo $banner['mobile_position'] === 'bottom' ? 'bottom: 0 !important;' : 'top: 0 !important;';
                     echo ' left: 0 !important; right: 0 !important; width: 100% !important;';
                 } else {
                     switch ($banner['position']) {
                         case 'bottom_left':
                             echo 'bottom: 20px !important; left: 20px !important;';
                             break;
                         case 'bottom_right':
                             echo 'bottom: 20px !important; right: 20px !important;';
                             break;
                         case 'top_left':
                             echo 'top: 20px !important; left: 20px !important;';
                             break;
                         case 'top_right':
                             echo 'top: 20px !important; right: 20px !important;';
                             break;
                     }
                 }
                 ?>">
                <div class="signalkit-banner-inner">
                    <div class="signalkit-content">
                        <h3 class="signalkit-headline"><?php echo esc_html($banner['headline']); ?></h3>
                        <p class="signalkit-description"><?php echo esc_html($banner['description']); ?></p>
                        <div class="signalkit-actions">
                            <a href="<?php echo esc_url($banner['button_url']); ?>" class="signalkit-button" target="_blank" onclick="return false;">
                                <?php echo esc_html($banner['button_text']); ?>
                            </a>
                            <?php if ($banner['show_educational'] && $banner['type'] === 'preferred'): ?>
                                <a href="<?php echo esc_url($banner['educational_url']); ?>" class="signalkit-educational-link" target="_blank" onclick="return false;">
                                    <?php echo esc_html($banner['educational_text']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($banner['dismissible']): ?>
                        <button class="signalkit-close" aria-label="Dismiss" onclick="return false;">&times;</button>
                    <?php endif; ?>
                    <div class="signalkit-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Info Badge -->
        <div style="text-align: center; margin-top: 20px;">
            <span style="display: inline-block; background: rgba(102, 126, 234, 0.1); color: #667eea; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                <svg style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                Preview Mode - Buttons are disabled
            </span>
        </div>
    </div>

    <!-- Preview Details -->
    <div class="signalkit-preview-details" style="margin-top: 20px; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 8px; backdrop-filter: blur(10px);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div style="text-align: center;">
                <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Position</div>
                <div style="color: #fff; font-size: 14px; font-weight: 600;">
                    <?php echo esc_html(ucwords(str_replace('_', ' ', $banner['device'] === 'mobile' ? $banner['mobile_position'] : $banner['position']))); ?>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Animation</div>
                <div style="color: #fff; font-size: 14px; font-weight: 600;">
                    <?php echo esc_html(ucwords(str_replace('_', ' ', $banner['animation']))); ?>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Width</div>
                <div style="color: #fff; font-size: 14px; font-weight: 600;">
                    <?php echo $banner['device'] === 'mobile' ? '100%' : esc_html($banner['banner_width'] . 'px'); ?>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Dismissible</div>
                <div style="color: #fff; font-size: 14px; font-weight: 600;">
                    <?php echo $banner['dismissible'] ? '✓ Yes' : '✗ No'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Smooth transitions for preview */
    .signalkit-preview-wrapper * {
        transition: all 0.3s ease;
    }
    
    /* Hover effect on device frame */
    .signalkit-device-frame:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 30px rgba(0,0,0,0.15) !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .signalkit-preview-wrapper {
            padding: 20px 15px !important;
        }
        
        .signalkit-preview-container {
            padding: 20px 15px !important;
        }
        
        .signalkit-preview-details {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>