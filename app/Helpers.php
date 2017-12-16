<?php
namespace CloudVerve\MediaOffloader;
use Carbon_Fields\Container;
use Carbon_Fields\Field;
use ChrisWhite\B2\Client;
use ChrisWhite\B2\Bucket;
use ChrisWhite\B2\Exceptions;

class Helpers extends Plugin {

  /**
    * Display a notice/message in WP Admin
    *
    * @param string $msg The message to display.
    * @param string $type The type of notice. Valid values:
    *    error, warning, success, info
    * @param bool $is_dismissible Specify whether or not the user may dismiss
    *    the notice.
    * @since 2.0.0
    */
  public static function show_notice( $msg, $type = 'error', $is_dismissible = false ) {

    add_action( 'admin_notices', function() use (&$msg, &$type, &$is_dismissible) {

      $class = 'notice notice-' . $type . ( $is_dismissible ? ' is-dismissible' : '' );
      printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $msg );

    });

  }

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

  /**
    * Returns script ?ver= version based on environment (WP_ENV)
    *
    * If WP_ENV is not defined or equals anything other than 'development' or 'staging'
    * returns $script_version (if defined) else plugin verson. If WP_ENV is defined
    * as 'development' or 'staging', returns string representing file last modification
    * date (to discourage browser during development).
    *
    * @param string $script The filesystem path (relative to the script location of
    *    calling script) to return the version for.
    * @param string $script_version (optional) The version that will be returned if
    *    WP_ENV is defined as anything other than 'development' or 'staging'.
    *
    * @return string
    * @since 0.1.0
    */
  public function get_script_version( $script, $return_minified = false, $script_version = null ) {
    $version = $script_version ?: self::$config->get( 'plugin/meta/Version' );
    if( self::is_production() ) return $version;

    $script = self::get_script_path( $script, $return_minified );
    if( file_exists($script) ) {
      $version = date( "ymd-Gis", filemtime( $script ) );
    }

    return $version;
  }

  /**
    * Returns script path or URL, either regular or minified (if exists).
    *
    * If in production mode or if @param $force_minify == true, inserts '.min' to the filename
    * (if exists), else return script name without (example: style.css vs style.min.css).
    *
    * @param string $script The relative (to the plugin folder) path to the script.
    * @param bool $return_minified If true and is_production() === true then will prefix the
    *   extension with .min. NB! Due to performance reasons, I did not include logic to check
    *   to see if the script_name.min.ext exists, so use only when you know it exists.
    * @param bool $return_url If true, returns full-qualified URL rather than filesystem path.
    *
    * @return string The URL or path to minified or regular $script.
    * @since 0.1.0
    */
  public function get_script_path( $script, $return_minified = true, $return_url = false ) {
    $script = trim( $script, '/' );
    if( $return_minified && strpos( $script, '.' ) && $this->is_production() ) {
      $script_parts = explode( '.', $script );
      $script_extension = end( $script_parts );
      array_pop( $script_parts );
      $script = implode( '.', $script_parts ) . '.min.' . $script_extension;
    }

    return self::$config->get( $return_url ? 'plugin/url' : 'plugin/path' ) . $script;
  }

  /**
    * Returns absolute URL of $script.
    *
    * @param string $script The relative (to the plugin folder) path to the script.
    * @param bool
    * @since 0.1.0
    */
  public function get_script_url( $script, $return_minified = false ) {
    return self::get_script_path( $script, $return_minified, true );
  }

}
