<?php
namespace CloudVerve\MediaOffloader;
use CloudVerve\MediaOffloader\Provider\B2;

class Core extends Plugin {

  private $mime_list;

  function __construct() {

    // Check API credentials and exit if offloading is disabled in settings
    if( !$this->get_carbon_plugin_option( 'enabled' ) || !parent::check_api_credentials( true ) ) return;

    // Get settings and get variables
    $this->mime_list = B2::get_mime_list();

    // Rewrite uploaded file URLs
    if( $this->get_carbon_plugin_option( 'rewrite_urls' ) ) {
      add_filter( 'wp_get_attachment_url', array( $this, 'rewrite_attachment_url' ), 10, 2 );
    }

    if( $this->get_carbon_plugin_option( 'enabled' ) ) {

      // Add 'Documents/Archives' file type to Media Library filter dropdown
      if( $this->get_carbon_plugin_option( 'add_media_library_document_type' ) ) {
        add_filter( 'post_mime_types', array( $this, 'post_mime_types_filter' ) );
      }

      // Add media upload filter
      add_filter( 'add_attachment', array( $this, 'add_attachment_filter' ), 10, 2 );

      // Add media delete filter
      add_action( 'delete_attachment', array( $this, 'delete_attachment_filter' ), 10, 2 );

      // Get image size when files are removed locally
      add_filter( 'image_send_to_editor', array( $this, 'insert_image_filter' ), 10, 9 );

      // Upload
      add_filter( 'wp_generate_attachment_metadata', array( $this, 'wp_generate_attachment_metadata_filter' ), 10, 2 );

    }

  }

  /**
    * Upload media file to B2 if MIME type is whitelisted
    *
    * @param int $attachment_id Post ID of media
    * @since 0.7.0
    */
  public function add_attachment_filter( $attachment_id ) {

    $bucket_id = $this->get_carbon_plugin_option( 'bucket_id' );
    $bucket_name = B2::get_bucket_by_id( $bucket_id, 'name' );
    $file = B2::get_attachment_info( $attachment_id );
    $valid_mime = !$this->mime_list || in_array( $file['mime_type'], $this->mime_list );
    $upload = null;

    if( !$bucket_name || !$file || !$valid_mime ) return;

    if( !self::$client ) self::$client = B2::auth();

    // Copy uploaded file to B2 bucket
    $upload = $this->upload_file_to_bucket( $file, $bucket_id );

    // Store image dimensions
    $file_type = $this->get_upload_filetype( $file['filepath'] );
    if( $file_type == 'image' && $this->get_carbon_plugin_option( 'remove_local_media' ) ) {
      $image_size = getimagesize( $file['filepath'] );
      if( isset( $image_size[0] ) && $image_size[0] ) update_post_meta( $attachment_id, self::prefix( 'dimensions' ), array( $image_size[0], $image_size[1] ) );
    }

    // Get upload filename
    $url = self::$client->getDownloadUrl( [ 'BucketName' => $bucket_name, 'FileName' => $file['destfile'] ] );

    // Set upload name
    update_post_meta( $attachment_id, self::prefix( 'external_url' ), $url );

    // Delete original file
    if( $upload && $valid_mime && $file_type != 'image' && $this->get_carbon_plugin_option( 'remove_local_media' ) ) {
      unlink( ABSPATH . $upload->getName() );
    }

  }

  /**
    * Upload resized images to B2 bucket
    *
    * @param string $metadata Image metadata
    * @param int Attachment ID
    * @since 0.7.0
    */
  public function wp_generate_attachment_metadata_filter( $metadata, $attachment_id ) {

    if( isset( $metadata['sizes'] ) ) {

      $bucket_id = $this->get_carbon_plugin_option( 'bucket_id' );
      $file = B2::get_attachment_info( $attachment_id );

      $active_mime = !$this->mime_list || in_array( $file['mime_type'], $this->mime_list );

      // Removed resized images
      foreach( $metadata['sizes'] as $size => $meta ) {

        $upload = $this->upload_file_to_bucket( $file, $bucket_id, $meta['file'] );

        if( $upload && $active_mime && $this->get_carbon_plugin_option( 'remove_local_media' ) ) {
          unlink( ABSPATH . $upload->getName() );
        }

      }

    }

    // Remove original file
    if( $active_mime && $this->get_carbon_plugin_option( 'remove_local_media' ) ) {
      unlink( $file['filepath'] );
    }

    return $metadata;

  }

