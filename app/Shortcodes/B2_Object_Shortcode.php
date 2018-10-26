<?php
namespace CloudVerve\MediaOffloader\Shortcodes;
use CloudVerve\MediaOffloader\Plugin;
use CloudVerve\MediaOffloader\Provider\B2;
use ChrisWhite\B2\Exceptions;

class B2_Object_Shortcode extends Plugin
{

  public function __construct() {

    if( !self::$client ) self::$client = B2::auth();

    // Usage example: [b2_object bucket="my-bucket" object="wp-conteent/uploads/example.pdf"]Example File[/b2_object]
    if ( ! shortcode_exists( 'b2_object' ) ) {
        add_shortcode( 'b2_object', array( $this, 'b2_object_shortcode' ) );
    }

  }

  /**
   * A short code that returns "Hello {$name}!", if provided
   *
   * @param $atts array Shortcode Attributes
   * @return string Output of shortcode
   * @since 0.8.0
   */
  public function b2_object_shortcode( $atts, $content = null ) {
    $atts = shortcode_atts( [
      'bucket' => null,
      'object' => null,
      'silent' => false,
      'output' => null, // image, link, url
      'title' => null
    ], $atts, 'b2_object' );

    // Validate inputs
    $atts['silent'] = filter_var( $atts['silent'], FILTER_VALIDATE_BOOLEAN );
    $content = trim( $content );
    $atts['output'] = strtolower( $atts['output'] );
    if( $atts['output'] == 'link' && !$content ) $atts['output'] = 'url';
    if( !$atts['output'] && $content ) $atts['output'] = 'image';
    $atts['title'] = trim( $atts['title'] ) ? ' title="' . esc_attr( trim( $atts['title'] ) ) . '"' : '';

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
    $custom_url = rtrim( trim( $this->get_carbon_plugin_option( 'custom_url' ) ), '/' );
    if( $this->get_carbon_plugin_option( 'enable_custom_url' ) && trim( $custom_url ) ) {
      $url = sprintf( '%s/file/%s/%s', $custom_url, $bucket['name'], $atts['object'] );
    } else {
      $url = self::$client->getDownloadUrl( [ 'BucketName' => $bucket['name'], 'FileName' => $atts['object'] ] );
    }

    // Create hyperlink
    if( $content && $atts['output'] != 'url' ) {
      switch( $atts['output'] ) {
        case 'image':
          return sprintf( '<img src="%s" alt="%s"%s />', esc_attr( $url ), esc_attr( $content ), $atts['title'] );
        default:
          return sprintf( '<a href="%s">%s</a>', esc_attr( $url ), do_shortcode( $content ) );
      }
    }

    return $url;

  }

}
