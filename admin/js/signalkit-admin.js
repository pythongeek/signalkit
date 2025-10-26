/**
 * SignalKit for Google - Admin JavaScript
 * Version: 2.1.2 - FIXED: Preview shows both banners, added color picker, animation previews, analytics chart
 */

(function($) {
    'use strict';

    // ========================================
    // DEBOUNCE UTILITY
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
    // COLOR PICKER UTILITY
    // ========================================
    function initializeColorPickers() {
        $('.signalkit-color-picker').wpColorPicker({
            change: function(event, ui) {
                $(this).trigger('change');
                console.debug('SignalKit: Color picker updated', ui.color.toString());
            },
            clear: function() {
                $(this).trigger('change');
                console.debug('SignalKit: Color picker cleared');
            }
        });
    }

    // ========================================
    // ANIMATION PREVIEW UTILITY
    // ========================================
    function initializeAnimationPreview() {
        $('.signalkit-animation-select').on('change', function() {
            const $this = $(this);
            const bannerType = $this.closest('.signalkit-tab-content').data('content');
            const animation = $this.val();
            const $preview = $(`#signalkit-preview-${bannerType}`);

            $preview.removeClass('slide-in-3d flip-in fold-down cube-rotate zoom-3d glitch bounce-3d swing-in');
            if (animation) {
                $preview.addClass(animation);
                setTimeout(() => $preview.removeClass(animation), 1000);
            }
            console.debug(`SignalKit: Animation preview triggered for ${bannerType}`, { animation });
        });
    }

    // ========================================
    // ANALYTICS CHART UTILITY
    // ========================================
    function initializeAnalyticsChart() {
        const ctx = $('#signalkit-analytics-chart').get(0);
        if (!ctx) return;

        $.ajax({
            url: signalkitAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'signalkit_get_analytics',
                nonce: signalkitAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Views', 'Clicks', 'Dismissals', 'Follows'],
                            datasets: [
                                {
                                    label: 'Follow Banner',
                                    data: [
                                        response.data.follow.views || 0,
                                        response.data.follow.clicks || 0,
                                        response.data.follow.dismissals || 0,
                                        response.data.follow.follows || 0
                                    ],
                                    backgroundColor: '#0073aa'
                                },
                                {
                                    label: 'Preferred Banner',
                                    data: [
                                        response.data.preferred.views || 0,
                                        response.data.preferred.clicks || 0,
                                        response.data.preferred.dismissals || 0,
                                        0 // Preferred banner doesn't track follows
                                    ],
                                    backgroundColor: '#00a0d2'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                    console.debug('SignalKit: Analytics chart rendered');
                }
            },
            error: function(xhr, status, error) {
                console.error('SignalKit: Analytics chart fetch error', { status, error });
            }
        });
    }

    // ========================================
    // LIVE PREVIEW ENGINE
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
            this.bindTabSwitch();
            this.initializePreview();
            initializeColorPickers();
            initializeAnimationPreview();
            initializeAnalyticsChart();
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
                self.updatePreviewVisibility();
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
            const self = this;
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

        bindTabSwitch: function() {
            const self = this;
            $('.signalkit-tab').on('click', function() {
                const $this = $(this);
                $('.signalkit-tab').removeClass('active');
                $this.addClass('active');
                const tabName = $this.data('tab');
                $('.signalkit-tab-content').removeClass('active').filter(`[data-content="${tabName}"]`).addClass('active');
                self.updatePreviewVisibility();
            });
        },

        initializePreview: function() {
            this.updatePreview('follow');
            this.updatePreview('preferred');
            this.updatePreviewVisibility();
        },

        updatePreviewVisibility: function() {
            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');

            this.$previewFollow.toggle(followEnabled);
            this.$previewPreferred.toggle(preferredEnabled);

            console.debug('SignalKit Preview: Visibility updated', {
                follow: followEnabled ? 'visible' : 'hidden',
                preferred: preferredEnabled ? 'visible' : 'hidden'
            });
        },

        updatePreview: function(bannerType) {
            const self = this;
            const prefix = bannerType + '_';
            const $form = $('.signalkit-form');

            // Collect all relevant settings
            const settings = {};
            $form.find(`[name^="signalkit_settings[${prefix}]"]`).each(function() {
                const $this = $(this);
                const name = $this.attr('name').replace('signalkit_settings[', '').replace(']', '');
                if ($this.is(':checkbox')) {
                    settings[name] = $this.is(':checked') ? 1 : 0;
                } else {
                    settings[name] = $this.val();
                }
            });

            // Add global site name
            settings.site_name = self.siteName;

            console.debug(`SignalKit Preview: Fetching ${bannerType} preview`, {
                device: self.currentDevice,
                settings: settings
            });

            $.ajax({
                url: signalkitAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_preview_banner',
                    nonce: signalkitAdmin.previewNonce,
                    banner_type: bannerType,
                    settings: settings,
                    device: self.currentDevice
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        const $target = bannerType === 'follow' ? self.$previewFollow : self.$previewPreferred;
                        $target.html(response.data.html).show();
                        console.debug(`SignalKit Preview: ${bannerType} updated successfully`);
                    } else {
                        console.warn(`SignalKit Preview: No HTML for ${bannerType}`, response);
                    }
                    self.updatePreviewVisibility();
                },
                error: function(xhr, status, error) {
                    console.error(`SignalKit Preview: AJAX error for ${bannerType}`, {status, error});
                }
            });
        }
    };

    // ========================================
    // DOCUMENT READY
    // ========================================
    $(document).ready(function() {
        // Reset Analytics
        $('.signalkit-reset-analytics').on('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            const bannerType = $this.data('banner-type');
            if (!confirm(signalkitAdmin.strings.confirmReset)) return;

            $this.prop('disabled', true);
            $.post(signalkitAdmin.ajaxUrl, {
                action: 'signalkit_reset_analytics',
                nonce: signalkitAdmin.nonce,
                banner_type: bannerType
            }, function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(signalkitAdmin.strings.error);
                }
            }).always(() => $this.prop('disabled', false));
        });

        // Export Settings
        $('.signalkit-export-settings').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            $btn.prop('disabled', true).text('Exporting...');
            $.post(signalkitAdmin.ajaxUrl, {
                action: 'signalkit_export_settings',
                nonce: signalkitAdmin.nonce
            }, function(res) {
                if (res.success) {
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

        // Import Settings
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
                SignalKitPreview.updatePreviewVisibility();
            });

        // Success message
        if (location.search.includes('settings-updated=true')) {
            $('.signalkit-settings-page h1').after('<div class="notice notice-success is-dismissible" style="margin-top:10px"><p><strong>Settings saved!</strong></p></div>');
            setTimeout(() => $('.notice-success').fadeOut(), 3000);
        }

        toggleDependentFields();
        SignalKitPreview.init();

        console.log('SignalKit Admin JS: Fully loaded v2.1.2');
    });

})(jQuery);