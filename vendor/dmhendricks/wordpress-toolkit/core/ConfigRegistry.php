<?php
namespace WordPress_ToolKit;
use WordPress_ToolKit\Helpers\FileHelper;

/**
  * A class to load an array or JSON file/data into a configuration registry.
  *
  * @author Chris Kankiewicz
  * @link https://github.com/PHLAK/Config Forked source
  * @since 0.1.0
  */
class ConfigRegistry
{

  protected $config = array();

  /**
   * Class constructor, runs on object creation.
   *
   * @param mixed $context Raw array of configuration options or path to a
   *                       JSON configuration file
   */
  public function __construct( $context = null, $suppress_errors = true )
  {

    /**
     * If $context not specified, look in default locations:
     *    If running inside plugin, look for plugins/plugin-dir/plugin.json
     *    If running inside theme, look for thtmes/theme-dir/theme.json
     */
    if( !$context ) {
      $context = FileHelper::get_config_base_path( true );
    }

    if( is_array( $context ) ) {
      $this->config = $context;
    } else if( is_string( $context ) ) {
      $this->load( $context );
    } else {
      if( $suppress_errors ) {
        $this->config = false;
      } else {
        throw new \Exception( 'Invalid configuration argument.' );
      }
    }

  }

  /**
   * Magic get method; allows accessing config items via object notation.
   *
   * @param string $key Unique configuration option key
   * @return mixed New ConfigRegistry object or config item value
   */
  public function __get( $key )
  {

    $item = $this->get( $key );
    if (is_array( $item )) {
        return new static( $item );
    }
    return $item;

  }

  /**
   * Magic toString method; returns config object as JSON-encoded string
   *
   * @return mixed New ConfigRegistry object or config item value
   */
  public function __toString()
  {
      return json_encode( $this->config );
  }

  /**
   * Load configuration options from a file or directory.
   *
   * @param string $path     Path to configuration file or directory
   * @param bool   $override Weather or not to override existing options with
   *                         values from the loaded file
   *
   * @return object This Config object
   */
  private function load( $path ) {

    $json = json_decode( file_get_contents( $path ), true );

    $this->config = $json ?: false;

  }

  /**
   * Retrieve a configuration option via a provided key.
   *
   * @param string $key     Unique configuration option key
   * @param mixed  $default Default value to return if option does not exist
   * @return mixed Stored config item or $default value
   */
  public function get($key = null, $default = null)
  {

    if ( !isset( $key ) ) {
      return $this->config;
    }
    $config = $this->config;
    foreach( explode('/', $key ) as $k ) {
      if ( !isset( $config[$k] ) ) {
        return $default;
      }
      $config = &$config[$k];
    }
    return $config;

  }

  /**
   * Store a config value with a specified key.
   *
   * @param string $key   Unique configuration option key
   * @param mixed  $value Config item value
   * @return object This ConfigRegistry object
   */
  public function set($key, $value)
  {

    $config = $this->config;
    foreach( explode('/', $key) as $k ) {
      $config = &$config[$k];
    }
    $config = $value;
    return true;

  }

  /**
   * Check for the existance of a config item.
   *
   * @param string $key Unique configuration option key
   * @return bool True if item existst, otherwise false
   */
  public function has($key)
  {

    $config = $this->config;
    foreach( explode('/', $key) as $k ) {
      if ( !isset( $config[$k] ) ) {
        return false;
      }
      $config = $config[$k];
    }
    return true;

  }

  /**
   * Merge another ConfigRegistry object into this one.
   *
   * @param Config $config Instance of ConfigRegistry
   * @return object This ConfigRegistry object
   */
  public function merge( ConfigRegistry $config )
  {

    $current = $config->get();
    if( $this->config && $current ) {
      $this->config = array_merge( $this->config, $current );
    }
    return $this;

  }

  /**
   * Split a sub-array of configuration options into it's own ConfigRegistry object.
   *
   * @param string $key Unique configuration option key
   * @return Config A new ConfigRegistry object
   */
  public function split($key)
  {
    return new static( $this->get($key) );
  }

}
