<?php
namespace WordPress_ToolKit;
use WordPress_ToolKit\Helpers\ArrayHelper;

/**
  * A class for converting server-side variables into client side CSS/JS
  *
  * @since 0.1.0
  */
class ScriptObject extends ToolKit {

  protected $values;
  protected $default_args;

  /**
   * Class constructor, runs on object creation.
   *
   * @param array $values An array of values to inject/enqueue
   * @link https://github.com/dmhendricks/wordpress-toolkit/wiki/ScriptObject
   * @since 0.1.0
   */
  public function __construct( $values ) {

    $this->values = $values;
    $this->default_args = array(
      'version'       => true,
      'target'        => 'wp',
      'css_media'     => null,
      'dependencies'  => array(),
      'localize'      => null,
      'variable_name' => $this->prefix( self::$config->get( 'js_object' ) ?: 'js_object' ),
      'handle'        => $this->prefix( 'dynamic_script' ),
      'script_dir'    => $this->prefix( self::$config->get( 'dynamic_scripts_directory' ) ?: 'dynamic' ),
      'filename'      => null
    );

  }

  /**
    * Method to inject values into the page head as CSS
    *
    * @param array $args Configuration array
    * @return bool Success or failure
    * @since 0.1.0
    */
  public function injectCSS( $args = array() ) {
    return $this->save( 'css', $args );
  }

  /**
    * Method to inject values into the page head as JavaScript
    *
    * @param array $args Configuration array
    * @return bool Success or failure
    * @since 0.1.0
    */
  public function injectJS( $args = array() ) {
    return $this->save( 'js', $args );
  }

  /**
    * Method to enqueue extranal CSS script and optionally update values
    *
    * @param array $args Configuration array
    * @param bool $update Whether or not to update the script before enqueuing
    * @return bool Success or failure
    * @since 0.1.0
    */
  public function enqueueCSS( $args = array(), $update = false ) {
    $success = true;
    if( $update ) $success = $this->save( 'css', $args, true );
    return $success ? $this->enqueue( 'css', $args ) : $success;
  }

  /**
    * Method to enqueue extranal JS script and optionally update values
    *
    * @param array $args Configuration array
    * @param bool $update Whether or not to update the script before enqueuing
    * @return bool Success or failure
    * @since 0.1.0
    */
  public function enqueueJS( $args = array(), $update = false ) {
    $success = true;
    if( $update ) $success = $this->save( 'js', $args, true );
    return $success ? $this->enqueue( 'js', $args ) : $success;
  }

  /**
    * Method to update/write extranal CSS script with provided values
    *
    * @param array $args Configuration array
    * @return bool Success or failure
    * @since 0.1.0
    */
  public function writeCSS( $args = array() ) {
    return $this->save( 'css', $args, true );
  }

  /**
    * Method to update/write extranal JS script with provided values
    *
    * @param array $args Configuration array
    * @return bool Success or failure
    * @since 0.1.0
    */
  public function writeJS( $args = array() ) {
    return $this->save( 'js', $args, true);
  }

