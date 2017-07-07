<?php

class Lot extends Genome {

    public static function set($key, $value = null, $scope = null) {
        if (__is_anemon__($key)) {
            $scope = '.' . md5($value ?: static::class);
            foreach ($key as $k => $v) {
                $GLOBALS[$scope][$k] = $v;
            }
        } else {
            $GLOBALS['.' . md5($scope ?: static::class)][$key] = $value;
        }
        return new static;
    }

    public static function get($key = null, $fail = false, $scope = null) {
        $scope = '.' . md5($scope ?: static::class);
        $data = isset($GLOBALS[$scope]) ? $GLOBALS[$scope] : [];
        if (isset($key)) {
            return array_key_exists($key, $data) ? $data[$key] : $fail;
        }
        return isset($data) ? $data : $fail;
    }

    public static function reset($key = null, $scope = null) {
        $scope = '.' . md5($scope ?: static::class);
        if (isset($key)) {
            unset($GLOBALS[$scope][$key]);
        } else {
            $GLOBALS[$scope] = [];
        }
        return new static;
    }

}