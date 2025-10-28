/**
 * SignalKit for Google - Public JavaScript
 * Version: 2.4.0
 * 
 * FIXED: Removed deprecated jQuery.fn.unbind() - replaced with .off()
 * FIXED: Added impression tracking functionality
 * ADDED: Dynamic body padding on mobile to prevent banner overlap
 * 
 * Security: Nonce verification for all AJAX calls
 * WordPress Compatible: Uses WordPress AJAX API
 * Envato Compatible: GPL-2.0+ license
 * 
 * Handles BOTH Follow and Preferred Source Banners
 */

(function($) {
    'use strict';

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
            
            if (this.$banner.length) {
                this.init();
            }
        }

        /**
         * Initialize banner
         */
        init() {
            console.log('SignalKit: Initializing ' + this.bannerType + ' banner', {
                analyticsEnabled: this.analyticsEnabled,
                settings: this.settings
            });
            
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
         */
        show() {
            this.$banner.show();
            
            // Add active class for CSS animation
            setTimeout(() => {
                this.$banner.addClass('active');
                updateBodyPadding(); // Update padding after show
            }, 50);

            console.log('SignalKit: ' + this.bannerType + ' banner displayed');
        }

        /**
         * Hide banner with animation
         */
        hide() {
            this.$banner.removeClass('active');
            
            setTimeout(() => {
                this.$banner.hide();
                updateBodyPadding(); // Update padding after hide
            }, 400);
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            const self = this;

            // Click tracking on CTA button
            // FIXED: Using .off() instead of deprecated .unbind()
            this.$banner.find('.signalkit-button')
                .off('click.signalkit')
                .on('click.signalkit', function(e) {
                    console.log('SignalKit: Button clicked', {
                        banner: self.bannerType,
                        url: $(this).attr('href')
                    });
                    
                    if (self.analyticsEnabled) {
                        self.trackClick();
                    }
                    // Link navigation happens naturally
                });

            // Close button handler
            // FIXED: Using .off() instead of deprecated .unbind()
            this.$banner.find('.signalkit-close')
                .off('click.signalkit')
                .on('click.signalkit', function(e) {
                    e.preventDefault();
                    console.log('SignalKit: Close button clicked', {
                        banner: self.bannerType
                    });
                    self.dismiss();
                });

            // Educational link click tracking (preferred banner only)
            if (this.bannerType === 'preferred') {
                this.$banner.find('.signalkit-educational-link')
                    .off('click.signalkit')
                    .on('click.signalkit', function() {
                        console.log('SignalKit: Educational link clicked', {
                            url: $(this).attr('href')
                        });
                    });
            }
        }

        /**
         * Track impression via AJAX
         * Security: Uses nonce verification
         */
        trackImpression() {
            if (this.impressionTracked) {
                console.log('SignalKit: Impression already tracked for ' + this.bannerType);
                return;
            }

            console.log('SignalKit: Tracking impression for ' + this.bannerType);

            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_impression',
                    nonce: signalkitData.nonce,
                    banner_type: this.bannerType
                },
                success: (response) => {
                    if (response && response.success) {
                        this.impressionTracked = true;
                        console.log('SignalKit: Impression tracked successfully', {
                            banner: this.bannerType,
                            response: response
                        });
                    } else {
                        console.error('SignalKit: Impression tracking failed', {
                            banner: this.bannerType,
                            response: response
                        });
                    }
                },
                error: (xhr, status, error) => {
                    console.error('SignalKit: Impression tracking AJAX error', {
                        banner: this.bannerType,
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        response: xhr.responseText
                    });
                }
            });
        }

        /**
         * Track click via AJAX
         * Security: Uses nonce verification
         */
        trackClick() {
            console.log('SignalKit: Tracking click for ' + this.bannerType);

            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_click',
                    nonce: signalkitData.nonce,
                    banner_type: this.bannerType
                },
                success: (response) => {
                    if (response && response.success) {
                        console.log('SignalKit: Click tracked successfully', {
                            banner: this.bannerType,
                            response: response
                        });
                    } else {
                        console.error('SignalKit: Click tracking failed', {
                            banner: this.bannerType,
                            response: response
                        });
                    }
                },
                error: (xhr, status, error) => {
                    console.error('SignalKit: Click tracking AJAX error', {
                        banner: this.bannerType,
                        status: status,
                        error: error
                    });
                }
            });
        }

        /**
         * Dismiss banner and track dismissal
         * Security: Uses nonce verification, sets secure cookie
         */
        dismiss() {
            const duration = this.settings.dismissDuration || 7;

            console.log('SignalKit: Dismissing ' + this.bannerType + ' banner', {
                duration: duration + ' days'
            });

            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_dismissal',
                    nonce: signalkitData.nonce,
                    banner_type: this.bannerType,
                    duration: duration
                },
                success: (response) => {
                    if (response && response.success) {
                        console.log('SignalKit: Dismissal tracked successfully', {
                            banner: this.bannerType,
                            response: response
                        });
                    } else {
                        console.error('SignalKit: Dismissal tracking failed', {
                            banner: this.bannerType,
                            response: response
                        });
                    }
                    this.hide();
                },
                error: (xhr, status, error) => {
                    console.error('SignalKit: Dismissal tracking AJAX error', {
                        banner: this.bannerType,
                        status: status,
                        error: error
                    });
                    // Still hide the banner even if tracking fails
                    this.hide();
                }
            });
        }
    }

    /**
     * Global function to update body padding on mobile for bottom banners
     * Prevents banner from overlapping site content
     */
    function updateBodyPadding() {
        const isMobile = window.innerWidth <= 480;
        if (!isMobile) {
            // Reset padding on desktop
            document.body.style.paddingBottom = '';
            return;
        }

        let maxBottom = 0;
        
        // Calculate total height of all active bottom banners
        $('.signalkit-banner.active.signalkit-position-mobile-bottom').each(function() {
            const style = getComputedStyle(this);
            const bottom = parseFloat(style.bottom) || 0;
            const height = $(this).outerHeight();
            maxBottom = Math.max(maxBottom, bottom + height);
        });

        // Apply padding to body
        document.body.style.paddingBottom = (maxBottom > 0 ? maxBottom + 20 : 0) + 'px';
        console.log('SignalKit: Updated body padding-bottom to ' + document.body.style.paddingBottom);
    }

    /**
     * Initialize on document ready
     * WordPress standard - uses jQuery ready event
     */
    $(document).ready(function() {
        console.log('SignalKit: DOM Ready', {
            ajaxUrl: signalkitData.ajaxUrl,
            analyticsEnabled: signalkitData.analyticsEnabled,
            followBannerExists: $('.signalkit-banner-follow').length > 0,
            preferredBannerExists: $('.signalkit-banner-preferred').length > 0
        });

        // Initialize Follow Banner
        if ($('.signalkit-banner-follow').length) {
            new SignalKitBanner('follow');
        }

        // Initialize Preferred Source Banner
        if ($('.signalkit-banner-preferred').length) {
            new SignalKitBanner('preferred');
        }

        // Debug: Log if no banners found
        if ($('.signalkit-banner-follow').length === 0 && $('.signalkit-banner-preferred').length === 0) {
            console.log('SignalKit: No banners found in DOM');
        }

        // Initial padding update after delay
        setTimeout(updateBodyPadding, 1500);
        
        // Update padding on window resize
        $(window).on('resize.signalkit', function() {
            updateBodyPadding();
        });
    });

})(jQuery);