  /**
    * Injects or enqueues JS/CSS data
    *
    * @param string $type Specifies whether to treat as CSS or JS. Valid values:
    *    'js' or 'css'
    * @param array $args Configuration associative array
    * @param bool $action Array of actions to perform: inject, enqueue, write
    * @return bool Success (true) or failure (false)
    * @since 0.1.0
    */
  private function save( $type, $args, $external = false ) { // , $update = false
    $args   = ArrayHelper::set_default_atts( $this->default_args, $args);
    $type   = strtolower( $type );
    $targets = (array) $args['target'];

    // Generate script content
    $content = '';
    if( $type == 'js' ) {

      $content = 'var ' . $args['variable_name'] . " = JSON.parse('" . str_replace( "'", "\'", json_encode( $this->values ) ) . "')";

    } else if ( $type == 'css' ) {

      foreach( $this->values as $key => $css ) {

        $explicit = strstr ( $key, '.' ) || strstr ( $key, '#' );
        $content .= ( $explicit ? $key : '.' . $key ) . ' { ' . $css . ' }' . ( $external ? "\n" : '' );

      }

    }
    if( !$content ) return false;

    // If we're not writing an external file, simply inject into head
    if( !$external ) {

      // Inject JavaScript variables into page head

      foreach( $targets as $target ) {

        if( $type == 'js' ) {

          add_action( $target . '_head', function() use ( &$args, &$content ) {
            echo '<script type="text/javascript">' . $content . '</script>';
          });

        } else if ( $type == 'css' ) {

          add_action( $target . '_head', function() use ( &$args, &$content ) {
            echo "\n" . '<style type="text/css">' . $content . '</style>' . "\n";
          });

        }

      }

      return true;

    }

    // Get upload directory and URL
    $upload_path = $this->get_script_upload_location( $args );

    // Write the script file
    $script_output_filename = $upload_path['dir'] . ( $args['filename'] ?: $this->prefix( 'dynamic_script' )  . '.' . $type );
    return file_put_contents( $script_output_filename, $content );

  }

  private function enqueue( $type, $args = array() ) {

    $upload_path = $this->get_script_upload_location( $args );
    $script_output_filename = $upload_path['dir'] . ( $args['filename'] ?: $this->prefix( 'dynamic_script' )  . '.' . $type );
    $targets = (array) $args['target'];

    if( file_exists( $script_output_filename ) ) {
      $script_url = $upload_path['url'] . ( $args['filename'] ?: $this->prefix( 'dynamic_script' )  . '.' . $type );

      // Set script version
      $script_version = get_bloginfo( 'version' );
      if( is_string( $args['version'] ) ) {

        // If $args['version'] is a string, use it a
        $script_version = $args['version'];

      } else if( is_bool( $args['version'] ) ) {

        if( $args['version'] ) {
          // If $args['version'] is true, get file modification date/time
          $script_version = date("ymd-Gis", filemtime( $script_output_filename ) );
        } else {
          // If $args['version'] is false, set to null
          $script_version = null;
        }

      }

      // Enqueue script
      foreach( $targets as $target ) {

        add_action( $target . '_enqueue_scripts', function() use ( &$type, &$args, &$content, &$script_url, &$script_version ) {

          if( $type == 'js' ) {

            wp_enqueue_script( $args['handle'], $script_url, $args['dependencies'], $script_version, true );
            if( isset( $args['localize'][0] ) ) wp_localize_script( $args['handle'], key( $args['localize'] ), array( current( $args['localize'] ) ) );

          } else if ( $type == 'css' ) {

            wp_enqueue_style( $args['handle'], $script_url, $args['dependencies'], $script_version, $args['css_media'] );

          }

        });

      }

    }


  }

  /**
    * Get the upload directory and URL for enqueued scripts
    *
    * @param array $args Configuration associative array
    * @return array The absolute path and URL to the script directory
    * @since 0.1.0
    */
  private function get_script_upload_location( $args = array() ) {

    $result = array( 'dir' => null, 'url' => null );

    $upload_dir = wp_upload_dir();
    if ( empty( $upload_dir['basedir'] ) ) return $result;

    // Set and create upload folder if it does exist
    $result['url'] = $upload_dir['baseurl'] . '/' . trim( $args['script_dir'], '/' ) . '/';
    $result['dir'] = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $args['script_dir'] );
    if ( !file_exists( $result['dir'] ) ) wp_mkdir_p( $result['dir'] );

    return $result;

  }

  /**
    * Magic method to retrieve values array as string
    * @return array Success (true) or failure (false)
    * @since 0.1.0
    */
  public function __toString() {
    return print_r( $this->values, true );
  }

  /**
    * Magic method to retrieve specific value
    *
    * @param string $key Key in $this->values to retrieve
    * @return string JSON encoded string of array $this->values
    * @since 0.1.0
    */
  public function __get( $key ) {
    return isset( $this->values[ $key ] ) ? json_encode( $this->values[ $key ] ) : null;
  }

}
