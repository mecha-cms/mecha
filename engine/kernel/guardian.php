<?php

class Guardian extends Genome {

    public static $config = [
        'session' => [
            'token' => 'mecha.guardian.token'
        ]
    ];

    public static function hash($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function token() {
        $key = self::$config['session']['token'];
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
        Session::set('url.previous', Hook::NS('url.previous', [$current, $path]));
        Hook::fire(__c2f__(static::class, '_') . '.kick.before', [$long, $path]);
        header('Location: ' . Hook::NS('url.next', [$long, $path]));
        exit;
    }

    public static function abort($message, $exit = true) {
        echo '<p style="color:#f00;">' . $message . '</p>';
        if ($exit) exit;
    }

}