  /**
    * Delete media file from B2 when deleted from WordPress Media Library
    *
    * @param int $attachment_id Post ID of media
    * @since 0.7.0
    */
  public function delete_attachment_filter( $attachment_id ) {

    // Check if file was uploaded to B2
    $attachment_meta = get_post_meta( $attachment_id );
    if( !isset( $attachment_meta[ $this->prefix( 'external_url' ) ] ) ) {
      return;
    }

    $bucket_id = $this->get_carbon_plugin_option( 'bucket_id' );
    $bucket_name = B2::get_bucket_by_id( $bucket_id, 'name' );
    $file = B2::get_attachment_info( $attachment_id );
    if( !$bucket_name || !$file ) return;

    if( !self::$client ) self::$client = B2::auth();

    // Delete file from B2 bucket
    $this->delete_file_from_bucket( $file, $bucket_name );

    // Delete resized images
    if( $this->get_upload_filetype( $file['filepath'] ) == 'image' ) {

      foreach( get_intermediate_image_sizes() as $size ) {

        $resized_image = $this->get_resized_image_path( $attachment_id, $size, $file['filename'], $file['destpath'] );
        $this->delete_file_from_bucket( $file, $bucket_name, $resized_image );

      }

    }

  }

  /**
    * Rewrite media URLs to B2 links
    *
    * @param string $url Original, local media URL
    * @return URL of B2 media object
    * @since 0.7.0
    */
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
    * Adds 'Documents/Archives' file type to Media Library filter dropdown
    * @param array $post_mime_types
    * @return array
    * @since 0.7.0
    */
  public function post_mime_types_filter( $post_mime_types ) {

    $post_mime_types['application'] = array( __( 'Documents/Archives', self::$textdomain ), __( 'Manage Documents/Archives', self::$textdomain ), _n_noop( 'Document/Archivee', 'Documents/Archives <span class="count">(%s)</span>' ) );
    return $post_mime_types;

  }

  /**
    * Returns the first half of the MIME type (ie, image, application, etc)
    * @param string $path The path of the file to check
    * @return string File type
    * @since 0.7.0
    */
  private function get_upload_filetype( $path ) {

    $file_type = wp_check_filetype( $path );
    if( isset( $file_type['type'] ) && strstr( $file_type['type'], '/' ) ) {
      return current( explode( '/', $file_type['type'] ) );
    } else {
      return null;
    }

  }

  /**
    * Get image size when files are removed locally
    * @return string $html Image HTML markup
    * @since 0.7.0
    */
  public function insert_image_filter( $html, $attachment_id, $caption, $title, $align, $url, $size, $alt ) {

    $attachment_meta = get_post_meta( $attachment_id );
    if( isset( $attachment_meta[ $this->prefix( 'dimensions' ) ][0] ) ) {

      $image_size = unserialize( $attachment_meta[ $this->prefix( 'dimensions' ) ][0] );

      $dom = new \DOMDocument;
      $dom->loadHTML( $html );
      $anchor = $dom->getElementsByTagName('a')->item(0);
      $image = $dom->getElementsByTagName('img')->item(0);

      if( $image ) {
        $image->setAttribute('width', $image_size[0]);
        $image->setAttribute('height', $image_size[1]);
        $html = $dom->saveHTML( $anchor ? $anchor : $image);
      }

    }

    return $html;

  }

  /**
    * Upload file to B2 bucket
    * @param array $file Array of file properties from Helpers::get_attachment_info()
    * @param string $bucket_id The B2 bucket ID to upload to
    * @return \ChrisWhite\B2\File Object
    * @since 0.7.0
    */
  public function upload_file_to_bucket( $file, $bucket_id, $resized_image = null ) {

    $destfile = $file['destfile'];
    if( $resized_image ) {
      $destfile = $file['destpath'] . '/' . $resized_image;
    }

    $srcfile = $file['filepath'];
    if( $resized_image ) {
      $srcfile = wp_upload_dir()['path'] . DIRECTORY_SEPARATOR . $resized_image;
    }

    try {
      $upload = self::$client->upload([
        'BucketId' => $bucket_id,
        'FileName' => $destfile,
        'Body' => fopen( $srcfile, 'r' )
      ]);
      return $upload;
    } catch ( \ChrisWhite\B2\Exceptions\BadJsonException $e ) {
      echo $e->getMessage();
      return null;
    }

  }

  /**
    * Delete file from B2 bucket
    * @param array $file Array of file properties from Helpers::get_attachment_info()
    * @param string $bucket_name The name of the bucket that contains the file
    * @return \ChrisWhite\B2\File Object
    * @since 0.7.0
    */
  public function delete_file_from_bucket( $file, $bucket_name, $resized_image = null ) {

    $target = $file['destfile'];
    if( $resized_image ) {
      $resized_image = parse_url( $resized_image, PHP_URL_PATH );
      $target = $file['destpath'] . $resized_image;
    }
    $args = [ 'BucketName' => $bucket_name, 'FileName' => $target ];

    if( !self::$client->fileExists( $args ) ) return;

    try {
      $delete = self::$client->deleteFile( $args );
    } catch ( \ChrisWhite\B2\Exceptions\NotFoundException $e ) {
      return;
    }

  }

  /**
    * Get resized image path
    * @return string Path to resize image
    * @since 0.7.0
    */
  public function get_resized_image_path( $attachment_id, $size, $filename, $dir ) {

    $url = wp_get_attachment_image_src( $attachment_id, $size );

    if( isset( $url[0] ) ) {
      $resized_image = end( explode( $dir, $url[0] ) );
      return trim( $resized_image, '/' );
    } else {
      return null;
    }

  }

}
