<?php

class __ {

    public static $_ = array();
    public static $_x = array();

    // Show the added method(s)
    public static function kin($kin = null, $fallback = false, $origin = false) {
        $c = get_called_class();
        if( ! is_null($kin)) {
            if( ! isset(self::$_x[$c][$kin])) {
                $output = isset(self::$_[$c][$kin]) ? self::$_[$c][$kin] : $fallback;
                return $origin && is_callable($c . '::' . $kin) ? 1 : $output;
            }
            return $fallback;
        }
        if($kin === true) {
            return ! empty(self::$_) ? self::$_ : $fallback;
        }
        return ! empty(self::$_[$c]) ? self::$_[$c] : $fallback;
    }

    // Add new method with `__::plug('foo')`
    public static function plug($kin, $action) {
        self::$_[get_called_class()][$kin] = $action;
    }

    // Remove the added method with `__::unplug('foo')`
    public static function unplug($kin) {
        if($kin === true) {
            self::$_ = self::$_x = array();
        } else {
            $c = get_called_class();
            self::$_x[$c][$kin] = 1;
            unset(self::$_[$c][$kin]);
        }
    }

    // Call the added method with `__::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        $c = get_called_class();
        if( ! isset(self::$_[$c][$kin])) {
            Guardian::abort('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array(self::$_[$c][$kin], $arguments);
    }

}