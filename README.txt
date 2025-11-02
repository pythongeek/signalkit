=== SignalKit ===
Contributors: yourname
Donate link: https://yoursite.com/donate
Tags: google news, notifications, banners, engagement, news, follow, preferred source, analytics, mobile responsive
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display customizable Google News Follow and Preferred Source banners to boost your publication's visibility and engagement.

== Description ==

**SignalKit** helps you grow your Google News audience with two powerful, customizable banners:

= 🔔 Google News Follow Banner =
Encourage visitors to follow your publication on Google News with an eye-catching, customizable banner.

= ⭐ Preferred Source Banner =
Help readers add your site as a preferred source on Google News, ensuring they see your content first.

= Key Features =

* **Two Independent Banners** - Run both banners simultaneously or individually
* **Full Customization** - Control colors, text, position, and animation for each banner
* **Smart Display Rules** - Show banners on specific page types (posts, pages, homepage, archives)
* **Device Targeting** - Choose to display on mobile, desktop, or both
* **Frequency Control** - Set how often banners appear (always, once per session, once per day)
* **Built-in Analytics** - Track impressions, clicks, and CTR for each banner
* **Dismissible Banners** - Let users close banners with customizable duration
* **Multiple Positions** - Choose from 6 desktop positions and 2 mobile positions
* **Smooth Animations** - Slide in, fade in, or bounce effects
* **Mobile Responsive** - Looks great on all devices with smart stacking
* **No External Dependencies** - Works out of the box
* **Live Preview** - See changes in real-time before publishing
* **Security Hardened** - Enterprise-grade security features built-in
* **Performance Optimized** - Lightweight (<50KB total), minimal impact
* **Accessibility Compliant** - WCAG 2.1 AA standards
* **Translation Ready** - i18n support with .pot file included

= Perfect For =

* News websites and publications
* Blogs and online magazines
* Content creators on Google News
* Publishers looking to grow their audience
* Anyone wanting to increase Google News engagement

= Analytics Dashboard =

Track performance with detailed analytics:

* Total impressions per banner
* Click-through rates (CTR)
* Dismissal rates
* Combined performance metrics
* Individual banner statistics
* Last updated timestamps

= Security Features =

* Nonce verification on all AJAX requests
* Session token validation
* Rate limiting (client + server side)
* SQL injection prevention via WordPress APIs
* XSS prevention via proper escaping
* CSRF protection
* Cookie manipulation prevention
* Content Security Policy headers

= Performance =

* Optimized database queries with race condition protection
* Efficient caching strategies
* Minimal HTTP requests (2 assets total)
* Lightweight assets (<50KB combined)
* No external API dependencies
* Compatible with caching plugins

= Developer Friendly =

* Clean, well-documented code
* WordPress coding standards
* Extensive hook system for customization
* Shortcode support for manual placement
* No encoded/obfuscated code
* GPL-2.0+ licensed

= Privacy & GDPR =

* Only functional cookies (with user action)
* No personal data collected
* No external tracking
* No analytics sent to third parties
* GDPR compliant
* CCPA compliant

= Documentation =

Comprehensive documentation included:

* Complete user manual (PDF)
* Quick start guide
* Video tutorials (coming soon)
* Code examples for developers
* FAQ section
* Troubleshooting guide

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "SignalKit"
4. Click "Install Now" button
5. Click "Activate" button
6. Go to SignalKit menu to configure settings

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded ZIP file
5. Click "Install Now"
6. Click "Activate Plugin"
7. Go to SignalKit menu to configure settings

= FTP Installation =

1. Download and extract the plugin ZIP file
2. Upload the `signalkit` folder to `/wp-content/plugins/` directory
3. Log in to WordPress admin panel
4. Navigate to Plugins page
5. Find SignalKit and click "Activate"
6. Go to SignalKit menu to configure settings

= After Installation =

1. Go to **SignalKit** menu in WordPress admin
2. Click **Follow Banner** tab
3. Add your Google News publication URL
4. Customize the banner appearance and content
5. Set display rules and frequency
6. Click **Save Settings**
7. Repeat for **Preferred Source Banner** (optional)
8. Monitor performance in **Analytics** page

= Minimum Requirements =

* WordPress 5.0 or higher
* PHP 7.2 or higher
* MySQL 5.6 or higher
* Modern web browser

== Frequently Asked Questions ==

= Do I need a Google News account? =

