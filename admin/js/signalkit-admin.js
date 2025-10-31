/**
 * SignalKit - Admin JavaScript
 * Version: 1.0.0 - SECURITY: File upload validation hardened
 * 
 * @package SignalKit_For_Google
 * @since 1.0.0
 * @author SignalKit Development Team
 * 
 * WordPress & Envato Compatible
 * - Uses proper localized variables from wp_localize_script
 * - Comprehensive error logging for debugging
 * - Nonce verification for security
 * - Follows WordPress JavaScript coding standards
 * - FIXED: jQuery attribute selector bracket escaping (critical bug)
 * - COMPLETE: All design controls (colors, fonts, spacing, position) working
 * - SECURITY: File upload validation (100KB limit, required keys, max 100 settings)
 */

(function($) {
    'use strict';

    /**
     * Debug logger - outputs to console with SignalKit prefix
     * WordPress debugging best practice
     * 
     * @param {string} message - Log message
     * @param {*} data - Additional data to log
     * @param {string} level - Log level: 'log', 'warn', 'error'
     */
    function signalkitLog(message, data, level) {
        level = level || 'log';
        const timestamp = new Date().toISOString();
        const prefix = '[SignalKit Admin ' + timestamp + ']';
        
        if (typeof console[level] === 'function') {
            if (data !== undefined) {
                console[level](prefix, message, data);
            } else {
                console[level](prefix, message);
            }
        }
    }

    /**
     * Verify required global variables exist
     * WordPress best practice: Check localized script data
     * Envato requirement: Proper error handling
     * 
     * @return {boolean} True if all required variables exist
     */
    function verifyGlobals() {
        const required = {
            'signalkitAdmin': 'WordPress localized admin object',
            'signalkitAdmin.ajaxUrl': 'AJAX URL',
            'signalkitAdmin.nonce': 'Security nonce',
            'signalkitNonce': 'Fallback nonce from inline script',
            'signalkitAjaxUrl': 'Fallback AJAX URL from inline script'
        };

        let allPresent = true;
        const missing = [];

        // Check primary localized object
        if (typeof signalkitAdmin === 'undefined') {
            missing.push('signalkitAdmin (primary localized object)');
            allPresent = false;
        } else {
            if (!signalkitAdmin.ajaxUrl) {
                missing.push('signalkitAdmin.ajaxUrl');
                allPresent = false;
            }
            if (!signalkitAdmin.nonce) {
                missing.push('signalkitAdmin.nonce');
                allPresent = false;
            }
        }

        // Check fallback inline variables
        if (typeof signalkitNonce === 'undefined') {
            missing.push('signalkitNonce (fallback)');
        }
        if (typeof signalkitAjaxUrl === 'undefined') {
            missing.push('signalkitAjaxUrl (fallback)');
        }

        if (!allPresent) {
            signalkitLog('CRITICAL: Missing required global variables', {
                missing: missing,
                availableGlobals: Object.keys(window).filter(k => k.includes('signalkit'))
            }, 'error');
        } else {
            signalkitLog('All required globals verified', {
                ajaxUrl: signalkitAdmin.ajaxUrl,
                hasNonce: !!signalkitAdmin.nonce,
                hasFallbacks: !!(typeof signalkitNonce !== 'undefined' && typeof signalkitAjaxUrl !== 'undefined')
            });
        }

        return allPresent;
    }

    /**
     * Get AJAX URL with fallback
     * WordPress compatibility: Supports both localized and inline script variables
     * 
     * @return {string} AJAX URL
     */
    function getAjaxUrl() {
        if (typeof signalkitAdmin !== 'undefined' && signalkitAdmin.ajaxUrl) {
            return signalkitAdmin.ajaxUrl;
        }
        if (typeof signalkitAjaxUrl !== 'undefined') {
            signalkitLog('Using fallback signalkitAjaxUrl', signalkitAjaxUrl, 'warn');
            return signalkitAjaxUrl;
        }
        signalkitLog('CRITICAL: No AJAX URL available', null, 'error');
        return '';
    }

    /**
     * Get nonce with fallback
     * WordPress security: Required for AJAX requests
     * Envato requirement: Proper nonce validation
     * 
     * @return {string} Nonce value
     */
    function getNonce() {
        if (typeof signalkitAdmin !== 'undefined' && signalkitAdmin.nonce) {
            return signalkitAdmin.nonce;
        }
        if (typeof signalkitNonce !== 'undefined') {
            signalkitLog('Using fallback signalkitNonce', null, 'warn');
            return signalkitNonce;
        }
        signalkitLog('CRITICAL: No nonce available', null, 'error');
        return '';
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
            signalkitLog('Initializing WordPress color pickers');
            $('.signalkit-color-picker').wpColorPicker({
                change: function(event, ui) {
                    // Trigger change on the hidden input to fire preview update
                    $(this).val(ui.color.toString()).trigger('change');
                },
                clear: function() {
                    $(this).trigger('change');
                }
            });
        } else {
            signalkitLog('WordPress color picker not available', null, 'warn');
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

        /**
         * Initialize preview system
         */
        init: function() {
            signalkitLog('Initializing preview system');

            // Get AJAX credentials
            this.ajaxUrl = getAjaxUrl();
            this.nonce = getNonce();

            if (!this.ajaxUrl || !this.nonce) {
                signalkitLog('CRITICAL: Cannot initialize preview - missing AJAX URL or nonce', {
                    ajaxUrl: this.ajaxUrl,
                    nonce: this.nonce ? '[present]' : '[missing]'
                }, 'error');
                this.showPreviewError('Preview system unavailable: Missing configuration');
                return;
            }

            // Cache jQuery objects for performance
            this.$previewViewport = $('.signalkit-preview-viewport');
            this.$previewScreen = $('.signalkit-preview-screen');
            this.$previewFollow = $('#signalkit-preview-follow');
            this.$previewPreferred = $('#signalkit-preview-preferred');

            // Verify DOM elements exist
            if (this.$previewFollow.length === 0) {
                signalkitLog('WARNING: #signalkit-preview-follow not found in DOM', null, 'warn');
            }
            if (this.$previewPreferred.length === 0) {
                signalkitLog('WARNING: #signalkit-preview-preferred not found in DOM', null, 'warn');
            }

            this.siteName = $('input[name="signalkit_settings[site_name]"]').val() || 'Your Site';

            // Bind events
            this.bindPreviewTriggers();
            this.bindDeviceSwitch();
            this.bindTabSwitch();
            initializeColorPickers();
            
            signalkitLog('Preview system initialized', {
                device: this.currentDevice,
                siteName: this.siteName,
                hasFollowContainer: this.$previewFollow.length > 0,
                hasPreferredContainer: this.$previewPreferred.length > 0
            });

            // Delayed initial preview load
            setTimeout(() => {
                this.initializePreview();
            }, 500);
        },

        /**
         * Show error message in preview area
         * 
         * @param {string} message - Error message to display
         */
        showPreviewError: function(message) {
            const errorHtml = '<div class="signalkit-preview-error" style="padding:20px;background:#fee;border:1px solid #c33;border-radius:4px;margin:20px;color:#c33;text-align:center;">' +
                '<strong>Preview Error:</strong> ' + message + 
                '<br><small>Check browser console for details</small></div>';
            
            if (this.$previewScreen && this.$previewScreen.length) {
                this.$previewScreen.html(errorHtml);
            }
        },

        /**
         * Bind input changes to preview updates
         * COMPLETE: All design controls trigger preview updates
         */
        bindPreviewTriggers: function() {
            const self = this;
            
            signalkitLog('Binding preview triggers to all customization controls');

            // Debounced update function to prevent excessive AJAX calls
            // Increased to 500ms for better performance with color pickers
            const debouncedUpdate = debounce(function(e) {
                const $target = $(e.target);
                const $tabContent = $target.closest('.signalkit-tab-content');
                const bannerType = $tabContent.data('content');

                signalkitLog('Preview trigger fired', {
                    inputName: $target.attr('name'),
                    inputType: $target.attr('type'),
                    inputClass: $target.attr('class'),
                    bannerType: bannerType,
                    value: $target.val()
                });

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
            }, 500);

            // Attach to ALL form inputs with preview trigger class
            // This includes: text, number, color, checkbox, select, textarea
            $('.signalkit-form').on('input change', '.signalkit-preview-trigger', debouncedUpdate);
            
            // Special handling for color pickers (wpColorPicker creates hidden inputs)
            $('.signalkit-form').on('change', '.signalkit-color-picker', debouncedUpdate);

            // Real-time number input display with live value updates
            $('input[type="number"].small-text').each(function() {
                const $this = $(this);
                const $value = $this.next('.signalkit-range-value');
                if ($value.length === 0) {
                    $this.after('<span class="signalkit-range-value">' + $this.val() + 'px</span>');
                } else {
                    $value.text($this.val() + 'px');
                }
            }).on('input', function() {
                $(this).next('.signalkit-range-value').text($(this).val() + 'px');
            });

            signalkitLog('Preview triggers bound successfully', {
                totalTriggers: $('.signalkit-preview-trigger').length,
                colorPickers: $('.signalkit-color-picker').length,
                numberInputs: $('input[type="number"].small-text').length
            });
        },

        /**
         * Bind device switcher buttons
         */
        bindDeviceSwitch: function() {
            const self = this;
            signalkitLog('Binding device switcher');

            $('.signalkit-preview-device').on('click', function() {
                const $this = $(this);
                self.currentDevice = $this.data('device');
                
                signalkitLog('Device switched', self.currentDevice);

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
         * Bind tab navigation
         */
        bindTabSwitch: function() {
            const self = this;
            signalkitLog('Binding tab switcher');

            $('.signalkit-tab').on('click', function() {
                const $this = $(this);
                const tabName = $this.data('tab');
                
                signalkitLog('Tab switched', tabName);

                // Update tab UI
                $('.signalkit-tab').removeClass('active').attr('aria-selected', 'false');
                $this.addClass('active').attr('aria-selected', 'true');
                
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
                } else if (tabName === 'global' || tabName === 'advanced') {
                    // Show both on global/advanced tabs
                    self.$previewFollow.show();
                    self.$previewPreferred.show();
                }
            });
        },

        /**
         * Initialize preview on page load
         */
        initializePreview: function() {
            signalkitLog('Initializing initial preview load');

            const followEnabled = $('input[name="signalkit_settings[follow_enabled]"]').is(':checked');
            const preferredEnabled = $('input[name="signalkit_settings[preferred_enabled]"]').is(':checked');

            signalkitLog('Initial banner states', {
                followEnabled: followEnabled,
                preferredEnabled: preferredEnabled
            });

            if (followEnabled) {
                this.updatePreview('follow');
            }
            if (preferredEnabled) {
                this.updatePreview('preferred');
            }

            if (!followEnabled && !preferredEnabled) {
                signalkitLog('No banners enabled - preview inactive');
            }
        },

        /**
         * Update preview via AJAX
         * WordPress AJAX implementation with comprehensive error tracking
         * 
         * CRITICAL FIX: Brackets in jQuery attribute selectors must be escaped with double backslashes
         * @link https://api.jquery.com/attribute-starts-with-selector/
         * 
         * @param {string} bannerType - 'follow' or 'preferred'
         */
        updatePreview: function(bannerType) {
            const self = this;
            const prefix = bannerType + '_';
            const settings = { site_name: this.siteName };

            signalkitLog('Updating preview for ' + bannerType + ' banner');

            // CRITICAL: Escape brackets in attribute selector
            // jQuery requires double backslashes to match literal [ and ] characters
            // Wrong:  [name^="signalkit_settings[follow_"]  -> Selector fails
            // Right:  [name^="signalkit_settings\\[follow_"] -> Matches correctly
            const selectorPrefix = 'signalkit_settings\\[' + prefix;
            const selectorSiteName = 'signalkit_settings\\[site_name\\]';

            // Collect all settings for the banner type
            let settingsCount = 0;
            $('.signalkit-form').find(
                '[name^="' + selectorPrefix + '"], [name="' + selectorSiteName + '"]'
            ).each(function() {
                const $this = $(this);
                const fullName = $this.attr('name');
                
                // Extract setting name from: signalkit_settings[follow_enabled] -> follow_enabled
                const name = fullName.replace('signalkit_settings[', '').replace(']', '');
                
                if ($this.is(':checkbox')) {
                    settings[name] = $this.is(':checked') ? 1 : 0;
                } else {
                    settings[name] = $this.val();
                }
                settingsCount++;
            });

            signalkitLog('Collected settings', {
                bannerType: bannerType,
                settingsCount: settingsCount,
                sampleKeys: Object.keys(settings).slice(0, 10),
                totalKeys: Object.keys(settings).length,
                colors: {
                    primary: settings[prefix + 'primary_color'],
                    secondary: settings[prefix + 'secondary_color'],
                    accent: settings[prefix + 'accent_color'],
                    text: settings[prefix + 'text_color']
                },
                sizes: {
                    width: settings[prefix + 'banner_width'],
                    padding: settings[prefix + 'banner_padding'],
                    radius: settings[prefix + 'border_radius']
                },
                fonts: {
                    headline: settings[prefix + 'font_size_headline'],
                    description: settings[prefix + 'font_size_description'],
                    button: settings[prefix + 'font_size_button']
                }
            });

            // Verify we collected settings (should be 25+ per banner)
            if (settingsCount < 10) {
                signalkitLog('WARNING: Very few settings collected - selector may be failing', {
                    bannerType: bannerType,
                    settingsCount: settingsCount,
                    selector: '[name^="' + selectorPrefix + '"]'
                }, 'warn');
            }

            const enabled = settings[prefix + 'enabled'];
            const $target = bannerType === 'follow' ? self.$previewFollow : self.$previewPreferred;

            // Verify target container exists
            if ($target.length === 0) {
                signalkitLog('ERROR: Preview container not found', {
                    bannerType: bannerType,
                    selector: bannerType === 'follow' ? '#signalkit-preview-follow' : '#signalkit-preview-preferred'
                }, 'error');
                return;
            }

            // Hide if disabled
            if (!enabled) {
                signalkitLog('Banner disabled - hiding preview', bannerType);
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

            signalkitLog('Sending AJAX request', {
                url: this.ajaxUrl,
                action: ajaxData.action,
                bannerType: bannerType,
                device: ajaxData.device,
                hasNonce: !!ajaxData.nonce,
                settingsKeys: Object.keys(settings).length
            });

            // AJAX request to generate preview
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                timeout: 15000, // 15 second timeout for slower servers
                beforeSend: function() {
                    $target.css('opacity', '0.5');
                },
                success: function(response) {
                    signalkitLog('AJAX response received', {
                        bannerType: bannerType,
                        success: response.success,
                        hasHtml: !!(response.data && response.data.html),
                        hasCss: !!(response.data && response.data.css),
                        htmlLength: response.data && response.data.html ? response.data.html.length : 0,
                        cssLength: response.data && response.data.css ? response.data.css.length : 0
                    });

                    if (response.success && response.data && response.data.html) {
                        $target.html(response.data.html).addClass('active').show().css('opacity', '1');
                        
                        // Inject preview-specific CSS
                        if (response.data.css) {
                            let $style = $('#signalkit-preview-css-' + bannerType);
                            if ($style.length === 0) {
                                $style = $('<style id="signalkit-preview-css-' + bannerType + '"></style>');
                                $('head').append($style);
                            }
                            $style.html(response.data.css);
                            signalkitLog('CSS injected', {
                                bannerType: bannerType,
                                cssLength: response.data.css.length
                            });
                        }

                        signalkitLog('✓ Preview updated successfully', {
                            bannerType: bannerType,
                            device: self.currentDevice
                        });
                    } else {
                        signalkitLog('Invalid response structure', {
                            bannerType: bannerType,
                            response: response
                        }, 'warn');
                        $target.css('opacity', '1');
                        self.showPreviewError('Invalid response from server');
                    }
                },
                error: function(xhr, status, error) {
                    signalkitLog('AJAX request failed', { 
                        bannerType: bannerType, 
                        status: status, 
                        error: error,
                        statusCode: xhr.status,
                        responseText: xhr.responseText ? xhr.responseText.substring(0, 500) : 'N/A'
                    }, 'error');
                    
                    $target.css('opacity', '1');
                    
                    let errorMsg = 'Preview update failed: ';
                    if (status === 'timeout') {
                        errorMsg += 'Request timeout (check server)';
                    } else if (xhr.status === 403) {
                        errorMsg += 'Permission denied (check nonce)';
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
    $(document).ready(function() {
        signalkitLog('DOM ready - initializing SignalKit admin');

        // Verify globals before proceeding
        if (!verifyGlobals()) {
            signalkitLog('CRITICAL: Cannot proceed - missing required variables', null, 'error');
            alert('SignalKit Error: Plugin configuration missing. Please refresh the page or contact support.');
            return;
        }

        /**
         * Analytics Reset Handler
         * WordPress AJAX with nonce verification
         */
        $('.signalkit-reset-analytics').on('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            const bannerType = $this.data('banner-type');
            
            signalkitLog('Reset analytics clicked', bannerType);

            if (!confirm('Are you sure you want to reset analytics data? This cannot be undone.')) {
                return;
            }
            
            $this.prop('disabled', true);
            
            $.post(getAjaxUrl(), {
                action: 'signalkit_reset_analytics',
                nonce: getNonce(),
                banner_type: bannerType
            }, function(response) {
                signalkitLog('Reset analytics response', response);
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to reset analytics. Please try again.');
                }
            }).always(function() {
                $this.prop('disabled', false);
            }).fail(function(xhr, status, error) {
                signalkitLog('Reset analytics failed', { status: status, error: error }, 'error');
            });
        });

        /**
         * Export Settings Handler
         * Creates downloadable JSON file
         * Envato requirement: Data portability
         */
        $('.signalkit-export-settings').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const originalText = $btn.text();
            
            signalkitLog('Export settings clicked');
            $btn.prop('disabled', true).text('Exporting...');
            
            $.post(getAjaxUrl(), {
                action: 'signalkit_export_settings',
                nonce: getNonce()
            }, function(response) {
                signalkitLog('Export response', response);

                if (response.success && response.data.settings) {
                    // Create downloadable JSON file
                    const blob = new Blob([JSON.stringify(response.data.settings, null, 2)], {
                        type: 'application/json'
                    });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'signalkit-settings-' + new Date().toISOString().slice(0, 10) + '.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    signalkitLog('Settings exported successfully');
                } else {
                    alert('Export failed. Please try again.');
                }
            }).fail(function(xhr, status, error) {
                signalkitLog('Export failed', { status: status, error: error }, 'error');
                alert('Export failed. Please try again.');
            }).always(function() {
                $btn.prop('disabled', false).text(originalText);
            });
        });

        /**
         * Import Settings Handler - SECURITY v2.5.1
         * Reads and uploads JSON file with comprehensive validation
         * - Client-side file size limit (100KB)
         * - Required keys validation (site_name, follow_enabled, preferred_enabled)
         * - Settings count limit (max 100)
         * - JSON structure validation
         */
        $('.signalkit-import-settings').on('click', function(e) {
            e.preventDefault();
            signalkitLog('Import settings clicked');
            $('#signalkit-import-file').click();
        });
        
        $('#signalkit-import-file').on('change', function(e) {
            const file = e.target.files[0];
            
            if (!file) {
                return;
            }
            
            signalkitLog('Import file selected', {
                name: file.name,
                type: file.type,
                size: file.size
            });

            // SECURITY: Client-side file type validation
            if (!file.type.includes('json') && !file.name.endsWith('.json')) {
                alert('Please select a valid JSON file.');
                signalkitLog('Import blocked: Invalid file type', { type: file.type }, 'warn');
                return;
            }

            // SECURITY: Client-side file size limit (100KB = 102400 bytes)
            if (file.size > 102400) {
                alert('File too large. Maximum size is 100KB.');
                signalkitLog('Import blocked: File too large', { size: file.size }, 'warn');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                try {
                    const settings = JSON.parse(event.target.result);
                    
                    signalkitLog('Import file parsed', {
                        settingsCount: Object.keys(settings).length
                    });

                    // SECURITY: Client-side validation of required keys
                    const requiredKeys = ['site_name', 'follow_enabled', 'preferred_enabled'];
                    const missingKeys = requiredKeys.filter(key => !settings.hasOwnProperty(key));
                    
                    if (missingKeys.length > 0) {
                        alert('Invalid settings file: Missing required fields (' + missingKeys.join(', ') + ')');
                        signalkitLog('Import blocked: Missing required keys', { missing: missingKeys }, 'warn');
                        return;
                    }

                    // SECURITY: Limit number of settings to prevent resource exhaustion
                    if (Object.keys(settings).length > 100) {
                        alert('Settings file contains too many entries (max 100).');
                        signalkitLog('Import blocked: Too many settings', { count: Object.keys(settings).length }, 'warn');
                        return;
                    }

                    if (!confirm('Import settings? This will overwrite your current settings.')) {
                        return;
                    }
                    
                    // Send as JSON string for server-side size validation
                    $.post(getAjaxUrl(), {
                        action: 'signalkit_import_settings',
                        nonce: getNonce(),
                        settings: JSON.stringify(settings)
                    }, function(response) {
                        signalkitLog('Import response', response);

                        if (response.success) {
                            alert('Settings imported successfully!');
                            location.reload();
                        } else {
                            const errorMsg = response.data && response.data.message 
                                ? response.data.message 
                                : 'Unknown error';
                            alert('Import failed: ' + errorMsg);
                        }
                    }).fail(function(xhr, status, error) {
                        signalkitLog('Import failed', { status: status, error: error }, 'error');
                        alert('Import failed. Please try again.');
                    });
                } catch (err) {
                    signalkitLog('JSON parse error', err.message, 'error');
                    alert('Invalid JSON file: ' + err.message);
                }
            };
            reader.readAsText(file);
            
            // Reset file input to allow re-importing the same file
            $(this).val('');
        });

        /**
         * Toggle dependent fields based on enable/disable toggles
         * WordPress UI pattern
         */
        function toggleDependentFields() {
            ['follow', 'preferred'].forEach(function(bannerType) {
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
            .on('change', function() {
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
            setTimeout(function() {
                $('.notice-success').fadeOut();
            }, 3000);
        }

        // Initialize dependent fields visibility
        toggleDependentFields();
        
        // Initialize preview system
        try {
            SignalKitPreview.init();
        } catch (err) {
            signalkitLog('Preview initialization failed', {
                error: err.message,
                stack: err.stack
            }, 'error');
        }

        signalkitLog('✓ SignalKit admin initialization complete - All customization controls ready');
    });


})(jQuery);
