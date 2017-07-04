<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Backblaze B2 Media Offloader
 * Plugin URI:        https://github.com/dmhendricks/backblaze-media-offloader
 * Description:       A simple plugin that allows you to serve your WordPress Media Library files via the Backblaze B2 cloud storage service.
 * Version:           0.1.3
 * Author:            2Lab Internet Services
 * Author URI:        https://www.danhendricks.com
 * Text Domain:       b2mo
 * Domain Path:       /languages
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
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
if(!defined('ABSPATH')) exit;

require( __DIR__ . '/vendor/autoload.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Initialize plugin
\TwoLabNet\BackblazeB2\Plugin::init(array(
	'slug' => substr(plugin_basename(__FILE__), 0, strpos(plugin_basename(__FILE__), '/')),
	'data' => get_plugin_data(__FILE__),
	'path' => realpath(plugin_dir_path(__FILE__)).DIRECTORY_SEPARATOR,
	'url' => plugin_dir_url(__FILE__),
	'prefix' => 'b2mo_',
	'b2' => ['apiUrl' => 'https://api.backblazeb2.com/b2api/v1/']
));
?>
