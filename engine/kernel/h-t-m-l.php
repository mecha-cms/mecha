<?php

class HTML {

    private static $lot;

    public static function __callStatic($kin, $lot = []) {
        if (!isset(self::$lot)) {
            self::$lot = new Union(Union::config['union'], __c2f__(static::class, '_'));
        }
        return call_user_func_array([self::$lot, $kin], $lot);
    }

}