<?php

class Base {

    protected static $o = array();

    // Show the added method(s)
    public static function kin($kin = null, $fallback = false) {
        $c = get_called_class();
        if( ! is_null($kin)) {
            return isset(self::$o[$c][$kin]) ? self::$o[$c][$kin] : $fallback;
        }
        return ! empty(self::$o[$c]) ? self::$o[$c] : $fallback;
    }

    // Add new method with `Base::plug('foo')`
    public static function plug($kin, $action) {
        self::$o[get_called_class()][$kin] = $action;
    }

    // Call the added method with `Base::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        $c = get_called_class();
        if( ! isset(self::$o[$c][$kin])) {
            Guardian::abort('Method <code>' . $c . '::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array(self::$o[$c][$kin], $arguments);
    }

}