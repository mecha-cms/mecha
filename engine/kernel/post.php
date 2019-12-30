<?php

final class Post extends Genome {

    public static function get($key = null) {
        return e(isset($key) ? get($_POST, $key) : ($_POST ?? []));
    }

    public static function let($key = null) {
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let($_POST, $key);
        } else {
            $_POST = [];
        }
    }

    public static function set(string $key, $value) {
        set($_POST, $key, $value);
    }

}