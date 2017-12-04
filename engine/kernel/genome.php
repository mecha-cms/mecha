<?php

abstract class Genome {

    // Instance(s)…
    public static $__instance__ = [];

    // Method(s)…
    public static $_ = [];

    // Set, get, reset…
    public static function _(...$lot) {
        $c = static::class;
        if (count($lot) === 0) {
            return isset(self::$_[$c]) ? self::$_[$c] : [];
        } else if (count($lot) === 1) {
            return isset(self::$_[$c][$lot[0]]) ? self::$_[$c][$lot[0]] : false;
        } else if ($lot[1] === null) {
            unset(self::$_[$c][$lot[0]]);
            return true;
        }
        self::$_[$c][$lot[0]] = $lot[1];
        return true;
    }

    // Count the instance with `count(Genome::$__instance__)`
    public function __construct() {
        self::$__instance__[] = $this;
    }

    // Call the added method with `$genome->foo()`
    public function __call($kin, $lot = []) {
        $c = static::class;
        if (!empty(self::$_[$c][$kin]) && is_callable(self::$_[$c][$kin])) {
            return call_user_func_array(\Closure::bind(self::$_[$c][$kin], $this), $lot);
        } else if (defined('DEBUG') && DEBUG) {
            echo '<p>Method <code>-&gt;' . $kin . '()</code> does not exist.</p>';
        }
        return false;
    }

    // Call the added method with `Genome::foo()`
    public static function __callStatic($kin, $lot = []) {
        $c = static::class;
        if (!empty(self::$_[$c][$kin]) && is_callable(self::$_[$c][$kin])) {
            return call_user_func_array(self::$_[$c][$kin], $lot);
        } else if (defined('DEBUG') && DEBUG) {
            echo '<p>Method <code>' . $c . '::' . $kin . '()</code> does not exist.</p>';
        }
        return false;
    }

}