<?php
namespace TwoLabNet\BackblazeB2;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Settings extends Plugin {

    public static function load() {
      // Carbon Fields Docs: https://carbonfields.net/docs/containers-theme-options/

      $bucket_list = array();

      B2::auth();
      if(Helpers::current_admin_page() == 'crbn-backblaze-b2.php') {
        $bucket_list = self::get_bucket_list();
      }

      Container::make('theme_options', 'Backblaze B2')
        ->set_page_parent('options-general.php')
        ->add_fields(array(
          Field::make('checkbox', parent::get_option().'enabled', 'Enable Plugin')->set_option_value(1)
            ->help_text('Check to enable the plugin. Images will be uploaded to your B2 bucket as specified below.'),
          Field::make('html', parent::get_option().'section_header_auth')
            ->set_html('<h3>Access Credentials</h2><p>You can find these values by logging into your <a href="https://www.backblaze.com/" target="_blank">Backblaze</a> account, clicking <strong>Buckets</strong, then clicking the <strong>Show Account ID and Application Key</strong> link.'),
          Field::make('text', parent::get_option().'account_id', 'Account ID'),
          Field::make('text', parent::get_option().'application_key', 'Application Key'),
          Field::make('html', parent::get_option().'section_header_bucket')
            ->set_html('<h3>Bucket &amp; Path</h2>'),
          Field::make('select', parent::get_option().'bucket_id', 'Bucket List')
            ->add_options($bucket_list)
            ->help_text('If you see <em>no options</em>, log into your Backblaze B2 account and make that you have at least one bucket created and that it is marked <strong>Public</strong>.'),
          Field::make('text', parent::get_option().'path', 'Path')
            ->help_text('Optional. The folder path that you want files uploaded to. Leave blank for the root.')
            ->set_default_value('wp-content/uploads/'), //->set_placeholder('wp-content/uploads/'),
          Field::make('html', parent::get_option().'section_header_optional')
            ->set_html('<h3>Optional Settings</h2>'),
          Field::make('checkbox', parent::get_option().'append_year_month', 'Add Year and Month to Path')->set_option_value(1)->set_default_value(1)
            ->help_text('For example, if your folder path is <tt>wp-content/uploads/</tt>, the resulting path will be: <tt>wp-content/uploads/'.date('Y').'/'.date('m').'/</tt>'),
          Field::make('checkbox', parent::get_option().'rewrite_urls', 'Rewrite Media URLs')->set_option_value(1)->set_default_value(1)
            ->help_text('If enabled, Media Library URLs will be changed to serve from Backblaze. <em>You probably want this checked unless you are using another plugin/method to rewrite URLs.</em>')
        )
      );

    }

    public static function get_bucket_list($_cache = false) {
      $_buckets_array = @B2::curl('b2_list_buckets', 'POST', ['accountId' => parent::get_option('b2')['accountId']])->buckets;
      if(!$_buckets_array) return array();

      $_buckets = array();
      foreach($_buckets_array as $_bucket) {
        if($_bucket->bucketType == 'allPublic') $_buckets[$_bucket->bucketId.':'.$_bucket->bucketName] = $_bucket->bucketName;
      }

      return $_buckets;
    }

    private function plugin_settings_link($links) {
      $settings_link = array('<a href="'.admin_url('options-general.php?page=crbn-backblaze-b2-media-offloader.php').'">Settings</a>');
      //array_unshift($links, $settings_link);
      //array_merge($links, $settings_link);
      return array_merge($links, $settings_link);
    }

}
