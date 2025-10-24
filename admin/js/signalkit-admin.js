/**
 * SignalKit for Google - Admin JavaScript
 * Version: 2.typo1.1 - DEBOUNCE FIXED + PREVIEW FULLY WORKING
 */

(function($) {
    'use strict';

    // ========================================
    // DEBOUNCE UTILITY - DEFINED FIRST (GLOBAL SCOPE)
    // ========================================
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ========================================
    // LIVE PREVIEW ENGINE (DEFINED AFTER DEBOUNCE)
    // ========================================
    const SignalKitPreview = {
        currentDevice: 'desktop',
        siteName: $('input[name="signalkit_settings[site_name]"]').val() || 'Your Site',
        $previewViewport: $('.signalkit-preview-viewport'),
        $previewFollow: $('#signalkit-preview-follow'),
        $previewPreferred: $('#signalkit-preview-preferred'),
        updateTimeout: null,

        init: function() {
            this.bindPreviewTriggers();
            this.bindDeviceSwitch();
            this.initializePreview();
            console.debug('SignalKit Preview: Initialized');
        },

        bindPreviewTriggers: function() {
            const self = this;

            const debouncedUpdate = debounce(function(e) {
                const $target = $(e.target);
                const bannerType = $target.closest('.signalkit-tab-content').data('content') || $target.data('banner');

                if (bannerType === 'global' || $target.attr('name') === 'signalkit_settings[site_name]') {
                    self.siteName = $('input[name="signalkit_settings[site_name]"]').val() || 'Your Site';
                    self.updatePreview('follow');
                    self.updatePreview('preferred');
                } else if (bannerType === 'follow' || bannerType === 'preferred') {
                    self.updatePreview(bannerType);
                }
            }, 250);

            $('.signalkit-form').on('input keyup change', '.signalkit-preview-trigger, .signalkit-color-picker', debouncedUpdate);

            // Update range value displays
            $('input[type="number"].small-text').each(function() {
                const $this = $(this);
                $this.next('.signalkit-range-value').text($this.val() + 'px');
            }).on('input', function() {
                $(this).next('.signalkit-range-value').text($(this).val() + 'px');
            });
        },

        bindDeviceSwitch: function() {
            const self = this; // ← FIXED: Declare self before use
            $('.signalkit-preview-device').on('click', function() {
                const $this = $(this);
                self.currentDevice = $this.data('device');

                $('.signalkit-preview-device').removeClass('active');
                $this.addClass('active');

                self.$previewViewport.attr('data-device', self.currentDevice);

                self.updatePreview('follow');
                self.updatePreview('preferred');
            });
        },

        initializePreview: function() {
            this.updatePreview('follow');
            this.updatePreview('preferred');
            const activeTab = $('.signalkit-tab.active').data('tab') || 'follow';
            this.updatePreviewVisibility(activeTab);
        },

        updatePreviewVisibility: function(tabName) {
            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');
            const showFollow = followEnabled && (tabName === 'follow' || tabName === 'global');
            const showPreferred = preferredEnabled && (tabName === 'preferred' || tabName === 'global');

            this.$previewFollow.toggle(showFollow);
            this.$previewPreferred.toggle(showPreferred);
        },

        updatePreview: function(bannerType) {
            if (this.updateTimeout) {
                clearTimeout(this.updateTimeout);
            }
            const self = this;
            this.updateTimeout = setTimeout(() => {
                self._performAjaxUpdate(bannerType);
                self.updateTimeout = null;
            }, 100);
        },

        _performAjaxUpdate: function(bannerType) {
            if (!bannerType || (bannerType !== 'follow' && bannerType !== 'preferred')) {
                return;
            }

            const $previewContainer = (bannerType === 'follow') ? this.$previewFollow : this.$previewPreferred;
            const settings = this.getSettings(bannerType);

            if (!settings.enabled) {
                $previewContainer.hide().html('');
                this.updatePreviewVisibility($('.signalkit-tab.active').data('tab'));
                return;
            }

            const showOnDesktop = $(`input[name="signalkit_settings[${bannerType}_desktop_enabled]"]`).is(':checked');
            const showOnMobile = $(`input[name="signalkit_settings[${bannerType}_mobile_enabled]"]`).is(':checked');

            if ((this.currentDevice === 'desktop' && !showOnDesktop) || (this.currentDevice === 'mobile' && !showOnMobile)) {
                $previewContainer.hide().html('');
                this.updatePreviewVisibility($('.signalkit-tab.active').data('tab'));
                return;
            }

            $.ajax({
                url: signalkitAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_preview_banner',
                    nonce: signalkitAdmin.previewNonce || signalkitAdmin.nonce,
                    banner_type: bannerType,
                    settings: settings,
                    device: this.currentDevice
                },
                success: (response) => {
                    if (response && response.success) {
                        $previewContainer.html(response.data.html);
                        this.applyCustomStyles($previewContainer.find('.signalkit-banner'), bannerType, settings, this.currentDevice);
                        this.updatePreviewVisibility($('.signalkit-tab.active').data('tab'));

                        // Trigger animation
                        const $banner = $previewContainer.find('.signalkit-banner');
                        $banner.removeClass('active');
                        setTimeout(() => $banner.addClass('active'), 50);
                    } else {
                        console.error('Preview error:', response?.data?.message || 'Unknown error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Preview Error:', xhr.status, xhr.responseText || error);
                }
            });
        },

        getSettings: function(bannerType) {
            const prefix = `signalkit_settings[${bannerType}_`;
            const $tabContent = $(`.signalkit-tab-content[data-content="${bannerType}"]`);

            const getValue = (selector) => {
                const $el = $tabContent.find(selector);
                if ($el.length === 0) return undefined;
                if ($el.is(':checkbox')) return $el.is(':checked');
                if ($el.is('select')) return $el.val();
                if ($el.is('textarea')) return $el.val();
                return $el.val();
            };

            return {
                enabled: getValue(`input[name="${prefix}enabled]"]`),
                headline: getValue(`input[name="${prefix}banner_headline]"]`) || '',
                description: getValue(`textarea[name="${prefix}banner_description]"]`) || '',
                buttonText: getValue(`input[name="${prefix}button_text]"]`) || '',
                primaryColor: getValue(`input[name="${prefix}primary_color]"]`) || '#4285f4',
                secondaryColor: getValue(`input[name="${prefix}secondary_color]"]`) || '#ffffff',
                accentColor: getValue(`input[name="${prefix}accent_color]"]`) || (bannerType === 'follow' ? '#34a853' : '#ea4335'),
                textColor: getValue(`input[name="${prefix}text_color]"]`) || '#1a1a1a',
                position: getValue(`select[name="${prefix}position]"]`) || 'bottom_left',
                mobilePosition: getValue(`select[name="${prefix}mobile_position]"]`) || 'bottom',
                animation: getValue(`select[name="${prefix}animation]"]`) || 'slide_in',
                dismissible: getValue(`input[name="${prefix}dismissible]"]`),
                bannerWidth: parseInt(getValue(`input[name="${prefix}banner_width]"]`) || 360),
                bannerPadding: parseInt(getValue(`input[name="${prefix}banner_padding]"]`) || 16),
                borderRadius: parseInt(getValue(`input[name="${prefix}border_radius]"]`) || 8),
                fontSizeHeadline: parseInt(getValue(`input[name="${prefix}font_size_headline]"]`) || 15),
                fontSizeDescription: parseInt(getValue(`input[name="${prefix}font_size_description]"]`) || 13),
                fontSizeButton: parseInt(getValue(`input[name="${prefix}font_size_button]"]`) || 14),
                siteName: $('input[name="signalkit_settings[site_name]"]').val() || 'Your Site',
                mobileStackOrder: parseInt(getValue(`input[name="${prefix}mobile_stack_order]"]`) || (bannerType === 'follow' ? 1 : 2)),
                // Preferred specific
                educationalText: getValue(`input[name="${prefix}educational_text]"]`) || '',
                showEducational: getValue(`input[name="${prefix}show_educational_link]"]`),
                educationalUrl: getValue(`input[name="${prefix}educational_post_url]"]`) || '#'
            };
        },

        applyCustomStyles: function($banner, bannerType, settings, device) {
            if (!$banner.length) return;

            // Remove old position/animation classes
            $banner.removeClass((index, className) => (className.match(/(^|\s)signalkit-(position|animation|stack-order)-[a-z0-9_\-]+/g) || []).join(' '));

            const positionClass = device === 'mobile' ? `signalkit-position-mobile-${settings.mobilePosition}` : `signalkit-position-${settings.position}`;
            const animationClass = `signalkit-animation-${settings.animation}`;
            $banner.addClass(positionClass).addClass(animationClass);

            if (device === 'mobile') {
                $banner.addClass(`signalkit-stack-order-${settings.mobileStackOrder}`);
            }

            const hoverColor = this.darkenColor(settings.primaryColor, 10);
            $banner.css({
                '--signalkit-primary': settings.primaryColor,
                '--signalkit-primary-hover': hoverColor,
                '--signalkit-secondary': settings.secondaryColor,
                '--signalkit-accent': settings.accentColor,
                '--signalkit-text': settings.textColor,
                'max-width': device === 'desktop' ? `${settings.bannerWidth}px` : 'calc(100% - 32px)',
                'padding': `${settings.bannerPadding}px`,
                'border-radius': `${settings.borderRadius}px`,
                'background-color': settings.secondaryColor,
                'border-left': `4px solid ${settings.primaryColor}`  // ← FIXED: uple → settings
            });

            $banner.find('.signalkit-headline').css({
                'font-size': `${settings.fontSizeHeadline}px`,
                'color': settings.textColor
            });
            $banner.find('.signalkit-description').css({
                'font-size': `${settings.fontSizeDescription}px`,
                'color': settings.textColor
            });
            $banner.find('.signalkit-button').css({
                'font-size': `${settings.fontSizeButton}px`,
                'background-color': settings.primaryColor,
                'color': settings.secondaryColor
            });

            $banner.find('.signalkit-button').off('mouseenter mouseleave')
                .on('mouseenter', function() { $(this).css('background-color', hoverColor); })
                .on('mouseleave', function() { $(this).css('background-color', settings.primaryColor); });

            $banner.find('.signalkit-close, .signalkit-educational-link').css('color', settings.textColor);
            $banner.find('.signalkit-icon svg path, .signalkit-icon svg circle').attr('fill', settings.primaryColor);
        },

        darkenColor: function(hex, percent) {
            if (!hex || hex.length < 6) return '#000000';
            hex = hex.replace('#', '');
            let num = parseInt(hex, 16);
            let r = (num >> 16), g = ((num >> 8) & 0x00FF), b = (num & 0x0000FF);
            r = Math.max(0, Math.min(255, Math.floor(r * (100 - percent) / 100)));
            g = Math.max(0, Math.min(255, Math.floor(g * (100 - percent) / 100)));
            b = Math.max(0, Math.min(255, Math.floor(b * (100 - percent) / 100)));
            return '#' + String("000000" + ((r << 16) | (g << 8) | b).toString(16)).slice(-6);
        }
    };

    // ========================================
    // DOCUMENT READY
    // ========================================
    $(document).ready(function() {

        // Initialize color pickers
        if ($.fn.wpColorPicker) {
            $('.signalkit-color-picker').wpColorPicker({
                change: function(event, ui) {
                    $(event.target).addClass('signalkit-preview-trigger').trigger('change');
                },
                clear: function(event) {
                    $(event.target).addClass('signalkit-preview-trigger').trigger('change');
                }
            });
        }

        // Tab switching
        $('.signalkit-tab').on('click', function(e) {
            e.preventDefault();
            const tabName = $(this).data('tab');
            $('.signalkit-tab').removeClass('active');
            $(this).addClass('active');
            $('.signalkit-tab-content').removeClass('active');
            $(`.signalkit-tab-content[data-content="${tabName}"]`).addClass('active');
            localStorage.setItem('signalkit_active_tab', tabName);
            SignalKitPreview.updatePreviewVisibility(tabName);
            SignalKitPreview.updatePreview('follow');
            SignalKitPreview.updatePreview('preferred');
        });

        const savedTab = localStorage.getItem('signalkit_active_tab');
        if (savedTab && $(`.signalkit-tab[data-tab="${savedTab}"]`).length) {
            $(`.signalkit-tab[data-tab="${savedTab}"]`).trigger('click');
        } else {
            $('.signalkit-tab:first').trigger('click');
        }

        // Analytics reset
        $('.signalkit-reset-analytics').on('click', function(e) {
            e.preventDefault();
            const bannerType = $(this).data('banner-type');
            const confirmMsg = bannerType === 'all'
                ? 'Are you sure you want to reset ALL analytics? This cannot be undone.'
                : signalkitAdmin.strings.confirmReset || 'Are you sure?';

            if (!confirm(confirmMsg)) return;

            const $btn = $(this).prop('disabled', true).text('Resetting...');
            $.post(signalkitAdmin.ajaxUrl, {
                action: 'signalkit_reset_analytics',
                nonce: signalkitAdmin.nonce,
                banner_type: bannerType
            }, function(res) {
                if (res.success) location.reload();
                else alert((signalkitAdmin.strings.error || 'Error') + (res.data ? ': ' + res.data.message : ''));
            }).always(() => $btn.prop('disabled', false).text('Reset'));
        });

        // Export / Import
        $('.signalkit-export-settings').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this).prop('disabled', true).text('Exporting...');
            $.post(signalkitAdmin.ajaxUrl, {
                action: 'signalkit_export_settings',
                nonce: signalkitAdmin.nonce
            }, function(res) {
                if (res.success) {  // ← FIXED: Removed invalid "seemingly"
                    const blob = new Blob([JSON.stringify(res.data.settings, null, 2)], {type: 'application/json'});
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'signalkit-settings-' + new Date().toISOString().slice(0,10) + '.json';
                    a.click();
                    URL.revokeObjectURL(url);
                } else {
                    alert('Export failed');
                }
            }).always(() => $btn.prop('disabled', false).text('Export Settings'));
        });

        $('.signalkit-import-settings').on('click', e => { e.preventDefault(); $('#signalkit-import-file').click(); });
        $('#signalkit-import-file').on('change', function(e) {
            const file = e.target.files[0];
            if (!file || !file.type.includes('json')) return alert('Invalid JSON file');

            const reader = new FileReader();
            reader.onload = function() {
                try {
                    const settings = JSON.parse(reader.result);
                    if (confirm('Import settings? This will overwrite current settings.')) {
                        $.post(signalkitAdmin.ajaxUrl, {
                            action: 'signalkit_import_settings',
                            nonce: signalkitAdmin.nonce,
                            settings: settings
                        }, res => res.success ? location.reload() : alert('Import failed'));
                    }
                } catch (err) {
                    alert('Invalid JSON: ' + err.message);
                }
            };
            reader.readAsText(file);
        });

        // Toggle dependent fields
        function toggleDependentFields() {
            ['follow', 'preferred'].forEach(bannerType => {
                const enabled = $(`input[name="signalkit_settings[${bannerType}_enabled]"]`).is(':checked');
                const $content = $(`.signalkit-tab-content[data-content="${bannerType}"]`);
                $content.find('.signalkit-setting-row').not(':first').css('opacity', enabled ? 1 : 0.5);

                if (bannerType === 'preferred') {
                    const showEdu = $(`input[name="signalkit_settings[preferred_show_educational_link]"]`).is(':checked');
                    const $row = $(`input[name="signalkit_settings[preferred_educational_text]"]`).closest('.signalkit-setting-row');
                    $row.toggle(enabled && showEdu);
                }
            });
        }

        $('input[name="signalkit_settings[follow_enabled]"], input[name="signalkit_settings[preferred_enabled]"], input[name="signalkit_settings[preferred_show_educational_link]"]')
            .on('change', function() {
                toggleDependentFields();
                const type = this.name.includes('follow') ? 'follow' : 'preferred';
                SignalKitPreview.updatePreview(type);
            });

        // Success message
        if (location.search.includes('settings-updated=true')) {
            $('.signalkit-settings-page h1').after('<div class="notice notice-success is-dismissible" style="margin-top:10px"><p><strong>Settings saved!</strong></p></div>');
            setTimeout(() => $('.notice-success').fadeOut(), 3000);
        }

        toggleDependentFields();
        SignalKitPreview.init();

        console.log('SignalKit Admin JS: Fully loaded v2.1.1');
    });

})(jQuery);