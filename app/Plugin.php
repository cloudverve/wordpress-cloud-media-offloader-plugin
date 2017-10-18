<?php
namespace TwoLabNet\BackblazeB2;
use WordPress_ToolKit\ObjectCache;
use WordPress_ToolKit\ConfigRegistry;
use WordPress_ToolKit\PluginTools;
use WordPress_ToolKit\Helpers\ArrayHelper;
use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Config;

class Plugin {

  public static $textdomain;
  public static $config;
  public static $client;
  protected static $cache;

  function __construct() {

    // Get plugin properties and meta data
    $plugin_obj = new PluginTools();
    $plugin_data = $plugin_obj->get_current_plugin_data( ARRAY_A );

    self::$config = new ConfigRegistry( $plugin_data['path'] . 'plugin.json' );
    self::$config = self::$config->merge( new ConfigRegistry( [ 'plugin' => $plugin_data ] ) );
    self::$textdomain = self::$config->get( 'plugin/meta/TextDomain' ) ?: self::$config->get( 'plugin/slug' );

    // Define plugin version constant
    if ( !defined( __NAMESPACE__ . '\VERSION' ) ) define( __NAMESPACE__ . '\VERSION', self::$config->get( 'plugin/meta/Version' ) );

    // Do not load on the frontend
    if( !is_admin() ) return;

    // Initialize ObjectCache
    self::$cache = new ObjectCache( self::$config );

    // Verify dependecies and load plugin logic
    register_activation_hook( self::$config->get( 'plugin/identifier' ), array( $this, 'activate' ) );
    add_action( 'plugins_loaded', array( $this, 'init' ) );

  }

  /**
    * Check plugin dependencies on activation.
    *
    * @since 0.2.0
    */
  public function activate() {

    $dependency_check = $this->verify_dependencies( true, array( 'activate' => true, 'echo' => false ) );
    if( $dependency_check !== true ) die( $dependency_check );

  }

  /**
    * Initialize Carbon Fields and load plugin logic
    *
    * @since 0.2.0
    */
  public function init() {

    if( class_exists( 'Carbon_Fields\\Carbon_Fields' ) ) {
      add_action( 'after_setup_theme', array( 'Carbon_Fields\\Carbon_Fields', 'boot' ) );
    }

    if( $this->verify_dependencies( 'carbon_fields' ) === true ) {
      add_action( 'carbon_fields_loaded', array( $this, 'load_plugin' ));
    }

  }

  /**
    * Load plugin classes
    *
    * @since 0.2.0
    */
  public function load_plugin() {

    if( !$this->verify_dependencies( 'carbon_fields' ) ) return;

    // Add admin settings page using Carbon Fields framework
    new Settings\Plugin_Settings();

    // Perform core plugin logic
    new Core();

  }

