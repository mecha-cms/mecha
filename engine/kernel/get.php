<?php

final class Get extends Genome {

    public function __construct() {
        // This was added only to remove the deprecation message: Methods with the same name as their class will not be constructors in a future version of PHP.
    }

    public static function get($key = null) {
        return e(isset($key) ? get($_GET, $key) : ($_GET ?? []));
    }

    public static function let($key = null) {
        $k = strtoupper(static::class);
        if (is_array($key)) {
            foreach ($key as $v) {
                self::let($v);
            }
        } else if (isset($key)) {
            let($_GET, $key);
        } else {
            $_GET = [];
        }
    }

    public static function set(string $key, $value) {
        set($_GET, $key, $value);
    }

}