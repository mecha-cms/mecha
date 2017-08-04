<?php

class Language extends Genome {

    public static function ignite(...$lot) {
        $id = '_' . __c2f__(static::class, '_');
        $language = Config::get('language');
        $f = LANGUAGE . DS . $language . '.page';
        if (Cache::expire($f)) {
            $i18n = new Page($f, [], 'language');
            $fn = 'From::' . __c2f__($i18n->type, '_');
            $content = is_callable($fn) ? call_user_func($fn, $i18n->content) : $i18n->content;
            Cache::set($f, $content);
        } else {
            $content = Cache::get($f);
        }
        return Config::set($id, $content)->get($id, []);
    }

    public static function set($key, $value = null) {
        $id = '_' . __c2f__(static::class, '_') . '.';
        if (!__is_anemon__($key)) {
            return Config::set($id . $key, $value);
        }
        foreach ($key as $k => $v) {
            $keys[$id . $k] = $v;
        }
        return Config::set(isset($keys) ? $keys : [], $value);
    }

    public static function get($key = null, $vars = [], $preserve_case = false) {
        $vars = array_merge(s((array) $vars), [""]);
        $fail = $key;
        $id = '_' . __c2f__(static::class, '_');
        if (!isset($key)) {
            return Config::get($id, $fail);
        }
        $s = Config::get($id . '.' . $key, $fail);
        if (is_string($s)) {
            if (!$preserve_case && strpos($s, '%') !== 0 && u($vars[0]) !== $vars[0]) {
                $vars[0] = l($vars[0]);
            }
            return __replace__($s, $vars);
        }
        return $s;
    }

    public static function __callStatic($kin, $lot = []) {
        return call_user_func_array([new static, $kin], $lot);
    }

    public function __construct($input = []) {
        if ($input) {
            self::set(From::yaml($input));
        }
        parent::__construct();
    }

    public function __call($key, $lot = []) {
        return self::get($key, array_shift($lot), array_shift($lot) ?: false);
    }

    public function __set($key, $value = null) {
        return self::set($key, $value);
    }

    public function __get($key) {
        return Config::get('_' . __c2f__(static::class, '_') . '.' . $key, $key);
    }

    public function __unset($key) {
        return Config::reset('_' . __c2f__(static::class, '_') . '.' . $key);
    }

    public function __toString() {
        return To::yaml(Config::get('_' . __c2f__(static::class, '_')));
    }

    public function __invoke($fail = []) {
        return Config::get('_' . __c2f__(static::class, '_'), o($fail));
    }

}