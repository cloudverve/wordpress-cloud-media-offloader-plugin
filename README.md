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

## Known Incompatibilities

* [Carbon Fields](https://wordpress.org/plugins/carbon-fields/) - If you have this plugin installed (or use a plugin/theme in which it is embedded), you/they must be running the latest _release_ version.

If you are aware of any other compatibility issues, please [report them](https://github.com/dmhendricks/backblaze-media-offloader/issues).

## Changelog

**0.2.0 (master)**
* Added MIME-type filtering
* Refactored to use latest Core template
* Added dependency checker
* Moved /src to /app
* Added Gulp support

**0.1.0**
* Initial commit

## Screenshot

*Note: The authentication fields contain fake data.*

![Settings Page](https://raw.githubusercontent.com/dmhendricks/backblaze-media-offloader/master/assets/screenshot-1.png "Settings Page")
