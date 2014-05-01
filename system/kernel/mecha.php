<?php

/**
 * ===========================================================
 *  SUCH MECHA . VERY ARRAY . MANY FUNCTIONS
 * ===========================================================
 *
 * -- CODE: --------------------------------------------------
 *
 *    // Sort an array ascending by key: 'id'
 *    $test = Mecha::eat($array)->order('ASC', 'id')->vomit();
 *
 *    var_dump(test);
 *
 * ------------------------------------------------------------
 *
 */

class Mecha {

    private static $stomach = array();

    /**
     * Convert array into object
     */
    public function O($array) {
        return is_array($array) ? (object) array_map('self::O', $array) : $array;
    }

    /**
     * Convert object into array
     */
    public function A($object) {
        return is_object($object) ? array_map('self::A', (array) $object) : $object;
    }

    /**
     * Sort array based on its key value
     */
    private static function sabikv($order = 'ASC', $key = null, $array) {

        /**
         * Inline array
         */
        if(is_null($key)) {
            if($order == 'ASC') {
                asort($array);
            } else {
                arsort($array);
            }
            return $array;

        /**
         * Multidimensional array
         */
        } else {
            if(count($array) > 0 || ! empty($array)) {
                foreach($array as $k => $v) {
                    $bucket[$k] = strtolower($v[$key]);
                }
                if ($order == 'ASC') {
                    asort($bucket);
                } else {
                    arsort($bucket);
                }
                foreach($bucket as $k => $v) {
                    $results[] = $array[$k];
                }
                return $results;
            }
        }

    }

    /**
     * Initialize with eating...
     */
    public static function eat($array) {
        self::$stomach = $array;
        return new static;
    }

    /**
     * Sort that array in our monster stomach
     */
    public static function order($order = 'ASC', $key = null) {
        self::$stomach = self::sabikv($order, $key, self::$stomach);
        return new static;
    }

    /**
     * Shake that array
     */
    public static function shake() {
        shuffle(self::$stomach);
        return new static;
    }

    /**
     * Then vomit it!
     */
    public static function vomit($param = null, $fallback = false) {
        $aa = self::$stomach;
        if(is_null($param)) {
            self::$stomach = array();
            return isset($aa) && ! empty($aa) ? $aa : $fallback;
        } else {

            /**
             * Support array childrens calls in dot notation styles
             * Something like => `Mecha::eat($array)->vomit('foo.bar');`
             */
            if(strpos($param, '.') !== false) {
                $segments = explode('.', $param);
                foreach($segments as $segment) {
                    $aa = $aa[$segment];
                }
                return isset($aa) && ! empty($aa) ? $aa : $fallback;
            }
            self::$stomach = array();
            return isset($aa[$param]) && ! empty($aa[$param]) ? $aa[$param] : $fallback;
        }
    }

    /**
     * Generate chunks of array
     */
    public static function chunk($index = 1, $count = 10) {
        if( ! self::$stomach) return new static;
        $results = array();
        $chunk = array_chunk(self::$stomach, $count, true);
        if(is_null($index)) {
            self::$stomach = isset($chunk) ? $chunk : false;
        } else {
            $chunk = isset($chunk[$index - 1]) ? $chunk[$index - 1] : false;
            self::$stomach = $chunk ? array_values($chunk) : false;
        }
        return new static;
    }

}