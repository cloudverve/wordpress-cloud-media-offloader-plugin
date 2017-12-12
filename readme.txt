=== Backblaze B2 Media Offloader ===
Contributors: hendridm
Tags: backblaze,b2,media,uploads,offload,images
Plugin URI: https://github.com/dmhendricks/backblaze-media-offloader
Donate link: https://paypal.me/danielhendricks
Requires at least: 4.0
Tested up to: 4.9.1
Requires PHP: 5.6
Stable tag: 0.7.2
License: GPL-2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to serve WordPress Media Library files from the Backblaze B2 cloud storage service.

== Description ==

This plugin allows you to serve your WordPress Media Library files via the [Backblaze B2](https://www.backblaze.com/b2/) cloud storage service.

It features to ability to limit offloading to specified MIME types.

===== Requirements =====

* WordPress 4.0 or higher
* PHP 5.6 or higher

== Installation ==
1. Install Backblaze B2 Media Offloader either via the WordPress.org plugin repository or manually by uploading the ZIP through the Add New plugin interface.
2. Activate the plugin.
3. Go to Settings > Backblaze B2 to configure and add your B2 credentials. You must save your credentials before you can choose a Backblaze B2 bucket.

== Frequently Asked Questions ==
= Q. Will this plugin upload existing media files to my B2 account? =
A. No, it will only act on newly uploaded media. You can, however, upload them yourself to you B2 account and use a plugin to rewrite the URLs.

== Screenshots ==
1. Settings Page

== Changelog ==
Please see the GitHub [Releases](https://github.com/dmhendricks/backblaze-media-offloader/releases) page.

== Upgrade Notice ==
The code has been completely refactored, a new B2 API is being used and some deprecation notices were fixed.
