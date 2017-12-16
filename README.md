[![Author](https://img.shields.io/badge/author-Daniel%20M.%20Hendricks-blue.svg?colorB=9900cc )](https://www.danhendricks.com)
[![Latest Version](https://img.shields.io/github/release/cloudverve/cloud-media-offloader.svg)](https://github.com/cloudverve/cloud-media-offloader/releases)
[![Packagist](https://img.shields.io/packagist/v/cloudverve/cloud-media-offloader.svg)](https://packagist.org/packages/cloudverve/cloud-media-offloader)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://paypal.me/danielhendricks)
[![WP Engine](https://img.shields.io/badge/WP%20Engine-Compatible-orange.svg)](http://bit.ly/WPEnginePlans)
[![GitHub License](https://img.shields.io/badge/license-GPLv2-yellow.svg)](https://raw.githubusercontent.com/cloudverve/cloud-media-offloader/master/LICENSE)
[![Twitter](https://img.shields.io/twitter/url/https/github.com/cloudverve/cloud-media-offloader.svg?style=social)](https://twitter.com/danielhendricks)

# Cloud Media Offloader Plugin for WordPress

This is currently under development and may contain bugs. Test in a development environment first and use at your own risk.

### Download Plugin

Installable WordPress ZIP file: [cloud-media-offloader.zip](https://github.com/cloudverve/cloud-media-offloader/releases/download/0.7.3/cloud-media-offloader.zip).

## Contents

- [Description](#description)
- [Installation](#installation)
   - [Requirements](#requirements)
   - [Automatic Updates](#automatic-updates)
- [Known Compatibilities & Conflicts](#known-compatibilities--conflicts)
- [Goals](#goals)
- [Change Log](#change-log)
- [Credits](#credits)
- [Screenshots](#screenshots)

## Description

This is a WordPress plugin that allows you to serve your WordPress Media Library files via the [Backblaze B2](https://www.backblaze.com/b2/cloud-storage.html#af9kre) cloud storage service.

### Contributing

One of the best ways that you can contribute is to help me make it better, either with code or with constructive feedback. Ways to help:

* I am open to pull requests and welcome improvements.
* [Feedback](https://github.com/cloudverve/cloud-media-offloader/issues) on how I can make it better.
* **Testing!** If you try the plugin - please use the [installable ZIP](#installation), let me know what works and what doesn't. If you have an [issue](https://github.com/cloudverve/cloud-media-offloader/issues), it is helpful if you can describe (as much as you are aware of) your environment - install method, WordPress version, PHP version, operating system, and web server (Apache, Nginx, etc).
* Translations (or [donations](https://paypal.me/danielhendricks) to create/maintain them)

## Installation

Download the [installable WordPress ZIP file](https://github.com/cloudverve/cloud-media-offloader/releases/download/0.7.3/cloud-media-offloader.zip) and add via **Plugins** > **Add New** in WP Admin.

### Requirements

- WordPress 4.0 or higher
- PHP 5.6 or higher

### Composer

If you manage plugins with Composer, this plugin is on [Packagist](https://packagist.org/packages/cloudverve/cloud-media-offloader).

```
composer require cloudverve/wordpress-cloud-media-offloader-plugin
```

## Known Compatibilities & Conflicts

### Known Compatibilities

* [Smush Image Compression and Optimization](https://wordpress.org/plugins/wp-smushit/)
* [Safe SVG](https://wordpress.org/plugins/safe-svg/)
* [SVG Support](https://wordpress.org/plugins/svg-support/)
* [WordPress SVG Plugin](https://github.com/Lewiscowles1986/WordPressSVGPlugin)

### Conflicts

* [Enable Media Replace](https://wordpress.org/plugins/enable-media-replace/) - This generally works, but occasionally and seemingly randomly throws an exception when uploading a replacement file. The file still uploads fine, however, there is a bug where an exception is sometimes thrown.

If you encounter any conflicts, please [report them](https://github.com/cloudverve/cloud-media-offloader/issues).

## Goals

#### Immediate

* Significant testing in various environments
* Improve documentation

#### Medium-Term

* Add hook on media upload
* Add ability to store files in private buckets
* Add support for other object storage services

#### Long-Term

* Add ability to password-protect content, time-expiring URLs and relevant tracking/statistics
* Add one-click feature to migrate existing local media to B2
* Look at options for supporting some popular WooCommerce digital media plugins.
* Possibility of serving external CSS/JS from B2 bucket
* Possibility to browse B2 files from WP Admin

## Change Log

Release changes will be noted on the [Releases](https://github.com/cloudverve/cloud-media-offloader/releases) page.

#### Branch: `master`

* Changed author label, renamed PHP namespaces, moved links to `dmhendricks/cloud-media-offloader`
* Removed GitHub Updater support in order to remove dependencies from repo

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

## Screenshots

![Settings Page](https://raw.githubusercontent.com/cloudverve/cloud-media-offloader/master/assets/screenshot-1.png "Settings Page")

![Media Library Image Properties](https://raw.githubusercontent.com/cloudverve/cloud-media-offloader/master/assets/screenshot-2.png "Media Library Image Properties")
