<?php
namespace CloudVerve\MediaOffloader\Settings;
use CloudVerve\MediaOffloader\Plugin;
use CloudVerve\MediaOffloader\Helpers;
use CloudVerve\MediaOffloader\Services\B2;
use Carbon_Fields\Datastore\Datastore\Serialized_Theme_Options_Datastore;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
  * An class to create a settings page for the plugin in WP Admin using Carbon Fields
  *
  * @since 0.1.0
  */
class Settings_Page extends Plugin {

  protected $settings_containers = [];

  public function __construct() {

    // Flush the cache when settings are saved
    add_action( 'carbon_fields_theme_options_container_saved', array( $this, 'options_saved_hook' ) );

    // Create tabbed plugin options page (Settings > Plugin Name)
    $this->create_tabbed_options_page();

    // Register custom MIME types
    if( $this->get_carbon_plugin_option( 'register_custom_mime_types' ) && $this->get_carbon_plugin_option( 'custom_mime_types' ) ) {
      add_filter( 'upload_mimes', array( $this, 'register_custom_mimes_types' ) );
    }

    // Inject custom style
    if( isset( $_GET['page'] ) && strpos( $_GET['page'], 'crb_carbon_fields_container' ) !== false ) {
      add_action( 'admin_enqueue_scripts', array( $this, 'inject_custom_css' ) );
    }

  }

  /**
    * Create a tabbed options/settings page in WP Admin
    *
    * @since 0.1.0
    */
  public function create_tabbed_options_page() {

    $container = Container::make( 'theme_options', self::$config->get( 'short_name' ) )
      ->set_page_parent('options-general.php')
      ->add_tab( __( 'General', self::$textdomain ), array(
        Field::make( 'checkbox', $this->prefix( 'enabled' ), __( 'Enable Media Offloading', self::$textdomain ) )
          ->set_default_value( 'yes' )
          ->help_text( __( 'Check to enable the plugin. Media Library items will be uploaded to the B2 bucket specified below.', self::$textdomain ) ),
        Field::make( 'checkbox', $this->prefix( 'rewrite_urls' ), __( 'Rewrite Media URLs', self::$textdomain ) )
          ->set_default_value( 'yes' )
          ->help_text( __( 'If enabled, Media Library URLs will be changed to serve from Backblaze. <em>It is <strong>highly likely</strong> that you\'ll want this checked unless you are using another plugin/method to rewrite URLs.</em>', self::$textdomain ) ),
        Field::make( 'checkbox', $this->prefix( 'remove_local_media' ), __( 'Remove Files From Server', self::$textdomain ) )
          ->help_text( __( 'If enabled, uploaded files will be deleted from your web host after they are uploaded to Backblaze B2.', self::$textdomain ) . '<br />' . __( '<strong>Caution:</strong> This may cause incompatibilities with other plugins that rely on a local copy of uploaded media. If you deactivate this plugin, the media links will be broken.', self::$textdomain ) ),
        Field::make( 'checkbox', $this->prefix( 'add_media_library_document_type' ), __( 'Add "Documents/Archives" to Media Library Filter Dropdown', self::$textdomain ) )
          ->set_default_value( 'yes' )
          ->help_text( __( 'For convenience, adds a <em>Documents/Archives</em> file type to the Media Library dropdown filter.', self::$textdomain ) ),
        Field::make( 'separator', $this->prefix( 'separator_general_credentials' ), __( 'Access Credentials', self::$textdomain ) ),
        Field::make( 'html', $this->prefix( 'html_general_credentials' ) )
          ->set_html( __( 'You can find these values by logging into your <a href="https://www.backblaze.com/b2/cloud-storage.html#af9kre" target="_blank">Backblaze</a> account, clicking <strong>Buckets</strong>, then clicking the <strong>Show Account ID and Application Key</strong> link.<br />After modifying your credentials, you must <strong>Save Changes</strong> to update bucket list.', self::$textdomain ) ),
        Field::make( 'text', $this->prefix( 'account_id' ), __( 'Account ID', self::$textdomain ) )
          ->set_classes( 'cmo-field-length-small' ),
        Field::make( 'text', $this->prefix( 'application_key' ), __('Application Key', self::$textdomain ) )
          ->set_classes( 'cmo-field-length-small' )
          ->set_attribute( 'type', 'password' ),
        Field::make( 'separator', $this->prefix( 'separator_general_bucket_path' ), __( 'Bucket & Path', self::$textdomain ) ),
        Field::make( 'select', $this->prefix( 'bucket_id' ), __( 'Bucket List', self::$textdomain ))
          ->add_options( B2::get_bucket_list( true ) )
          ->set_classes( 'cmo-field-length-small' )
          ->help_text( __( 'If you see <em>no options</em>, log into your Backblaze B2 account and make that you have at least one bucket created and that it is marked <strong>Public</strong>.', self::$textdomain ) ),
        Field::make( 'text', $this->prefix( 'path' ), __( 'Path', self::$textdomain ) )
          ->help_text( __( 'Optional. The folder path that you want files uploaded to. Leave blank for the root of the bucket.', self::$textdomain ) )
          ->set_attribute( 'placeholder', 'wp-content/uploads/' )
          ->set_classes( 'cmo-field-length-medium' )
          ->set_default_value( 'wp-content/uploads/' )
        )
      )
      ->add_tab( __( 'MIME Types', self::$textdomain ), array(
        Field::make( 'checkbox', $this->prefix( 'limit_mime_types' ), __( 'Limit to Specific MIME Types', self::$textdomain ) )
          ->help_text( __( 'If checked, uploads to Backblaze B2 are limited to specific MIME types.', self::$textdomain ) ),
        Field::make( 'complex', $this->prefix( 'custom_mime_types' ), __( 'Custom MIME Types', self::$textdomain ) )
          ->set_datastore( new Serialized_Theme_Options_Datastore() )
          ->add_fields( array(
            Field::make( 'text', 'label', __( 'Extension/Label', self::$textdomain ) )
              ->set_attribute( 'placeholder', __( 'Example:', self::$textdomain ) . ' WEBP' ),
            Field::make( 'text', 'mime', __( 'MIME Type', self::$textdomain ) )
              ->set_attribute( 'placeholder', __( 'Example:', self::$textdomain ) . ' image/webp' )
          ))
          ->help_text( __( 'Add extra MIME types that are not listed below.', self::$textdomain ) . ' <a href="https://www.sitepoint.com/mime-types-complete-list/" target="_blank">' . __( 'Examples', self::$textdomain ) . '</a>' )
          ->set_layout( 'tabbed-vertical' )
          ->setup_labels( array( 'plural_name' => __( 'MIME Types', self::$textdomain ), 'singular_name' => __( 'MIME Type', self::$textdomain ) ) )
          ->set_conditional_logic( array( array(
            'field' => $this->prefix( 'limit_mime_types' ),
            'value' => true )
          ))
          ->set_header_template( '<% if (label) { %><%- _.upperCase(label) %><% } else { %>' . __( 'Add New', self::$textdomain ) . '<% } %>' ),
        Field::make( 'checkbox', $this->prefix( 'register_custom_mime_types' ), __( 'Register Custom MIME Types', self::$textdomain ) )
          ->help_text( __( 'Registers custom MIME types (if specified).', self::$textdomain ) )
          ->set_default_value( 'yes' )
          ->set_conditional_logic( array( array(
            'field' => $this->prefix( 'limit_mime_types' ),
            'value' => true )
          )
        ),
        Field::make( 'set', $this->prefix( 'mime_types' ), __( 'Built-in MIME Types', self::$textdomain ) )
          ->set_conditional_logic( array( array(
            'field' => $this->prefix( 'limit_mime_types' ),
            'value' => true )
          )
        )
        ->set_default_value( [ 'application/zip', 'application/pdf', 'video/avi', 'video/x-flv', 'video/mov', 'video/mp4', 'video/webm' ] )
        ->add_options( $this->get_formatted_mime_types() ),
      )
    );

    // Store container and fields for register_uninstall_hook
    //$this->settings_containers[] = $container;

  }

