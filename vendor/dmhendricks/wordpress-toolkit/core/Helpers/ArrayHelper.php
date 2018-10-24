<?php
namespace WordPress_ToolKit\Helpers;
use WordPress_ToolKit\ToolKit;

/**
  * A class with various static functions to manipulate arrays
  *
  * @since 0.1.0
  */
class ArrayHelper extends ToolKit
{

  /**
    * Combine function attributes with known attributes and fill in defaults when needed.
    *
    * @param array  $pairs     Entire list of supported attributes and their defaults.
    * @param array  $atts      User defined attributes in shortcode tag.
    * @return array Combined and filtered attribute list.
    * @since 0.1.0
    */
  public static function set_default_atts( $pairs, $atts ) {

    $atts = (array)$atts;
    $result = array();

    foreach ($pairs as $name => $default) {
      if ( array_key_exists($name, $atts) ) {
        $result[$name] = $atts[$name];
      } else {
        $result[$name] = $default;
      }
    }

    return $result;

  }

}
