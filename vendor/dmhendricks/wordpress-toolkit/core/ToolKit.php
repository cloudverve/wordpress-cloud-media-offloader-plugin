<?php
namespace WordPress_ToolKit;

/**
 * ToolKit base class
 *
 * Loads configuration and sets constants
 */
class ToolKit {

  protected static $config;

  function __construct( $args = null )
  {

    // Set toolkit configuration defaults
    $defaults = new ConfigRegistry( dirname( __DIR__ ) . '/config.json' );

    // Define toolkit version
    if ( !defined( __NAMESPACE__ . '\VERSION' ) ) define( __NAMESPACE__ . '\VERSION', $defaults->get( 'version' ) );

    // Replace default settings with those provided
    self::$config = $defaults->merge( new ConfigRegistry( $args ) );

  }

  /**
    * Append a field prefix as defined in $config
    *
    * @param string|null $str The string/field to prefix
    * @return string Prefixed string/field value
    * @since 0.1.0
    */
  public function prefix( $field_name = '' ) {
    return self::$config->get( 'prefix' ) . $field_name;
  }

}
