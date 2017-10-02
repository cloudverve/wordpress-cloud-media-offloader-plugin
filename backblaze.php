<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Backblaze B2 Media Offloader
 * Plugin URI:        https://github.com/dmhendricks/backblaze-media-offloader
 * Description:       A simple plugin that allows you to serve your WordPress Media Library files via the Backblaze B2 cloud storage service.
 * Version:           0.7.0
 * Author:            Daniel M. Hendricks
 * Author URI:        https://www.danhendricks.com
 * License:           GPL-2.0
 * License URI:       https://opensource.org/licenses/GPL-2.0
 * Text Domain: 			backblaze-media-offloader
 * Domain Path: 			languages
 * GitHub Plugin URI: dmhendricks/backblaze-media-offloader
 */

/*	Copyright 2017	  Daniel M. Hendricks (https://www.danhendricks.com/)

		This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if(!defined('ABSPATH')) die();

require( __DIR__ . '/vendor/autoload.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Initialize plugin
new \TwoLabNet\BackblazeB2\Plugin();
