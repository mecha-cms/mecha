<?php

final class Asset extends Genome {

    public static $lot = [];

    public static function URL(string $url) {
        $path = self::path($url);
        return isset($path) ? To::URL($path) : (false !== strpos($url, '://') || 0 === strpos($url, '//') ? $url : null);
    }

    public static function get(string $path = null, int $i = 1) {
        if (isset($path)) {
            $path = realpath($path) ?: $path;
            return self::$lot[$i]['.' . pathinfo($path, PATHINFO_EXTENSION)][$path] ?? null;
        }
        return self::$lot[$i] ?? [];
    }

    public static function join(string $x, string $join = "") {
        if ($v = self::_($x)) {
            $fn = $v[0];
            if (isset(self::$lot[1][$x])) {
                $assets = (new Anemon(self::$lot[1][$x]))->sort([1, 'stack'], true);
                $out = [];
                if (is_callable($fn)) {
                    foreach ($assets as $k => $v) {
                        $out[] = fire($fn, [$v, $k], static::class);
                    }
                } else {
                    foreach ($assets as $k => $v) {
                        if (isset($v['path']) && is_file($v['path'])) {
                            $out[] = file_get_contents($v['path']);
                        }
                    }
                }
                return implode($join, $out);
            }
        }
        return "";
    }

    public static function path(string $path) {
        if (false !== strpos($path, '://') || 0 === strpos($path, '//')) {
            // External URL, nothing to check!
            $host = $GLOBALS['url']->host ?? "";
            if (false === strpos($path, '://' . $host) && 0 !== strpos($path, '//' . $host)) {
                return null;
            }
        }
        // Full path, be quick!
        if (0 === strpos($path = strtr($path, '/', DS), ROOT)) {
            return File::exist($path) ?: null;
        }
        // Return the path relative to the `.\lot\asset` or `.` folder if exist!
        $path = ltrim($path, DS);
        return File::exist([
            LOT . DS . 'asset' . DS . $path,
            ROOT . DS . $path
        ]) ?: null;
    }

    public static function let($path = null) {
        if (is_array($path)) {
            foreach ($path as $v) {
                self::let($v);
            }
        } else if (isset($path)) {
            $path = realpath($path) ?: $path;
            $x = '.' . pathinfo($path, PATHINFO_EXTENSION);
            self::$lot[0][$x][$path] = 1;
            unset(self::$lot[1][$x][$path]);
        } else {
            self::$lot[1] = [];
        }
    }

    public static function set($path, float $stack = 10, array $data = []) {
        if (is_array($path)) {
            $i = 0;
            foreach ($path as $v) {
                self::set($v, $stack + $i, $data);
                $i += .1;
            }
        } else {
            $path = realpath($path) ?: $path;
            $x = '.' . pathinfo($path, PATHINFO_EXTENSION);
            if (!isset(self::$lot[0][$x][$path])) {
                self::$lot[1][$x][$path] = [
                    '0' => null,
                    '1' => "",
                    '2' => $data,
                    'path' => self::path($path),
                    'url' => self::URL($path),
                    'stack' => (float) $stack
                ];
            }
        }
    }

}
