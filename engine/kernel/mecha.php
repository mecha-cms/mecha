<?php

class Mecha extends Genome {

    // Current version
    const version = '2.0.0';

    // Compare with current version
    public static function version($v = null, $c = null) {
        if (!isset($c)) {
            $c = self::version;
        }
        if (!isset($v)) {
            return $c;
        }
        $v = explode(' ', $v);
        if (count($v) === 1) {
            array_unshift($v, '=');
        }
        return version_compare($c, $v[1], $v[0]);
    }

}