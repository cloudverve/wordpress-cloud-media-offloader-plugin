<?php
namespace WordPress_ToolKit\Helpers;
use WordPress_ToolKit\ToolKit;

/**
  * A class with various static functions to perform filesystem tasks
  *
  * @since 0.1.0
  */
class FileHelper extends ToolKit
{

  /**
    * Determines if this class is being run within a plugin or a theme file based
    *    on current path.
    *
    * @param bool|string|null $file Filename (string) to append to config base path,
    *    'true' to return base path + 'plugin.json' or 'theme.json' appended, false or
    *    null to return only the base path.
    * @param bool $return_null Return null if $file does not exist.
    * @return string Base bath to plugins or theme directors (plus $file, if specified)
    * @since 0.1.0
    */
  public static function get_config_base_path( $file = '', $return_null = false ) {

    if( strstr( __DIR__, WP_PLUGIN_DIR ) ) {

      // If plugin, look for plugin.json in plugin root
      $file = $file === true ? 'plugin.json' : $file;
      $plugin_dir = str_replace( WP_PLUGIN_DIR, '', plugin_dir_path( __FILE__ ) );
      $plugin_dir = trailingslashit( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . self::get_current_plugin_slug() );
      if( !$file ) return $plugin_dir;

      $config_file = $file ? $plugin_dir . trim( $file, '/' ) : $plugin_dir;
      return $return_null || file_exists( $config_file ) ? $config_file : null;

    } else {

      // If not plugin or file not found, try theme base
      $file = $file === true ? 'theme.json' : $file;
      $theme_dir = trailingslashit( get_template_directory() );
      if( !$file ) return $theme_dir;

      $config_file = $file ? $theme_dir . $file : $theme_dir;
      return $return_null || file_exists( $config_file ) ? $config_file : null;
    }

    return null;

  }

  /**
    * Combine function attributes with known attributes and fill in defaults when needed.
    *
    * @param array  $pairs     Entire list of supported attributes and their defaults.
    * @param array  $atts      User defined attributes in shortcode tag.
    * @return array Combined and filtered attribute list.
    * @since 0.1.0
    */
  public static function get_current_plugin_slug() {

    // Check if we're running inside of a plugin
    if( strstr( __DIR__, WP_PLUGIN_DIR ) ) {
      return current( explode( DIRECTORY_SEPARATOR, plugin_basename( __FILE__ ) ) );
    } else {
      return null;
    }

  }

}
