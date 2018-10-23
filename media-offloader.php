<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Cloud Media Offloader
 * Plugin URI:        https://github.com/cloudverve/wordpress-cloud-media-offloader-plugin
 * Description:       Serve your WordPress Media Library files from the Backblaze B2 cloud storage service.
 * Version:           0.7.5
 * Author:            CloudVerve, LLC
 * Author URI:        https://www.cloudverve.com
 * License:           GPL-2.0
 * License URI:       https://opensource.org/licenses/GPL-2.0
 * Text Domain:       cloud-media-offloader
 * Domain Path:       languages
 * GitHub Plugin URI: cloudverve/cloud-media-offloader
 */

/*	Copyright 2018	  CloudVerve, LLC (https://www.cloudverve.com/)

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

if( !defined( 'ABSPATH' ) ) die();

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
  require( __DIR__ . '/vendor/autoload.php' );
}

// Initialize plugin
\CloudVerve\MediaOffloader\Plugin::instance();
