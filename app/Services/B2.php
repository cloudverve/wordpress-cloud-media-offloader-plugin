<?php
namespace CloudVerve\MediaOffloader\Services;
use CloudVerve\MediaOffloader\Plugin;
use Carbon_Fields\Container;
use Carbon_Fields\Field;
use ChrisWhite\B2\Client;
use ChrisWhite\B2\Bucket;
use ChrisWhite\B2\Exceptions;

class B2 extends Plugin {

  /**
    * Authenticate to Backblaze API and create client object
    *
    * @return object ChrisWhite\B2\Client object
    * @since 0.7.0
    */
  public static function auth() {

    $account_id = self::$cache->get_object( self::prefix( 'account_id' ), function() {
      return get_option( self::prefix( 'account_id', '_' ) );
    });
    $application_key = self::$cache->get_object( self::prefix( 'application_key' ), function() {
      return get_option( self::prefix( 'application_key', '_' ) );
    });

    if( !$account_id || !$application_key ) return null;

    try {
      $client = new Client( $account_id, $application_key );
      return $client;
    } catch ( \ChrisWhite\B2\Exceptions\B2Exception $e ) {
      return null;
    }

  }

  /**
    * Fetch a list of available B2 buckets
    *
    * @param bool|null $associative If true, return associative array. If false,
    *    return multidimensional. If null, both types are returned.
    * @return array List of B2 buckets
    * @since 0.7.0
    */
  public static function get_bucket_list( $public = null, $associative = true ) {

    // Determine type of buckets to retrieve
    $bucket_scope = null;
    if( is_bool( $public ) ) {
      $bucket_scope = $public ? 'allPublic' : 'allPrivate';
    }

    // Get Account ID and Application Key from Settings
    $account_id = self::$cache->get_object( self::prefix( 'account_id' ), function() {
      return get_option( self::prefix( 'account_id', '_' ) );
    });
    $application_key = self::$cache->get_object( self::prefix( 'application_key' ), function() {
      return get_option( self::prefix( 'application_key', '_' ) );
    });

    if( !$account_id || !$application_key ) return array();

    // Fetch bucket list from object cache, if enabled, else load from database
    $buckets = self::$cache->get_object( self::prefix( 'b2_bucket_list' ), function() use ( &$account_id, &$application_key ) {
      try {
        self::$client = self::auth();
        if( !self::$client ) return array();
        return self::$client->listBuckets();
      } catch( B2Exception $e ) {
        self::show_notice( $e->getMessage(), 'error', true );
      }
    });

    if( !$buckets ) return array();

    // Build result array
    $result = array();
    foreach( $buckets as $bucket ) {

      if( !$bucket_scope || $bucket_scope == $bucket->getType() ) {

        if( $associative ) {

          $result[ $bucket->getId() ] = $bucket->getName();

        } else {

          $result[] = array(
            'ID' => $bucket->getId(),
            'name' => $bucket->getName(),
            'public' => $bucket->getType() == 'allPublic'
          );

        }

      }

    }

    return $result;

  }

  /**
    * Retrieves bucket info by ID
    *
    * @param string $bucket_id The ID of the bucket
    * @return string The bucket info, including ID, name and scope
    * @since 0.7.0
    */
  public static function get_bucket_by_id( $bucket_id, $field = null ) {

    $buckets = self::get_bucket_list( null, false );
    foreach( $buckets as $bucket ) {
      if( $bucket['ID'] == $bucket_id ) return $field ? $bucket[$field] : $bucket;
    }
    return null;

  }

  /**
    * Retrieves file and path info
    *
    * @param int Attachment ID
    * @return array File and path info of attachment
    * @since 0.7.0
    */
  public static function get_attachment_info( $attachment_id ) {

    $uploads = wp_upload_dir();
    $filepath = get_attached_file( $attachment_id );
    $remote_path = untrailingslashit( self::get_plugin_option( 'path' ) );

    return array(
      'ID' => $attachment_id,
      'filepath' => $filepath,
      'filename' => basename( $filepath ),
      'url' => wp_get_attachment_url( $attachment_id ),
      'subdir' => trim( $uploads['subdir'], DIRECTORY_SEPARATOR ),
      'destpath' => $remote_path . untrailingslashit( $uploads['subdir'] ),
      'destfile' => $remote_path . trailingslashit( $uploads['subdir'] ) . basename( $filepath ),
      'mime_type' => get_post_mime_type( $attachment_id )
    );

  }

  /**
    * Retrieves a list of MIME types to process from the Settings page
    *
    * @return array
    * @since 0.7.0
    */
  public static function get_mime_list() {

    if( !self::get_plugin_option( 'limit_mime_types' ) ) {
      return false;
    }

    $mime_types = self::get_plugin_option( 'mime_types' ) ?: array();
    $custom_mimes = self::get_plugin_option( 'custom_mime_types' ) ?: array();
    if( !$mime_types ) $mime_types = array();

    foreach( $custom_mimes as $type ) {
      $mime_types[] = $type['mime'];
    }

    return $mime_types;

  }

}
