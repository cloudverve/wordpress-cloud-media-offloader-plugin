<?php
namespace TwoLabNet\BackblazeB2;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Settings extends Plugin {

  private $mine_filter_options;

  /**
    * Create a options/settings page in WP Admin
    */
  function __construct() {

    $this->mine_filter_options = array('*' => __('All File Types'), 'exclude' => __('All Types Except'), 'include' => __('Only Types Specified'));

    // Create admin options page
    $this->add_plugin_options_page();

  }

  private function add_plugin_options_page() {

    $bucket_list = array();

    B2::auth();
    if(Helpers::current_admin_page() == 'crbn-backblaze-b2.php') {
      $bucket_list = self::get_bucket_list();
    }

    Container::make('theme_options', 'Backblaze B2')
      ->set_page_parent('options-general.php')
      ->add_tab( __('General'), array(
        Field::make('checkbox', self::$prefix.'enabled', __('Enable Plugin'))->set_option_value(1)
          ->help_text(__('Check to enable the plugin. Images will be uploaded to your B2 bucket as specified below.')),
        Field::make('html', self::$prefix.'section_header_auth')
          ->set_html('<h3>'.__('Access Credentials').'</h3><p>'.__('You can find these values by logging into your').' <a href="https://www.backblaze.com/" target="_blank">Backblaze</a> '.('account, clicking <strong>Buckets</strong, then clicking the <strong>Show Account ID and Application Key</strong> link')),
        Field::make('text', self::$prefix.'account_id', __('Account ID')),
        Field::make('text', self::$prefix.'application_key', __('Application Key')),
        Field::make('html', self::$prefix.'section_header_bucket')
          ->set_html('<h3>'.__('Bucket &amp; Path').'</h3>'),
        Field::make('select', self::$prefix.'bucket_id', __('Bucket List'))
          ->add_options($bucket_list)
          ->help_text('If you see <em>no options</em>, log into your Backblaze B2 account and make that you have at least one bucket created and that it is marked <strong>Public</strong>.'),
        Field::make('text', self::$prefix.'path', 'Path')
          ->help_text('Optional. The folder path that you want files uploaded to. Leave blank for the root of the bucket.')
          ->set_default_value('wp-content/uploads/'), //->set_placeholder('wp-content/uploads/'),
        Field::make('html', self::$prefix.'section_header_optional')
          ->set_html('<h3>Advanced Settings</h3>'),
        Field::make('checkbox', self::$prefix.'append_year_month', 'Add Year and Month to Path')->set_option_value(1)->set_default_value(1)
          ->help_text('For example, if your folder path is <tt>wp-content/uploads/</tt>, the resulting path will be: <tt>wp-content/uploads/'.date('Y').'/'.date('m').'/</tt>'),
        Field::make('checkbox', self::$prefix.'rewrite_urls', 'Rewrite Media URLs')->set_option_value(1)->set_default_value(1)
          ->help_text('If enabled, Media Library URLs will be changed to serve from Backblaze. <em>You will likely want this checked unless you are using another plugin/method to rewrite URLs.</em>')
      ))
      ->add_tab( __('MIME Types'), array(
        Field::make('radio', self::$prefix.'filter_mime_type', __('File Types to Process'))->set_default_value('*')
          ->help_text(__('Unless specified, all file (MIME) types are processed.'))
          ->add_options( array($this->mine_filter_options)),
        Field::make( 'set', self::$prefix.'mime_types', __('File (MIME) Types') )
          ->set_conditional_logic( array(array( 'field' => self::$prefix.'filter_mime_type', 'value' => array('include', 'exclude'), 'compare' => 'IN' )) )
          ->set_default_value(array('image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff', 'image/svg', 'image/svgz'))
          ->add_options( $this->get_formatted_mime_types() )
      ));
  }

  public static function get_bucket_list($_cache = false) {
    $_buckets_array = @B2::curl('b2_list_buckets', 'POST', ['accountId' => self::$settings['b2']['accountId']])->buckets;
    if(!$_buckets_array) return array();

    $_buckets = array();
    foreach($_buckets_array as $_bucket) {
      if($_bucket->bucketType == 'allPublic') $_buckets[$_bucket->bucketId.':'.$_bucket->bucketName] = $_bucket->bucketName;
    }

    return $_buckets;
  }

  private function plugin_settings_link($links) {
    // TODO
    $settings_link = array('<a href="'.admin_url('options-general.php?page=crbn-backblaze-b2-media-offloader.php').'">Settings</a>');
    //array_unshift($links, $settings_link);
    //array_merge($links, $settings_link);
    return array_merge($links, $settings_link);
  }

  private function get_formatted_mime_types() {
    $mime_types = get_allowed_mime_types();
    $types = array();

    foreach($mime_types as $label => $mime) {
      $types[$mime] = str_replace('|', '/', strtoupper($label)) . ' (<tt>' . $mime . '</tt>)';
    }

    return $types;
  }

  /*
  private function get_filter_list_label() {
    $filter_type = carbon_get_theme_option(self::$prefix.'filter_mime_type');
    if(in_array($filter_type, array('include', 'exclude'))) array_search ($filter_type, $this->mine_filter_options);
    return '';
  }
  */

}
