<?php
namespace CloudVerve\MediaOffloader\Shortcodes;
use CloudVerve\MediaOffloader\Plugin;
use CloudVerve\MediaOffloader\Services\B2;
use ChrisWhite\B2\Exceptions;

class B2_Link_Shortcode extends Plugin
{

  public function __construct() {

    if( !self::$client ) self::$client = B2::auth();

    // Usage example: [b2_link bucket="my-bucket" object="wp-conteent/uploads/example.pdf"]Example File[/b2_link]
    if ( ! shortcode_exists( 'b2_link' ) ) {
        add_shortcode( 'b2_link', array( $this, 'b2_link_shortcode' ) );
    }

  }

  /**
   * A short code that returns "Hello {$name}!", if provided
   *
   * @param $atts array Shortcode Attributes
   * @return string Output of shortcode
   * @since 0.8.0
   */
  public function b2_link_shortcode( $atts, $content = null ) {
    $atts = shortcode_atts( [
      'bucket' => null,
      'object' => null,
      'silent' => false
    ], $atts, 'b2_link' );

    // Validate inputs
    $atts['silent'] = filter_var( $atts['silent'], FILTER_VALIDATE_BOOLEAN );

    // Get bucket object
    $bucket = $atts['bucket'] ? B2::get_bucket_by_name( $atts['bucket'] ) : B2::get_bucket_by_id( $this->get_carbon_plugin_option( 'bucket_id' ) );
    if( empty( $atts['object'] ) || !$bucket ) return $atts['silent'] ? '' : __( 'Invalid bucket', self::$textdomain );

    // Get file object
    try {
      $file_object = self::$client->getFile( [ 'BucketName' => $bucket['name'], 'FileName' => $atts['object'] ] );
    } catch ( \ChrisWhite\B2\Exceptions\NotFoundException $e ) {
      return $atts['silent'] ? '' : __( 'Object not found', self::$textdomain );
    }

    // Get object URL
    $url = self::$client->getDownloadUrl( [ 'BucketName' => $bucket['name'], 'FileName' => $atts['object'] ] );

    // Create hyperlink
    if( $content ) {
      return sprintf( '<a href="%s">%s</a>', $url, do_shortcode( $content ) );
    }

    return $url;

  }

}
