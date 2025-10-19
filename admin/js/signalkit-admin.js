/**
 * SignalKit for Google - Admin JavaScript
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
        
        // Form validation
        $('form.signalkit-form').on('submit', function(e) {
            let isValid = true;
            const errors = [];
            
            // Validate Follow Banner if enabled
            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            if (followEnabled) {
                const followUrl = $('input[name="signalkit_settings[follow_google_news_url]"]').val();
                if (!followUrl || followUrl.trim() === '') {
                    errors.push('Follow Banner: Google News URL is required when banner is enabled.');
                    isValid = false;
                }
            }
            
            // Validate Preferred Banner if enabled
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');
            if (preferredEnabled) {
                const preferredUrl = $('input[name="signalkit_settings[preferred_google_preferences_url]"]').val();
                if (!preferredUrl || preferredUrl.trim() === '') {
                    errors.push('Preferred Source Banner: Google Preferences URL is required when banner is enabled.');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errors.join('\n'));
            }
        });
        
        // Toggle dependent fields
        function toggleDependentFields() {
            // Follow Banner
            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            $('input[name^="signalkit_settings[follow_"]').not('[name="signalkit_settings[follow_enabled]"]')
                .closest('.signalkit-setting-row')
                .toggle(followEnabled);
            
            // Preferred Banner
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');
            $('input[name^="signalkit_settings[preferred_"]').not('[name="signalkit_settings[preferred_enabled]"]')
                .closest('.signalkit-setting-row')
                .toggle(preferredEnabled);
            
            // Educational link fields
            const showEducational = $('input[name="signalkit_settings[preferred_show_educational_link]"]').is(':checked');
            $('input[name="signalkit_settings[preferred_educational_text]"]')
                .closest('.signalkit-setting-row')
                .toggle(preferredEnabled && showEducational);
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
                const siteName = $('input[name="signalkit_settings[site_name]"]').val();
                const preview = value.replace(/\[site_name\]/g, siteName || 'Your Site');
                
                if (!$input.next('.signalkit-preview').length) {
                    $input.after('<div class="signalkit-preview" style="margin-top: 8px; padding: 12px; background: #e7f5ff; border-left: 4px solid #2271b1; border-radius: 4px;"><strong>Preview:</strong> ' + preview + '</div>');
                } else {
                    $input.next('.signalkit-preview').html('<strong>Preview:</strong> ' + preview);
                }
            } else {
                $input.next('.signalkit-preview').remove();
            }
        });
        
        // Smooth scroll to errors
        if ($('.settings-error').length) {
            $('html, body').animate({
                scrollTop: $('.settings-error').offset().top - 100
            }, 500);
        }
        
        console.log('SignalKit Admin: Initialized');
    });

})(jQuery);