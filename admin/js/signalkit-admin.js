/**
 * SignalKit for Google - Admin JavaScript (COMPLETE FIXED VERSION)
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Initialize color pickers
        if ($.fn.wpColorPicker) {
            $('.signalkit-color-picker').wpColorPicker();
        }
        
        // Tab switching
        $('.signalkit-tab').on('click', function() {
            const tabName = $(this).data('tab');
            
            // Update tabs
            $('.signalkit-tab').removeClass('active');
            $(this).addClass('active');
            
            // Update content
            $('.signalkit-tab-content').removeClass('active');
            $('.signalkit-tab-content[data-content="' + tabName + '"]').addClass('active');
            
            // Save active tab to localStorage
            localStorage.setItem('signalkit_active_tab', tabName);
        });
        
        // Restore active tab from localStorage
        const savedTab = localStorage.getItem('signalkit_active_tab');
        if (savedTab) {
            $('.signalkit-tab[data-tab="' + savedTab + '"]').trigger('click');
        }
        
        // Reset analytics button
        $('.signalkit-reset-analytics').on('click', function(e) {
            e.preventDefault();
            
            const bannerType = $(this).data('banner-type');
            const confirmMessage = signalkitAdmin.strings.confirmReset;
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            const $button = $(this);
            $button.prop('disabled', true).text('Resetting...');
            
            $.ajax({
                url: signalkitAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_reset_analytics',
                    nonce: signalkitAdmin.nonce,
                    banner_type: bannerType
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show reset data
                        location.reload();
                    } else {
                        alert(signalkitAdmin.strings.error);
                        $button.prop('disabled', false).text('Reset');
                    }
                },
                error: function() {
                    alert(signalkitAdmin.strings.error);
                    $button.prop('disabled', false).text('Reset');
                }
            });
        });
        
        // Form validation - IMPROVED VERSION
        $('form.signalkit-form').on('submit', function(e) {
            let isValid = true;
            const errors = [];
            
            // Get active tab
            const activeTab = $('.signalkit-tab.active').data('tab');
            
            // Validate only if banner is enabled
            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');
            
            // Validate Follow Banner if enabled
            if (followEnabled) {
                const followUrl = $('input[name="signalkit_settings[follow_google_news_url]"]').val();
                if (!followUrl || followUrl.trim() === '') {
                    errors.push('Follow Banner: Google News URL is required when banner is enabled.');
                    isValid = false;
                    
                    // Switch to follow tab if error
                    if (activeTab !== 'follow') {
                        $('.signalkit-tab[data-tab="follow"]').trigger('click');
                    }
                }
                
                // Validate dismiss duration
                const followDuration = parseInt($('input[name="signalkit_settings[follow_dismiss_duration]"]').val());
                if (isNaN(followDuration) || followDuration < 1) {
                    $('input[name="signalkit_settings[follow_dismiss_duration]"]').val(7);
                }
            }
            
            // Validate Preferred Banner if enabled
            if (preferredEnabled) {
                const preferredUrl = $('input[name="signalkit_settings[preferred_google_preferences_url]"]').val();
                if (!preferredUrl || preferredUrl.trim() === '') {
                    errors.push('Preferred Source Banner: Google Preferences URL is required when banner is enabled.');
                    isValid = false;
                    
                    // Switch to preferred tab if error
                    if (activeTab !== 'preferred' && isValid) {
                        $('.signalkit-tab[data-tab="preferred"]').trigger('click');
                    }
                }
                
                // Validate dismiss duration
                const preferredDuration = parseInt($('input[name="signalkit_settings[preferred_dismiss_duration]"]').val());
                if (isNaN(preferredDuration) || preferredDuration < 1) {
                    $('input[name="signalkit_settings[preferred_dismiss_duration]"]').val(7);
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Show errors in WordPress style
                const errorHtml = '<div class="notice notice-error is-dismissible"><p><strong>Please fix the following errors:</strong><ul style="list-style: disc; margin-left: 20px;">' + 
                    errors.map(err => '<li>' + err + '</li>').join('') + 
                    '</ul></p></div>';
                
                // Remove existing errors
                $('.notice-error').remove();
                
                // Add new error
                $('.signalkit-settings-page h1').after(errorHtml);
                
                // Scroll to top
                $('html, body').animate({
                    scrollTop: $('.signalkit-settings-page').offset().top - 50
                }, 500);
                
                return false;
            }
            
            // Fix any invalid values before submit
            fixInvalidValues();
        });
        
        // Function to fix invalid values
        function fixInvalidValues() {
            // Fix dismiss duration values
            $('input[type="number"][min="1"]').each(function() {
                const val = parseInt($(this).val());
                const min = parseInt($(this).attr('min'));
                if (isNaN(val) || val < min) {
                    $(this).val(min);
                }
            });
        }
        
        // Toggle dependent fields
        function toggleDependentFields() {
            // Follow Banner
            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            const $followTab = $('.signalkit-tab-content[data-content="follow"]');
            
            if (!followEnabled) {
                $followTab.find('.signalkit-setting-row').not(':first').css('opacity', '0.5');
            } else {
                $followTab.find('.signalkit-setting-row').css('opacity', '1');
            }
            
            // Preferred Banner
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');
            const $preferredTab = $('.signalkit-tab-content[data-content="preferred"]');
            
            if (!preferredEnabled) {
                $preferredTab.find('.signalkit-setting-row').not(':first').css('opacity', '0.5');
            } else {
                $preferredTab.find('.signalkit-setting-row').css('opacity', '1');
            }
            
            // Educational link fields
            const showEducational = $('input[name="signalkit_settings[preferred_show_educational_link]"]').is(':checked');
            const $educationalRow = $('input[name="signalkit_settings[preferred_educational_text]"]').closest('.signalkit-setting-row');
            
            if (preferredEnabled && showEducational) {
                $educationalRow.show();
            } else {
                $educationalRow.hide();
            }
        }
        
        // Initial toggle
        toggleDependentFields();
        
        // Bind toggle events
        $('input[name="signalkit_settings[follow_enabled]"]').on('change', toggleDependentFields);
        $('input[name="signalkit_settings[preferred_enabled]"]').on('change', toggleDependentFields);
        $('input[name="signalkit_settings[preferred_show_educational_link]"]').on('change', toggleDependentFields);
        
        // Live preview helper text
        $('.signalkit-setting-row input, .signalkit-setting-row textarea').on('input', function() {
            const $input = $(this);
            const value = $input.val();
            
            // Replace [site_name] placeholder in real-time
            if (value.includes('[site_name]')) {
                const siteName = $('input[name="signalkit_settings[site_name]"]').val() || 'Your Site';
                const preview = value.replace(/\[site_name\]/g, siteName);
                
                if (!$input.next('.signalkit-preview').length) {
                    $input.after('<div class="signalkit-preview" style="margin-top: 8px; padding: 12px; background: #e7f5ff; border-left: 4px solid #2271b1; border-radius: 4px;"><strong>Preview:</strong> ' + preview + '</div>');
                } else {
                    $input.next('.signalkit-preview').html('<strong>Preview:</strong> ' + preview);
                }
            } else {
                $input.next('.signalkit-preview').remove();
            }
        });
        
        // Auto-fix number inputs on blur
        $('input[type="number"]').on('blur', function() {
            const $this = $(this);
            const val = parseInt($this.val());
            const min = parseInt($this.attr('min'));
            const max = parseInt($this.attr('max'));
            
            if (isNaN(val) || val < min) {
                $this.val(min);
            } else if (max && val > max) {
                $this.val(max);
            }
        });
        
        // Smooth scroll to errors
        if ($('.settings-error').length) {
            $('html, body').animate({
                scrollTop: $('.settings-error').offset().top - 100
            }, 500);
        }
        
        // Show success message on save
        if (window.location.search.includes('settings-updated=true')) {
            const successHtml = '<div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully!</strong></p></div>';
            $('.signalkit-settings-page h1').after(successHtml);
            
            // Auto dismiss after 3 seconds
            setTimeout(function() {
                $('.notice-success').fadeOut();
            }, 3000);
        }
        
        console.log('SignalKit Admin: Initialized');
    });

})(jQuery);