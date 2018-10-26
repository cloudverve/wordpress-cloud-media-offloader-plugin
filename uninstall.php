<?php
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) die();

// Load plugin settings
$__uninstall_settings = json_decode( trim( file_get_contents( trailingslashit( __DIR__ ) . 'plugin.json' ) ) );
$__uninstall_prefix = '_' . $__uninstall_settings->prefix . '_';

// Check if Remove Settings on Uninstall is enabled
if( get_option( $__uninstall_prefix . 'uninstall_remove_settings' ) ) {

  $__settings_fields = get_option( $__uninstall_prefix . 'settings_fields' );

  // Delete settings from database
  foreach( $__settings_fields as $_key ) {
    delete_option( $_key );
  }

  delete_option( $__uninstall_prefix . 'settings_fields' );

}

// Remove credentials cheeck transient
delete_transient( $__uninstall_prefix . 'credentials_check' );
