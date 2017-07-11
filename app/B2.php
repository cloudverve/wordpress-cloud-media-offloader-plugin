<?php
namespace TwoLabNet\BackblazeB2;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class B2 extends Plugin {

  public static function auth() {

    // If not, get a new authorization token
    $_auth = (array)self::curl('b2_authorize_account');
    if(isset($_auth['apiUrl'])) $_auth['apiUrl'] = $_auth['apiUrl'] . '/b2api/v1/';

    // Display error if not authenticated
    if(self::$b2_account_id && !array_key_exists('authorizationToken', $_auth)) {
      add_action( 'admin_notices', function() {
        Helpers::show_notice(__('<strong>Error:</strong> Authentication to Backblaze B2 failed.'), 'error', true);
      });

    }

    // Add results to session configuration
    self::$settings['b2'] = Helpers::array_merge_recursive_distinct(self::$settings['b2'], $_auth);

    // Update cache
    $_auth_cache = $_auth;
    unset($_auth_cache['authorizationToken'], $_auth_cache['minimumPartSize']);
    update_option(self::$prefix.'auth_cache', $_auth_cache, 'yes');

    return $_auth;
  }

  public static function curl($endpoint, $action = 'GET', $data = array(), $headers = null, $_api_url = null, $post_fields = null) {
    // Reference: http://php.net/manual/en/function.curl-setopt.php
    $_api_url = $_api_url ? $_api_url : self::$settings['b2']['apiUrl'] . $endpoint;

    //echo $action.' - '; print_r($data);
    $_token = @self::$settings['b2']['authorizationToken'];

    $ch = curl_init($_api_url);

    if(!$headers) {
      $headers = array(
        'Accept: application/json',
        'Authorization: ' . ($_token ? $_token : 'Basic ' . self::$settings['b2']['credentials'])
      );
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if($post_fields) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    }

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result);

  }

}
