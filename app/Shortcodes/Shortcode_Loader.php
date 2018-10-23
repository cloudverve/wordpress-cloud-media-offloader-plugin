<?php
namespace CloudVerve\MediaOffloader\Shortcodes;
use CloudVerve\MediaOffloader\Plugin;

class Shortcode_Loader extends Plugin {

  /**
   * @var array Shortcode class name to register
   * @since 0.3.0
   */
  protected $shortcodes;

  public function __construct() {

    $this->shortcodes = array(
      B2_Link_Shortcode::class
    );

    foreach( $this->shortcodes as $shortcodeClass ) {

      new $shortcodeClass();

    }

  }

}
