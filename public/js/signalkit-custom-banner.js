/**
 * SignalKit Custom Banner JavaScript
 * Form handling, triggers, and interactions
 * 
 * @package SignalKit
 * @version 2.0.0
 */

(function () {
    'use strict';

    // ========================================
    // CUSTOM BANNER CONTROLLER
    // ========================================

    const SignalKitCustomBanner = {

        /**
         * Banner element
         */
        banner: null,

        /**
         * Form element
         */
        form: null,

        /**
         * Settings from data attributes
         */
        settings: {},

        /**
         * State tracking
         */
        state: {
            isVisible: false,
            isSubmitted: false,
            isTriggered: false,
            scrollPercentage: 0
        },

        /**
         * Storage key
         */
        storageKey: 'signalkit_custom_banner',

        /**
         * Initialize
         */
        init: function () {
            this.banner = document.getElementById('signalkit-banner-custom');

            if (!this.banner) {
                return;
            }

            // Reset state for re-initialization
            this.state = {
                isVisible: false,
                isSubmitted: false,
                isTriggered: false,
                scrollPercentage: 0
            };

            this.form = this.banner.querySelector('.signalkit-custom-form');
            this.loadSettings();

            // Check if should show
            if (!this.shouldShow()) {
                return;
            }

            this.bindEvents();
            this.setupTriggers();
        },

        /**
         * Load settings from data attributes
         */
        loadSettings: function () {
            this.settings = {
                bannerType: this.banner.dataset.bannerType || 'newsletter',
                delay: parseInt(this.banner.dataset.delay || 3, 10) * 1000,
                scrollTrigger: this.banner.dataset.scrollTrigger === 'true',
                scrollPercentage: parseInt(this.banner.dataset.scrollPercentage || 50, 10),
                exitIntent: this.banner.dataset.exitIntent === 'true',
                successMessage: this.banner.dataset.successMessage || 'Thank you!',
                dismissible: this.banner.dataset.dismissible !== 'false'
            };
        },

        /**
         * Check if banner should show
         */
        shouldShow: function () {
            const stored = this.getStoredData();

            // Already submitted
            if (stored.submitted) {
                return false;
            }

            // Dismissed
            if (stored.dismissed) {
                const dismissedAt = new Date(stored.dismissedAt);
                const dismissDuration = stored.dismissDuration || 7; // days
                const now = new Date();
                const daysSinceDismiss = (now - dismissedAt) / (1000 * 60 * 60 * 24);

                if (daysSinceDismiss < dismissDuration) {
                    return false;
                }
            }

            return true;
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            const self = this;

            // Close button - use more robust binding
            const closeBtn = this.banner.querySelector('.signalkit-close');
            if (closeBtn) {
                // Remove any existing listeners first (for re-initialization)
                const newCloseBtn = closeBtn.cloneNode(true);
                closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);

                newCloseBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    self.dismiss();
                }, true); // Use capture phase
            }

            // Modal overlay click to close
            const overlay = this.banner.querySelector('.signalkit-modal-overlay');
            if (overlay) {
                overlay.addEventListener('click', function () {
                    if (self.settings.dismissible) {
                        self.dismiss();
                    }
                });
            }

            // Form submission
            if (this.form) {
                this.form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    self.handleSubmit();
                });
            }

            // Escape key to close
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && self.state.isVisible && self.settings.dismissible) {
                    self.dismiss();
                }
            });

            // Promo code copy button
            const copyBtn = this.banner.querySelector('.signalkit-copy-code');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    const code = this.dataset.code;
                    if (code && navigator.clipboard) {
                        navigator.clipboard.writeText(code).then(function () {
                            // Visual feedback
                            const originalHTML = copyBtn.innerHTML;
                            copyBtn.innerHTML = '✓';
                            copyBtn.style.background = 'var(--signalkit-primary)';
                            copyBtn.style.color = '#fff';
                            setTimeout(function () {
                                copyBtn.innerHTML = originalHTML;
                                copyBtn.style.background = '';
                                copyBtn.style.color = '';
                            }, 1500);
                        });
                    }
                });
            }
        },

        /**
         * Setup display triggers
         */
        setupTriggers: function () {
            const self = this;

            // Delay trigger
            if (this.settings.delay > 0 && !this.settings.scrollTrigger && !this.settings.exitIntent) {
                setTimeout(function () {
                    self.show();
                }, this.settings.delay);
            }

            // Scroll trigger
            if (this.settings.scrollTrigger) {
                window.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
            }

            // Exit intent (desktop only)
            if (this.settings.exitIntent && !this.isMobile()) {
                document.addEventListener('mouseout', this.handleExitIntent.bind(this));
            }

            // If no triggers, show after delay
            if (!this.settings.scrollTrigger && !this.settings.exitIntent) {
                if (this.settings.delay === 0) {
                    this.show();
                }
            }
        },

        /**
         * Handle scroll for trigger
         */
        handleScroll: function () {
            if (this.state.isTriggered) {
                return;
            }

            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;

            if (scrollPercent >= this.settings.scrollPercentage) {
                this.state.isTriggered = true;

                // Apply delay if set
                if (this.settings.delay > 0) {
                    setTimeout(() => this.show(), this.settings.delay);
                } else {
                    this.show();
                }
            }
        },

        /**
         * Handle exit intent
         */
        handleExitIntent: function (e) {
            if (this.state.isTriggered || this.state.isVisible) {
                return;
            }

            // Check if cursor left viewport from top
            if (e.clientY <= 0) {
                this.state.isTriggered = true;
                this.show();
            }
        },

        /**
         * Show banner
         */
        show: function () {
            if (this.state.isVisible) {
                return;
            }

            this.state.isVisible = true;

            // Remove aria-hidden FIRST before any focus (prevents accessibility warning)
            this.banner.removeAttribute('aria-hidden');

            this.banner.classList.add('active');

            // Focus management for accessibility - delay to allow animation
            const self = this;
            const firstFocusable = this.banner.querySelector('input, button:not(.signalkit-close)');
            if (firstFocusable) {
                setTimeout(function () {
                    // Only focus if banner is still visible
                    if (self.state.isVisible) {
                        firstFocusable.focus();
                    }
                }, 400);
            }

            // Track impression if analytics enabled
            this.trackEvent('impression');
        },

        /**
         * Hide banner
         */
        hide: function () {
            this.state.isVisible = false;

            // Remove focus from banner before hiding (accessibility)
            // Critical to prevent aria-hidden warning
            try {
                if (document.activeElement && this.banner && this.banner.contains(document.activeElement)) {
                    document.activeElement.blur();
                }
            } catch (e) {
                // Silent catch
            }

            this.banner.classList.remove('active');
            this.banner.setAttribute('aria-hidden', 'true');
        },

        /**
         * Dismiss banner
         */
        dismiss: function () {
            this.hide();

            // Store dismissal
            this.setStoredData({
                dismissed: true,
                dismissedAt: new Date().toISOString(),
                dismissDuration: 7 // days - could be made configurable
            });

            // Track event
            this.trackEvent('dismiss');
        },

        /**
         * Handle form submission
         */
        handleSubmit: function () {
            const self = this;
            const form = this.form;
            const button = form.querySelector('.signalkit-button');
            const messageEl = form.querySelector('.signalkit-form-message');

            // Get form data
            const formData = new FormData(form);
            const email = formData.get('signalkit_email');
            const name = formData.get('signalkit_name') || '';

            // Validate email
            if (!this.validateEmail(email)) {
                const errorMsg = (typeof signalkitStrings !== 'undefined' && signalkitStrings.emailInvalid)
                    ? signalkitStrings.emailInvalid
                    : 'Please enter a valid email address';
                this.showFormMessage(messageEl, errorMsg, 'error');
                return;
            }

            // Show loading state
            button.classList.add('loading');
            button.disabled = true;

            // Submit via AJAX
            const ajaxData = new FormData();
            ajaxData.append('action', 'signalkit_custom_submit');
            ajaxData.append('email', email);
            ajaxData.append('name', name);
            ajaxData.append('banner_type', this.settings.bannerType);
            ajaxData.append('nonce', form.querySelector('[name="signalkit_custom_nonce"]')?.value || '');

            fetch(signalkitData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: ajaxData,
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    button.classList.remove('loading');
                    button.disabled = false;

                    if (data.success) {
                        self.handleSubmitSuccess();
                    } else {
                        const errorMsg = data.data?.message ||
                            ((typeof signalkitStrings !== 'undefined' && signalkitStrings.errorGeneral)
                                ? signalkitStrings.errorGeneral
                                : 'Something went wrong');
                        self.showFormMessage(messageEl, errorMsg, 'error');
                    }
                })
                .catch(error => {
                    button.classList.remove('loading');
                    button.disabled = false;

                    // Still treat as success for UX (submission may have worked)
                    self.handleSubmitSuccess();
                });
        },

        /**
         * Handle successful submission
         */
        handleSubmitSuccess: function () {
            // Mark as submitted
            this.state.isSubmitted = true;
            this.banner.classList.add('submitted');

            // Store submission
            this.setStoredData({
                submitted: true,
                submittedAt: new Date().toISOString()
            });

            // Track conversion
            this.trackEvent('submit');

            // Check for redirect
            const redirectUrl = document.querySelector('[name="signalkit_redirect_url"]')?.value;
            if (redirectUrl) {
                setTimeout(function () {
                    window.location.href = redirectUrl;
                }, 1500);
            } else {
                // Auto-hide after showing success
                setTimeout(() => this.hide(), 3000);
            }
        },

        /**
         * Show form message
         */
        showFormMessage: function (el, message, type) {
            if (!el) return;

            el.textContent = message;
            el.className = 'signalkit-form-message ' + type;

            // Auto-hide after 5 seconds
            setTimeout(function () {
                el.className = 'signalkit-form-message';
            }, 5000);
        },

        /**
         * Validate email
         */
        validateEmail: function (email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        /**
         * Track event
         */
        trackEvent: function (eventType) {
            if (typeof signalkitData === 'undefined' || !signalkitData.analyticsEnabled) {
                return;
            }

            // Map event types to correct AJAX actions
            const actionMap = {
                'impression': 'signalkit_track_impression',
                'dismiss': 'signalkit_track_dismissal',
                'click': 'signalkit_track_click',
                'submit': 'signalkit_track_impression' // Use impression for submit tracking
            };

            const action = actionMap[eventType];
            if (!action) {
                return;
            }

            // Use existing analytics system with correct action names
            const data = new FormData();
            data.append('action', action);
            data.append('nonce', signalkitData.nonce);
            data.append('banner_type', 'custom');
            data.append('session_token', signalkitData.sessionToken || '');

            fetch(signalkitData.ajaxUrl, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            }).catch(function (error) {
                // Silent catch
            });
        },

        /**
         * Get stored data
         */
        getStoredData: function () {
            try {
                const data = localStorage.getItem(this.storageKey);
                return data ? JSON.parse(data) : {};
            } catch (e) {
                return {};
            }
        },

        /**
         * Set stored data
         */
        setStoredData: function (data) {
            try {
                const existing = this.getStoredData();
                localStorage.setItem(this.storageKey, JSON.stringify({ ...existing, ...data }));
            } catch (e) {
                // Storage not available
            }
        },

        /**
         * Check if mobile device
         */
        isMobile: function () {
            return window.innerWidth <= 768 || /Mobi|Android/i.test(navigator.userAgent);
        }
    };

    // ========================================
    // INITIALIZE ON DOM READY
    // ========================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            SignalKitCustomBanner.init();
        });
    } else {
        SignalKitCustomBanner.init();
    }

    // Expose globally for re-initialization
    window.SignalKitCustomBanner = SignalKitCustomBanner;

})();
