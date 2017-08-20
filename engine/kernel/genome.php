<?php

abstract class Genome {

    // Instance(s)…
    public static $__instance__ = [];

    // Instance counter
    public function __construct() {
        self::$__instance__[] = $this;
    }

    // Method(s)…
    public static $_ = [];

    // Show the added method(s)
    public static function kin($kin = null, $fail = false, $origin = false) {
        $c = static::class;
        if (isset($kin)) {
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
        self::$_[1][static::class][$kin] = $fn;
        return true;
    }

    // Remove the added method with `Genome::eject('foo')`
    public static function eject($kin = null) {
        if (isset($kin)) {
            $c = static::class;
            self::$_[0][$c][$kin] = 1;
            unset(self::$_[1][$c][$kin]);
        } else {
            self::$_ = [];
        }
        return true;
    }

    // Call the added method with `$instance->foo()`
    public function __call($kin, $lot = []) {
        $c = static::class;
        if (!isset(self::$_[1][$c][$kin]) && defined('DEBUG') && DEBUG) {
            echo '<p>Method <code>-&gt;' . $kin . '()</code> does not exist.</p>';
            return false;
        }
        return call_user_func_array(Closure::bind(self::$_[1][$c][$kin], $this), $lot);
    }

    // Call the added method with `Genome::foo()`
    public static function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (!isset(self::$_[1][$c][$kin]) && defined('DEBUG') && DEBUG) {
            echo '<p>Method <code>' . $c . '::' . $kin . '()</code> does not exist.</p>';
            return false;
        }
        return call_user_func_array(self::$_[1][$c][$kin], (array) $lot);
    }

}