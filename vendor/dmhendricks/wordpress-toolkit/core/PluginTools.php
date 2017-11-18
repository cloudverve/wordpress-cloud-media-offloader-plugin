<?php
namespace WordPress_ToolKit;
use WordPress_ToolKit\ConfigRegistry;

/**
  * A class for retrieving data and performing various tasks on plugins
  *
  * @since 0.2.0
  */
class PluginTools extends ToolKit {

  protected $plugin_dir;

  /**
   * Class constructor, runs on object creation.
   *
   * @param array $values An array of values to inject/enqueue
   * @link https://github.com/dmhendricks/wordpress-toolkit/wiki/PluginTools
   * @since 0.2.0
   */
  public function __construct( $plugin_dir = __DIR__ ) {

    $this->plugin_dir = $plugin_dir;

  }

  /**
    * Get current plugin properties
    *
    * @param string $field Return specific field
    * @return ConfigRegistry object
    * @since 0.2.0
    */
  public function get_current_plugin_data( $type = ConfigRegistry ) {

    $plugin_data['slug'] = current( explode( DIRECTORY_SEPARATOR, plugin_basename( $this->plugin_dir ) ) );
    $plugin_data['path'] = trailingslashit( str_replace( plugin_basename( $this->plugin_dir ), '', $this->plugin_dir ) . $plugin_data['slug'] );
    $plugin_data['url'] = current( explode( $plugin_data['slug'] . '/', plugin_dir_url( $this->plugin_dir ) ) ) . $plugin_data['slug'] . '/';

    // Get plugin path/file identifier
    foreach( get_plugins() as $key => $plugin ) {

      if( strstr( $key, trailingslashit( $plugin_data['slug'] ) ) ) {
        $parts = explode( DIRECTORY_SEPARATOR, $key );
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
    * Hide plugin(s) from WP Admin plugins menu (except multisite network admin)
    *
    * @param array|string $plugins String or array of plugins to hide. Format:
    *     array( 'plugin-folder/plugin-main-file.php', 'other-plugin/other-plugin.php' )
    * @since 0.2.0
    */
  public function hide_plugins( $plugin_list, $only_when_active = false ) {

    if( !is_array( $plugin_list ) ) $plugin_list = array( $plugin_list );

    if( $plugin_list ) {

      add_filter( 'all_plugins', function( $plugins ) use ( &$plugin_list, &$only_when_active ) {

        foreach( $plugin_list as $plugin ) {

          if( !$only_when_active || ( $only_when_active && is_plugin_active( $plugin ) ) ) {
            unset( $plugins[ $plugin ] );
          }

        }

        return $plugins;

      });

    }

  }

}
