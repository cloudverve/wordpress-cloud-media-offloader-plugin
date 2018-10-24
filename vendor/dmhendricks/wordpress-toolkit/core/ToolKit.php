<?php
namespace WordPress_ToolKit;

/**
 * ToolKit base class
 *
 * Loads configuration and sets constants
 */
class ToolKit {

  protected static $cache;
  protected static $config;
  protected static $admin_dir;
  protected static $salt;

  protected function init( $base_dir = null, $args = null ) {

    self::$admin_dir = ABSPATH . ( defined( 'WP_ADMIN_DIR' ) ? WP_ADMIN_DIR : 'wp-admin' );
    include_once( self::$admin_dir . '/includes/plugin.php' );

    // Define cookies
    $http_host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : parse_url( site_url(), PHP_URL_HOST );
    $site_slug = strtolower( sanitize_title( $http_host ) );
    if( !defined( 'SECURE_AUTH_COOKIE' ) ) define( 'SECURE_AUTH_COOKIE', $site_slug . '_sec_' . md5( SECURE_AUTH_SALT ) );
    if( !defined( 'AUTH_COOKIE' ) ) define( 'AUTH_COOKIE', $site_slug . '_' . md5( AUTH_SALT ) );
    if( !defined( 'LOGGED_IN_COOKIE' ) ) define( 'LOGGED_IN_COOKIE', $site_slug . '_logged_in_' . md5( LOGGED_IN_SALT ) );

    // Load ToolKit defaults
    $config = new ConfigRegistry( trailingslashit( dirname( __DIR__ ) ) . 'config.json' );
    $wp_upload_dir = wp_upload_dir();
    if( $base_dir ) $config->merge( new ConfigRegistry(
      [
        'base_dir' => trailingslashit( $base_dir ),
        'wordpress' => [
          'version' => get_bloginfo('version'),
          'root_dir' => $this->get_wordpress_config_dir(),
          'upload_dir' => trailingslashit( $wp_upload_dir['basedir'] ),
          'upload_url' => $wp_upload_dir['baseurl']
        ]
      ]
    ));

    // Add theme or plugin properties
    if( $args ) $config->merge( new ConfigRegistry( $args ) );

    // Define toolkit version
    if ( !defined( __NAMESPACE__ . '\VERSION' ) ) define( __NAMESPACE__ . '\VERSION', $config->get( 'toolkit-version' ) );

    // Initialize ObjectCache
    self::$cache = new ObjectCache( $config );

    // Set ecryption salt
    if( defined( 'WP_ENCRYPT_KEY' ) ) {
      self::$salt = WP_ENCRYPT_KEY;
    } else if( $config->get( 'encrypt/salt' ) ) {
      self::$salt = $config->get( 'encrypt/salt' );
    } else {
      self::$salt = SECURE_AUTH_KEY;
    }

    // Load Environmental Variables
    $this->load_env_vars( [ $base_dir, $config->get( 'wordpress/root_dir' ) ] );

    self::$config = $config;
    return $config;

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
    * Fetch a config value or entire object config
    *
    * @param string|null $key The configuration key path to return. If null,
    *   returns all config values.
    * @return string|ConfigRegistry Config key path value or ConfigRegistry object
    * @since 0.1.4
    */
  protected function get_config( $key = null) {
    return self::$config->get( $key );
  }

  /**
    * Get current plugin properties
    *
    * @param string $field Return specific field
    * @return ConfigRegistry object
    * @since 0.2.0
    */
  protected function get_current_plugin_meta( $type = ConfigRegistry ) {
    if( !self::$config->get( 'base_dir' ) ) return [];

    $plugin_data['slug'] = current( explode( DIRECTORY_SEPARATOR, plugin_basename( self::$config->get( 'base_dir' ) ) ) );
    $plugin_data['path'] = trailingslashit( str_replace( plugin_basename( self::$config->get( 'base_dir' ) ), '', rtrim( self::$config->get( 'base_dir' ), '/' ) ) . $plugin_data['slug'] );
    $plugin_data['url'] = current( explode( $plugin_data['slug'] . '/', plugin_dir_url( self::$config->get( 'base_dir' ) ) ) ) . $plugin_data['slug'] . '/';

    // Get plugin path/file identifier
    foreach( get_plugins() as $key => $plugin ) {

      if( strstr( $key, trailingslashit( $plugin_data['slug'] ) ) ) {
        $parts = explode( '/', $key );
        $plugin_data['identifier'] = $key;
        $plugin_data['file'] = end( $parts );
        $plugin_data['meta'] = get_plugin_data( $plugin_data['path'] . $plugin_data['file'] );
      }

    }

    if( $type == 'ConfigRegistry' ) {
      $plugin_data = new ConfigRegistry( $plugin_data );
    }

    return $plugin_data;

  }

  /**
    * Returns the directory location of wp-config.php
    *
    * @return bool
    * @since 0.3.0
    */
  private function get_wordpress_config_dir() {
    $dir = dirname( __FILE__ );
    do {
      if( file_exists( $dir . '/wp-config.php' ) ) {
        return $dir;
      }
    } while( $dir = realpath( trailingslashit( $dir ) . ".." ) );
    return null;
  }

  /**
    * Load environment variables from various .env, if present.
    *
    * @param string|array $paths The directories to look within for .env file
    * @return bool Returns true if .env found and loaded
    * @since 0.3.0
    */
  private function load_env_vars( $paths = __DIR__ ) {

    $paths = (array) $paths;

    $result = null;
    foreach( $paths as $path ) {
      $result = $this->load_env_from_apth( $path );
    }

    return $result;
  }

  /**
    * Load environment variables from specified .env file.
    *
    * @param string $path The directory load .env file from
    * @return bool
    * @since 0.3.0
    */
  private function load_env_from_apth( $path ) {
    try {
      $env = new \Dotenv\Dotenv( $path );
      $env->load();
    } catch ( \Dotenv\Exception\InvalidPathException $e ) {
    } catch ( Exception $e ) { }

    $this->set_environment();
  }

  /**
    * Returns true if request is via AJAX.
    *
    * @return bool
    * @since 0.2.1
    */
  public function is_ajax() {
    return defined( 'DOING_AJAX' ) && DOING_AJAX;
  }

  /**
    * Returns true if environment is anything other than 'development' or
    *   'staging'. For example, for determining whether or not to enqueue a
    *    minified or non-minified script (which can be useful for debugging via
    *    browser).
    *
    * @return bool
    * @see ToolKit::get_environment()
    * @since 0.1.0
    */
  public static function is_production() {
    return strtolower( self::get_environment() ) == 'production';
  }

  /**
    * Sets the environmental variable `ENVIRONMENT` if $env is passed or if
    *    constant WP_ENV is define.
    *
    * @param string $env If provided, set's the enviroment to string provided
    * @since 0.3.0
    */
  public static function set_environment( $env = null ) {

    switch( true ) {
      case ( is_string( $env ) ):
        putenv( 'ENVIRONMENT=' . $env );
        break;
      case ( defined( 'WP_ENV' ) ):
        putenv( 'ENVIRONMENT=' . WP_ENV );
        break;
    }

  }

  /**
    * Gets the current development environmental variable if set via WP_ENV
    *    constant or .env values.
    *
    * @param string $env If provided, set's the enviroment to string provided
    * @see ToolKit::load_env_vars()
    * @see ToolKit::set_environment()
    * @since 0.3.0
    */
  public static function get_environment() {
    return getenv( 'ENVIRONMENT' ) ?: 'production';
  }

}
