=== SignalKit ===
Contributors: N4Nion,BdowneerTech
Tags: google news, notifications, banners, engagement, lead capture
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

News publisher notification plugin with Follow, Preferred Source, and Lead Capture banners to help grow your audience and collect leads.

== Description ==

**SignalKit** is an engagement tool for WordPress publishers. It helps you connect with your Google News audience, capture leads, and increase reader retention with smart-loading banners.

= 🚀 Growth Tools =

1.  **🔔 Follow Banner**: Convert casual visitors into loyal news subscribers with a prominent follow prompt.
2.  **⭐ Preferred Source Banner**: Boost your visibility by getting readers to mark you as a "Preferred Source".
3.  **✉️ Custom Lead Capture**: Grow your newsletter with **Data Capture** banners featuring "Lead Gradient", "Glassmorphism", and "Neon" templates.
4.  **🧠 Intelligent Stacking**: Our collision detection engine ensures banners never overlap, preventing "Ad Clutter" penalties and ensuring a smooth user experience.

= ✨ Key Features =

*   **NEW: Shortcode Banners**: Embed any banner directly inside post content with 5 style variations:
    * `[signalkit_follow style="leaderboard"]` - 728x90 horizontal
    * `[signalkit_follow style="skyscraper"]` - Vertical sidebar
    * `[signalkit_follow style="rectangle"]` - 300x250 box
    * `[signalkit_follow style="compact"]` - Minimal inline
    * `[signalkit_follow style="full"]` - Full width (default)
*   **NEW: Smart Banner Replacement**: Intelligent collision detection repositions overlapping banners automatically.
*   **NEW: Works When Disabled**: Shortcode banners display even if global auto-insertion is turned off.
*   **Intelligent Layout Engine**: Banners automatically detect each other and reposition to avoid overlaps.
*   **Advanced Mobile Strategy**: Control how banners behave on mobile devices:
    *   **Stack**: Show all banners one after another.
    *   **Rotate**: Randomly show a different banner on each page load.
    *   **Priority**: Only show the most important banner.
*   **Universal Loader**: Smart injection system ensures banners appear on **any theme** and works with **all caching plugins** (WP Rocket, LiteSpeed, etc.).
*   **Templates**: Includes modern "Glassmorphism", "Lead Gradient", "Neon", and "Modern Card" styles.
*   **Smart Triggers**: Display banners based on:
    *   Scroll percentage (e.g., 50% down)
    *   Time delay (e.g., 5 seconds)
    *   Exit intent (desktop only)
    *   Page type (Posts, Pages, Front Page, Archives)
*   **Built-in Analytics Dashboard**: Track Impressions, Clicks, CTR, Dismissals, and Lead Submissions securely.
*   **Excel/CSV Data Export**: Easily export your lead capture data and analytics reports for offline analysis or marketing tools.
*   **Privacy First**: Fully GDPR compliant. Uses only functional cookies (no tracking). All data stored on your server. No external requests.
*   **Accessibility Ready**: WCAG 2.1 AA compliant with proper ARIA labels and focus management.
*   **Editor Preview**: See banners directly in WordPress Block Editor.

= 📊 Analytics & Dashboards =

Track your success with a built-in dashboard:
*   Real-time conversion tracking (CTR)
*   Lead capture submission logs (**Export to Excel/CSV**)
*   Separate stats for Follow, Preferred, and Custom banners
*   Visual charts and performance metrics

= 🎯 Shortcode System =

Embed banners anywhere in your content:

**Basic Usage:**
`[signalkit_follow]` - Follow Banner
`[signalkit_preferred]` - Preferred Source Banner
`[signalkit_custom]` - Custom Lead Capture Banner

**With Styles:**
`[signalkit_follow style="leaderboard" align="center"]`
`[signalkit_custom style="rectangle" align="right"]`

= 🛡️ Comprehensive Security Features =

*   Nonce verification on all AJAX requests
*   Session token validation to prevent analytics spam
*   Rate limiting (client + server side) to prevent abuse
*   Input sanitization and output escaping via WordPress APIs

== Installation ==

= Automatic Installation =

1.  Log in to your WordPress admin panel
2.  Navigate to Plugins > Add New
3.  Search for "SignalKit"
4.  Click "Install Now" button
5.  Click "Activate" button
6.  Go to SignalKit menu to configure settings

== Frequently Asked Questions ==

= How do I add a banner inside my post content? =

Use shortcodes! Add `[signalkit_follow]` anywhere in your post or page content. You can also specify styles like `[signalkit_follow style="leaderboard"]` for a horizontal 728x90 layout.

= Do shortcodes work when the banner is disabled? =

Yes! Shortcodes work independently of the global enable/disable setting. You can disable auto-injection while still manually placing banners via shortcodes.

= Do I need a Google News account? =

Yes, you need to have your publication listed on Google News to use the Follow banner. The Preferred Source and Custom banners work for any website.

= Can I run all banners at the same time? =

Absolutely! The new **Intelligent Positioning System** ensures they never overlap on desktop. On mobile, you can choose to stack them or rotate them.

= Will this slow down my website? =

No. SignalKit is lightweight (<50KB) and loads asynchronously. It uses a smart mutation observer to inject banners without blocking the main thread.

= Can I export my lead capture data? =

