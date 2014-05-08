<?php

/**
 * ========================
 *  MECHA'S GUARDIAN ANGEL
 * ========================
 */

class Guardian {

    protected static $token = 'mecha_token';
    protected static $login = 'mecha_login';
    protected static $cache = 'mecha_form';

    public static $math = 'mecha_math';

    protected function __construct() {}
    protected function __clone() {}

    // Get user details
    public static function get($key = null) {
        $log = Session::get(self::$login);
        if(is_null($key)) {
            return $log;
        } else {
            return isset($log[$key]) ? $log[$key] : "";
        }
    }

    // Generate a unique token
    public static function makeToken() {
        $file = SYSTEM . '/log/' . Text::parse(self::get('username'))->to_slug_moderate . '.token.txt';
        $token = File::exist($file) ? File::open($file)->read() : sha1(uniqid(mt_rand(), true));
        Session::set(self::$token, $token);
        return $token;
    }

    // Check for invalid security token
    public static function checkToken($token) {
        if(Session::get(self::$token) === "" || Session::get(self::$token) !== $token) {
            Notify::error(Config::speak('notify_invalid_token'));
            self::reject()->kick(Config::get('manager')->slug . '/login');
        }
    }

    // Security token delete
    public static function deleteToken() {
        File::open(SYSTEM . '/log/' . Text::parse(self::get('username'))->to_slug_moderate . '.token.txt')->delete();
        Session::kill(self::$token);
    }

    // Input validation check
    public static function check($input, $compare = "") {
        return (object) array(
            'this_is_IP' => filter_var($input, FILTER_VALIDATE_IP),
            'this_is_URL' => filter_var($input, FILTER_VALIDATE_URL),
            'this_is_email' => filter_var($input, FILTER_VALIDATE_EMAIL),
            'this_is_boolean' => filter_var($input, FILTER_VALIDATE_BOOLEAN),
            'this_is_correct' => $input === $compare ? true : false
        );
    }

    // URL redirection
    public static function kick($path = "") {
        if(strpos($path, '://') === false) {
            $path = Config::get('url') . '/' . trim($path, '/');
        }
        header('Location: ' . $path);
        exit;
    }

    // Store the posted data into session
    public static function memorize($memo = "") {
        if(empty($memo)) {
            $memo = $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST : "";
        }
        if(is_object($memo)) {
            $memo = Mecha::A($memo);
        }
        Session::set(self::$cache, $memo);
    }

    // Delete the stored post data
    public static function forget() {
        Session::kill(self::$cache);
    }

    // Spell the stored data
    public static function wayback($name = null) {
        if(is_null($name)) return Session::get(self::$cache);
        $cache = Session::get(self::$cache);
        $value = isset($cache[$name]) ? $cache[$name] : "";
        $_SESSION[self::$cache][$name] = ""; // :(
        return $value;
    }

    // Logging in ...
    public static function authorize() {
        $users = Text::toArray(File::open(SYSTEM . '/log/users.txt')->read());
        $authors = array();
        $config = Config::get();
        $speak = Config::speak();
        foreach($users as $user => $detail) {
            $name = preg_match('#(.*?) +\((.*?)\:(pilot|[a-z0-9]+)\)#', $detail, $matches);
            $authors[$user] = array(
                'password' => trim($matches[1]),
                'name' => trim($matches[2]),
                'status' => trim($matches[3])
            );
        }
        self::checkToken($_POST['token']);
        if(isset($_POST['username']) && isset($_POST['password']) && ! empty($_POST['username']) && ! empty($_POST['password'])) {
            if(isset($authors[$_POST['username']]) && $_POST['password'] == $authors[$_POST['username']]['password']) {
                $token = self::makeToken();
                Session::set(self::$login, array(
                    'token' => $token,
                    'username' => $_POST['username'],
                    // 'password' => $authors[$_POST['username']]['password'],
                    'author' => $authors[$_POST['username']]['name'],
                    'status' => $authors[$_POST['username']]['status']
                ));
                File::write($token)->saveTo(SYSTEM . '/log/' . Text::parse($_POST['username'])->to_slug_moderate . '.token.txt');
                chmod(SYSTEM . DS . 'log' . DS . 'users.txt', 0600);
                chmod(SYSTEM . DS . 'log' . DS . Text::parse($_POST['username'])->to_slug_moderate . '.token.txt', 0600);
            } else {
                Notify::error($speak->notify_error_username_or_password);
                self::kick($config->manager->slug . '/login');
            }
        } else {
            Notify::error($speak->notify_error_empty_fields);
            self::kick($config->manager->slug . '/login');
        }
        return new static;
    }

    // Logging out ...
    public static function reject() {
        self::deleteToken();
        Session::kill(self::$login);
        return new static;
    }

    // Logged in
    public static function happy() {
        $file = SYSTEM . '/log/' . Text::parse(self::get('username'))->to_slug_moderate . '.token.txt';
        $auth = Session::get(self::$login);
        return isset($auth['token']) && File::exist($file) && $auth['token'] === File::open($file)->read() ? true : false;
    }

    // Something goes wrong!
    public static function abort($reasons = "") {
        echo '<div style="font:normal normal 18px/1.4 Helmet,FreeSans,Sans-Serif;background-color:#3F3F3F;color:#DFC37D;padding:1em 1.2em">' . $reasons . '</div>';
        exit;
    }

    // Math challenge
    public static function math($range = 10, $extra = "") {
        $x = mt_rand(1, $range);
        $y = mt_rand(1, $range);
        if($x - $y > 0) {
            $question = $x . ' - ' . $y;
            Session::set(self::$math, $x - $y);
        } else {
            $question = $x . ' + ' . $y;
            Session::set(self::$math, $x + $y);
        }
        return $question . $extra;
    }

}