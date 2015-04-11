<?php

class Plugger {

    protected static $o = array();

    // Show the added method(s)
    public static function kin($kin = null, $fallback = false) {
        $kin = ! is_null($kin) ? get_called_class() . '::' . $kin : false;
        if( ! $kin) {
            return ! empty(self::$o) ? self::$o : $fallback;
        }
        return isset(self::$o[$kin]) ? self::$o[$kin] : $fallback;
    }

    // Add new method with `Plugger::plug('foo')`
    public static function plug($kin, $action) {
        self::$o[get_called_class() . '::' . $kin] = $action;
    }

    // Call the added method with `Plugger::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        $kin = get_called_class() . '::' . $kin;
        if( ! isset(self::$o[$kin])) {
            Guardian::abort('Method <code>' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array(self::$o[$kin], $arguments);
    }

}