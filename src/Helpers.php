<?php
namespace TwoLabNet\BackblazeB2;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Helpers extends Plugin {

    public function __construct() { }

    public static function current_admin_page() {
      return isset($_GET['page']) ? $_GET['page'] : null;
    }

    public static function array_merge_recursive_distinct( array &$array1, array &$array2 ) {
      // Attribution: http://php.net/manual/en/function.array-merge-recursive.php#92195
      $merged = $array1;

      foreach ( $array2 as $key => &$value )
      {
        if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
          $merged [$key] = self::array_merge_recursive_distinct ( $merged [$key], $value );
        } else {
          $merged [$key] = $value;
        }
      }

      return $merged;
    }

    public static function show_notice($msg, $type = 'error', $is_dismissible = false) {
      $class = 'notice notice-'.$type.($is_dismissible ? ' is-dismissible' : '');
      $msg = __( $msg, parent::get_option('data')['TextDomain'] );

      printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $msg );
    }

}
