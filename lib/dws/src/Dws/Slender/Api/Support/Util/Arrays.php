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

    /**
     *
     * @param array $array
     * @param array $remove
     */
    public static function array_unset_recursive(&$array, $remove)
    {
        if (!is_array($remove)) {
            $remove = array($remove);
        }
        foreach ($array as $key => &$value) {
            if (in_array($value, $remove)) {
                unset($array[$key]);
            } else if (is_array($value)) {
                self::array_unset_recursive($value, $remove);
            }
        }
    }

    /**
     * Given: an array of keys ['key1', 'key2', ..., 'keyN'],
     * and a leaf value $leafValue, create an array $tree with:
     *
     * $tree ['key1']['key2']...['keyN'] = $leafValue
     *
     * @param array $tree
     * @param array $keys
     * @param mixed $leafValue
     * @return true
     */
    public static function setValueAsLeafViaPathKeys($pathKeys, $leafValue)
    {
        $return  = [];
        if (empty($pathKeys)) {
            return $leafValue;
        }
        $key = array_shift($pathKeys);
        $return[$key] = self::setValueAsLeafViaPathKeys($pathKeys, $leafValue);
        return $return;
    }

    static function deep_ksort(&$arr) {
        ksort($arr);
        foreach ($arr as &$a) {
            if (is_array($a) && !empty($a)) {
                self::deep_ksort($a);
            }
        }
    }

    /**
     * Shift a value off the given array using a key
     *
     * @param array $data
     * @return mixed|null
     */
    public static function shiftByKey(&$data, $key = '_id')
    {
        $key = (string) $key;

        if (isset($data[$key])) {
            $id = $data[$key];
            unset($data[$key]);
            return $id;
        } else {
            return null;
        }
    }

    /**
     * Shift a value off the gibven array using key _id
     * 
     * @param type $data
     * @return type
     */
    public static function shiftId(&$data)
    {
        return self::shiftByKey($data, '_id');
    }
}
