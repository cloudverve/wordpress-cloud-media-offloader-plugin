[![Author](https://img.shields.io/badge/author-Daniel%20M.%20Hendricks-blue.svg?colorB=9900cc )](https://www.danhendricks.com)
[![Latest Version](https://img.shields.io/github/release/dmhendricks/backblaze-media-offloader.svg)](https://github.com/dmhendricks/backblaze-media-offloader/releases)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://paypal.me/danielhendricks)
[![WP Engine](https://img.shields.io/badge/WP%20Engine-Compatible-orange.svg)](http://bit.ly/WPEnginePlans)
[![GitHub License](https://img.shields.io/badge/license-GPLv2-yellow.svg)](https://raw.githubusercontent.com/dmhendricks/backblaze-media-offloader/master/LICENSE)
[![Twitter](https://img.shields.io/twitter/url/https/github.com/dmhendricks/backblaze-media-offloader.svg?style=social)](https://twitter.com/danielhendricks)

# Backblaze B2 Media Offloader Plugin for WordPress

This is currently **under development and only supports basic functions**. Test in a development environment first and use at your own risk.

**Note:** If you want to try this plugin, use the [installable WordPress ZIP file](#installation). If you download the repo ZIP file, it will fail to activate unless you run `composer install` first.

## Description

This is a WordPress plugin that allows you to serve your WordPress Media Library files via the [Backblaze B2](https://www.backblaze.com/b2/cloud-storage.html#af9kre) cloud storage service.

From their web site:

> *The lowest cost cloud storage on the planet: $0.005/GB a month. Try it and get the first 10 GB free on us.*

### Contributing

I am open to pull requests as well as other feedback. One of the best ways that you can contribute is to help me make it better, either with code or with constructive feedback. Other ways:

* I am open to pull requests and welcome improvements.
* [Feedback](https://github.com/dmhendricks/backblaze-media-offloader/issues) on how I can make it better.
* **Testing!** If you try the plugin - please use the [installable ZIP](#installation), let me know what works and what doesn't. If you have an [issue](https://github.com/dmhendricks/backblaze-media-offloader/issues), it is helpful if you can describe (as much as you are aware of) your environment - install method, WordPress version, PHP version, operating system, and web server (Apache, Nginx, etc).
* Translations (or [donations](https://paypal.me/danielhendricks) to create/maintain them)
* Sponsor a personal account on WP Engine (for testing plugins) or provide me with SFTP access to a transferrable install.

## Requirements

* WordPress 4.0 or higher
* PHP 5.6 or higher

### Installation

Until sufficient testing and a proper translation file has been completed, you may [download an installable ZIP](https://f001.backblazeb2.com/file/hendricks/projects/github/dmhendricks/backblaze-media-offloader/releases/backblaze-media-offloader.zip) of this plugin.

### Automatic Updates

Once I produce a release version, automatic updates will be available via WordPress. For now, you will have to update manually.

I have disabled GitHub Updater support because I don't want to store the `/vendor` folder in the repo (plus other technical reasons). Rest assured, when this plugin reaches a reasonable level of stability, automatic updates will be supported.

## Known Compatibilities & Conflicts

### Known Compatibilities

* [Safe SVG](https://wordpress.org/plugins/safe-svg/)
* [SVG Support](https://wordpress.org/plugins/svg-support/)

### Conflicts

* [Carbon Fields](https://wordpress.org/plugins/carbon-fields/) - This plugin uses the latest _release_ version of the Carbon Fields framework (currently 2.1.0). As such, it is **_not_** compatible with **legacy** versions of Carbon Fields. If it detects that a version <=1.6.0 is loaded, it will deactivate. (Note: If you wish to use Carbon Fields as a _plugin_, consider using [Carbon Fields Loader](https://github.com/dmhendricks/carbon-fields-loader) instead of the version on the wordpress.org repo)

If you encounter any other in/compatibilities, please [report them](https://github.com/dmhendricks/backblaze-media-offloader/issues).

## Goals

#### Immediate

* Fix compatibility issues when "Remove Files From Server" is enabled, including image preview
* Testing in various environments

#### Medium-Term

* Add ability to store files in private buckets
* Add ability to password-protect content, time-expiring URLs and relevant tracking/statistics
* Add various shortcodes & hooks

#### Long-Term

* Add one-click feature to migrate existing local media to B2
* Look at options for supporting some popular WooCommerce digital media plugins.
* Possibility of serving external CSS/JS from B2 bucket
* Add support for [Enable Media Replace](https://wordpress.org/plugins/enable-media-replace/)
* Possibility to browse B2 files from WP Admin

## Change Log

Release changes will be noted on the [Releases](https://github.com/dmhendricks/backblaze-media-offloader/releases) page.

#### Branch: `master`

* Completely refactored code
* Switched from direct API calls to a fork of [Backblaze B2 SDK for PHP](https://github.com/cwhite92/b2-sdk-php/)
* Added option to delete local media after being uploaded to B2.
* Added initial POT translation file
* Added setting to register custom MIME types
* Fixed unhandled exception triggered by invalid API credentials
* Fixed image dimensions bug when inserting images into a post

## Credits

Please support [humans.txt](http://humanstxt.org/). It's an initiative for knowing the people behind the web. It's an unobtrusive text file that contains information about the different people who have contributed to building the web site/project.

**Carbon Fields**

	URL: http://carbonfields.net/
	Author: htmlBurger.com
	Twitter: @htmlburger
	Author URI: https://htmlburger.com/
	Location: London, England

**Backblaze B2 PHP SDK**

	URL: https://github.com/cwhite92/b2-sdk-php/
	Author: Chris White
	Twitter: @cwhite_92
	Author URI: https://cwhite.me/
	Location: Edinburgh, United Kingdom

## Screenshot

![Settings Page](https://raw.githubusercontent.com/dmhendricks/backblaze-media-offloader/master/assets/screenshot-1.png "Settings Page")