  /**
    * Generate an array of allowed MIME types
    *
    * @since 0.2.0
    */
  private function get_formatted_mime_types() {

    $mime_types = get_allowed_mime_types();
    $result = array();

    foreach( $mime_types as $label => $mime ) {
      $result[$mime] = str_replace( '|', '/', strtoupper( $label ) ) . ' (' . $mime . ')';
    }

    // Add SVG to image/svg+xml type
    if( isset( $result['image/svg+xml'] ) ) $result['image/svg+xml'] = 'SVG/SVGZ (image/svg+xml)';

    return $result;

  }

  /**
    * Flush the WordPress object cache when settings are saved.
    *
    * @since 0.3.0
    */
  public function options_saved_hook() {

    delete_option( $this->prefix( 'credentials_check' ) );
    self::$cache->flush();

  }

  /**
    * Register custom MIME types
    *
    * @since 0.7.0
    */
  public function register_custom_mimes_types( $mimes ) {

    $custom_types = $this->get_carbon_plugin_option( 'custom_mime_types' );

    foreach( $custom_types as $mime ) {
      $mimes[ $mime['label'] ] = $mime['mime'];
    }

    return $mimes;

  }

  /**
   * Inject CSS into page head
   *
   * @return void
   * @since 0.8.0
   */
  public function inject_custom_css() {

    $style = "div.cmo-field-length-small select, div.cmo-field-length-small input { max-width: 325px; } div.cmo-field-length-medium input { max-width: 500px; }";

    wp_enqueue_style( 'admin-bar' );
    wp_add_inline_style( 'admin-bar', $style );

  }

}
