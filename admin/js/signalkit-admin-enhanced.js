/**
 * SignalKit Admin Enhanced JavaScript v1.0
 * Full Live Preview Support for Advanced Customization
 *
 * @package SignalKit
 * @version 2.0.0
 */

(function ($) {
    'use strict';

    // ========================================
    // ENHANCED PREVIEW SYSTEM
    // ========================================

    const SignalKitEnhancedPreview = {

        /**
         * Initialize enhanced preview functionality
         */
        init: function () {
            this.bindStylePresets();
            this.bindRangeSliders();
            this.bindEffectToggles();
            this.bindColorPickers();
            this.bindAnimationTest();
            this.bindGradientSettings();
            this.updatePresetActiveStates();
            this.setupColorThemePresets();
        },

        /**
         * Bind style preset radio buttons
         */
        bindStylePresets: function () {
            const self = this;

            $(document).on('change', 'input[name*="banner_style"]', function () {
                const bannerType = $(this).data('banner');
                const style = $(this).val();

                self.updatePreviewStyle(bannerType, style);
                self.updatePresetActiveStates();
                self.toggleGradientSettings(bannerType, style);
            });
        },

        /**
         * Update preset active states
         */
        updatePresetActiveStates: function () {
            $('.signalkit-preset-option').each(function () {
                const $option = $(this);
                const $input = $option.find('input[type="radio"]');

                if ($input.is(':checked')) {
                    $option.addClass('active');
                } else {
                    $option.removeClass('active');
                }
            });
        },

        /**
         * Toggle gradient settings visibility based on style
         */
        toggleGradientSettings: function (bannerType, style) {
            const $gradientSettings = $(`[data-show-for-style="gradient"]`).closest('.signalkit-setting-row');

            if (style === 'gradient') {
                $gradientSettings.slideDown(200);
            } else {
                $gradientSettings.slideUp(200);
            }
        },

        /**
         * Bind range sliders with live value display
         */
        bindRangeSliders: function () {
            const self = this;

            $(document).on('input', '.signalkit-range', function () {
                const $slider = $(this);
                const value = $slider.val();
                const $valueDisplay = $slider.siblings('.signalkit-range-value');
                const fieldName = $slider.attr('name');
                const bannerType = $slider.data('banner');

                // Update value display
                if (fieldName.includes('opacity')) {
                    $valueDisplay.text(value + '%');
                } else if (fieldName.includes('angle')) {
                    $valueDisplay.text(value + '°');
                } else {
                    $valueDisplay.text(value + 'px');
                }

                // Update preview
                self.updatePreviewFromSlider(bannerType, fieldName, value);
            });
        },

        /**
         * Bind effect toggle switches
         */
        bindEffectToggles: function () {
            const self = this;

            // Glow toggle
            $(document).on('change', 'input[name*="enable_glow"]', function () {
                const $toggle = $(this);
                const isEnabled = $toggle.is(':checked');
                const $glowSettings = $toggle.closest('.signalkit-setting-row').find('.signalkit-glow-settings');
                const bannerType = $toggle.data('banner');

                if (isEnabled) {
                    $glowSettings.slideDown(200);
                } else {
                    $glowSettings.slideUp(200);
                }

                self.updatePreviewEffect(bannerType, 'glow', isEnabled);
            });

            // Float toggle
            $(document).on('change', 'input[name*="enable_float"]', function () {
                const $toggle = $(this);
                const isEnabled = $toggle.is(':checked');
                const bannerType = $toggle.data('banner');

                self.updatePreviewEffect(bannerType, 'float', isEnabled);
            });
        },

        /**
         * Bind color pickers for gradient colors
         */
        bindColorPickers: function () {
            const self = this;

            // Initialize color pickers if wp-color-picker is available
            if ($.fn.wpColorPicker) {
                $('.signalkit-color-picker').each(function () {
                    const $input = $(this);
                    const bannerType = $input.data('banner');

                    $input.wpColorPicker({
                        change: function (event, ui) {
                            setTimeout(function () {
                                self.updatePreviewFromColorPicker(bannerType, $input.attr('name'), ui.color.toString());
                            }, 10);
                        },
                        clear: function () {
                            self.updatePreviewFromColorPicker(bannerType, $input.attr('name'), '');
                        }
                    });
                });
            }
        },

        /**
         * Bind gradient settings
         */
        bindGradientSettings: function () {
            // Initialize gradient settings visibility on load
            $('input[name*="banner_style"]:checked').each(function () {
                const style = $(this).val();
                const bannerType = $(this).data('banner');

                if (style !== 'gradient') {
                    $(`[data-show-for-style="gradient"]`).closest('.signalkit-setting-row').hide();
                }
            });
        },

        /**
         * Bind animation test buttons
         */
        bindAnimationTest: function () {
            const self = this;

            $(document).on('click', '.signalkit-test-animation', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const bannerType = $btn.data('banner');

                self.testAnimation(bannerType);
            });
        },

        /**
         * Test animation on preview banner
         */
        testAnimation: function (bannerType) {
            const $preview = $('#signalkit-preview-' + bannerType);
            const $banner = $preview.find('.signalkit-banner');

            if ($banner.length === 0) return;

            // Get current animation class
            const animationSelect = $('#' + bannerType + '_animation');
            const animation = animationSelect.length ? animationSelect.val() : 'slide_in';

            // Remove active class and reset
            $banner.removeClass('active');

            // Force reflow
            $banner[0].offsetHeight;

            // Re-add active class to trigger animation
            setTimeout(function () {
                $banner.addClass('active');
            }, 50);
        },

        /**
         * Update preview style class
         */
        updatePreviewStyle: function (bannerType, style) {
            const $preview = $('#signalkit-preview-' + bannerType);
            const $banner = $preview.find('.signalkit-banner');

            if ($banner.length === 0) return;

            // Remove all style classes
            const styleClasses = [
                'signalkit-style-modern-card',
                'signalkit-style-glass',
                'signalkit-style-solid',
                'signalkit-style-gradient',
                'signalkit-style-dark',
                'signalkit-style-toast',
                'signalkit-style-bubble',
                'signalkit-style-neon'
            ];

            $banner.removeClass(styleClasses.join(' '));
            $banner.addClass('signalkit-style-' + style);

            // Add animation feedback
            $banner.addClass('signalkit-animating');
            setTimeout(function () {
                $banner.removeClass('signalkit-animating');
            }, 300);
        },

        /**
         * Update preview from slider change
         */
        updatePreviewFromSlider: function (bannerType, fieldName, value) {
            const $preview = $('#signalkit-preview-' + bannerType);
            const $banner = $preview.find('.signalkit-banner');

            if ($banner.length === 0) return;

            // Map field names to CSS variables
            const cssVarMap = {
                'backdrop_blur': '--signalkit-backdrop-blur',
                'backdrop_opacity': '--signalkit-backdrop-opacity',
                'glow_intensity': '--signalkit-glow-intensity',
                'gradient_angle': '--signalkit-gradient-angle',
                'icon_size': '--signalkit-icon-size',
                'border_radius': '--signalkit-radius',
                'banner_width': '--signalkit-width',
                'banner_padding': '--signalkit-padding'
            };

            Object.keys(cssVarMap).forEach(function (key) {
                if (fieldName.includes(key)) {
                    let cssValue = value;

                    if (key === 'backdrop_opacity') {
                        cssValue = (value / 100);
                    } else if (key === 'gradient_angle') {
                        cssValue = value + 'deg';
                    } else if (!key.includes('opacity')) {
                        cssValue = value + 'px';
                    }

                    $banner[0].style.setProperty(cssVarMap[key], cssValue);
                }
            });
        },

        /**
         * Update preview from color picker change
         */
        updatePreviewFromColorPicker: function (bannerType, fieldName, value) {
            const $preview = $('#signalkit-preview-' + bannerType);
            const $banner = $preview.find('.signalkit-banner');

            if ($banner.length === 0) return;

            // Map field names to CSS variables
            const cssVarMap = {
                'gradient_start': '--signalkit-gradient-start',
                'gradient_end': '--signalkit-gradient-end',
                'border_color': '--signalkit-border-color',
                'primary_color': '--signalkit-primary',
                'secondary_color': '--signalkit-secondary',
                'accent_color': '--signalkit-accent',
                'text_color': '--signalkit-text'
            };

            Object.keys(cssVarMap).forEach(function (key) {
                if (fieldName.includes(key) && value) {
                    $banner[0].style.setProperty(cssVarMap[key], value);

                    // Also update RGB version if needed
                    if (['primary', 'secondary', 'accent', 'text'].some(c => key.includes(c))) {
                        const rgb = hexToRgb(value);
                        if (rgb) {
                            $banner[0].style.setProperty(cssVarMap[key] + '-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
                        }
                    }
                }
            });
        },

        /**
         * Update preview effect (glow, float)
         */
        updatePreviewEffect: function (bannerType, effect, enabled) {
            const $preview = $('#signalkit-preview-' + bannerType);
            const $banner = $preview.find('.signalkit-banner');

            if ($banner.length === 0) return;

            const effectClasses = {
                'glow': 'signalkit-pulse-glow',
                'float': 'signalkit-float-enabled'
            };

            if (enabled) {
                $banner.addClass(effectClasses[effect]);
            } else {
                $banner.removeClass(effectClasses[effect]);
            }
        },

        /**
         * Setup color theme presets
         */
        setupColorThemePresets: function () {
            const self = this;

            // Add color theme preset buttons if not already present
            this.colorThemes = {
                'ocean-blue': {
                    name: 'Ocean Blue',
                    primary: '#0077b6',
                    secondary: '#ffffff',
                    accent: '#00b4d8',
                    gradient_start: '#0077b6',
                    gradient_end: '#00b4d8'
                },
                'forest-green': {
                    name: 'Forest Green',
                    primary: '#2d6a4f',
                    secondary: '#ffffff',
                    accent: '#40916c',
                    gradient_start: '#2d6a4f',
                    gradient_end: '#40916c'
                },
                'sunset': {
                    name: 'Sunset',
                    primary: '#f72585',
                    secondary: '#ffffff',
                    accent: '#ff8500',
                    gradient_start: '#f72585',
                    gradient_end: '#ff8500'
                },
                'midnight-purple': {
                    name: 'Midnight Purple',
                    primary: '#7b2cbf',
                    secondary: '#ffffff',
                    accent: '#c77dff',
                    gradient_start: '#7b2cbf',
                    gradient_end: '#c77dff'
                },
                'coral-reef': {
                    name: 'Coral Reef',
                    primary: '#ff6b6b',
                    secondary: '#ffffff',
                    accent: '#feca57',
                    gradient_start: '#ff6b6b',
                    gradient_end: '#feca57'
                },
                'arctic-frost': {
                    name: 'Arctic Frost',
                    primary: '#48cae4',
                    secondary: '#ffffff',
                    accent: '#90e0ef',
                    gradient_start: '#48cae4',
                    gradient_end: '#90e0ef'
                },
                'dark-mode': {
                    name: 'Dark Mode',
                    primary: '#bb86fc',
                    secondary: '#1e1e1e',
                    accent: '#03dac6',
                    text: '#ffffff',
                    gradient_start: '#bb86fc',
                    gradient_end: '#03dac6'
                },
                'google-news': {
                    name: 'Google News (Default)',
                    primary: '#4285f4',
                    secondary: '#ffffff',
                    accent: '#34a853',
                    gradient_start: '#4285f4',
                    gradient_end: '#34a853'
                }
            };

            // Bind theme preset clicks
            $(document).on('click', '.signalkit-color-theme-btn', function (e) {
                e.preventDefault();
                const theme = $(this).data('theme');
                const bannerType = $(this).data('banner');

                self.applyColorTheme(bannerType, theme);
            });
        },

        /**
         * Apply color theme preset
         */
        applyColorTheme: function (bannerType, themeName) {
            const theme = this.colorThemes[themeName];
            if (!theme) return;

            const prefix = bannerType + '_';

            // Update color picker values
            const colorFields = ['primary_color', 'secondary_color', 'accent_color', 'gradient_start', 'gradient_end'];

            colorFields.forEach(function (field) {
                const themeKey = field.replace('_color', '');
                const $input = $(`input[name="signalkit_settings[${prefix}${field}]"]`);

                if ($input.length && theme[themeKey]) {
                    $input.val(theme[themeKey]);

                    // Update color picker if initialized
                    if ($input.hasClass('wp-color-picker')) {
                        $input.wpColorPicker('color', theme[themeKey]);
                    }
                }
            });

            // Update text color if provided
            if (theme.text) {
                const $textInput = $(`input[name="signalkit_settings[${prefix}text_color]"]`);
                if ($textInput.length) {
                    $textInput.val(theme.text);
                    if ($textInput.hasClass('wp-color-picker')) {
                        $textInput.wpColorPicker('color', theme.text);
                    }
                }
            }

            // Trigger preview update
            this.refreshFullPreview(bannerType);

            // Show success feedback
            this.showThemeAppliedFeedback(theme.name);
        },

        /**
         * Refresh full preview for a banner type
         * Uses event-based triggering instead of global function calls (Envato compliance)
         */
        refreshFullPreview: function (bannerType) {
            // Trigger change on a main element to refresh preview
            // This uses the existing event binding in signalkit-admin.js
            $(`#${bannerType}_primary_color`).trigger('change');
        },

        /**
         * Show feedback when theme is applied
         */
        showThemeAppliedFeedback: function (themeName) {
            const $feedback = $('<div class="signalkit-theme-feedback"><span class="dashicons dashicons-yes-alt"></span> ' + themeName + ' theme applied!</div>');

            $('body').append($feedback);

            setTimeout(function () {
                $feedback.addClass('show');
            }, 10);

            setTimeout(function () {
                $feedback.removeClass('show');
                setTimeout(function () {
                    $feedback.remove();
                }, 300);
            }, 2000);
        }
    };

    // ========================================
    // COLOR THEME PRESET SELECTOR UI
    // ========================================

    const SignalKitColorThemes = {

        /**
         * Initialize color theme UI
         */
        init: function () {
            this.injectThemeSelector();
        },

        /**
         * Inject theme selector buttons into the color settings section
         */
        injectThemeSelector: function () {
            const themes = SignalKitEnhancedPreview.colorThemes;

            ['follow', 'preferred'].forEach(function (bannerType) {
                const $colorSection = $(`#${bannerType}_primary_color`).closest('.signalkit-setting-row');

                if ($colorSection.length === 0) return;

                let html = '<div class="signalkit-color-themes-wrapper">';
                html += '<h4><span class="dashicons dashicons-art"></span> Quick Color Themes</h4>';
                html += '<div class="signalkit-color-themes">';

                Object.keys(themes).forEach(function (key) {
                    const theme = themes[key];
                    html += `
                        <button type="button" 
                                class="signalkit-color-theme-btn" 
                                data-theme="${key}" 
                                data-banner="${bannerType}"
                                title="${theme.name}">
                            <span class="signalkit-theme-preview">
                                <span class="signalkit-theme-color" style="background: ${theme.primary}"></span>
                                <span class="signalkit-theme-color" style="background: ${theme.accent}"></span>
                            </span>
                            <span class="signalkit-theme-name">${theme.name}</span>
                        </button>
                    `;
                });

                html += '</div></div>';

                $colorSection.before(html);
            });
        }
    };

    // ========================================
    // UTILITY FUNCTIONS
    // ========================================

    /**
     * Convert hex color to RGB object
     */
    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    // ========================================
    // ADDITIONAL CSS FOR THEME SELECTOR
    // ========================================

    const additionalStyles = `
        <style id="signalkit-enhanced-inline-styles">
            .signalkit-color-themes-wrapper {
                margin-bottom: 20px;
                padding: 16px;
                background: #f8f9fa;
                border-radius: 8px;
                border: 1px solid #e0e0e0;
            }
            
            .signalkit-color-themes-wrapper h4 {
                margin: 0 0 12px 0;
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
                color: #1e1e1e;
            }
            
            .signalkit-color-themes-wrapper h4 .dashicons {
                color: #2271b1;
            }
            
            .signalkit-color-themes {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .signalkit-color-theme-btn {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
                font-size: 12px;
            }
            
            .signalkit-color-theme-btn:hover {
                border-color: #2271b1;
                background: #f0f7ff;
            }
            
            .signalkit-theme-preview {
                display: flex;
                gap: 2px;
            }
            
            .signalkit-theme-color {
                width: 14px;
                height: 14px;
                border-radius: 3px;
                box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);
            }
            
            .signalkit-theme-name {
                color: #444;
                white-space: nowrap;
            }
            
            .signalkit-theme-feedback {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 100000;
                padding: 12px 20px;
                background: #2271b1;
                color: #fff;
                border-radius: 8px;
                font-size: 14px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                gap: 8px;
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.3s ease;
            }
            
            .signalkit-theme-feedback.show {
                opacity: 1;
                transform: translateY(0);
            }
            
            .signalkit-theme-feedback .dashicons {
                font-size: 20px;
                width: 20px;
                height: 20px;
            }
            
            @media (max-width: 782px) {
                .signalkit-color-themes {
                    flex-direction: column;
                }
                
                .signalkit-color-theme-btn {
                    width: 100%;
                    justify-content: flex-start;
                }
            }
        </style>
    `;

    // ========================================
    // INITIALIZE ON DOCUMENT READY
    // ========================================

    $(document).ready(function () {
        // Only run on SignalKit admin pages
        if ($('.signalkit-settings-page').length === 0) {
            return;
        }

        // Add inline styles
        $('head').append(additionalStyles);

        // Initialize enhanced preview
        SignalKitEnhancedPreview.init();

        // Initialize color theme selector
        SignalKitColorThemes.init();
    });

})(jQuery);
