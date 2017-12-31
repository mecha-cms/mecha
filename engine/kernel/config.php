<?php

class Config extends Genome {

    protected static $bucket = [];

    public static function ignite(...$lot) {
        return (self::$bucket = State::config());
    }

    public static function set($key, $value = null) {
        if (__is_anemon__($key)) $key = a($key);
        if (__is_anemon__($value)) $value = a($value);
        $cargo = [];
        if (!is_array($key)) {
            Anemon::set($cargo, $key, $value);
        } else {
            foreach ($key as $k => $v) {
                Anemon::set($cargo, $k, $v);
            }
        }
        self::$bucket = array_replace_recursive(self::$bucket, $cargo);
        return new static;
    }

    public static function get($key = null, $fail = false) {
        if (!isset($key)) {
            return !empty(self::$bucket) ? o(self::$bucket) : $fail;
        }
        if (__is_anemon__($key)) {
            $output = [];
            foreach ($key as $k => $v) {
                $output[$k] = self::get($k, $v);
            }
            return o($output);
        }
        return o(Anemon::get(self::$bucket, $key, $fail));
    }

    public static function reset($key = null) {
        if (isset($key)) {
            foreach ((array) $key as $value) {
                Anemon::reset(self::$bucket, $value);
            }
        } else {
            self::$bucket = [];
        }
        return new static;
    }

    public static function extend(...$lot) {
        self::set(...$lot);
        self::$bucket = array_replace_recursive(self::$bucket, State::config());
        return new static;
    }

    public static function __callStatic($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $fail = false;
        if (count($lot)) {
            $kin .= '.' . array_shift($lot);
            $fail = array_shift($lot) ?: false;
        }
        return self::get($kin, $fail);
    }

    public function __call($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__call($kin, $lot);
        }
        $fail = false;
        if ($count = count($lot)) {
            if ($count > 1) {
                $kin = $kin . '.' . array_shift($lot);
            }
            $fail = array_shift($lot) ?: false;
            $fail_alt = array_shift($lot) ?: false;
        }
        if ($fail instanceof \Closure) {
            return call_user_func(\Closure::bind($fail, $this), self::get($kin, $fail_alt));
        }
        return self::get($kin, $fail);
    }

    public function __set($key, $value = null) {
        return self::set($key, $value);
    }

    public function __get($key) {
        return self::get($key, null);
    }

    // Fix case for `isset($config->key)` or `!empty($config->key)`
    public function __isset($key) {
        return !!self::get($key);
    }

    public function __unset($key) {
        self::reset($key);
    }

    public function __toString() {
        return To::yaml(self::get());
    }

    public function __invoke($fail = []) {
        return self::get(null, o($fail));
    }

}