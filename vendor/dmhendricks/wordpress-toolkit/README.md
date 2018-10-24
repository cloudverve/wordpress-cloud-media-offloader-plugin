[![Author](https://img.shields.io/badge/author-Daniel%20M.%20Hendricks-blue.svg?colorB=9900cc&style=flat-square)](https://www.danhendricks.com/?utm_source=github.com&utm_medium=campaign&utm_content=button&utm_campaign=dmhendricks%2Fwordpress-toolkit)
[![Latest Release](https://img.shields.io/github/release/dmhendricks/wordpress-toolkit.svg?style=flat-square)](https://github.com/dmhendricks/wordpress-toolkit/releases)
[![GitHub License](https://img.shields.io/badge/license-GPLv2-yellow.svg?style=flat-square)](https://raw.githubusercontent.com/dmhendricks/wordpress-toolkit/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/dmhendricks/wordpress-toolkit.svg?style=flat-square)](https://packagist.org/packages/dmhendricks/wordpress-toolkit)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg?style=flat-square)](https://paypal.me/danielhendricks)
[![Flywheel](https://img.shields.io/badge/style-Flywheel-green.svg?style=flat-square&label=get%20hosted&colorB=AE2A21)](https://share.getf.ly/e25g6k?utm_source=github.com&utm_medium=campaign&utm_content=button&utm_campaign=dmhendricks%2Fwordpress-toolkit)
[![Twitter](https://img.shields.io/twitter/url/https/github.com/dmhendricks/wordpress-toolkit.svg?style=social)](https://twitter.com/danielhendricks)

# WordPress Tool Kit

A collection of classes that I use in my WordPress projects & plugins.

### Contributing

If you can make the code better or recommend/contribute code that would be useful to include, [please let me know](https://github.com/dmhendricks/wordpress-toolkit/issues).

## Features

* [ConfigRegistry](https://github.com/dmhendricks/wordpress-toolkit/wiki/ConfigRegistry) class - Loads plugin/theme settings from an array or JSON file.
* [Licensing](https://github.com/dmhendricks/wordpress-toolkit/wiki/Licensing) class - Currently only support license code validation via the [Software Licensing](https://www.whmcs.com/software-licensing/?utm_source=github.com&utm_medium=referral&utm_content=link&utm_campaign=dmhendricks%2Fwordpress-toolkit) addon for WHMCS.
* [ObjectCache](https://github.com/dmhendricks/wordpress-toolkit/wiki/ObjectCache) class - A wrapper for setting/fetching values from the WordPress object cache, where available.
* [PluginTools](https://github.com/dmhendricks/wordpress-toolkit/wiki/PluginTools) class - A class for retrieving data and performing various tasks on plugins.
* [ScriptObject](https://github.com/dmhendricks/wordpress-toolkit/wiki/ScriptObject) class - Inject JavaScript variables or CSS into the page head or write/enqueue external files.

## Installation

### Requirements

* WordPress 4.7 or higher
* PHP 7.0 or higher

Compatibility tested with WordPress 5.0, multisite and PHP 7.3.

### Install with Composer

```bash
composer require dmhendricks/wordpress-toolkit
```

## Usage

Please see the [Documentation](https://github.com/dmhendricks/wordpress-toolkit/wiki) page.
