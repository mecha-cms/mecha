<?php

/**
 * =============================================================
 *  SUCH MECHA . VERY ARRAY . MANY FUNCTIONS
 * =============================================================
 */

class Mecha {

    private static $stomach = array();

    /**
     * Convert Array into Object
     * -------------------------
     */

    public static function O($array) {
        return is_array($array) ? (object) array_map('self::O', $array) : $array;
    }

    /**
     * Convert Object into Array
     * -------------------------
     */

    public static function A($object) {
        return is_object($object) ? array_map('self::A', (array) $object) : $object;
    }

    /**
     * Set Array Value Recursively
     * ---------------------------
     */

    public static function SVR(&$array, $segments, $value = "") {
        $segments = explode('.', $segments);
        while(count($segments) > 1) {
            $segment = array_shift($segments);
            if( ! array_key_exists($segment, $array)) {
                $array[$segment] = array();
            }
            $array =& $array[$segment];
        }
        $array[array_shift($segments)] = $value;
    }

    /**
     * Get Array Value Recursively
     * ---------------------------
     */

    public static function GVR(&$array, $segments = null, $fallback = false) {
        if(is_null($segments)) {
            return $array;
        }
        foreach(explode('.', $segments) as $segment) {
            if( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return $fallback;
            }
            $array =& $array[$segment];
        }
        return $array;
    }

    /**
     * Unset Array Value Recursively
     * -----------------------------
     */

    public static function UVR(&$array, $segments) {
        $segments = explode('.', $segments);
        while(count($segments) > 1) {
            $segment = array_shift($segments);
            if(array_key_exists($segment, $array)) {
                $array =& $array[$segment];
            }
        }
        if(is_array($array) && array_key_exists($segment = array_shift($segments), $array)) {
            unset($array[$segment]);
        }
    }

    /**
     * Initialize with Eating
     * ----------------------
     */

    public static function eat($array) {
        self::$stomach = $array;
        return new static;
    }

    /**
     * Sort Array Based on Its Value's Key
     * -----------------------------------
     */

    public static function order($order = 'ASC', $key = null) {
        if(is_null($key)) {
            if($order == 'ASC') {
                asort(self::$stomach);
            } else {
                arsort(self::$stomach);
            }
        } else {
            $before = array();
            $after = array();
            if(self::$stomach && ! empty(self::$stomach)) {
                foreach(self::$stomach as $k => $v) {
                    $before[$k] = strtolower($v[$key]);
                }
                if ($order == 'ASC') {
                    asort($before);
                } else {
                    arsort($before);
                }
                foreach($before as $k => $v) {
                    $after[] = self::$stomach[$k];
                }
            }
            self::$stomach = $after;
        }
        return new static;
    }

    /**
     * Array Shake
     * -----------
     */

    public static function shake() {
        shuffle(self::$stomach);
        return new static;
    }

    /**
     * Vomit! BLARGH!
     * --------------
     */

    public static function vomit($param = null, $fallback = false) {
        $array = self::$stomach;
        self::$stomach = array();
        return self::GVR($array, $param, $fallback);
    }

    /**
     * Generate Chunks of Array
     * ------------------------
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