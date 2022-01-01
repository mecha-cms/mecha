<?php

abstract class Genome {

    // This property is supposed to be private, but who knows someone might need it, for example to check the data
    // outside of the extended class. So I set this as a public property. Just don’t write it down in the manual.
    // This property could change at any time.
    public static $_ = [];

    public function __call(string $kin, array $lot = []) {
        $c = static::class;
        $m = '_' . $kin . '_';
        $this->_call = T_OBJECT_OPERATOR;
        if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
            $a = self::$_[$c][$kin];
            if (is_callable($a[0])) {
                // Alter default function argument(s)
                if (isset($a[1])) {
                    $lot = array_replace((array) $a[1], $lot);
                }
                // Limit function argument(s)
                if (isset($a[2])) {
                    $lot = array_slice($lot, 0, $a[2]);
                }
                return fire($a[0], $lot, $this/*, $c */);
            }
            return $a[0];
        } else if (method_exists($this, $m) && (new \ReflectionMethod($this, $m))->isProtected()) {
            return $this->{$m}(...$lot);
        } else if (defined('DEBUG') && DEBUG) {
            throw new \BadMethodCallException('Method $' . c2f($c, '_', '/') . '->' . $kin . '() does not exist.');
        }
    }

    public static function _(...$lot) {
        $c = static::class;
        // Get
        if (0 === count($lot)) {
            return self::$_[$c] ?? [];
        }
        // Get
        if (1 === count($lot)) {
            return self::$_[$c][$lot[0]] ?? null;
        }
        // Let
        if (null === $lot[1]) {
            unset(self::$_[$c][$lot[0]]);
        // Set
        } else {
            self::$_[$c][$lot[0]] = (array) $lot[1];
        }
    }

    public static function __callStatic(string $kin, array $lot = []) {
        $c = static::class;
        $m = '_' . $kin . '_';
        $that = new static;
        $that->_call = T_DOUBLE_COLON;
        if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
            $a = self::$_[$c][$kin];
            if (is_callable($a[0])) {
                // Alter default function argument(s)
                if (isset($a[1])) {
                    $lot = array_replace((array) $a[1], $lot);
                }
                // Limit function argument(s)
                if (isset($a[2])) {
                    $lot = array_slice($lot, 0, $a[2]);
                }
                return fire($a[0], $lot, $that/*, $c */);
            }
            return $a[0];
        } else if (method_exists($that, $m) && (new \ReflectionMethod($that, $m))->isProtected()) {
            return $that->{$m}(...$lot);
        } else if (defined('DEBUG') && DEBUG) {
            throw new \BadMethodCallException('Method ' . $c . '::' . $kin . '() does not exist.');
        }
    }

}