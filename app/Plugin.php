<?php
namespace CloudVerve\MediaOffloader;
use CloudVerve\MediaOffloader\Services\B2;
use WordPress_ToolKit\ObjectCache;
use WordPress_ToolKit\ConfigRegistry;
use WordPress_ToolKit\Helpers\ArrayHelper;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Plugin extends \WordPress_ToolKit\ToolKit {

  private static $instance;
  public static $textdomain;
  public static $config;
  public static $client;

  public static function instance() {

    if ( !isset( self::$instance ) && !( self::$instance instanceof Plugin ) ) {

      self::$instance = new Plugin;

      // Load plugin configuration
      self::$config = self::$instance->init( dirname( __DIR__ ), trailingslashit( dirname( __DIR__ ) ) . 'plugin.json' );
      self::$config->merge( new ConfigRegistry( [ 'plugin' => self::$instance->get_current_plugin_meta( ARRAY_A ) ] ) );

      // Set Text Domain
      self::$textdomain = self::$config->get( 'plugin/meta/TextDomain' ) ?: self::$config->get( 'plugin/slug' );

      // Define plugin version
      if ( !defined( __NAMESPACE__ . '\VERSION' ) ) define( __NAMESPACE__ . '\VERSION', self::$config->get( 'plugin/meta/Version' ) );

      // Load dependecies and plugin logic
      register_activation_hook( self::$config->get( 'plugin/identifier' ), array( self::$instance, 'activate' ) );
      add_action( 'plugins_loaded', array( self::$instance, 'load_dependencies' ) );

    }

    return self::$instance;

  }

  /**
    * Load plugin classes - Modify as needed, remove features that you don't need.
    *
    * @since 0.2.0
    */
  public function load_plugin() {

    if( !$this->verify_dependencies() ) {
      deactivate_plugins( self::$config->get( 'plugin/identifier' ) );
      return;
    }

    // Add admin settings page using Carbon Fields framework
    new Settings\Settings_Page();

    // Load shortcodes
    new Shortcodes\Shortcode_Loader();

    // Perform core plugin logic
    new Core();

  }

  /**
    * Check plugin dependencies on activation.
    *
    * @since 0.2.0
    */
  public function activate() {

    $this->verify_dependencies( true, true );

  }

  /**
    * Initialize Carbon Fields and load plugin logic
    *
    * @since 0.2.0
    */
  public function load_dependencies() {

    if( class_exists( 'Carbon_Fields\\Carbon_Fields' ) ) {
      add_action( 'after_setup_theme', array( 'Carbon_Fields\\Carbon_Fields', 'boot' ) );
    }

    add_action( 'carbon_fields_fields_registered', array( $this, 'load_plugin' ));

  }

  /**
    * Function to verify dependencies, such as if an outdated version of Carbon
    *    Fields is detected.
    *
    * @param bool $die If true, plugin execution is halted with die(), useful for
    *    outputting error(s) in during activate()
    * @return bool
    * @since 0.2.0
    */
  private function verify_dependencies( $die = false, $activate = false ) {

    // Check if underDEV_Requirements class is loaded
    if( !class_exists( 'underDEV_Requirements' ) ) {
      if( $die ) {
        die( sprintf( __( '<strong>%s</strong>: One or more dependencies failed to load', self::$textdomain ), __( self::$config->get( 'plugin/meta/Name' ) ) ) );
      } else {
        return false;
      }
    }

    $requirements = new \underDEV_Requirements( __( self::$config->get( 'plugin/meta/Name' ), self::$textdomain ), self::$config->get( 'dependencies' ) );

    // Check for WordPress Toolkit
    $requirements->add_check( 'wordpress-toolkit', function( $val, $res ) {
      $wordpress_toolkit_version = defined( '\WordPress_ToolKit\VERSION' ) ? \WordPress_ToolKit\VERSION : null;
      if( !$wordpress_toolkit_version ) {
        $res->add_error( __( 'WordPress ToolKit not loaded.', self::$textdomain ) );
      } else if( version_compare( $wordpress_toolkit_version, self::$config->get( 'dependencies/wordpress-toolkit' ), '<' ) ) {
        $res->add_error( sprintf( __( 'An outdated version of WordPress ToolKit has been detected: %s (&gt;= %s required).', self::$textdomain ), $wordpress_toolkit_version, self::$config->get( 'dependencies/wordpress-toolkit' ) ) );
      }
    });

    // Check for Carbon Fields
    $requirements->add_check( 'carbon_fields', function( $val, $res ) use ( &$activate ) {
      if( $activate ) return;
      $cf_version = defined('\\Carbon_Fields\\VERSION') ? current( explode( '-', \Carbon_Fields\VERSION ) ) : null;
      if( !$cf_version ) {
        $res->add_error( sprintf( __( 'The <a href="%s" target="_blank">Carbon Fields</a> framework is not loaded.', self::$textdomain ), 'https://carbonfields.net/release-archive/' ) );
      } else if( version_compare( $cf_version, self::$config->get( 'dependencies/carbon_fields' ), '<' ) ) {
        $res->add_error( sprintf( __( 'An outdated version of Carbon Fields has been detected: %s (&gt;= %s required).', self::$textdomain ), $cf_version, self::$config->get( 'dependencies/carbon_fields' ) ) );
      }
    });

    // Display errors if requirements not met
    if( !$requirements->satisfied() ) {
      if( $die ) {
        die( $requirements->notice() );
      } else {
        add_action( 'admin_notices', array( $requirements, 'notice' ) );
        return false;
      }
    }

    return true;

  }

  /**
    * Get Carbon Fields option, with object caching (if available). Currently
    *   only supports plugin options because meta fields would need to have the
    *   cache flushed appropriately.
    *
    * @param string $key The name of the option key
    * @param bool $cache Whether or not to attempt to get cached value
    * @return mixed The value of specified Carbon Fields option key
    * @link https://carbonfields.net/docs/containers-usage/ Carbon Fields containers
    * @since 0.2.0
    *
    */
  public static function get_carbon_plugin_option( $key, $cache = true ) {

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
    * Get Carbon Fields network container option (if multisite enabled)
    *
    * @param string $key The name of the option key
    * @param string $container The name of the Carbon Fields network container
    * @param bool $cache Whether or not to attempt to get cached value
    * @param int $site_id The network site ID to use - default: SITE_ID_CURRENT_SITE
    * @return mixed The value of specified Carbon Fields option key
    * @link https://carbonfields.net/docs/containers-usage/ Carbon Fields containers
    * @since 0.5.0
    *
    */
  public static function get_carbon_network_option( $key, $cache = true, $site_id = null ) {

    if( !$site_id ) {
      if( !defined( 'SITE_ID_CURRENT_SITE' ) ) return null;
      $site_id = SITE_ID_CURRENT_SITE;
    }

    $key = self::prefix( $key );

    if( $cache ) {
      // Attempt to get value from cache, else fetch value from database
      return self::$cache->get_object( $key, function() use ( &$site_id, &$key ) {
        return carbon_get_network_option( $site_id, $key );
      }, null, [ 'network_global' => true ] );
    } else {
      // Return uncached value
      return carbon_get_network_option( $site_id, $key );
    }

  }

  /**
    * Append a field prefix as defined in $config
    *
    * @param string|null $field_name The string/field to prefix
    * @param string $before String to add before the prefix
    * @param string $after String to add after the prefix
    * @return string Prefixed string/field value
    * @since 0.1.0
    */
  public static function prefix( $field_name = null, $before = '', $after = '_' ) {

    $prefix = $before . self::$config->get( 'prefix' ) . $after;
    return $field_name !== null ? $prefix . $field_name : $prefix;

  }

  /**
    * Check if provided B2 credentials are valid. Store valid result in database,
    *    (cached, where availavle) so we don't hammer the B2 API. This value is
    *    reset every time settings are saved.
    * @since 0.7.0
    */
  public function check_api_credentials( $show_notice = false ) {

    $credentials_check = get_transient( $this->prefix( 'credentials_check', '_' ) );

    if( $credentials_check ) {
      return true;
    } else {
      $credentials_check = B2::auth();
    }
    set_transient( $this->prefix( 'credentials_check', '_' ), !is_null( $credentials_check ), HOUR_IN_SECONDS );

    $settings_page = get_admin_url( null, 'options-general.php?page=crb_carbon_fields_container_media_offloader.php#!general' );
    $settings_notice = __( 'Please check your {|access credentials|}.', self::$textdomain );
    $settings_parts = preg_split('/[{}]/', $settings_notice, null, PREG_SPLIT_NO_EMPTY);

    if( count( $settings_parts ) > 1 ) {

      $settings_notice = '';
      foreach( $settings_parts as $part ) {
        $settings_notice .= strstr( $part, '|' ) ? '<a href="' . $settings_page . '">' . trim( $part, '|' ) . '</a>' : $part;
      }

    }

    if( $credentials_check ) {
      return true;
    } else {
      if( $show_notice ) Helpers::show_notice( '<strong>' . self::$config->get('plugin/meta/Name') . '</strong>: ' . __( 'Unable to connect to the Backblaze B2 API.', self::$textdomain ) . ' ' . $settings_notice, 'error', false );
      return false;
    }

  }

}
