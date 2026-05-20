/**
 * SignalKit - Admin JavaScript
 * Version: 2.0.0
 *
 * @package SignalKit
 * @since 1.0.0
 * @author SignalKit Development Team
 *
 * WordPress & Envato Compatible
 * - Uses proper localized variables from wp_localize_script
 * - Nonce verification for security
 * - Follows WordPress JavaScript coding standards
 * - FIXED: jQuery attribute selector bracket escaping (critical bug)
 * - COMPLETE: All design controls (colors, fonts, spacing, position) working
 * - SECURITY: File upload validation (100KB limit, required keys, max 100 settings)
 * - FEATURE: Import/Export Encryption
 * - FIXED: Rate limiting error handling with user-friendly messages
 */

(function ($) {
    'use strict';

    /**
     * Get AJAX URL from localized script data
     * WordPress best practice: Use wp_localize_script data only
     *
     * @return {string} AJAX URL
     */
    function getAjaxUrl() {
        if (typeof signalkitAdmin !== 'undefined' && signalkitAdmin.ajaxUrl) {
            return signalkitAdmin.ajaxUrl;
        }
        return '';
    }

    /**
     * Get nonce from localized script data
     * WordPress security: Required for AJAX requests
     *
     * @return {string} Nonce value
     */
    function getNonce() {
        if (typeof signalkitAdmin !== 'undefined' && signalkitAdmin.nonce) {
            return signalkitAdmin.nonce;
        }
        return '';
    }

    /**
     * Get localized string
     * Envato requirement: All user-facing strings must be translatable
     *
     * @param {string} key - String key from signalkitAdmin.strings
     * @param {string} fallback - Fallback if key not found
     * @return {string} Localized string
     */
    function getString(key, fallback) {
        if (typeof signalkitAdmin !== 'undefined' && signalkitAdmin.strings && signalkitAdmin.strings[key]) {
            return signalkitAdmin.strings[key];
        }
        return fallback || key;
    }

    /**
     * Debounce function to limit rapid function calls
     * Performance optimization for real-time preview updates
     *
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @return {Function} Debounced function
     */
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

    /**
     * Initialize WordPress color pickers
     * WordPress compatibility requirement
     */
    function initializeColorPickers() {
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            $('.signalkit-color-picker').wpColorPicker({
                change: function (event, ui) {
                    // Trigger change on the hidden input to fire preview update
                    $(this).val(ui.color.toString()).trigger('change');
                },
                clear: function () {
                    $(this).trigger('change');
                }
            });
        }
    }

    /**
     * Main Preview Handler Object
     * Manages live preview functionality with error tracking
     */
    const SignalKitPreview = {
        currentDevice: 'desktop',
        siteName: '',
        $previewViewport: null,
        $previewScreen: null,
        $previewFollow: null,
        $previewPreferred: null,
        updateTimeout: null,
        ajaxUrl: '',
        nonce: '',
        rateLimitNotified: false,

        /**
         * Initialize preview system
         */
        init: function () {
            // Get AJAX credentials
            this.ajaxUrl = getAjaxUrl();
            this.nonce = getNonce();

            if (!this.ajaxUrl || !this.nonce) {
                this.showPreviewError('Preview system unavailable: Missing configuration');
                return;
            }

            // Cache jQuery objects for performance
            this.$previewViewport = $('.signalkit-preview-viewport');
            this.$previewScreen = $('.signalkit-preview-screen');
            this.$previewFollow = $('#signalkit-preview-follow');
            this.$previewPreferred = $('#signalkit-preview-preferred');

            this.siteName = $('input[name="signalkit_settings[site_name]"]').val() || 'Your Site';

            // Bind events
            this.bindPreviewTriggers();
            this.bindDeviceSwitch();
            this.bindTabSwitch();
            initializeColorPickers();

            // Delayed initial preview load
            setTimeout(() => {
                this.initializePreview();
            }, 500);
        },

        /**
         * Show error message in preview area
         *
         * @param {string} message - Error message to display
         * @param {string} type - 'error' or 'warning'
         */
        showPreviewError: function (message, type) {
            type = type || 'error';
            const bgColor = type === 'warning' ? '#fff3cd' : '#fee';
            const borderColor = type === 'warning' ? '#ffc107' : '#c33';
            const textColor = type === 'warning' ? '#856404' : '#c33';

            const errorHtml = '<div class="signalkit-preview-' + type + '" style="padding:20px;background:' + bgColor + ';border:1px solid ' + borderColor + ';border-radius:4px;margin:20px;color:' + textColor + ';text-align:center;">' +
                '<strong>' + (type === 'warning' ? 'Notice:' : 'Preview Error:') + '</strong> ' + message +
                '</div>';

            if (this.$previewScreen && this.$previewScreen.length) {
                this.$previewScreen.html(errorHtml);
            }
        },

        /**
         * Show rate limit notification
         * User-friendly message instead of error state
         *
         * @param {string} message - Server rate limit message
         * @param {object} $target - jQuery object for preview container
         */
        showRateLimitNotification: function (message, $target) {
            if (this.rateLimitNotified) {
                return; // Only show once per page load
            }

            this.rateLimitNotified = true;

            const notificationHtml = '<div class="signalkit-rate-limit-notice" style="padding:15px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;margin:10px;color:#856404;text-align:center;font-size:13px;">' +
                '<strong>⚠️ Preview Temporarily Limited</strong><br>' +
                '<span style="font-size:12px;">' + message + '</span><br>' +
                '<small style="color:#666;">The preview will update automatically when available</small>' +
                '</div>';

            $target.html(notificationHtml).addClass('active').show().css('opacity', '1');

            // Clear notification after 5 seconds
            setTimeout(() => {
                this.rateLimitNotified = false;
            }, 5000);
        },

        /**
         * Bind input changes to preview updates
         * COMPLETE: All design controls trigger preview updates
         */
        bindPreviewTriggers: function () {
            const self = this;

            // Debounced update function to prevent excessive AJAX calls
            // Increased to 800ms to prevent rate limiting
            const debouncedUpdate = debounce(function (e) {
                const $target = $(e.target);
                const $tabContent = $target.closest('.signalkit-tab-content');
                const bannerType = $tabContent.data('content');

                // Update site name for both banners
                if ($target.attr('name') === 'signalkit_settings[site_name]') {
                    self.siteName = $target.val() || 'Your Site';
                    self.updatePreview('follow');
                    self.updatePreview('preferred');
                } else if (bannerType === 'follow' || bannerType === 'preferred') {
                    self.updatePreview(bannerType);
                } else if (bannerType === 'global') {
                    // Global tab - update both
                    self.updatePreview('follow');
                    self.updatePreview('preferred');
                }
            }, 800);

            // Attach to ALL form inputs with preview trigger class
            // This includes: text, number, color, checkbox, select, textarea
            $('.signalkit-form').on('input change', '.signalkit-preview-trigger', debouncedUpdate);

            // Special handling for color pickers (wpColorPicker creates hidden inputs)
            $('.signalkit-form').on('change', '.signalkit-color-picker', debouncedUpdate);

            // Real-time number input display with live value updates
            $('input[type="number"].small-text').each(function () {
                const $this = $(this);
                const $value = $this.next('.signalkit-range-value');
                if ($value.length === 0) {
                    $this.after('<span class="signalkit-range-value">' + $this.val() + 'px</span>');
                } else {
                    $value.text($this.val() + 'px');
                }
            }).on('input', function () {
                $(this).next('.signalkit-range-value').text($(this).val() + 'px');
            });

            // **FIX: Range slider inputs (Close Button Size) - value display update**
            // Uses .signalkit-range class for range sliders, maps to CSS variable --signalkit-close-size
            $('input[type="range"].signalkit-range').each(function () {
                const $this = $(this);
                const $value = $this.next('.signalkit-range-value');
                if ($value.length) {
                    $value.text($this.val() + 'px');
                }
            }).on('input', function () {
                const $this = $(this);
                const $value = $this.next('.signalkit-range-value');
                if ($value.length) {
                    $value.text($this.val() + 'px');
                }
            });
        },

        /**
         * Bind device switcher buttons
         */
        bindDeviceSwitch: function () {
            const self = this;

            $('.signalkit-preview-device').on('click', function () {
                const $this = $(this);
                self.currentDevice = $this.data('device');

                // Update UI
                $('.signalkit-preview-device').removeClass('active').attr('aria-pressed', 'false');
                $this.addClass('active').attr('aria-pressed', 'true');
                self.$previewViewport.attr('data-device', self.currentDevice);

                // Refresh previews for new device
                const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
                const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');

                if (followEnabled) {
                    self.updatePreview('follow');
                }
                if (preferredEnabled) {
                    self.updatePreview('preferred');
                }
            });
        },

        /**
         * Bind tab navigation with URL hash persistence
         */
        bindTabSwitch: function () {
            const self = this;

            // Function to switch to a specific tab
            function switchToTab(tabName) {
                const $tab = $(`.signalkit-tab[data-tab="${tabName}"]`);
                if (!$tab.length) return false;

                // Update tab UI
                $('.signalkit-tab').removeClass('active').attr('aria-selected', 'false');
                $tab.addClass('active').attr('aria-selected', 'true');

                // Update content panels
                $('.signalkit-tab-content').removeClass('active');
                $(`.signalkit-tab-content[data-content="${tabName}"]`).addClass('active');

                // Show relevant preview when switching tabs
                if (tabName === 'follow') {
                    self.$previewFollow.show();
                    self.$previewPreferred.hide();
                } else if (tabName === 'preferred') {
                    self.$previewFollow.hide();
                    self.$previewPreferred.show();
                } else if (tabName === 'custom') {
                    // Custom banner - hide both standard previews
                    self.$previewFollow.hide();
                    self.$previewPreferred.hide();
                } else if (tabName === 'global' || tabName === 'advanced') {
                    // Show both on global/advanced tabs
                    self.$previewFollow.show();
                    self.$previewPreferred.show();
                }

                return true;
            }

            // Check for hash on page load and restore tab
            if (window.location.hash) {
                const hashTab = window.location.hash.replace('#', '');
                switchToTab(hashTab);
            }

            // Bind click events
            $('.signalkit-tab').on('click', function () {
                const $this = $(this);
                const tabName = $this.data('tab');

                // Update URL hash without scrolling
                history.replaceState(null, null, '#' + tabName);

                switchToTab(tabName);
            });
        },

        /**
         * Initialize preview on page load
         */
        initializePreview: function () {
            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');

            if (followEnabled) {
                this.updatePreview('follow');
            }
            if (preferredEnabled) {
                this.updatePreview('preferred');
            }
        },

        /**
         * Update preview via AJAX
         * WordPress AJAX implementation with comprehensive error tracking
         * FIXED: Rate limiting error handling
         *
         * CRITICAL FIX: Brackets in jQuery attribute selectors must be escaped with double backslashes
         * @link https://api.jquery.com/attribute-starts-with-selector/
         *
         * @param {string} bannerType - 'follow' or 'preferred'
         */
        updatePreview: function (bannerType) {
            const self = this;
            const prefix = bannerType + '_';
            const settings = { site_name: this.siteName };

            // CRITICAL: Escape brackets in attribute selector
            // jQuery requires double backslashes to match literal [ and ] characters
            // Wrong:  [name^="signalkit_settings[follow_"]  -> Selector fails
            // Right:  [name^="signalkit_settings\\[follow_"] -> Matches correctly
            const selectorPrefix = 'signalkit_settings\\[' + prefix;
            const selectorSiteName = 'signalkit_settings\\[site_name\\]';

            // Collect all settings for the banner type
            $('.signalkit-form').find(
                '[name^="' + selectorPrefix + '"], [name="' + selectorSiteName + '"]'
            ).each(function () {
                const $this = $(this);
                const fullName = $this.attr('name');

                // Extract setting name from: signalkit_settings[follow_enabled] -> follow_enabled
                const name = fullName.replace('signalkit_settings[', '').replace(']', '');

                if ($this.is(':checkbox')) {
                    settings[name] = $this.is(':checked') ? 1 : 0;
                } else {
                    settings[name] = $this.val();
                }
            });

            const enabled = settings[prefix + 'enabled'];
            const $target = bannerType === 'follow' ? self.$previewFollow : self.$previewPreferred;

            // Verify target container exists
            if ($target.length === 0) {
                return;
            }

            // Hide if disabled
            if (!enabled) {
                $target.removeClass('active').hide();
                return;
            }

            // Prepare AJAX data - WordPress standard
            const ajaxData = {
                action: 'signalkit_preview_banner',
                nonce: this.nonce,
                banner_type: bannerType,
                settings: settings,
                device: self.currentDevice
            };

            // AJAX request to generate preview
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                timeout: 15000, // 15 second timeout for slower servers
                beforeSend: function () {
                    $target.css('opacity', '0.5');
                },
                success: function (response) {
                    // FIXED: Check for rate limiting response
                    if (response && response.success === false && response.data && response.data.message) {
                        const message = response.data.message;

                        // Rate limiting detected
                        if (message.toLowerCase().includes('too many') || message.toLowerCase().includes('rate limit')) {
                            self.showRateLimitNotification(message, $target);
                            return;
                        }

                        // Other server errors
                        self.showPreviewError(message, 'warning');
                        $target.css('opacity', '1');
                        return;
                    }

                    if (response && response.success === true && response.data && response.data.html) {
                        $target.html(response.data.html).addClass('active').show().css('opacity', '1');

                        // Inject preview-specific CSS
                        if (response.data.css) {
                            let $style = $('#signalkit-preview-css-' + bannerType);
                            if ($style.length === 0) {
                                $style = $('<style id="signalkit-preview-css-' + bannerType + '"></style>');
                                $('head').append($style);
                            }
                            $style.html(response.data.css);
                        }
                    } else {
                        $target.css('opacity', '1');
                    }
                },
                error: function (xhr, status, error) {
                    $target.css('opacity', '1');

                    let errorMsg = 'Preview update failed: ';
                    if (status === 'timeout') {
                        errorMsg += 'Request timeout (check server)';
                    } else if (xhr.status === 403) {
                        errorMsg += 'Permission denied (check nonce)';
                    } else if (xhr.status === 429) {
                        errorMsg += 'Too many requests - please wait a moment';
                        self.showRateLimitNotification('Too many preview requests. Please wait a moment and try again.', $target);
                        return;
                    } else if (xhr.status === 500) {
                        errorMsg += 'Server error (check PHP logs)';
                    } else {
                        errorMsg += status + ' (' + xhr.status + ')';
                    }

                    self.showPreviewError(errorMsg);
                }
            });
        }
    };

    /**
     * Document Ready
     * Initialize all functionality with error handling
     * WordPress standard: All initialization in document.ready
     */
    $(document).ready(function () {
        // Verify localized data before proceeding
        if (typeof signalkitAdmin === 'undefined' || !signalkitAdmin.ajaxUrl || !signalkitAdmin.nonce) {
            alert(getString('configMissing', 'SignalKit Error: Plugin configuration missing.'));
            return;
        }

        /**
         * Analytics Reset Handler
         * WordPress AJAX with nonce verification
         */
        $('.signalkit-reset-analytics').on('click', function (e) {
            e.preventDefault();
            const $this = $(this);
            const bannerType = $this.data('banner-type');

            if (!confirm(getString('confirmReset', 'Are you sure you want to reset analytics data?'))) {
                return;
            }

            $this.prop('disabled', true);

            $.post(getAjaxUrl(), {
                action: 'signalkit_reset_analytics',
                nonce: getNonce(),
                banner_type: bannerType
            }, function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(getString('resetFailed', 'Failed to reset analytics.'));
                }
            }).always(function () {
                $this.prop('disabled', false);
            }).fail(function (xhr, status, error) {
                // Silent fail
            });
        });

        /**
         * Export Settings Handler
         * Creates downloadable JSON or encrypted TXT file
         * Envato requirement: Data portability
         * FEATURE: Encryption
         */
        $('.signalkit-export-settings').on('click', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const originalText = $btn.text();

            $btn.prop('disabled', true).text(getString('exporting', 'Exporting...'));

            $.post(getAjaxUrl(), {
                action: 'signalkit_export_settings',
                nonce: getNonce()
            }, function (response) {
                if (response.success) {
                    const isEncrypted = response.data.encrypted;
                    const dataToExport = isEncrypted ? response.data.settings : JSON.stringify(response.data.settings, null, 2);
                    const fileType = isEncrypted ? 'text/plain' : 'application/json';
                    const fileExtension = isEncrypted ? '.txt' : '.json';
                    const fileName = 'signalkit-settings-' + new Date().toISOString().slice(0, 10) + fileExtension;

                    // Create downloadable file
                    const blob = new Blob([dataToExport], { type: fileType });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } else {
                    alert(getString('exportFailed', 'Export failed.'));
                }
            }).fail(function (xhr, status, error) {
                alert(getString('exportFailed', 'Export failed.'));
            }).always(function () {
                $btn.prop('disabled', false).text(originalText);
            });
        });

        /**
         * Import Settings Handler - SECURITY v1.0.0
         * Reads and uploads JSON or encrypted TXT file
         * - Client-side file size limit (100KB)
         * - JSON structure validation (if not encrypted)
         * - FEATURE: Encryption
         */
        $('.signalkit-import-settings').on('click', function (e) {
            e.preventDefault();
            $('#signalkit-import-file').click();
        });

        $('#signalkit-import-file').on('change', function (e) {
            const file = e.target.files[0];

            if (!file) {
                return;
            }

            // SECURITY: Client-side file type validation (allow .json or .txt for encrypted)
            if (!file.type.includes('json') && !file.type.includes('text') && !file.name.endsWith('.json') && !file.name.endsWith('.txt')) {
                alert(getString('invalidFile', 'Please select a valid JSON or TXT file.'));
                return;
            }

            // SECURITY: Client-side file size limit (100KB = 102400 bytes)
            if (file.size > 102400) {
                alert(getString('fileTooLarge', 'File too large. Maximum size is 100KB.'));
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                const fileContent = event.target.result;
                let settings;
                let isEncrypted = false;

                try {
                    // Try to parse as JSON first
                    settings = JSON.parse(fileContent);
                } catch (err) {
                    // If parse fails, assume it's an encrypted string
                    settings = fileContent;
                    isEncrypted = true;
                }

                // Run client-side validation ONLY if it's a JSON file
                if (!isEncrypted) {
                    // SECURITY: Client-side validation of required keys
                    const requiredKeys = ['site_name', 'follow_enabled', 'preferred_enabled'];
                    const missingKeys = requiredKeys.filter(key => !settings.hasOwnProperty(key));

                    if (missingKeys.length > 0) {
                        alert(getString('missingFields', 'Invalid settings file: Missing required fields') + ' (' + missingKeys.join(', ') + ')');
                        return;
                    }

                    // SECURITY: Limit number of settings to prevent resource exhaustion
                    if (Object.keys(settings).length > 100) {
                        alert(getString('tooManySettings', 'Settings file contains too many entries (max 100).'));
                        return;
                    }
                }

                if (!confirm(getString('confirmImport', 'Import settings? This will overwrite your current settings.'))) {
                    return;
                }

                // Prepare AJAX data
                const ajaxData = {
                    action: 'signalkit_import_settings',
                    nonce: getNonce(),
                    settings: isEncrypted ? settings : JSON.stringify(settings),
                    encrypted: isEncrypted ? 1 : 0
                };

                // Send data to server
                $.post(getAjaxUrl(), ajaxData, function (response) {
                    if (response.success) {
                        alert(getString('importSuccess', 'Settings imported successfully!'));
                        location.reload();
                    } else {
                        const errorMsg = response.data && response.data.message
                            ? response.data.message
                            : 'Unknown error';
                        alert(getString('importFailedMsg', 'Import failed:') + ' ' + errorMsg);
                    }
                }).fail(function (xhr, status, error) {
                    alert(getString('importFailed', 'Import failed. Please try again.'));
                });
            };

            reader.readAsText(file);

            // Reset file input to allow re-importing the same file
            $(this).val('');
        });

        /**
         * Custom Banner Type Selector
         * Handles visual selection and showing/hiding dependent fields
         */
        function initializeCustomBannerTypeSelector() {
            // Visual selection
            $('.signalkit-banner-type-option').on('click', function () {
                // Update visual state
                $('.signalkit-banner-type-option').removeClass('active');
                $(this).addClass('active');

                // Select radio button
                const $radio = $(this).find('input[type="radio"]');
                $radio.prop('checked', true);

                // Handle dependent fields
                const selectedType = $radio.val();
                toggleCustomBannerFields(selectedType);

                // Optional: Update defaults for preview (UX enhancement)
                updateDefaultsForType(selectedType);
            });

            // Initialize state
            const currentType = $('input[name="signalkit_settings[custom_banner_type]"]:checked').val() || 'newsletter';
            toggleCustomBannerFields(currentType);
        }

        function toggleCustomBannerFields(type) {
            // Promo code field
            if (type === 'promo') {
                $('.signalkit-promo-field').slideDown(200);
            } else {
                $('.signalkit-promo-field').slideUp(200);
            }
        }

        function updateDefaultsForType(type) {
            // Only update if fields are empty or match previous defaults (smart defaults)
            // This prevents overwriting user customization
            const defaults = {
                'newsletter': { h: '📧 Subscribe to Our Newsletter', b: 'Subscribe' },
                'lead': { h: '🎯 Get Your Free Guide', b: 'Get Access' },
                'cta': { h: '🚀 Ready to Get Started?', b: 'Get Started' },
                'announcement': { h: '📢 Important Update', b: 'Learn More' },
                'promo': { h: '🏷️ Limited Time Offer!', b: 'Claim Offer' }
            };

            const def = defaults[type];
            if (def) {
                const $headline = $('#custom_headline');
                const $btn = $('#custom_button_text');

                // Simple heuristic: if empty, fill it
                if ($headline.val() === '') $headline.val(def.h);
                if ($btn.val() === '') $btn.val(def.b);
            }
        }

        initializeCustomBannerTypeSelector();

        /**
         * Toggle dependent fields based on enable/disable toggles
         * WordPress UI pattern
         */
        function toggleDependentFields() {
            ['follow', 'preferred'].forEach(function (bannerType) {
                const enabled = $(`input[name="signalkit_settings[${bannerType}_enabled]"]`).is(':checked');

                // Dim disabled fields
                $(`.signalkit-tab-content[data-content="${bannerType}"]`)
                    .find('.signalkit-setting-row')
                    .not(':first')
                    .css('opacity', enabled ? 1 : 0.5);

                // Show/hide educational link field for preferred banner
                if (bannerType === 'preferred') {
                    const showEdu = $(`input[name="signalkit_settings[preferred_show_educational_link]"]`).is(':checked');
                    $(`input[name="signalkit_settings[preferred_educational_text]"]`)
                        .closest('.signalkit-setting-row')
                        .toggle(enabled && showEdu);
                }
            });
        }

        /**
         * Bind enable/disable toggles
         */
        $('input[name="signalkit_settings[follow_enabled]"], input[name="signalkit_settings[preferred_enabled]"], input[name="signalkit_settings[preferred_show_educational_link]"]')
            .on('change', function () {
                toggleDependentFields();

                // Trigger preview update
                const type = this.name.includes('follow') ? 'follow' : 'preferred';
                SignalKitPreview.updatePreview(type);
            });

        /**
         * Show success message after save
         * WordPress admin notice pattern
         */
        if (location.search.includes('settings-updated=true')) {
            $('.signalkit-settings-page h1').after(
                '<div class="notice notice-success is-dismissible" style="margin-top:10px">' +
                '<p><strong>Settings saved successfully!</strong></p>' +
                '</div>'
            );
            setTimeout(function () {
                $('.notice-success').fadeOut();
            }, 3000);
        }

        // Initialize dependent fields visibility
        toggleDependentFields();

        // Preserve tab hash on form submission
        $('.signalkit-form').on('submit', function () {
            const currentHash = window.location.hash;
            if (currentHash) {
                // Store the current tab in a hidden field or update action
                const action = $(this).attr('action') || '';
                $(this).attr('action', action.split('#')[0] + currentHash);
            }
        });

        // Initialize preview system
        try {
            SignalKitPreview.init();
        } catch (err) {
            // Silent fail
        }
    });

})(jQuery);