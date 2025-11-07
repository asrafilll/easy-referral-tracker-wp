=== EasyReferralTracker - Privacy-Focused Referral Tracking ===
Contributors: asrafilll
Tags: referral, tracking, analytics, qr-code, app-download, gdpr, privacy, marketing, campaigns, attribution
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Privacy-focused referral tracking for app downloads. Track campaigns without collecting personal data. GDPR compliant with dynamic QR codes.

== Description ==

**EasyReferralTracker** is a privacy-first WordPress plugin designed to track referral codes for app downloads while respecting user privacy. Perfect for mobile apps, SaaS products, and any business that needs attribution tracking without compromising on GDPR compliance.

### 🔒 Privacy-First Design

Unlike other tracking solutions, EasyReferralTracker collects **ZERO personal data**:

* ❌ No IP addresses
* ❌ No user agents
* ❌ No browser fingerprints
* ❌ No personal information
* ✅ 100% GDPR compliant

### ✨ Key Features

**📱 Automatic App Store Integration**
* Modifies iOS App Store links automatically
* Modifies Google Play Store links automatically
* Campaign attribution parameters
* Real-time click tracking

**🎯 Dynamic QR Codes**
* Personalized QR codes for each visitor
* Automatic referral code inclusion
* Customizable styling and colors
* Live preview in admin
* Download as PNG

**📊 Analytics Dashboard**
* Total visits and unique referrals
* Click-through rates
* Top performing campaigns
* Recent activity feed
* Easy-to-read statistics

**🛡️ Enterprise Security**
* SQL injection protection
* XSS prevention
* CSRF protection
* Two-layer rate limiting
* Secure cookie handling

### 🚀 How It Works

1. **Create referral links:** `yoursite.com/?r=campaign123`
2. **Share with users:** Email, social media, ads, etc.
3. **Track automatically:** Plugin modifies app store links
4. **View analytics:** See performance in dashboard
5. **Generate QR codes:** Dynamic codes for each visitor

### 💡 Perfect For

* Mobile app developers
* SaaS companies
* Affiliate marketers
* Marketing agencies
* Anyone needing attribution tracking

### 🌟 Why Choose EasyReferralTracker?

**Privacy Matters:** We believe in tracking that respects users. EasyReferralTracker proves you can have powerful analytics without compromising privacy.

**Performance:** Only ~0.01s overhead. Your site stays fast.

**Simple Setup:** 5-minute installation. No complex configuration.

**Open Source:** Community-driven development. Transparent code.

### 📖 Documentation

