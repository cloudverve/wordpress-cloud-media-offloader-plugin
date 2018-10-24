<?php
namespace WordPress_ToolKit;

/**
  * A class for retrieving data and performing various tasks on plugins
  *
  * @since 0.2.0
  */
class PluginTools extends ToolKit {

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
