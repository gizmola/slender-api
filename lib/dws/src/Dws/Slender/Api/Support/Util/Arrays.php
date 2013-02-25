<?php

namespace Dws\Slender\Api\Support\Util;

/**
 * Array utilities
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class Arrays
{
    /**
     * @see: http://www.php.net/manual/en/function.array-merge-recursive.php#92195
     * @param array $array1
     * @param array $array2
     * @return type
     */
    public static function merge_recursive_distinct ( array &$array1, array &$array2 )
    {
      $merged = $array1;

      foreach ( $array2 as $key => &$value )
      {
        if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
        {
          $merged [$key] = self::merge_recursive_distinct ( $merged [$key], $value );
        }
        else
        {
          $merged [$key] = $value;
        }
      }

      return $merged;
    }
}
