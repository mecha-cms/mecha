<?php

class Layout extends Genome {

    protected static $lot;

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

    public static function get($id, array $lot = [], int $status = null) {
        $data = [];
        foreach (array_replace($GLOBALS, $lot) as $k => $v) {
            // Sanitize array key
            $k = preg_replace('/\W/', "", strtr($k, '-', '_'));
            $data[$k] = $v;
        }
        if (isset($status) && !headers_sent()) {
            status($status);
        }
        unset($k, $status, $v);
        if ($f = self::path($id)) {
            extract($data, EXTR_SKIP);
            ob_start();
            if (isset($lot['data'])) {
                $data = $lot['data'];
            }
            require $f;
            return ob_get_clean();
        }
        return null;
    }

    public static function path($value) {
        $out = [];
        $c = static::class;
        $path = LOT . D . 'y';
        if (is_string($value)) {
            // Full path, be quick!
            if (0 === strpos($value, PATH) && is_file($value)) {
                return $value;
            }
            $id = strtr($value, D, '/');
            // Added by the `Layout::set()`
            if (isset(self::$lot[$c][1][$id]) && !isset(self::$lot[$c][0][$id])) {
                return exist(self::$lot[$c][1][$id], 1) ?: null;
            }
            // Guessing…
            $out = array_unique(array_values(step($id, '/')));
        } else {
            $out = (array) $value;
        }
        $files = [];
        foreach ($out as $v) {
            $v = strtr($v, '/', D);
            // Iterate over the `.\lot\y` folder to find active layout(s)
            foreach (g($path, 0) as $kk => $vv) {
                if (!is_file($kk . D . 'index.php')) {
                    continue;
                }
                $files[] = 0 !== strpos($v, $kk) ? $kk . D . $v . '.php' : $v;
            }
        }
        return exist($files) ?: null;
    }

    public static function let($id = null) {
        if (is_array($id)) {
            foreach ($id as $v) {
                self::let($v);
            }
        } else if (isset($id)) {
            $id = strtr($id, D, '/');
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
                $id = strtr($id, D, '/');
                self::$lot[$c][1][$id] = $path;
            }
        }
    }

}