/**
 * SignalKit for Google - Public JavaScript
 * Version: 2.3.0 - ADDED: Dynamic body padding on mobile to prevent banner overlap with site content
 * 
 * Handles BOTH Follow and Preferred Source Banners
 */

(function($) {
    'use strict';

    /**
     * Banner Controller Class
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

        init() {
            console.log('SignalKit: Initializing ' + this.bannerType + ' banner', {
                analyticsEnabled: this.analyticsEnabled,
                settings: this.settings
            });
            
            // Track impression immediately (once)
            if (this.analyticsEnabled && !this.impressionTracked) {
                this.trackImpression();
            }
            
            // Show banner with delay
            setTimeout(() => {
                this.show();
            }, 1000);

            // Bind events
            this.bindEvents();
        }

        show() {
            this.$banner.show();
            
            // Add active class for animation
            setTimeout(() => {
                this.$banner.addClass('active');
                updateBodyPadding(); // Update padding after show
            }, 50);

            console.log('SignalKit: ' + this.bannerType + ' banner displayed');
        }

        hide() {
            this.$banner.removeClass('active');
            
            setTimeout(() => {
                this.$banner.hide();
                updateBodyPadding(); // Update padding after hide
            }, 400);
        }

        bindEvents() {
            const self = this;

            // Click tracking on CTA button
            this.$banner.find('.signalkit-button').on('click', function(e) {
                console.log('SignalKit: Button clicked', {
                    banner: self.bannerType,
                    url: $(this).attr('href')
                });
                
                if (self.analyticsEnabled) {
                    self.trackClick();
                }
                // Link navigation happens naturally
            });

            // Close button
            this.$banner.find('.signalkit-close').on('click', function(e) {
                e.preventDefault();
                console.log('SignalKit: Close button clicked', {
                    banner: self.bannerType
                });
                self.dismiss();
            });

            // Educational link click (preferred banner only)
            if (this.bannerType === 'preferred') {
                this.$banner.find('.signalkit-educational-link').on('click', function() {
                    console.log('SignalKit: Educational link clicked', {
                        url: $(this).attr('href')
                    });
                });
            }
        }

        /**
         * Track impression via AJAX
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
                        response: xhr.responseText
                    });
                }
            });
        }

        /**
         * Track click via AJAX
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
     */
    function updateBodyPadding() {
        const isMobile = window.innerWidth <= 480;
        if (!isMobile) return;

        let maxBottom = 0;
        $('.signalkit-banner.active.signalkit-position-mobile-bottom').each(function() {
            const style = getComputedStyle(this);
            const bottom = parseFloat(style.bottom) || 0;
            const height = $(this).outerHeight();
            maxBottom = Math.max(maxBottom, bottom + height);
        });

        document.body.style.paddingBottom = (maxBottom > 0 ? maxBottom + 20 : 0) + 'px';
        console.log('SignalKit: Updated body padding-bottom to ' + document.body.style.paddingBottom);
    }

    /**
     * Initialize on document ready
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

        // Initial padding update
        setTimeout(updateBodyPadding, 1500);
    });

})(jQuery);