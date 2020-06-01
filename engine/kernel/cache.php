<?php

final class Cache extends Genome {

    private static function f($id) {
        return LOT . DS . 'cache' . DS . rtrim(chunk_split(md5($id), 2, DS), DS) . '.php';
    }

    private static function t($in, $t = null) {
        return is_string($in) ? strtotime($in) - ($t ?? time()) : $in;
    }

    public static function get(string $id) {
        return is_file($f = self::f($id)) ? require $f : null;
    }

    public static function hit($file, callable $fn) {
        if (is_array($file)) {
            $i = 0;
            $files = [];
            foreach ($file as $v) {
                if (is_file($v)) {
                    $files[] = $v;
                    if ($i < ($t = filemtime($v))) {
                        $i = $t;
                    }
                }
            }
            $f = self::f($id = json_encode($files));
            return !is_file($f) || $i > filemtime($f) ? self::set($id, $fn, [$files, $f])[0] : self::get($id);
        }
        $f = self::f($file);
        return !is_file($f) || filemtime($file) > filemtime($f) ? self::set($file, $fn, [$file, $f])[0] : self::get($file);
    }

    public static function let($id = null): array {
        $out = [];
        if (is_array($id)) {
            foreach ($id as $v) {
                if (is_file($f = self::f($v))) {
                    $out[] = unlink($f) ? $f : null;
                }
            }
            return $out;
        } else if (isset($id)) {
            return is_file($f = self::f($id)) ? (unlink($f) ? $f : null) : false;
        }
        foreach (g(LOT . DS . 'cache', 1, true) as $k => $v) {
            $out[] = unlink($k) ? $k : null;
        }
        return $out;
    }

    public static function live(string $id, callable $fn, $for = '1 day') {
        return self::stale($id, $for) ? self::set($id, $fn, [$id, self::f($id)])[0] : self::get($id);
    }

    public static function set(string $id, callable $fn, array $lot = []): array {
        if (!is_dir($d = dirname($f = self::f($id)))) {
            mkdir($d, 0775, true);
        }
        file_put_contents($f, '<?php return ' . z($r = call_user_func($fn, ...$lot)) . ';');
        @chmod($f, 0600);
        return [$r, $f, filemtime($f)];
    }

    public static function stale(string $id, $for = '1 day') {
        if (is_file($f = self::f($id))) {
            return time() + self::t($for) > filemtime($f);
        }
        return true;
    }

}
