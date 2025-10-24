/**
 * SignalKit for Google - Public JavaScript
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
            
            if (this.$banner.length) {
                this.init();
            }
        }

        init() {
            console.log('SignalKit: Initializing ' + this.bannerType + ' banner');
            
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
            }, 50);

            console.log('SignalKit: ' + this.bannerType + ' banner displayed');
        }

        hide() {
            this.$banner.removeClass('active');
            
            setTimeout(() => {
                this.$banner.hide();
            }, 400);
        }

        bindEvents() {
            const self = this;

            // Click tracking on CTA button
            this.$banner.find('.signalkit-button').on('click', function(e) {
                self.trackClick();
            });

            // Close button
            this.$banner.find('.signalkit-close').on('click', function(e) {
                e.preventDefault();
                self.dismiss();
            });

            // Educational link click (preferred banner only)
            if (this.bannerType === 'preferred') {
                this.$banner.find('.signalkit-educational-link').on('click', function() {
                    console.log('SignalKit: Educational link clicked');
                });
            }
        }

        trackClick() {
            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_click',
                    nonce: signalkitData.nonce,
                    banner_type: this.bannerType
                },
                success: function(response) {
                    console.log('SignalKit: Click tracked for ' + this.bannerType, response);
                }.bind(this),
                error: function(xhr, status, error) {
                    console.error('SignalKit: Click tracking failed', error);
                }
            });
        }

        dismiss() {
            const duration = this.settings.dismissDuration;

            $.ajax({
                url: signalkitData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'signalkit_track_dismissal',
                    nonce: signalkitData.nonce,
                    banner_type: this.bannerType,
                    duration: duration
                },
                success: function(response) {
                    console.log('SignalKit: Dismissal tracked for ' + this.bannerType, response);
                    this.hide();
                }.bind(this),
                error: function(xhr, status, error) {
                    console.error('SignalKit: Dismissal tracking failed', error);
                    this.hide();
                }.bind(this)
            });
        }
    }

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        console.log('SignalKit: DOM Ready');

        // Initialize Follow Banner
        if ($('.signalkit-banner-follow').length) {
            new SignalKitBanner('follow');
        }

        // Initialize Preferred Source Banner
        if ($('.signalkit-banner-preferred').length) {
            new SignalKitBanner('preferred');
        }
    });

})(jQuery);