<?php

class Base {

    protected static $o = array();

    // Show the added method(s)
    public static function kin($kin = null, $fallback = false) {
        if( ! is_null($kin)) {
            return isset(self::$o[get_called_class()][$kin]) ? self::$o[get_called_class()][$kin] : $fallback;
        }
        return ! empty(self::$o[get_called_class()]) ? self::$o[get_called_class()] : $fallback;
    }

    // Add new method with `Base::plug('foo')`
    public static function plug($kin, $action) {
        self::$o[get_called_class()][$kin] = $action;
    }

    // Call the added method with `Base::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        if( ! isset(self::$o[get_called_class()][$kin])) {
            Guardian::abort('Method <code>' . get_called_class() . '::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array(self::$o[get_called_class()][$kin], $arguments);
    }

}