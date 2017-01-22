<?php
namespace TwoLabNet\BackblazeB2;

class EnqueueScripts extends Plugin {

    public function __construct()
    {
        $this->admin_scripts();
    }

    // Enqueue admin scripts
    public function admin_scripts() {

        // Only load script(s) on edit pages
        if( Helpers::current_admin_page() == 'crbn-backblaze-b2-image-offloader.php' ) {

        	add_action( 'admin_enqueue_scripts', function() {
            // Load custom JavaScript in admin
            wp_enqueue_script( 'backblaze-admin', parent::get_option('url') . 'assets/js/backblaze.js', array(), date("ymd-Gis", filemtime( parent::get_option('path') . 'assets/js/backblaze.js' )), true );
        	});

        }

    }

}
