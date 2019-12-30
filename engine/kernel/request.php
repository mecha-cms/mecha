<?php

final class Request extends Genome {

    public static function get($key = null) {
        return e(isset($key) ? get($_REQUEST, $key) : ($_REQUEST ?? []));
    }

    public static function is(string $name = null, string $key = null) {
        $r = strtoupper($_SERVER['REQUEST_METHOD']);
        if (isset($name)) {
            $name = strtoupper($name);
            if (isset($key)) {
                $a = $GLOBALS['_' . $name] ?? [];
                return null !== get($a, $key);
            }
            return $name === $r;
        }
        return ucfirst(strtolower($r));
    }

    public static function let($key = null) {
        $k = strtoupper(static::class);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let($_REQUEST, $key);
        } else {
            $_REQUEST = [];
        }
    }

    public static function set(string $key, $value) {
        set($_REQUEST, $key, $value);
    }

}