  /**
    * Function to verify dependencies, such as if an outdated version of Carbon
    *    Fields is detected.
    *
    * Normally, we wouldn't be so persistant about checking for dependencies and
    *    I would just pass it off to TGMPA, however, if they have an ancient version
    *    of Carbon Fields loaded (via plugin or dependency), it causes problems.
    *
    * @param string|array|bool $deps A string (single) or array of deps to check. `true`
    *    checks all defined dependencies.
    * @param array $args An array of arguments.
    * @return bool|string Result of dependency check. Returns bool if $args['echo']
    *    is false, string if true.
    * @since 0.2.0
    */
  private function verify_dependencies( $deps = true, $args = array() ) {

    if( is_bool( $deps ) && $deps ) $deps = self::$config->get( 'dependencies' );
    if( !is_array( $deps ) ) $deps = array( $deps => self::$config->get( 'dependencies/' . $deps ) );

    $args = ArrayHelper::set_default_atts( array(
      'echo' => true,
      'activate' => true
    ), $args);

    $notices = array();

    foreach( $deps as $dep => $version ) {

      switch( $dep ) {

        case 'php':

          if( version_compare( phpversion(), $version, '<' ) ) {
            $notices[] = __( 'This plugin is not supported on versions of PHP below', self::$textdomain ) . ' ' . self::$config->get( 'dependencies/php' ) . '.' ;
          }
          break;

        case 'carbon_fields':

          //if( defined('\\Carbon_Fields\\VERSION') || ( defined('\\Carbon_Fields\\VERSION') && version_compare( \Carbon_Fields\VERSION, $version, '<' ) ) ) {
          if( !$args['activate'] && !defined('\\Carbon_Fields\\VERSION') ) {
            $notices[] = __( 'An unknown error occurred while trying to load the Carbon Fields framework.', self::$textdomain );
          } else if ( defined('\\Carbon_Fields\\VERSION') && version_compare( \Carbon_Fields\VERSION, $version, '<' ) ) {
            $notices[] = __( 'An outdated version of Carbon Fields has been detected:', self::$textdomain ) . ' ' . \Carbon_Fields\VERSION . ' (&gt;= ' . self::$config->get( 'dependencies/carbon_fields' ) . ' ' . __( 'required', self::$textdomain ) . ').' . ' <strong>' . self::$config->get( 'plugin/meta/Name' ) . '</strong> ' . __( 'deactivated', self::$textdomain ) . '.' ;
          }
          break;

        }

    }

    if( $notices ) {

      deactivate_plugins( self::$config->get( 'plugin/identifier' ) );

      $notices = '<ul><li>' . implode( "</li>\n<li>", $notices ) . '</li></ul>';

      if( $args['echo'] ) {
        Helpers::show_notice($notices, 'error', false);
        return false;
      } else {
        return $notices;
      }

    }

    return !$notices;

  }

  /**
    * Get Carbon Fields option, with object caching (if available). Currently
    *   only supports plugin options because meta fields would need to have the
    *   cache flushed appropriately.
    *
    * @param string $key The name of the option key
    * @return mixed The value of specified Carbon Fields option key
    * @link https://carbonfields.net/docs/containers-usage/ Carbon Fields containers
    * @since 0.2.0
    *
    */
  public function get_plugin_option( $key, $cache = true ) {
    $key = self::prefix( $key );

    if( $cache ) {
      // Attempt to get value from cache, else fetch value from database
      return self::$cache->get_object( $key, function() use ( &$key ) {
        return carbon_get_theme_option( $key );
      });
    } else {
      // Return uncached value
      return carbon_get_theme_option( $key );
    }

  }

  /**
    * A wrapper for the plugin's data fiala prefix as defined in $config
    *
    * @param string|null $field_name The string/field to prefix
    * @param string $start Optional string to prefix field with
    * @return string Prefixed string/field value
    * @since 0.2.0
    */
  public function prefix( $field_name = null, $start = '' ) {
    return $field_name !== null ? $start . self::$config->get( 'prefix' ) . $field_name : self::$config->get( 'prefix' );
  }

  /**
    * Returns true if WP_ENV is anything other than 'development' or 'staging'.
    *   Useful for determining whether or not to enqueue a minified or non-
    *   minified script (which can be useful for debugging via browser).
    *
    * @return bool
    * @since 0.1.0
    */
  public function is_production() {
    $env = @constant( self::$config->get( 'environment_constant' ) ) ?: 'production';
    return ( !in_array( $env, array('development', 'staging') ) );
  }

  /**
    * Returns true if request is via Ajax.
    *
    * @return bool
    * @since 0.1.0
    */
  public function is_ajax() {
    return defined('DOING_AJAX') && DOING_AJAX;
  }

  /**
    * Returns WordPress root directory.
    *
    * @return string Path to WordPress root directory
    * @since 0.7.0
    */
  public function get_wordpress_root( $filename = '' ) {
    return trailingslashit( implode( DIRECTORY_SEPARATOR, array_slice( explode( DIRECTORY_SEPARATOR, self::$config->get( 'plugin/path' ) ), 0, -4 ) ) ) . $filename;
  }


}
