<?php

final class Route extends Genome {

    private static $r;

    public static function fire(string $id, array $lot = []) {
        if ($v = self::get($id)) {
            fire($v['fn'], array_replace(self::is($id)[2] ?? [], $lot), new static);
        }
    }

    public static function get(string $id = null) {
        if (isset($id)) {
            return self::$r[1][$id] ?? null;
        }
        return self::$r[1] ?? [];
    }

    public static function hit($id, callable $fn = null, float $stack = 10) {
        if (is_array($id)) {
            if (!isset($fn)) {
                $out = [];
                foreach ($id as $v) {
                    $out[$v] = self::hit($v);
                }
                return $out;
            }
            $i = 0;
            foreach ($id as $v) {
                self::hit($v, $fn, $stack + $i);
                $i += .1;
            }
        } else {
            if (!isset($fn)) {
                return self::$r[2][$id] ?? null;
            }
            if (empty(self::$r[0][$id])) {
                self::$r[2][$id][] = [
                    '0' => null,
                    '1' => "",
                    '2' => [],
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        }
    }

    public static function is($id, string $path = null) {
        if (is_array($id)) {
            $out = [];
            foreach ($id as $v) {
                $out[$v] = self::is($v, $path);
            }
            return $out;
        }
        $id = trim($id, '/');
        $path = trim($path ?? $GLOBALS['url']['path'], '/');
        // Plain pattern, be quick!
        if (false === strpos('/' . $id, '/:') && false === strpos('/' . $id, '/*')) {
            $id = strtr($id, ["\\:" => ':']);
            return $id === $path ? [
                '0' => null,
                '1' => "",
                '2' => [],
                'id' => $id,
                'path' => '/' . $path
            ] : false;
        }
        $chops = explode('/', $id);
        foreach ($chops as &$v) {
            if (0 === strpos($v, ':') && strlen($v) > 1) {
                if ('{' === $v[1] && '}' === substr($v, -1)) {
                    $v = '(' . substr(substr($v, 2), 0, -1) . ')';
                } else {
                    $v = '([^/]+)';
                }
            } else if ('*' === $v) {
                $v = '(.+)';
            } else {
                $v = preg_quote($v);
            }
            $v = strtr($v, ["\\\\:" => ':']);
        }
        if (preg_match('#^' . implode('/', $chops) . '$#', $path, $m)) {
            array_shift($m); // Remove the first match
            return [
                '0' => null,
                '1' => "",
                '2' => e($m),
                'id' => $id,
                'path' => '/' . $path
            ];
        }
        return false;
    }

    public static function let($id = null) {
        if (is_array($id)) {
            foreach ($id as $v) {
                self::let($v);
            }
        } else if (isset($id)) {
            self::$r[0][$id] = self::$r[1][$id] ?? 1;
            unset(self::$r[1][$id]);
        } else {
            self::$r[1] = [];
        }
    }

    public static function set(...$lot) {
        // `Route::set('foo/bar', 404, function() {}, 10)`
        $id = $lot[0] ?? null;
        $status = $lot[1] ?? 200;
        $fn = $lot[2] ?? null;
        $stack = $lot[3] ?? 10;
        // `Route::set('foo/bar', function() {}, 10)`
        if (is_callable($status)) {
            $stack = $fn ?? 10;
            $fn = $status;
            $status = 200;
        }
        if (is_array($id)) {
            $i = 0;
            foreach ($id as $v) {
                self::set($v, $status, $fn, $stack + $i);
                $i += .1;
            }
        } else {
            if (empty(self::$r[0][$id])) {
                self::$r[1][$id] = [
                    '0' => null,
                    '1' => "",
                    '2' => [],
                    'fn' => $fn,
                    'stack' => (float) $stack,
                    'status' => $status
                ];
            }
        }
    }

    public static function start() {
        $routes = (new Anemon(self::$r[1] ?? []))->sort([1, 'stack'], true);
        foreach ($routes as $k => $v) {
            // If matched with the URL path, then …
            if ($lot = self::is($k)) {
                $route = new static;
                // Loading hook(s)…
                if (isset(self::$r[2][$k])) {
                    $fn = (new Anemon(self::$r[2][$k]))->sort([1, 'stack']);
                    foreach ($fn as $f) {
                        fire($f['fn'], $lot[2] ?? [], $route);
                    }
                }
                // Passed!
                http_response_code($v['status']);
                fire($v['fn'], $lot[2] ?? [], $route);
                break;
            }
        }
    }

}
