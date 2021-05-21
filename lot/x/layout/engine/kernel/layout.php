<?php

class Layout extends Genome {

    protected static $lot;

    const state = [
        'path' => LOT . DS . 'layout',
        'x' => ['html', 'php']
    ];

    public static $state = self::state;

    public static function __callStatic(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $kin = p2f($kin);
        // `self::fake('foo/bar', ['key' => 'value'])`
        if ($lot) {
            // `self::fake(['key' => 'value'])`
            if (is_array($lot[0])) {
                // → is equal to `self::fake("", ['key' => 'value'])`
                array_unshift($lot, "");
            }
            $kin = trim($kin . '/' . array_shift($lot), '/');
        }
        return self::get($kin, ...$lot);
    }

    public static function get($id, array $lot = []) {
        $vars = [];
        foreach (array_replace($GLOBALS, $lot) as $k => $v) {
            // Validate array key
            $k = strtr($k, '-', '_');
            $k = preg_replace('/\W/', "", $k);
            if ("" === $k) {
                continue;
            }
            if (is_numeric($k[0])) {
                $k = '_' . $k;
            }
            $vars[$k] = $v;
        }
        unset($k, $v);
        // Need to use special variable name to prevent `extract()` from
        // changing this variable value into something else before it is
        // required out immediately after the output buffer starts.
        if (${__FILE__} = self::path($id)) {
            extract($vars, EXTR_SKIP);
            ob_start();
            if (array_key_exists('vars', $lot)) {
                $vars = $lot['vars'];
            }
            require ${__FILE__};
            return ob_get_clean();
        }
        return null;
    }

    public static function path($value) {
        $out = [];
        $c = static::class;
        $path = static::$state['path'];
        $x = static::$state['x'];
        if (is_string($value)) {
            // Full path, be quick!
            if (0 === strpos($value, ROOT) && is_file($value)) {
                return $value;
            }
            $id = strtr($value, DS, '/');
            // Added by the `Layout::get()`
            if (isset(self::$lot[$c][1][$id]) && !isset(self::$lot[$c][0][$id])) {
                return File::exist(self::$lot[$c][1][$id]) ?: null;
            }
            // Guessing…
            $out = array_values(step($id, '/'));
            array_unshift($out, strtr($out[0], '/', '.'));
            $out = array_unique($out);
        } else {
            $out = $value;
        }
        $any = [];
        foreach ((array) $out as $v) {
            $v = strtr($v, '/', DS);
            if (0 !== strpos($v, $path)) {
                $vv = pathinfo($v, PATHINFO_EXTENSION);
                foreach ($x as $xx) {
                    $any[] = $path . DS . $v . (!$vv || $xx !== $vv ? '.' . $xx : "");
                }
            } else {
                $any[] = $v;
            }
        }
        return File::exist($any) ?: null;
    }

    public static function let($id = null) {
        if (is_array($id)) {
            foreach ($id as $v) {
                self::let($v);
            }
        } else if (isset($id)) {
            $id = strtr($id, DS, '/');
            $c = static::class;
            self::$lot[$c][0][$id] = 1;
            unset(self::$lot[$c][1][$id]);
        } else {
            self::$lot[$c] = [];
        }
    }

    public static function set($id, string $path = null) {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                self::set($k, $v);
            }
        } else {
            $c = static::class;
            if (!isset(self::$lot[$c][0][$id])) {
                $id = strtr($id, DS, '/');
                self::$lot[$c][1][$id] = $path;
            }
        }
    }

}
