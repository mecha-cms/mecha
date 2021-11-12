<?php

final class Server extends Genome {

    public static function __callStatic(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        return $_SERVER[strtr(strtoupper($kin), '-', '_')] ?? null;
    }

    public static function get($key = null) {
        return e(isset($key) ? get($_SERVER, strtr(strtoupper($key), '-', '_')) : ($_SERVER ?? []));
    }

    public static function let($key = null) {
        $k = strtoupper(static::class);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let($_SERVER, strtr(strtoupper($key), '-', '_'));
        } else {
            $_SERVER = [];
        }
    }

    public static function set(string $key, $value) {
        set($_SERVER, strtr(strtoupper($key), '-', '_'), $value);
    }

}