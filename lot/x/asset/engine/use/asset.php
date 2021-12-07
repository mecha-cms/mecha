<?php

final class Asset extends Genome {

    public static $lot = [];

    public static function URL(string $url) {
        $path = self::path($url);
        return isset($path) ? To::URL($path) : (false !== strpos($url, '://') || 0 === strpos($url, '//') ? $url : null);
    }

    public static function get(string $path = null, int $i = 1) {
        if (isset($path)) {
            $path = stream_resolve_include_path($path) ?: $path;
            return self::$lot[$i]['.' . pathinfo($path, PATHINFO_EXTENSION)][$path] ?? null;
        }
        return self::$lot[$i] ?? [];
    }

    public static function join(string $x, string $join = "") {
        if ($v = self::_($x)) {
            $fn = $v[0];
            if (isset(self::$lot[1][$x])) {
                $any = (new Anemone(self::$lot[1][$x]))->sort([1, 'stack'], true);
                $out = [];
                if (is_callable($fn)) {
                    foreach ($any as $k => $v) {
                        $out[] = fire($fn, [$v, $k], static::class);
                    }
                } else {
                    foreach ($any as $k => $v) {
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
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
            if (false === strpos($path, '://' . $host) && 0 !== strpos($path, '//' . $host)) {
                return null;
            }
        }
        // Full path, be quick!
        if (0 === strpos($path = strtr($path, '/', D), PATH)) {
            return exist($path) ?: null;
        }
        // Return the path relative to the `.\lot\asset` or `.` folder if exist!
        $path = ltrim($path, D);
        return exist([
            LOT . D . 'asset' . D . $path,
            PATH . D . $path
        ]) ?: null;
    }

    public static function let($path = null) {
        if (is_array($path)) {
            foreach ($path as $v) {
                self::let($v);
            }
        } else if (isset($path)) {
            $path = stream_resolve_include_path($path) ?: $path;
            $x = '.' . pathinfo($path, PATHINFO_EXTENSION);
            self::$lot[0][$x][$path] = 1;
            unset(self::$lot[1][$x][$path]);
        } else {
            self::$lot[1] = [];
        }
    }

    public static function set($path, float $stack = 10, array $lot = []) {
        if (is_array($path)) {
            $i = 0;
            foreach ($path as $v) {
                self::set($v, $stack + $i, $lot);
                $i += .1;
            }
        } else {
            $path = stream_resolve_include_path($path) ?: $path;
            $x = '.' . pathinfo($path, PATHINFO_EXTENSION);
            if (!isset(self::$lot[0][$x][$path])) {
                self::$lot[1][$x][$path] = [
                    '0' => c2f(static::class),
                    '1' => "",
                    '2' => $lot,
                    'link' => self::URL($path),
                    'path' => self::path($path),
                    'stack' => (float) $stack
                ];
            }
        }
    }

}

foreach ([
    '.css' => function($value, $key) {
        $link = $value['link'];
        $path = $value['path'];
        $x = false !== strpos($link, '://') || 0 === strpos($link, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $link . '?v=' . (is_file($path) ? filemtime($path) : 0) : $link;
        extract($value[2], EXTR_SKIP);
        if (isset($href) && is_callable($href)) {
            $value[2]['href'] = fire($href, [$u, $value, $key], null, Asset::class);
        } else if (empty($href)) {
            $value[2]['href'] = $u;
        }
        $value[0] = 'link';
        $value[1] = false;
        $value[2]['rel'] = 'stylesheet';
        unset($value['link'], $value['path'], $value['stack']);
        return new HTML($value);
    },
    '.js' => function($value, $key) {
        $link = $value['link'];
        $path = $value['path'];
        $x = false !== strpos($link, '://') || 0 === strpos($link, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $link . '?v=' . (is_file($path) ? filemtime($path) : 0) : $link;
        extract($value[2], EXTR_SKIP);
        if (isset($src) && is_callable($src)) {
            $value[2]['src'] = fire($src, [$u, $value, $key], null, Asset::class);
        } else if (empty($src)) {
            $value[2]['src'] = $u;
        }
        $value[0] = 'script';
        unset($value['link'], $value['path'], $value['stack']);
        return new HTML($value);
    }
] as $k => $v) {
    Asset::_($k, $v);
}

foreach (['script', 'style', 'template'] as $v) {
    Asset::_($v, function(string $content, float $stack = 10, array $lot = []) use($v) {
        $id = !empty($lot['id']) ? $lot['id'] : $v . ':' . sprintf('%u', crc32($content));
        if (!isset(static::$lot[0][$v][$id])) {
            static::$lot[1][$v][$id] = [
                '0' => $v,
                '1' => n(trim($content)),
                '2' => $lot,
                'link' => null,
                'path' => null,
                'stack' => (float) $stack
            ];
        }
    });
}