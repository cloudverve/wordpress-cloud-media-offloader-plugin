=== Cloud Media Offloader ===
Contributors: hendridm,cloudverve
Tags: cloud,media,uploads,offload,block,storage
Plugin URI: https://github.com/cloudverve/wordpress-cloud-media-offloader-plugin
Donate link: https://paypal.me/danielhendricks
Requires at least: 4.0
Tested up to: 4.9.1
Requires PHP: 5.6.4
Stable tag: 0.7.4
License: GPL-2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Serve WordPress Media Library files from the Backblaze B2 cloud storage service.

== Description ==

This plugin allows you to serve your WordPress Media Library files from the [Backblaze B2](https://www.backblaze.com/b2/) cloud storage service.

It features to ability to limit offloading to specified MIME types.

*This is a third-party plugin and is neither officially endorsed nor supported by Backblaze, Inc.*

===== Requirements =====

* WordPress 4.0 or higher
* PHP 5.6 or higher

== Installation ==
1. Install Cloud Media Offloader either via the WordPress.org plugin repository or manually by uploading the ZIP through the Add New plugin interface.
2. Activate the plugin.
3. Go to Settings > Cloud Media Offloader to configure and add your B2 credentials. You must save your credentials before you can choose a B2 bucket.

== Frequently Asked Questions ==
= Q. Will this plugin upload existing media files to my B2 account? =
A. No, it will only act on newly uploaded media. You can, however, upload them yourself to you B2 account and use a plugin to rewrite the URLs.

== Screenshots ==
1. Settings Page

== Changelog ==
Please see the GitHub [Releases](https://github.com/cloudverve/wordpress-cloud-media-offloader-plugin/releases) page.

== Upgrade Notice ==
The code has been completely refactored, a new B2 API is being used and some deprecation notices were fixed.
