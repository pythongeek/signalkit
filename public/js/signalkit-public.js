/**
 * SignalKit - Public JavaScript
 *
 * @package SignalKit
 * @version 1.0.0
 *
 * This file handles the frontend behavior of the SignalKit banners,
 * including display animations, event tracking (impressions, clicks, dismissals),
 * and dynamic mobile layout adjustments for banner stacking.
 *
 * Features:
 * - Client-side rate limiting for analytics.
 * - Secure AJAX tracking with nonce and session tokens.
 * - Dynamic stacking and body padding for mobile devices.
 * - WCAG-compliant event handling.
 */

(function($) {
    'use strict';

    /**
     * Rate Limiter Class
     * Client-side rate limiting to prevent abuse
     */
    class RateLimiter {
        constructor() {
            this.limits = {
                impression: { max: 10, window: 60000, attempts: [] },
                click: { max: 5, window: 60000, attempts: [] },
                dismiss: { max: 3, window: 60000, attempts: [] }
            };
        }

        check(action) {
            if (!this.limits[action]) return true;
            
            const limit = this.limits[action];
            const now = Date.now();
            
            // Clean old attempts
            limit.attempts = limit.attempts.filter(time => (now - time) < limit.window);
            
            // Check if limit exceeded
            if (limit.attempts.length >= limit.max) {
                return false;
            }
            
            // Add current attempt
            limit.attempts.push(now);
            return true;
        }
    }

    const rateLimiter = new RateLimiter();

    /**
     * Banner Controller Class
     * Manages individual banner lifecycle and interactions
     */
    class SignalKitBanner {
        constructor(bannerType) {
            this.bannerType = bannerType;
            this.$banner = $('.signalkit-banner-' + bannerType);
            this.settings = signalkitData[bannerType + 'Settings'];
            this.analyticsEnabled = signalkitData.analyticsEnabled || false;
            this.impressionTracked = false;
            this.sessionToken = signalkitData.sessionToken || '';
            this.rateLimitingEnabled = signalkitData.rateLimitingEnabled || false;
            
            if (this.$banner.length) {
                this.init();
            }
        }

        /**
         * Initialize banner
         */
        init() {
            // Validate session token
            if (!this.sessionToken || this.sessionToken.length !== 64) {
                this.analyticsEnabled = false;
            }
            
            // Track impression immediately (once per page load)
            if (this.analyticsEnabled && !this.impressionTracked) {
                this.trackImpression();
            }
            
            // Show banner with delay for smooth animation
            setTimeout(() => {
                this.show();
            }, 1000);

            // Bind event handlers
            this.bindEvents();
        }

        /**
         * Show banner with animation
         * FIXED: Proper reflow forcing to prevent animation flicker
         */
        show() {
            this.$banner.css('display', 'block');
            
            // Force reflow to ensure CSS is applied
            this.$banner[0].offsetHeight;
            
            requestAnimationFrame(() => {
                this.$banner.addClass('active');
                updateMobileLayout();
            });
        }

        /**
         * Hide banner with animation
         */
        hide() {
            this.$banner.removeClass('active');
            
            setTimeout(() => {
                this.$banner.hide();
                updateMobileLayout();
            }, 400);
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            const self = this;

            // Click tracking on CTA button
            this.$banner.find('.signalkit-button')
                .off('click.signalkit')
                .on('click.signalkit', function(e) {
                    if (self.analyticsEnabled) {
                        self.trackClick();
                    }
                });

            // Close button handler
            this.$banner.find('.signalkit-close')
                .off('click.signalkit')
                .on('click.signalkit', function(e) {
                    e.preventDefault();
                    self.dismiss();
                });

            // Educational link click tracking (preferred banner only)
            if (this.bannerType === 'preferred') {
                this.$banner.find('.signalkit-educational-link')
                    .off('click.signalkit')
                    .on('click.signalkit', function() {
                        // Educational link clicked
                    });
            }
        }

        /**
         * Track impression via AJAX
         * SECURITY: Session token + nonce verification, rate limiting
         */
        trackImpression() {
            if (this.impressionTracked) {
                return;
            }

            // Client-side rate limiting
            if (this.rateLimitingEnabled && !rateLimiter.check('impression')) {
                return;
            }

            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_impression',
                    nonce: signalkitData.nonce,
                    sessionToken: this.sessionToken,
                    banner_type: this.bannerType
                },
                success: (response) => {
                    if (response && response.success) {
                        this.impressionTracked = true;
                    }
                },
                error: (xhr, status, error) => {
                    // Handle session errors
                    if (xhr.status === 403) {
                        this.analyticsEnabled = false;
                    }
                }
            });
        }

        /**
         * Track click via AJAX
         * SECURITY: Session token + nonce verification, rate limiting
         */
        trackClick() {
            // Client-side rate limiting
            if (this.rateLimitingEnabled && !rateLimiter.check('click')) {
                return;
            }

            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_click',
                    nonce: signalkitData.nonce,
                    sessionToken: this.sessionToken,
                    banner_type: this.bannerType
                },
                success: (response) => {
                    // Click tracked
                },
                error: (xhr, status, error) => {
                    if (xhr.status === 403) {
                        this.analyticsEnabled = false;
                    }
                }
            });
        }

        /**
         * Dismiss banner and track dismissal
         * SECURITY: Session token + nonce verification, rate limiting
         */
        dismiss() {
            const duration = this.settings.dismissDuration || 7;

            // Client-side rate limiting
            if (this.rateLimitingEnabled && !rateLimiter.check('dismiss')) {
                this.hide(); // Still hide the banner
                return;
            }

            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_dismissal',
                    nonce: signalkitData.nonce,
                    sessionToken: this.sessionToken,
                    banner_type: this.bannerType,
                    duration: duration
                },
                success: (response) => {
                    this.hide();
                },
                error: (xhr, status, error) => {
                    // Still hide the banner even if tracking fails
                    this.hide();
                }
            });
        }
    }

    /**
     * [NEW] Dynamically stack mobile banners to prevent overlap.
     */
    function updateMobileBannerPositions() {
        if (window.innerWidth > 768) {
            // On desktop, reset any mobile positioning overrides
            $('.signalkit-banner').css({ 'bottom': '', 'top': '' });
            return;
        }

        // --- Handle Bottom Banners ---
        const $bottomBanners = $('.signalkit-banner.active.signalkit-position-mobile-bottom');
        let bottomOffset = 0;
        $bottomBanners.sort((a, b) => {
            return ($(a).data('stack-order') || 99) - ($(b).data('stack-order') || 99);
        }).each(function() {
            $(this).css('bottom', bottomOffset + 'px');
            bottomOffset += $(this).outerHeight();
        });

        // --- Handle Top Banners ---
        const $topBanners = $('.signalkit-banner.active.signalkit-position-mobile-top');
        let topOffset = 0;
        $topBanners.sort((a, b) => {
            return ($(a).data('stack-order') || 99) - ($(b).data('stack-order') || 99);
        }).each(function() {
            $(this).css('top', topOffset + 'px');
            topOffset += $(this).outerHeight();
        });
    }

    /**
     * Global function to update body padding on mobile for bottom banners
     * Prevents banner from overlapping site content
     */
    function updateBodyPadding() {
        const isMobile = window.innerWidth <= 768;
        if (!isMobile) {
            document.body.style.paddingBottom = '';
            document.body.style.paddingTop = '';
            return;
        }

        let totalBottomHeight = 0;
        $('.signalkit-banner.active.signalkit-position-mobile-bottom').each(function() {
            totalBottomHeight += $(this).outerHeight();
        });
        document.body.style.paddingBottom = (totalBottomHeight > 0 ? totalBottomHeight + 20 : 0) + 'px';

        let totalTopHeight = 0;
        $('.signalkit-banner.active.signalkit-position-mobile-top').each(function() {
            totalTopHeight += $(this).outerHeight();
        });
        document.body.style.paddingTop = (totalTopHeight > 0 ? totalTopHeight + 20 : 0) + 'px';
    }

    /**
     * [NEW] Combined update function for mobile layout.
     */
    function updateMobileLayout() {
        updateMobileBannerPositions();
        updateBodyPadding();
    }


    /**
     * Initialize on document ready
     * WordPress standard - uses jQuery ready event
     */
    $(document).ready(function() {
        // Initialize Follow Banner
        if ($('.signalkit-banner-follow').length) {
            new SignalKitBanner('follow');
        }

        // Initialize Preferred Source Banner
        if ($('.signalkit-banner-preferred').length) {
            new SignalKitBanner('preferred');
        }

        // Initial layout update after delay for rendering
        setTimeout(updateMobileLayout, 1500);
        
        // Update layout on window resize
        let resizeTimer;
        $(window).on('resize.signalkit', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateMobileLayout, 100);
        });
    });

})(jQuery);
