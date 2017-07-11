<?php
namespace TwoLabNet\BackblazeB2;

class Core extends Plugin {

  function __construct() {

    // Rage against Automattic's war on SVG
    add_filter('upload_mimes', array($this, 'add_svg_to_mime_types'));

    // Add WordPress pre-upload filters
    add_filter( 'upload_dir', array($this, 'upload_dir_handler') );
    add_filter( 'wp_handle_upload_prefilter', array($this, 'upload_prefilter_handler') );

  }

  public function upload_prefilter_handler($file) {

    // Determine if MIME type should be processed
    $filter_type = carbon_get_theme_option(self::$prefix.'filter_mime_type');
    $mime_types = carbon_get_theme_option(self::$prefix.'mime_types');
    if(in_array($filter_type, array('include', 'exclude'))) {
      $in_filter_list = in_array($file['type'], $mime_types);
      if(($filter_type == 'exclude' && $in_filter_list) || ($filter_type == 'include' && !$in_filter_list)) return $file;
    }

    // Authenticate
    B2::auth();

    // Update file download path
    $_new_media_file_url = $this->create_media_url();
    add_filter( 'pre_option_upload_url_path', function() {
      return $_new_media_file_url;
    });

    // Get Backblack B2 upload URL, if not set
    if(!isset(self::$settings['b2']['uploadUrl'])) {
      $_bucket_id = explode(':', carbon_get_theme_option(self::$prefix.'bucket_id'))[0];
      $_upload_creds = B2::curl('b2_get_upload_url', 'POST', ['bucketId' => $_bucket_id]); // Verify what auth token should be
      self::$settings['b2']['uploadUrl'] = $_upload_creds->uploadUrl;
      self::$settings['b2']['uploadAuthorizationToken'] = $_upload_creds->authorizationToken;
    }

    // Upload file to B2 Bucket
    $_folder = carbon_get_theme_option(self::$prefix.'path');
    if(strpos($_folder, '/') !== 0) $_folder = trim($_folder, '/').'/';
    if(substr($_folder, strlen($_folder)-2, 1) == '/') $_folder .= substr($_folder, 0, strlen($_folder)-2);

    $handle = fopen($file['tmp_name'], 'r');
    $read_file = fread($handle,filesize($file['tmp_name']));
    $headers = array(
      'Authorization: ' . self::$settings['b2']['uploadAuthorizationToken'],
      'X-Bz-File-Name: ' . $_folder . $file['name'],
      'Content-Type: ' . $file['type'],
      'X-Bz-Content-Sha1: ' . sha1_file($file['tmp_name'])
    );
    $result = B2::curl('b2_upload_file', 'POST', array(), $headers, self::$settings['b2']['uploadUrl'], $read_file);

    //wp_mail('hendridm@gmail.com', 'file array', print_r($file, true));

    return $file;
  }

  public function upload_dir_handler($file) {
    $new_file = $file;
    $new_file['url'] = $this->create_media_url();
    return $new_file;
  }

  public function add_svg_to_mime_types($mimes) {
    $mimes = array_merge(array('svg|svgz' => 'image/svg+xml'), $mimes);
    return $mimes;
  }

  private function create_media_url() {
    // TODO: remove @
    $_url = @self::$settings['b2']['downloadUrl'].'/file/';

    // Get bucket path
    $_bucket = carbon_get_theme_option(self::$prefix.'bucket_id');
    if(!$_bucket) return;
    $_bucket = explode(':', $_bucket);

    $_folder = carbon_get_theme_option(self::$prefix.'path');
    if(strpos($_folder, '/') !== 0) $_folder = '/'.trim($_folder, '/');
    if(substr($_folder, strlen($_folder)-2, 1) == '/') $_folder .= substr($_folder, 0, strlen($_folder)-2);

    return $_url . $_bucket[1] . $_folder;
  }

}