* [Installation Guide](https://github.com/asrafilll/easyreferraltracker/wiki/Installation)
* [Configuration Guide](https://github.com/asrafilll/easyreferraltracker/wiki/Configuration)
* [FAQ](https://github.com/asrafilll/easyreferraltracker/wiki/FAQ)

### 🤝 Support

* [GitHub Issues](https://github.com/asrafilll/easyreferraltracker/issues)
* [Community Forum](https://wordpress.org/support/plugin/easyreferraltracker)

== Installation ==

### Automatic Installation

1. Log in to your WordPress admin panel
2. Navigate to **Plugins > Add New**
3. Search for **"EasyReferralTracker"**
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin from WordPress.org
2. Unzip the downloaded file
3. Upload the `easyreferraltracker` folder to `/wp-content/plugins/`
4. Activate the plugin through the **Plugins** menu

### Configuration

1. Go to **EasyReferralTracker > Settings**
2. Enter your iOS App ID and Android Package Name
3. (Optional) Add Apple Provider Token for App Store Connect analytics
4. Save changes
5. Start creating referral links!

== Frequently Asked Questions ==

= Is EasyReferralTracker really GDPR compliant? =

Yes! EasyReferralTracker collects zero personal data. We don't track IP addresses, user agents, or any identifiable information. Only anonymous referral codes, landing pages, and timestamps are stored.

= How do referral links work? =

Add `?r=yourcode` to any URL on your site. Example: `yoursite.com/?r=facebook_jan2025`. The plugin stores this code in a cookie and automatically modifies all App Store links with the referral code.

= Can I use this without a mobile app? =

While designed for app downloads, EasyReferralTracker can track any referral campaigns. It works great for tracking marketing campaign performance.

= Do I need coding knowledge? =

No! Everything is configured through the WordPress admin interface. The QR code shortcode is copy-paste simple: `[easyreferraltracker_qr]`

= How much does it cost? =

EasyReferralTracker is 100% free and open source. No hidden fees, no premium versions, no limitations.

= Will this slow down my website? =

No. EasyReferralTracker adds only ~0.01s overhead, which is imperceptible to users. The plugin is optimized for performance.

= Can I customize the QR codes? =

Yes! You can customize size (100-500px), label text, base URL, colors, padding, and border radius.

= Does this work with caching plugins? =

Yes! EasyReferralTracker is fully compatible with LiteSpeed Cache, Cloudflare, W3 Total Cache, WP Rocket, and other popular caching solutions. QR codes are generated locally and cached as static PNG files for maximum performance.

= How long do referral cookies last? =

Default is 30 days, but you can configure anywhere from 1-365 days in the settings.

= What data is tracked? =

Only 3 things: referral code (e.g., "campaign123"), landing page URL, and timestamp. No personal information.

= Can I export the data? =

Currently, you can view all data in the dashboard. CSV export is planned for a future update.

= Does this work on multisite? =

Yes! Each site in a multisite network gets its own tracking and settings.

= What if a user has an ad blocker? =

EasyReferralTracker still works! We don't use tracking pixels or external analytics services. Everything is self-contained within your WordPress site.

= How do I create dynamic QR codes? =

1. Go to **EasyReferralTracker > QR Generator**
2. Configure your settings
3. Copy the shortcode: `[easyreferraltracker_qr]`
4. Paste anywhere on your site
5. Each visitor gets a personalized QR code!

= Can I use multiple shortcodes? =

Yes! Use as many QR codes as you want. Each can have custom settings: `[easyreferraltracker_qr size="400" label="Download Now"]`

= How does QR code caching work? =

When a visitor with a referral code views a page with a QR code:
1. **First visit:** QR code is generated locally using PHP and saved to `/wp-content/uploads/ert-qr/`
2. **Subsequent visits:** The cached PNG file is served directly (100x faster!)
3. **Cache management:** Clear cache anytime from Settings page
4. **Auto-cleanup:** Old QR codes (90+ days) are automatically deleted weekly

= How much disk space do QR codes use? =

Very little! Each QR code is ~1-3KB. Even with 1,000 unique referral codes, you'll only use ~3MB of disk space.

== Screenshots ==

1. **Dashboard Overview** - View total visits, unique referrals, and click rates at a glance
2. **Top Performing Referrals** - See which campaigns are driving the most traffic
3. **Settings Page** - Simple configuration with GDPR compliance information
4. **QR Code Generator** - Create dynamic QR codes with live preview
5. **Recent Activity** - Monitor referral traffic in real-time
6. **Privacy-Focused** - Clear information about what data is (and isn't) collected

== Changelog ==

= 1.0.0 - 2025-01-01 =
* 🎉 Initial release
* ✨ Privacy-focused tracking (no IP, no user agent)
* 📱 iOS and Android app store integration
* 🎨 Dynamic QR code generator with customizable styling
* 📊 Analytics dashboard with top performers
* 🛡️ Cookie-based rate limiting (two-layer protection)
* 🔒 100% GDPR compliant
* 📖 Comprehensive documentation
* ✅ WordPress 6.4 tested
* ✅ PHP 8.2 compatible

== Upgrade Notice ==

= 1.0.0 =
Initial release. Track referrals without compromising user privacy!

== Additional Information ==

### Privacy Policy

EasyReferralTracker respects your users' privacy:

**What We Collect:**
* Referral codes (e.g., "campaign123")
* Landing page URLs
* Visit timestamps

**What We DON'T Collect:**
* IP addresses
* User agents (browser/device info)
* Personal information
* Tracking pixels
* Third-party cookies

**Cookie Usage:**
* `ert_referral` - Stores the referral code (30 days default)
* `rf_tracked_*` - Prevents duplicate visit counting
* `rf_rate_limit_user` - Rate limiting (1 hour)

All cookies use secure flags: HTTPOnly, Secure (on HTTPS), SameSite.

### Performance

* Page load overhead: ~0.01s
* JavaScript size: ~2KB (inline, no external requests)
* Database growth: ~18MB per year (1,000 visits/month)
* Memory usage: +5MB
* QR code generation: 500ms first time, 5ms cached
* QR cache size: ~1-3KB per unique referral code

### Security

* Prepared statements prevent SQL injection
* All output properly escaped prevents XSS
* WordPress nonces prevent CSRF
* Strict input validation
* Two-layer rate limiting
* QR cache directory protected with .htaccess
* No external dependencies for QR generation

### Compatibility

**Tested With:**
* WordPress 5.0 - 6.4
* PHP 7.2 - 8.2
* MySQL 5.6+
* Popular themes (Astra, GeneratePress, Hello, etc.)
* Page builders (Elementor, Beaver Builder, etc.)
* Caching plugins (LiteSpeed Cache, Cloudflare, W3 Total Cache, WP Rocket, etc.)

### Contributing

EasyReferralTracker is open source! Contributions welcome:

* GitHub: https://github.com/asrafilll/easyreferraltracker
* Report bugs: https://github.com/asrafilll/easyreferraltracker/issues
* Suggest features: https://github.com/asrafilll/easyreferraltracker/discussions

### Support

Need help? We're here:

* Documentation: https://github.com/asrafilll/easyreferraltracker/wiki
* Community Forum: https://wordpress.org/support/plugin/easyreferraltracker
* GitHub Issues: https://github.com/asrafilll/easyreferraltracker/issues

### Credits

Created with ❤️ by the EasyReferralTracker Team

Special thanks to:
* WordPress community
* Early adopters and testers
* Contributors and translators

### Translations

EasyReferralTracker is translation-ready! Current translations:

* English (default)

Want to translate EasyReferralTracker into your language? Join us on [GitHub](https://github.com/asrafilll/easyreferraltracker)!

== Third-Party Services ==

**Version 1.1.0+:** EasyReferralTracker does NOT use any third-party services. QR codes are generated locally on your server using the PHPQRCode library (LGPL 3.0). All data stays on your WordPress site.

**Version 1.0.0 (legacy):** Used Google Charts API for QR code generation. This has been replaced with local generation for better performance and privacy.

== Developer Hooks ==

### Filters

`easyreferraltracker_cookie_duration` - Modify cookie duration
`easyreferraltracker_default_referral` - Change default referral code
`easyreferraltracker_qr_base_url` - Modify QR code base URL
`easyreferraltracker_rate_limit_user` - Adjust per-user rate limit
`easyreferraltracker_rate_limit_global` - Adjust global rate limit

### Actions

`easyreferraltracker_after_visit_tracked` - Fires after visit is tracked
`easyreferraltracker_after_click_tracked` - Fires after click is tracked
`easyreferraltracker_settings_saved` - Fires after settings are saved

Example:
```php
// Modify cookie duration
add_filter('easyreferraltracker_cookie_duration', function($days) {
    return 60; // Change to 60 days
});

// Custom action after visit
add_action('easyreferraltracker_after_visit_tracked', function($referral_code) {
    // Send to external analytics
    track_custom_event('referral_visit', $referral_code);
});
```

Full developer documentation: https://github.com/asrafilll/easyreferraltracker/wiki/Developer-Hooks