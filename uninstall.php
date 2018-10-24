<?php
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) die();

$cmo_uninstall_settings = json_decode( file_get_contents( 'plugin.json' ) );
$cmo_uninstall_prefix = '_' . $cmo_uninstall_settings->prefix . '_';

if( get_option( $cmo_uninstall_prefix . 'uninstall_remove_settings' ) ) {

  $cmo_settings_fields = get_transient( $cmo_uninstall_prefix . 'settings_fields' );

  foreach( $cmo_settings_fields as $_key ) {
    delete_option( $_key );
  }

  delete_transient( $cmo_uninstall_prefix . 'settings_fields' );

}

delete_transient( $cmo_uninstall_prefix . 'credentials_check' );
