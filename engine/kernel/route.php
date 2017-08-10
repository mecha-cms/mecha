<?php

class Route extends Genome {

    public static $lot = [];
    public static $lot_o = [];

    public static function set($id = null, $fn = null, $stack = null, $pattern = false) {
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = URL::short($v, false);
            if (!isset(self::$lot[0][$v])) {
                self::$lot[1][$v] = [
                    'fn' => $fn,
                    'stack' => (float) ((isset($stack[$k]) ? $stack[$k] : (end($stack) !== false ? end($stack) : 10)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
        return true;
    }

    public static function reset($id) {
        foreach ((array) $id as $v) {
            $v = URL::short($v, false);
            self::$lot[0][$v] = isset(self::$lot[1][$v]) ? self::$lot[1][$v] : 1;
            unset($this->lot[1][$v]);
        }
        return true;
    }

    public static function lot($id, $fn = null, $stack = null, $pattern = false) {
        $i = 0;
        $id = (array) $id;
        $stack = (array) $stack;
        foreach ($id as $k => $v) {
            $v = URL::short($v, false);
            if (!isset(self::$lot_o[0][$v])) {
                self::$lot_o[1][$v][] = [
                    'fn' => $fn,
                    'stack' => (float) ((isset($stack[$k]) ? $stack[$k] : (end($stack) !== false ? end($stack) : 10)) + $i),
                    'is' => ['pattern' => $pattern]
                ];
                $i += .1;
            }
        }
        return true;
    }

    public static function pattern($pattern, $fn = null, $stack = null) {
        return self::set($pattern, $fn, $stack, true);
    }

    public static function is($id, $fail = false, $pattern = false) {
        $id = URL::short($id, false);
        $path = URL::path();
        if (strpos($id, '%') === false) {
            return $path === $id ? [
                'pattern' => $id,
                'path' => $path,
                'lot' => []
            ] : $fail;
        }
        if (preg_match(!$pattern ? '#^' . __format__($id, '\/\n', '#', false) . '$#' : $id, $path, $m)) {
            array_shift($m);
            return [
                'pattern' => $id,
                'path' => $path,
                'lot' => e($m)
            ];
        }
        return $fail;
    }

    public static function get($id = null, $fail = false) {
        if (isset($id)) {
            return isset(self::$lot[1][$id]) ? self::$lot[1][$id] : $fail;
        }
        return !empty(self::$lot[1]) ? self::$lot[1] : $fail;
    }

    public static function contain($id = null, $stack = null, $fail = false) {
        if (isset($id)) {
            if (isset($stack)) {
                $routes = [];
                foreach (self::$lot_o[1][$id] as $v) {
                    if (
                        $v['fn'] === $stack || // `$stack` as `$fn`
                        is_numeric($stack) && $v['stack'] === (float) $stack
                    ) {
                        $routes[] = $v;
                    }
                }
                return !empty($routes) ? $routes : $fail;
            } else {
                return isset(self::$lot_o[1][$id]) ? self::$lot_o[1][$id] : $fail;
            }
        }
        return !empty(self::$lot_o[1]) ? self::$lot_o[1] : $fail;
    }

    public static function fire($id = null, $lot = []) {
        $s = __c2f__(static::class, '_') . '.';
        if (isset($id)) {
            $id = URL::short($id, false);
            if (isset(self::$lot[1][$id])) {
                call_user_func_array(self::$lot[1][$id]['fn'], $lot);
                return true;
            }
        } else {
            global $url;
            $id = $url->path;
            if (isset(self::$lot[1][$id])) {
                // Loading cargo(s)…
                if (isset(self::$lot_o[1][$id])) {
                    Hook::fire($s . 'lot.enter', [self::$lot_o[1][$id], self::$lot[1][$id]]);
                    $fn = Anemon::eat(self::$lot_o[1][$id])->sort([1, 'stack'])->vomit();
                    foreach ($fn as $v) {
                        call_user_func_array($v['fn'], $lot);
                    }
                    Hook::fire($s . 'lot.exit', [self::$lot_o[1][$id], self::$lot[1][$id]]);
                }
                // Passed!
                Hook::fire($s . 'enter', [self::$lot[1][$id], null]);
                call_user_func_array(self::$lot[1][$id]['fn'], $lot);
                Hook::fire($s . 'exit', [self::$lot[1][$id], null]);
                return true;
            } else {
                $routes = Anemon::eat(isset(self::$lot[1]) ? self::$lot[1] : [])->sort([1, 'stack'], true)->vomit();
                foreach ($routes as $k => $v) {
                    // If matched with the URL path, then …
                    if ($route = self::is($k, false, $v['is']['pattern'])) {
                        // Loading hook(s)…
                        if (isset(self::$lot_o[1][$k])) {
                            Hook::fire($s . 'lot.enter', [self::$lot_o[1][$k], self::$lot[1][$k]]);
                            $fn = Anemon::eat(self::$lot_o[1][$k])->sort([1, 'stack'])->vomit();
                            foreach ($fn as $f) {
                                if (!is_callable($f['fn'])) continue;
                                call_user_func_array($f['fn'], $route['lot']);
                            }
                            Hook::fire($s . 'lot.exit', [self::$lot_o[1][$k], self::$lot[1][$k]]);
                        }
                        // Passed!
                        Hook::fire($s . 'enter', [self::$lot[1][$k], null]);
                        call_user_func_array($v['fn'], $route['lot']);
                        Hook::fire($s . 'exit', [self::$lot[1][$k], null]);
                        return true;
                    }
                }
            }
        }
        return null;
    }

}