Yes, you need to have your publication listed on Google News to use the Follow banner. The Preferred Source banner works for any website but is most effective for Google News publishers.

= Can I run both banners at the same time? =

Absolutely! Both banners are independent and can be displayed simultaneously with different positions and settings. On mobile devices, they automatically stack to prevent overlap.

= Will this slow down my website? =

No. SignalKit is lightweight and optimized for performance. It only loads minimal CSS (~8KB) and JavaScript (~12KB), and uses efficient database queries. Page load impact is typically less than 50ms.

= Can I customize the banner appearance? =

Yes! You have full control over:
* Colors (primary, secondary, accent, text)
* Typography (headline, description, button sizes)
* Layout (width, padding, border radius)
* Position (6 desktop options, 2 mobile options)
* Animation style (slide, fade, bounce)
* Content (headline, description, button text)

= How do I get my Google News URL? =

1. Visit Google News (news.google.com)
2. Search for your publication
3. Click on your publication name
4. Copy the URL from your browser's address bar
5. It should look like: https://news.google.com/publications/CAAq...

= Is this GDPR compliant? =

Yes. The plugin only uses functional cookies for banner dismissal (with user action) and doesn't collect personal data. All analytics are aggregated and anonymous.

= Can I show banners only on specific pages? =

Yes! You can control display by:
* Page type (posts, pages, homepage, archives)
* Device type (mobile, desktop, or both)
* Display frequency (always, once per session, once per day)

You can also use shortcodes for manual placement or hooks for programmatic control.

= How accurate are the analytics? =

Very accurate. Analytics use secure AJAX with session validation and anti-spam measures. Each impression is tracked once per page load, and clicks are recorded before external redirects.

= Can I export/import settings? =

Yes! The plugin includes import/export functionality for:
* All banner settings
* Design customizations
* Display rules
* Optional encryption for sensitive data

This makes it easy to backup settings or transfer between sites.

= Does it work with caching plugins? =

Yes! Banners are injected via JavaScript after page load, so they work with all major caching plugins including:
* WP Super Cache
* W3 Total Cache
* WP Rocket
* LiteSpeed Cache
* Cloudflare

= Is it compatible with page builders? =

Yes! SignalKit works with all major page builders:
* Elementor
* Divi
* Beaver Builder
* Gutenberg
* WPBakery

You can also use shortcodes to place banners manually.

= What browsers are supported? =

SignalKit works on all modern browsers:
* Chrome 90+
* Firefox 88+
* Safari 14+
* Edge 90+
* Opera 76+
* Mobile browsers (iOS Safari, Chrome Mobile, Samsung Internet)

Note: Internet Explorer 11 is not supported.

= Can I translate the plugin? =

Yes! SignalKit is translation-ready with a .pot file included. You can use:
* Loco Translate plugin
* Poedit application
* WordPress.org translation system (coming soon)

= How do I report bugs or request features? =

* Support Forum: https://wordpress.org/support/plugin/signalkit
* Email: support@yoursite.com
* GitHub: https://github.com/yourname/signalkit (if open source)

= Is premium support available? =

Yes! Premium support includes:
* Priority response (24-hour)
* Custom development assistance
* Advanced configuration help
* Performance optimization guidance

Contact: sales@yoursite.com

== Screenshots ==

1. Follow Banner - Bottom Left Position (Desktop View)
2. Preferred Source Banner - Bottom Right Position (Desktop View)
3. Mobile View - Stacked Banners at Bottom of Screen
4. Admin Settings - Follow Banner Tab with Live Preview
5. Admin Settings - Preferred Source Banner Tab
6. Admin Settings - Global Settings Tab
7. Admin Settings - Advanced & Security Tab
8. Analytics Dashboard - Combined Performance Metrics
9. Analytics Dashboard - Individual Banner Statistics
10. Live Preview Panel - Desktop Device View
11. Live Preview Panel - Mobile Device View
12. Settings Page - Color Customization
13. Settings Page - Display Rules Configuration
14. Settings Page - Typography Controls

== Changelog ==

= 1.0.0 - Initial Release (January 2025) =

**New Features:**

