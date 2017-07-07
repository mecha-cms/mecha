<?php

class Cookie extends Genome {

    public static function set($key, $value = "", $config = []) {
        if (is_numeric($config)) {
            $config = ['expire' => (int) $config];
        }
        $cc = [
            'expire' => 1,
            'path' => '/',
            'domain' => "",
            'secure' => false,
            'http_only' => false
        ];
        Anemon::extend($cc, $config);
        $cc = array_values($cc);
        $cc[0] = time() + 60 * 60 * 24 * $cc[0]; // 1 day
        $key = '_' . md5($key);
        $value = self::x($value);
        $_COOKIE[$key] = $value;
        array_unshift($cc, $key, $value);
        call_user_func_array('setcookie', $cc);
        return new static;
    }

    public static function get($key = null, $fail = null) {
        $c = e($_COOKIE);
        if (!isset($key)) {
            $o = [];
            foreach ($_COOKIE as $k => $v) {
                $o[$k] = self::v($v);
            }
            return $o;
        }
        $key = '_' . md5($key);
        return isset($c[$key]) ? self::v($c[$key]) : $fail;
    }

    public static function reset($key = null) {
        if (!isset($key)) {
            $_COOKIE = [];
            foreach (explode(';', isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : "") as $v) {
                $c = explode('=', $v, 2);
                $n = trim($c[0]);
                setcookie($n, null, -1);
                setcookie($n, null, -1, '/');
            }
            return new static;
        }
        $key = '_' . md5($key);
        unset($_COOKIE[$key]);
        setcookie($key, null, -1);
        setcookie($key, null, -1, '/');
        return new static;
    }

    private static function x($input) {
        return __c2f__(static::class) . ':' . base64_encode(json_encode($input));
    }

    private static function v($input) {
        if (strpos($input, __c2f__(static::class) . ':') === 0) {
            return json_decode(base64_decode(explode(':', $input, 2)[1]), true);
        }
        return $input;
    }

}