<?php
namespace WordPress_ToolKit\Licensing;
use WordPress_ToolKit\ConfigRegistry;
use WordPress_ToolKit\Helpers;
use WordPress_ToolKit\ToolKit;

/**
  * A class to perform license code verification via the WHMCS Licensing Addon.
  *   Extended from WHMCS Licensing Addon - Integration Code Sample
  *   Requires the WHMCS Licensing Addon to be used.
  *
  * @link https://www.whmcs.com/software-licensing/
  * @see https://docs.whmcs.com/Licensing_Addon
  * @copyright WHMCS Limited (Original Integration Code Sample)
  * @license http://www.whmcs.com/license/ WHMCS EULA
  * @since 0.2.0
  */
class WHMCS_License extends ToolKit
{

  /**
   * Validate user-provided license key
   *
   * @param string $key License key to validate
   * @param string $local_key The locally-stored key from previous check, used
   *    to stop the license checking code having to call your server on every
   *    page load.
   * @return array Result from validation attempt
   * @see https://docs.whmcs.com/Licensing_Addon#What_is_the_local_key.3F
   * @since 0.2.0
   */
  public function validate( $license_key, $local_key = '' )
  {

    // Set validation variables
    $check_token = time() . md5( mt_rand( 1000000000, 9999999999 ) . $license_key );
    $checkdate = date( 'Ymd' );
    $domain = $_SERVER['SERVER_NAME'];
    $usersip = isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
    $dirpath = $this->get_config( 'plugin/path' );
    $verifyfilepath = 'modules/servers/licensing/verify.php';
    $local_keyvalid = false;

    // Process local key, if set
    if( $local_key ) {

      $local_key = str_replace( "\n", '', $local_key ); # Remove the line breaks
      $localdata = substr( $local_key, 0, strlen( $local_key ) - 32 ); # Extract License Data
      $md5hash = substr( $local_key, strlen( $local_key ) - 32 ); # Extract MD5 Hash

      if( $md5hash == md5( $localdata . $this->get_config( 'whmcs/product_key' ) ) ) {

        $localdata = strrev( $localdata ); # Reverse the string
        $md5hash = substr( $localdata, 0, 32 ); # Extract MD5 Hash
        $localdata = substr( $localdata, 32 ); # Extract License Data
        $localdata = base64_decode( $localdata );
        $local_keyresults = unserialize( $localdata );
        $originalcheckdate = $local_keyresults['checkdate'];

        if( $md5hash == md5( $originalcheckdate . $this->get_config( 'whmcs/product_key' ) ) ) {
          $localexpiry = date( 'Ymd', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $this->get_config( 'whmcs/local_key_expire_days' ), date( 'Y' ) ) );

          if( $originalcheckdate > $localexpiry ) {
            $local_keyvalid = true;
            $results = $local_keyresults;
            $validdomains = explode( ',', $results['validdomain'] );

            if( !in_array( $_SERVER['SERVER_NAME'], $validdomains ) ) {
              $local_keyvalid = false;
              $local_keyresults['status'] = 'Invalid';
              $results = array();
            }

            if( isset( $results['validip'] ) && !in_array( $usersip, explode( ',', $results['validip'] ) ) ) {
              $local_keyvalid = false;
              $local_keyresults['status'] = 'Invalid';
              $results = array();
            }

            if( isset( $results['validdirectory'] ) && !in_array( $dirpath, explode( ',', $results['validdirectory'] ) ) ) {
              $local_keyvalid = false;
              $local_keyresults['status'] = 'Invalid';
              $results = array();
            }
          }
        }
      }
    }

    // If local key is invalid or expired, recheck
    if( !$local_keyvalid ) {

      $responseCode = 0;
      $postfields = array(
        'licensekey' => $license_key,
        'domain' => $domain,
        'ip' => $usersip,
        'dir' => $dirpath,
      );

      if( $check_token ) $postfields['check_token'] = $check_token;
      $query_string = '';
      foreach ( $postfields as $k => $v ) {
        $query_string .= $k.'='.urlencode($v).'&';
      }

      if( function_exists( 'curl_exec' ) ) {

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $this->get_config( 'whmcs/url' ) . $verifyfilepath );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $data = curl_exec( $ch );
        $responseCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

      } else {

        $responseCodePattern = '/^HTTP\/\d+\.\d+\s+(\d+)/';
        $fp = @fsockopen( $this->get_config( 'whmcs/url' ), 80, $errno, $errstr, 5 );

        if ( $fp ) {

          $newlinefeed = "\r\n";
          $header = 'POST ' . $this->get_config( 'whmcs/url' ) . $verifyfilepath . ' HTTP/1.0' . $newlinefeed;
          $header .= 'Host: ' . $this->get_config( 'whmcs/url' ) . $newlinefeed;
          $header .= 'Content-type: application/x-www-form-urlencoded' . $newlinefeed;
          $header .= 'Content-length: ' . @strlen($query_string) . $newlinefeed;
          $header .= 'Connection: close' . $newlinefeed . $newlinefeed;
          $header .= $query_string;
          $data = $line = '';

          @stream_set_timeout( $fp, 20 );
          @fputs( $fp, $header );
          $status = @socket_get_status( $fp );

          while( !@feof( $fp ) && $status ) {
            $line = @fgets( $fp, 1024 );
            $patternMatches = array();
            if( !$responseCode && preg_match( $responseCodePattern, trim( $line ), $patternMatches ) ) {
              $responseCode = ( empty( $patternMatches[1] ) ) ? 0 : $patternMatches[1];
            }
            $data .= $line;
            $status = @socket_get_status( $fp );
          }
          @fclose ( $fp );
        }

      }

      if( $responseCode != 200 ) {
        $localexpiry = date( 'Ymd', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - ( $this->get_config( 'whmcs/local_key_expire_days' ) + $this->get_config( 'whmcs/allow_check_fail_days' ) ), date( 'Y' ) ) );

        if( isset( $originalcheckdate ) && $originalcheckdate > $localexpiry ) {
          $results = $local_keyresults;
        } else {
          $results = array();
          $results['status'] = 'Invalid';
          $results['description'] = 'Remote Check Failed';
          return $results;
        }

      } else {

        preg_match_all( '/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches );
        $results = array();
        foreach( $matches[1] as $k => $v ) {
          $results[$v] = $matches[2][$k];
        }

      }

      if( !is_array( $results ) ) {
        // TODO
        die( 'Invalid License Server Response' );
      }

      if( isset( $results['md5hash'] ) && $results['md5hash'] ) {
        if( $results['md5hash'] != md5( $this->get_config( 'whmcs/product_key' ) . $check_token ) ) {
          $results['status'] = 'Invalid';
          $results['description'] = 'MD5 Checksum Verification Failed';
          return $results;
        }
      }

      if ($results['status'] == 'Active') {

        $results['checkdate'] = $checkdate;
        $data_encoded = serialize( $results );
        $data_encoded = base64_encode( $data_encoded );
        $data_encoded = md5( $checkdate . $this->get_config( 'whmcs/product_key' ) ) . $data_encoded;
        $data_encoded = strrev( $data_encoded );
        $data_encoded = $data_encoded . md5( $data_encoded . $this->get_config( 'whmcs/product_key' ) );
        $data_encoded = wordwrap( $data_encoded, 80, "\n", true );
        $results['localkey'] = $data_encoded;

      }

      $results['remotecheck'] = true;

    }

    unset( $postfields, $data, $matches, $checkdate, $usersip, $md5hash );
    return $results;

  }

}
