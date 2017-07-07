<?php

abstract class Genome {

    // Instance(s)…
    public static $__instance__ = [];

    // Instance counter
    public function __construct() {
        self::$__instance__[] = $this;
    }

    // Static method name’s suffix
    public static $_suf = '_';

    // Method(s)…
    public static $_ = [];

    // Show the added method(s)
    public static function kin($kin = null, $fail = false, $origin = false) {
        $c = static::class;
        if (isset($kin)) {
            $kin .= self::$_suf;
            if (!isset(self::$_[0][$c][$kin])) {
                $output = isset(self::$_[1][$c][$kin]) ? self::$_[1][$c][$kin] : $fail;
                return $origin && method_exists($c, $kin) ? 1 : $output;
            }
            return $fail;
        }
        return !empty(self::$_[1][$c]) ? self::$_[1][$c] : $fail;
    }

    // Add new method with `Genome::plug('foo')`
    public static function plug($kin, $fn) {
        self::$_[1][static::class][$kin . self::$_suf] = $fn;
        return true;
    }

    // Remove the added method with `Genome::eject('foo')`
    public static function eject($kin = null) {
        if (isset($kin)) {
            $c = static::class;
            $kin .= self::$_suf;
            self::$_[0][$c][$kin] = 1;
            unset(self::$_[1][$c][$kin]);
        } else {
            self::$_ = [];
        }
        return true;
    }

    // Call the added method with `Genome::foo()`
    public static function __callStatic($kin, $lot) {
        $c = static::class;
        $k = $kin . self::$_suf;
        if (method_exists($c, $k)) {
            return call_user_func_array('self::' . $k, $lot);
        }
        if (!isset(self::$_[1][$c][$k]) && defined('DEBUG') && DEBUG) {
            echo '<p>Method <code>' . $c . '::' . $kin . '()</code> does not exist.</p>';
            return false;
        }
        return call_user_func_array(self::$_[1][$c][$k], $lot);
    }

}