Yes! Go to SignalKit → Lead Submissions to view all captured leads and export them as CSV/Excel file.

== Screenshots ==

1.  Follow Banner - Bottom Left Position (Desktop View)
2.  Preferred Source Banner - Bottom Right Position (Desktop View)
3.  Mobile View - Stacked Banners at Bottom of Screen
4.  Admin Settings - Follow Banner Tab with Live Preview
5.  Shortcode Banner - Inline Content Display
6.  Admin Settings - Custom Banner Configuration
7.  Analytics Dashboard - Combined Performance Metrics
8.  Lead Submissions Page - Export to CSV
9.  Live Preview Panel - Desktop Device View
10. Live Preview Panel - Mobile Device View

== Changelog ==

= 2.0.0 (January 2026) =
*   **SECURITY:** Removed custom update checker for full CodeCanyon/Envato marketplace compliance
*   **PERFORMANCE:** Moved database table creation to activation hook (eliminates runtime database queries)
*   **SECURITY:** All global functions renamed with unique prefixes to prevent namespace conflicts
*   **PRIVACY:** Added explicit privacy disclosure for webhook data transmission feature
*   **COMPLIANCE:** Fully GDPR compliant with functional cookies only (no tracking cookies)
*   **MAJOR:** Complete Shortcode System overhaul for all banner types
*   **NEW:** 5 visual styles for shortcodes (Leaderboard, Skyscraper, Rectangle, Compact, Full)
*   **NEW:** Smart Banner Collision Detection - Automatically prevents banner overlap on desktop
*   **NEW:** Inline Banner Smart Positioning logic for content-embedded banners
*   **NEW:** WordPress Editor Live Preview support for all banner types
*   **IMPROVED:** CodeCanyon marketplace compliance (security, performance, privacy standards)
*   **IMPROVED:** Enhanced namespace safety for multi-plugin WordPress environments
*   **IMPROVED:** Visual aesthetics with compact, efficient layouts
*   **IMPROVED:** Performance optimization for inline CSS rendering
*   **FIXED:** Post content injection logic for shortcode banners

= 1.2.0 (January 2025) =
*   **NEW:** Shortcode system for all 3 banner types with style variations
*   **NEW:** 5 shortcode styles - Leaderboard (728x90), Skyscraper, Rectangle (300x250), Compact, Full
*   **NEW:** Shortcodes work independently of global enable setting
*   **NEW:** Alignment options for shortcodes (left/center/right)
*   **NEW:** Compact inline banner design with reduced whitespace
*   **NEW:** Editor preview - See banners in WordPress Block Editor
*   **IMPROVED:** Visual optimization for inline banners - tighter padding, smaller icons
*   **IMPROVED:** Mobile responsiveness for all shortcode styles
*   **IMPROVED:** Documentation with comprehensive shortcode guide

= 1.1.0 (January 2025) =
*   **NEW:** Intelligent Banner Positioning - Automatically prevents banner overlap on desktop
*   **NEW:** Mobile Banner Strategy - Options to Stack, Rotate, or Prioritize banners on mobile
*   **NEW:** "Lead Gradient" & "Glassmorphism" banner styles
*   **NEW:** Excel/CSV export for lead submissions
*   **IMPROVED:** Universal Loader - Now prevents duplicate notifications on complex themes
*   **FIXED:** Custom Banner close button clickability (z-index hardening)
*   **FIXED:** Accessibility focus management (aria-hidden warnings)
*   **FIXED:** Analytics tracking for Custom Banners (fixed 403/400 errors)
*   **FIXED:** CSV Export data handling

= 1.0.8 - Initial Release =
*   Initial public release
*   Three banner types: Follow, Preferred Source, Custom Lead Capture
*   Full customization controls and analytics
*   Security hardened and performance optimized

== Additional Info ==

**Support Resources:**

* Documentation: https://signalkit.wikiofautomation.com/docs
* Support Email: support@signalkit.wikiofautomation.com

**Privacy & GDPR Compliance:**

SignalKit is fully GDPR compliant and respects user privacy:
* Uses only essential functional cookies (no tracking cookies)
* No data transmitted to external services unless you explicitly configure webhooks
* All analytics data stored locally in your WordPress database
* Clear privacy notices displayed when external integrations are configured
* Users can dismiss banners at any time (preference stored in cookies)
* No personal data collected without explicit user submission
* Lead capture data stored securely on your server only
* **Security & Rate Limiting**: To prevent abuse and protect your site, we temporarily process User Agent strings and specific IP addresses. These are used solely for security checks (rate limiting) and are not stored persistently or used for tracking.

**Technical Implementation Notes:**

SignalKit uses several standard WordPress optimization patterns:
* **Critical CSS Inline**: Banners include minimal critical CSS inline in the head for performance (prevents flash of unstyled content). Full styles are loaded via wp_enqueue_style().
* **JSON-LD Schema Markup**: Structured data (schema.org) is output for SEO benefits - this is standard practice for organization/website markup and does not track users.
* **Local Analytics Tracking**: All banner analytics (impressions, clicks, dismissals) are stored in your local WordPress database only. No external analytics services are used.
* **Webhook Consent**: External webhook transmission only occurs when you explicitly opt-in via the webhook consent setting. By default, no data leaves your server.

**License:**

SignalKit is licensed under GPL-2.0+ (GNU General Public License v2 or later).
