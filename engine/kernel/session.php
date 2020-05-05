<?php

final class Session extends Genome {

    public static function get($key = null) {
        return isset($key) ? get($_SESSION, $key) : ($_SESSION ?? []);
    }

    public static function let($key = null) {
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key) && true !== $key) {
            let($_SESSION, $key);
        } else {
            $_SESSION = [];
            if (true === $key) {
                session_destroy();
            }
        }
    }

    public static function set(string $key, $value) {
        set($_SESSION, $key, $value);
    }

    public static function start(...$lot) {
        $path = $lot[0] ?? constant(u(static::class));
        if (isset($path)) {
            mkdir($path, 0755, true);
            session_save_path($path);
        }
        return !session_id() ? session_start() : true;
    }

}
