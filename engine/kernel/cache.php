<?php

class Cache extends Genome {

    public static $cache = [];

    public static function set($from, $content = null, $id = null) {
        $n = self::_n($from);
        $t = $id ? (string) $id : File::T($from, 0);
        $x = File::open($n)->import([-1]);
        if (is_string($t) && $t !== $x[0] || $t > $x[0]) {
            $c = $content && is_file($from) ? file_get_contents($from) : "";
            $content = isset($content) ? $content : (strpos($c, '<?php') === 0 ? require $from : $c);
            File::export([$t, $content])->saveTo($n, 0600);
            return $content;
        }
        return false;
    }

    public static function get($from, $fail = false) {
        return File::open(self::_n($from))->import([0, $fail])[1];
    }

    public static function reset($from = null) {
        if (isset($from)) {
            File::open(self::_n($from))->delete();
        } else {
            foreach (self::$cache as $k => $v) {
                File::open($k)->delete();
            }
        }
        return true;
    }

    public static function expire($from, $id = null) {
        $n = self::_n($from);
        if (!file_exists($n)) {
            return true;
        }
        $t = $id ? (string) $id : File::T($from, 0);
        $x = File::open($n)->import([-1]);
        return is_string($t) && $t !== $x[0] || $t > $x[0];
    }

    public static function id($from, $fail = -1) {
        return File::open(self::_n($from))->import([$fail])[0];
    }

    // alias of `id`
    public static function i_d($from, $fail = -1) {
        return self::id($from, $fail);
    }

    private static function _n($s) {
        if (is_dir($s) || !file_exists($s)) {
            $s .= '.cache';
            File::write("")->saveTo($s, 0600);
        }
        $f = str_replace(ROOT, CACHE, $s) . '.php';
        self::$cache[$f] = File::T($f, false);
        return $f;
    }

}