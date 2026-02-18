=== CBX Search Tracker ===
Contributors: codeboxr
Tags: search, search analytics, keyword tracker, search tracker, admin dashboard
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cbxsearchtracker

Track WordPress search keywords and display the most searched keywords in a simple admin dashboard.

== Description ==

CBX Search Tracker records every keyword searched on your WordPress site and keeps track of how many times each keyword has been searched.

It provides a clean admin dashboard page where you can view the most searched keywords, along with search count and last searched date.

Perfect for:

* Understanding user intent
* Discovering popular topics
* Identifying content gaps
* Improving SEO strategy
* Building data-driven content

== Features ==

* Automatically tracks all WordPress search queries
* Stores search count per keyword
* Normalizes keywords (lowercase + trimmed)
* Displays top 100 searched keywords
* Shows last searched date
* Delete individual keywords from dashboard
* Secure admin actions (nonce protected)
* Translation ready
* Lightweight and database-efficient

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Search Tracker** in the WordPress admin menu
4. Start analyzing your search data

== Frequently Asked Questions ==

= Does this track admin searches? =

No. Only frontend searches are tracked.

= Does this affect performance? =

No noticeable impact. It performs a single lightweight database query per search.

= Can I delete keywords? =

Yes. Each keyword can be deleted individually from the dashboard.

= Is it translation ready? =

Yes. The plugin uses the text domain `cbxsearchtracker`.

= Does it track zero-result searches? =

Currently it tracks all searches. Zero-result filtering can be added in a future version.

== Screenshots ==

1. Admin dashboard showing most searched keywords
2. Delete keyword action
3. Empty state when no searches recorded

== Changelog ==
= 1.2.0 =
* Added delete all feature

= 1.1.0 =
* Added translation support
* Added delete keyword option
* Added nonce verification for security
* Improved keyword normalization

= 1.0.0 =
* Initial release
* Tracks search keywords
* Admin dashboard listing

== Upgrade Notice ==

= 1.1.0 =
Improved security and added delete functionality.

== Support ==

For support, feature requests, or bug reports:
https://codeboxr.com/

== Privacy ==

This plugin stores search keywords in your WordPress database. It does not send any data to external services.
