<?php
namespace TwoLabNet\BackblazeB2;

class EnqueueScripts extends Plugin {

  public static function load()
  {
    // Enqueue scripts
    add_action( 'wp_loaded', function() {
      self::enqueue_admin_scripts();
    });
  }

  /**
    * Enqueue scripts used in WP admin interface
    */
  private static function enqueue_admin_scripts() {

    // Only load script(s) on edit pages
    if( Helpers::current_admin_page() == 'crbn-backblaze-b2.php' ) {
      add_action( 'admin_enqueue_scripts', function() {
          wp_enqueue_style( 'backblaze-admin', self::get_script_url('assets/js/backblaze.js'), array(), self::get_script_modified_version(self::get_script_path('assets/js/backblaze.js')) );
      });
    }
  }

  /**
    * Returns script ?ver= version based on environment (WP_ENV)
    *
    * If in production mode, returns @param string $script_version (if specified),
    * else, returns $plugin_version, else if WP_ENV is equal to anything else, returns
    * string representing file last modified change (to prevent caching during development).
    *
    * @param string $script The filesystem path (relative to the script location of calling
    *    script) to return the version for.
    * @param string $script_version (optional) The version that will be returned if
    *    WP_ENV == 'production'. If not specified, plugin version will be used.
    *
    * @return string
    */
  public static function get_script_modified_version($script, $script_version = null) {
    $script_version = $script_version ? $script_version : parent::get_option('data')['Version'];
    if(!defined('WP_ENV')) return $script_version;

    try {
      $script_version = self::is_production() ? $script_version : date("ymd-Gis", filemtime( $script ));
    } catch (Exception $e) {
      error_log(parent::get_option('data')['Plugin Name'].': '.$e->getMessage());
    }

    return $script_version;
  }

  /**
    * Returns script path or URL, either regular or minified (if exists).
    *
    * If in production mode or if @param $force_minify == true, inserts '.min' to the filename
    * (if exists), else return script name without (example: style.css vs style.min.css).
    *
    * @param string $script The relative (to the plugin folder) path to the script.
    * @param bool $enable_minify Enables checking for minified version and returning that instead
    * @param bool $return_url If true, returns full-qualified URL rather than filesystem path.
    *
    * @return string The URL or path to minified or regular $script.
    */
  public static function get_script_path($script, $enable_minify = false, $return_url = false) {
    if(!strpos($script, '.')) return $script;
    $script = trim($script, '/');

    // Determine if minimized script is to be used and is present
    $minify = $enable_minify && self::is_production();
    if($minify) {
      $script_parts = explode('.', $script);
      $script_ext   = '.min.' . $script_parts[sizeof($script_parts)-1];
      array_pop($script_parts);
      $new_script   = implode('.', $script_parts).$script_ext;
      if(file_exists(parent::get_option('path') . trim($new_script, '/'))) $script = $new_script;
      $script = $new_script;
    }

    // Return system path or URL depending on requested value
    if($return_url) {
      return parent::get_option('url') . $script;
    } else {
      return parent::get_option('path') . $script;
    }

  }

  public static function get_script_url($script, $enable_minify = false, $force_ssl = false) {
    $_url = self::get_script_path($script, $enable_minify, true);
    if($force_ssl) {
      $_parts = explode('://');
      if(count($_parts) > 1) array_shift($_parts);
      $_url = 'https://'.implode('://', $_parts);
    }
    return self::get_script_path($script, $enable_minify, true);
  }

  public static function is_production() {
    return !defined('WP_ENV') ? true : WP_ENV == 'production';
  }

}