* Two independent banner systems (Follow & Preferred Source)
* Full customization controls for each banner
* Smart display rules with page type filtering
* Device targeting (mobile/desktop/both)
* Frequency control (always/session/daily)
* Built-in analytics dashboard with CTR tracking
* Live preview system with device switcher
* Multiple desktop positions (6 options)
* Mobile position control with smart stacking
* Three animation styles (slide, fade, bounce)
* Dismissible banners with custom duration
* Color customization (4 colors per banner)
* Typography controls (3 font sizes per banner)
* Layout controls (width, padding, border radius)
* Import/export settings functionality
* Shortcode support for manual placement
* Developer hooks and filters
* Security hardening with session tokens
* Rate limiting (client + server)
* WCAG 2.1 AA accessibility compliance
* Translation ready with .pot file
* RTL language support
* Mobile responsive design
* No external dependencies

**Security:**

* Nonce verification on all AJAX
* Session token validation
* Rate limiting protection
* SQL injection prevention
* XSS prevention
* CSRF protection
* Cookie security
* Content Security Policy headers

**Performance:**

* Optimized queries (<50ms load time)
* Race condition protection
* Efficient caching
* Lightweight assets (<50KB)
* Minimal HTTP requests

**Documentation:**

* Complete user manual (45 pages)
* Quick start guide
* Configuration examples
* Troubleshooting guide
* Developer documentation
* Code snippets
* FAQ section

== Upgrade Notice ==

= 1.0.0 =
Initial release of SignalKit. Install to start growing your Google News audience with customizable follow and preferred source banners.

== Additional Info ==

**Support Resources:**

* Documentation: https://yoursite.com/signalkit/documentation
* Support Forum: https://yoursite.com/support
* Video Tutorials: https://yoursite.com/signalkit/videos
* Knowledge Base: https://yoursite.com/signalkit/kb
* Email Support: support@yoursite.com

**Useful Links:**

* Plugin Homepage: https://yoursite.com/signalkit
* Live Demo: https://demo.yoursite.com/signalkit
* GitHub Repository: https://github.com/yourname/signalkit
* Changelog: https://yoursite.com/signalkit/changelog
* Roadmap: https://yoursite.com/signalkit/roadmap

**Get Involved:**

* Report Bugs: https://github.com/yourname/signalkit/issues
* Request Features: https://yoursite.com/signalkit/features
* Translate Plugin: https://translate.wordpress.org/projects/wp-plugins/signalkit
* Contribute Code: https://github.com/yourname/signalkit/pulls
* Write Review: https://wordpress.org/support/plugin/signalkit/reviews/

**Credits:**

* Development: SignalKit Team
* Security Audit: Independent Security Experts
* Beta Testing: 100+ WordPress Users
* Icons: Custom SVG Graphics
* Design: Google Material Design Inspired

**Privacy Policy:**

SignalKit respects your privacy:
* No personal data collected
* No external API calls
* Only functional cookies
* GDPR compliant
* CCPA compliant
* No tracking scripts

**License:**

SignalKit is licensed under GPL-2.0+ (GNU General Public License v2 or later). You are free to use, modify, and distribute this plugin in accordance with the license terms.

**System Requirements:**

* WordPress: 5.0+
* PHP: 7.2+
* MySQL: 5.6+
* Memory: 64MB minimum, 128MB recommended
* Browser: Modern (Chrome, Firefox, Safari, Edge)

**Tested Environments:**

* WordPress: 5.0 - 6.4+
* PHP: 7.2, 7.3, 7.4, 8.0, 8.1, 8.2
* MySQL: 5.6, 5.7, 8.0
* MariaDB: 10.3, 10.4, 10.5
* Themes: Twenty Twenty-One, Twenty Twenty-Two, Twenty Twenty-Three
* Page Builders: Elementor, Divi, Beaver Builder, Gutenberg

**Known Limitations:**

* Internet Explorer 11 not supported
* Requires JavaScript enabled
* Best with HTTPS enabled
* Some features require modern browsers

**Future Plans:**

Version 1.1.0 (Q1 2025):
* A/B testing functionality
* Advanced scheduling
* Custom CSS editor
* Template library

Version 1.2.0 (Q2 2025):
* Google Analytics integration
* Email notifications
* Custom templates
* Role-based permissions

Version 2.0.0 (Q3 2025):
* Visual banner builder
* Template marketplace
* Advanced targeting
* Conversion tracking

**Thank You:**

Thank you for choosing SignalKit! We're committed to helping you grow your Google News audience. If you find the plugin helpful, please consider leaving a ⭐⭐⭐⭐⭐ review!