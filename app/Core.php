<?php
namespace TwoLabNet\BackblazeB2;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Core extends Plugin {

  function __construct() {

    // Check API credentials
    $this->check_api_credentials();

    // Add 'Document' file type to Media Library filter dropdown
    if( $this->get_plugin_option( 'add_media_library_document_type' ) ) {
      add_filter( 'post_mime_types', array( $this, 'post_mime_types_filter' ) );
    }

    // Rewrite uploaded file URLs
    if( $this->get_plugin_option( 'rewrite_urls' ) ) {
      add_filter( 'wp_get_attachment_url', array( $this, 'rewrite_attachment_url' ), 10, 2 );
    }

    if( $this->get_plugin_option( 'enabled' ) ) {

      // Add media upload filter
      add_filter( 'add_attachment', array( $this, 'add_attachment_handler' ), 10, 2 );

      // Add media delete filter
      add_action( 'delete_attachment', array( $this, 'delete_attachment_handler' ), 10, 2 );

    }

  }

  public function add_attachment_handler( $attachment_id ) {

    $bucket_id = $this->get_plugin_option( 'bucket_id' );
    $bucket_name = Helpers::get_bucket_by_id( $bucket_id, 'name' );
    $file = Helpers::get_attachment_info( $attachment_id );
    $mime_list = Helpers::get_mime_list();
    $valid_mime = !$mime_list || in_array( $file['mime_type'], $mime_list );

    if( !$bucket_name || !$file || !$valid_mime ) return;

    if( !self::$client ) self::$client = Helpers::auth();

    $upload = self::$client->upload([
      'BucketId' => $bucket_id,
      'FileName' => $file['destfile'],
      'Body' => fopen($file['filepath'], 'r')
    ]);

    // Get upload filename
    $url = self::$client->getDownloadUrl( [ 'BucketName' => $bucket_name, 'FileName' => $file['destfile'] ] );

    // Set upload name
    update_post_meta( $attachment_id, self::prefix( 'external_url' ), $url );

  }

  public function delete_attachment_handler( $attachment_id ) {

    // Check if file was uploaded to B2
    $attachment_meta = get_post_meta( $attachment_id );
    if( !isset( $attachment_meta[ $this->prefix( 'external_url' ) ] ) ) {
      return;
    }

    $bucket_id = $this->get_plugin_option( 'bucket_id' );
    $bucket_name = Helpers::get_bucket_by_id( $bucket_id, 'name' );
    $file = Helpers::get_attachment_info( $attachment_id );
    if( !$bucket_name || !$file ) return;

    if( !self::$client ) self::$client = Helpers::auth();

    $delete = self::$client->deleteFile([
        'BucketName' => $bucket_name,
        'FileName' => $file['destfile']
    ]);

  }

  public function rewrite_attachment_url( $url ) {

    $post_id = attachment_url_to_postid( $url );
    $attachment_meta = get_post_meta( $post_id );

    if( isset( $attachment_meta[ $this->prefix( 'external_url' ) ] ) ) {
      return current( $attachment_meta[ $this->prefix( 'external_url' ) ] );
    } else {
      return $url;
    }

  }

  /**
    * Check if provided B2 credentials are valid. Store valid result in database,
    *    (cached, where availavle) so we don't hammer the B2 API. This value is
    *    reset every time settings are saved.
    * @since 0.7.0
    */
  public function check_api_credentials() {

    $credentials_check = self::$cache->get_object( self::prefix( 'credentials_check' ), function() {
      return get_option( $this->prefix( 'credentials_check' ) );
    });

    if( $credentials_check ) {
      return;
    } else {
      $credentials_check = Helpers::auth();
      update_option( $this->prefix( 'credentials_check' ), !is_null( $credentials_check ) );
    }

    $settings_page = get_admin_url( null, 'options-general.php?page=crb_carbon_fields_container_backblaze_b2.php#!general' );
    $settings_notice = __( 'Please check your {|access credentials|}.', self::$textdomain );
    $settings_parts = preg_split('/[{}]/', $settings_notice, null, PREG_SPLIT_NO_EMPTY);

    if( count( $settings_notice > 1 ) ) {

      $settings_notice = '';
      foreach( $settings_parts as $part ) {
        $settings_notice .= strstr( $part, '|' ) ? '<a href="' . $settings_page . '">' . trim( $part, '|' ) . '</a>' : $part;
      }

    }

    if( !$credentials_check ) {
      Helpers::show_notice( '<strong>' . self::$config->get('plugin/meta/Name') . '</strong>: ' . __( 'Unable to connect to the Backblaze B2 API.', self::$textdomain ) . ' ' . $settings_notice, 'error', false );
    }

  }

  /**
    * Adds 'Document' file type to Media Library filter dropdown
    * @param array $post_mime_types
    * @return array
    * @since 0.7.0
    */
  function post_mime_types_filter( $post_mime_types ) {

      $post_mime_types['application'] = array( __( 'Document', self::$textdomain ), __( 'Manage Documents', self::$textdomain ), _n_noop( 'Document <span class="count">(%s)</span>', 'Documents <span class="count">(%s)</span>' ) );
      return $post_mime_types;

  }

}
