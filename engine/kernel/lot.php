<?php

final class Lot extends Genome {

    public static function get($key = null) {
        if (isset($key)) {
            $key = strtoupper($key);
            $v = $_SERVER['HTTP_' . strtr($key, '-', '_')] ?? null;
            if (null === $v) {
                if (function_exists('apache_request_headers')) {
                    $v = array_change_key_case(apache_request_headers(), CASE_UPPER)[$key] ?? null;
                }
            }
            return e($v);
        }
        $out = [];
        if (function_exists('apache_request_headers')) {
            $out = e(array_change_key_case((array) apache_request_headers(), CASE_LOWER));
        }
        foreach ($_SERVER as $k => $v) {
            if (0 === strpos($k, 'HTTP_')) {
                $out[strtolower(strtr(substr($k, 5), '_', '-'))] = e($v);
            }
        }
        return $out;
    }

    public static function let($key = null) {
        if (isset($key)) {
            if (is_array($key)) {
                foreach ($key as $v) {
                    self::let($v);
                }
            } else {
                header_remove($key);
                unset($_SERVER['HTTP_' . strtr(strtoupper($key), '-', '_')]);
            }
        } else {
            header_remove();
            foreach ($_SERVER as $k => $v) {
                if (0 === strpos($k, 'HTTP_')) {
                    unset($_SERVER[$k]);
                }
            }
        }
    }

    public static function set($key = null, $value = null) {
        if (!isset($key)) {
            $out = [];
            if (function_exists('apache_response_headers')) {
                $out = e(array_change_key_case((array) apache_response_headers(), CASE_LOWER));
            }
            foreach (headers_list() as $v) {
                $v = explode(':', $v, 2);
                if (isset($v[1])) {
                    $out[strtolower($v[0])] = e(trim($v[1]));
                }
            }
            return $out;
        }
        if (is_string($key) && !isset($value)) {
            return self::set()[strtolower($key)] ?? null;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                header($k . ': ' . $v, true);
            }
        } else {
            header($key . ': ' . $value, true);
        }
    }

    public static function status(int $value = null) {
        if (isset($value)) {
            http_response_code($value);
        }
        return http_response_code();
    }

    public static function type(string $type = null, array $lot = []) {
        if (!isset($type)) {
            return self::get('content-type');
        }
        foreach ($lot as $k => $v) {
            $type .= '; ' . $k . '=' . $v;
        }
        self::set('content-type', $type);
    }

}
