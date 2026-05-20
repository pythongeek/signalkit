<?php
/**
 * SignalKit Branding Logo Component
 *
 * A reusable responsive animated logo for SignalKit plugin branding.
 * Optimized for performance - CSS/animations only load when needed.
 *
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Template partial variables - intentionally unprefixed as these are passed from including context
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template partial receives variables from including context
// Default size options: 'small' (80px), 'medium' (120px), 'large' (180px), 'hero' (240px)
$size = isset($args['size']) ? sanitize_text_field($args['size']) : 'medium';
$show_text = isset($args['show_text']) ? (bool)$args['show_text'] : true;
$animate = isset($args['animate']) ? (bool)$args['animate'] : true;
$classes = isset($args['classes']) ? esc_attr($args['classes']) : '';

// Size mappings
$size_map = [
    'small' => 80,
    'medium' => 120,
    'large' => 180,
    'hero' => 240
];

$icon_size = isset($size_map[$size]) ? $size_map[$size] : 120;
$text_size = $icon_size * 0.44; // Proportional text sizing

?>
<div class="signalkit-branding-logo <?php echo esc_attr($classes); ?>" 
     data-size="<?php echo esc_attr($size); ?>" 
     data-animate="<?php echo esc_attr($animate ? 'true' : 'false'); ?>">
    
    <!-- SVG ICON -->
    <svg class="signalkit-logo-icon" 
         width="<?php echo esc_attr($icon_size); ?>" 
         height="<?php echo esc_attr($icon_size); ?>" 
         viewBox="0 0 200 200" 
         xmlns="http://www.w3.org/2000/svg"
         aria-label="SignalKit Logo">
        <defs>
            <linearGradient id="signalkit-gradient-<?php echo esc_attr(wp_rand(1000, 9999)); ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#2563eb" />
                <stop offset="50%" style="stop-color:#9333ea" />
                <stop offset="100%" style="stop-color:#0ea5e9" />
            </linearGradient>
        </defs>
        
        <!-- Tech Polygon Core -->
        <path d="M100,60 L135,80 L135,120 L100,140 L65,120 L65,80 Z" 
              fill="url(#signalkit-gradient-<?php echo esc_attr(wp_rand(1000, 9999)); ?>)"
              stroke="rgba(255,255,255,0.2)" 
              stroke-width="1" />
        
        <!-- Center Dot -->
        <circle cx="100" cy="100" r="12" fill="white" />
        
        <!-- Orbital Rings (Crisp Strokes) -->
        <path d="M100,30 Q170,30 170,100" 
              fill="none" 
              stroke="url(#signalkit-gradient-<?php echo esc_attr(wp_rand(1000, 9999)); ?>)" 
              stroke-width="6"
              stroke-linecap="round" />
        <path d="M100,170 Q30,170 30,100" 
              fill="none" 
              stroke="url(#signalkit-gradient-<?php echo esc_attr(wp_rand(1000, 9999)); ?>)" 
              stroke-width="6"
              stroke-linecap="round" />
        
        <!-- Dotted Segments -->
        <path d="M170,100 Q170,170 100,170" 
              fill="none" 
              stroke="#cbd5e1" 
              stroke-width="4" 
              stroke-dasharray="8 8"
              stroke-linecap="round" 
              opacity="0.6" />
        <path d="M30,100 Q30,30 100,30" 
              fill="none" 
              stroke="#cbd5e1" 
              stroke-width="4" 
              stroke-dasharray="8 8"
              stroke-linecap="round" 
              opacity="0.6" />
        
        <?php if ($animate): ?>
        <!-- Animated Pulse Ring -->
        <circle cx="100" cy="100" r="60" 
                class="signalkit-pulse-ring" 
                fill="none" 
                stroke="#3b82f6" 
                stroke-width="2" />
        <?php endif; ?>
    </svg>
    
    <?php if ($show_text): ?>
    <!-- TEXT -->
    <div class="signalkit-brand-text-container">
        <div class="signalkit-brand-text" 
             data-text="SIGNALKIT" 
             style="font-size: <?php echo esc_attr($text_size); ?>px;">
            SIGNAL<span class="signalkit-text-light">KIT</span>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals ?>
