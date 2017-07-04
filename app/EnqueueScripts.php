<?php
namespace TwoLabNet\BackblazeB2;

class EnqueueScripts extends Plugin {

  function __construct() {

    // Enqueue admin scripts
    if( Helpers::current_admin_page() == 'crbn-backblaze-b2.php' ) {
      add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts') );
    }

  }

  /**
    * Enqueue scripts used in WP admin interface
    */
  public function enqueue_admin_scripts() {

    wp_enqueue_script( 'b2mo-admin', plugins_url('/assets/js/backblaze.min.js', dirname(__FILE__)), array('jquery'), $this->get_script_version('backblaze.min.js') );

  }

}
