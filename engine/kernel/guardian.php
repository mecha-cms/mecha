<?php

class Guardian extends Genome {

    const config = [
        'session' => [
            'token' => 'mecha.guardian.token'
        ]
    ];

    public static $config = self::config;

    public static function hash($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function check($token, $id = 0, $fail = false) {
        $s = Session::get(self::$config['session']['token'] . '.' . $id);
        return $s && $token && $s === $token ? $token : $fail;
    }

    public static function token($id = 0) {
        $key = self::$config['session']['token'] . '.' . $id;
        $token = Session::get($key, self::hash());
        Session::set($key, $token);
        return $token;
    }

    public static function kick($path = null) {
        global $url;
        $current = $url->current;
        if (!isset($path)) {
            $path = $current;
        }
        $long = URL::long($path, false);
        Session::set('url.previous', Hook::fire('url.previous', [$current, $path]));
        $s = __c2f__(static::class, '_') . '.kick.';
        Hook::fire($s . 'before', [$long, $path]);
        header('Location: ' . Hook::fire('url.next', [$long, $path]));
        Hook::fire($s . 'after', [$long, $path]);
        exit;
    }

    public static function abort($message, $exit = true) {
        echo '<p style="color:#f00;">' . $message . '</p>';
        if ($exit) exit;
    }

}