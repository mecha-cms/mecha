<?php

abstract class Genome {

    // This property is supposed to be private, but someone might need it in the future, for example to check the data
    // from outside of the extended class. So I set this as a public property. This property could change at any time.
    public static $_ = [];

    public function __call(string $kin, array $lot = []) {
        $this->_call = T_OBJECT_OPERATOR;
        foreach (array_merge([$n = static::class], array_slice(class_parents($n), 0, -1, false)) as $c) {
            if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
                $v = self::$_[$c][$kin];
                if (is_callable($v[0])) {
                    // Alter default function argument(s)
                    if (isset($v[1])) {
                        $lot = array_replace((array) $v[1], $lot);
                    }
                    // Limit function argument(s)
                    if (isset($v[2])) {
                        $lot = array_slice($lot, 0, $v[2]);
                    }
                    return fire($v[0], $lot, $this/*, $n */);
                }
                return $v[0];
            }
            if (defined('TEST') && TEST) {
                throw new \BadMethodCallException('Method $' . c2f($c, '_', '/') . '->' . $kin . '() does not exist.');
            }
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
        $that = new static;
        $that->_call = T_DOUBLE_COLON;
        foreach (array_merge([$n = static::class], array_slice(class_parents($n), 0, -1, false)) as $c) {
            if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
                $r = self::$_[$c][$kin];
                if (is_callable($r[0])) {
                    // Alter default function argument(s)
                    if (isset($r[1])) {
                        $lot = array_replace((array) $r[1], $lot);
                    }
                    // Limit function argument(s)
                    if (isset($r[2])) {
                        $lot = array_slice($lot, 0, $r[2]);
                    }
                    return fire($r[0], $lot, $that/*, $n */);
                }
                return $r[0];
            }
            if (defined('TEST') && TEST) {
                throw new \BadMethodCallException('Method ' . $c . '::' . $kin . '() does not exist.');
            }
        }
    }

}