# Backblaze B2 Media Offloader for WordPress

**Warning:** This is currently **under development and does not work** yet. Test in a development environment first and use at your own risk.

## Description

This is a WordPress plugin that allows you to serve your WordPress Media Library files via the [Backblaze B2](https://www.backblaze.com/b2/cloud-storage.html#af9kre) cloud storage service.

From their web site:

> *The lowest cost cloud storage on the planet: $0.005/GB a month. Try it and get the first 10 GB free on us.*

## Requirements

* PHP 5.5 or greater

## Installation

1. Download and extract the ZIP archive from [GitHub](https://github.com/dmhendricks/backblaze-media-offloader)
2. Upload to wp-content/plugins/ folder
3. Activate through wp-admin/plugin.php page.

## Known Compatibilities & Conflicts

### Compatible Plugins

* [SVG Support](https://wordpress.org/plugins/svg-support/)

### Conflicts

* [Carbon Fields](https://wordpress.org/plugins/carbon-fields/) - If you have this plugin installed (or use a plugin/theme in which it is embedded), you/they must be running the latest _release_ version.

If you are aware of any others, please [share](https://github.com/dmhendricks/backblaze-media-offloader/issues).

## Goals

### TODO

* Currently, the plugin uploads the files to B2. However:
	* It does not rewrite URLs
	* When you delete a file from Media Library, it is not deleted from B2.
 * Extensive documentation & code cleanup
 * Test on ancient versions of WordPress to determine compatibility
 * Language translations

### Long-term

* Hooks
* Support for file upload fields in frameworks like [ACF](https://www.advancedcustomfields.com/) and [CMB2](https://wordpress.org/plugins/cmb2/), [Pods](https://wordpress.org/plugins/pods/), [Xbox Framework](https://codecanyon.net/item/xbox-framework-create-meta-boxes-theme-options-admin-pages-for-wordpress/19250995), etc...
* Ability to selectively password protect files and/or the possibility of link expiration (WooCommerce integration?).

## Changelog

**0.2.0 (master)**
* Added some translatable strings
* Added MIME-type filtering
* Refactored to use latest Core template
* Added dependency checker
* Moved /src to /app
* Added Gulp love

**0.1.0**
* Initial commit

## Screenshot

![Settings Page](https://raw.githubusercontent.com/dmhendricks/backblaze-media-offloader/master/assets/screenshot-1.png "Settings Page")
