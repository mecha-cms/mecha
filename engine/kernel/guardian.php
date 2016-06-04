<?php

class Guardian extends __ {

    public static $token = 'token';
    public static $user = 'user';
    public static $form = 'form';
    public static $math = 'math';
    public static $captcha = 'captcha';

    protected static $validator = array();

    /**
     * ============================================================
     *  GET USER DETAIL(S)
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::get('name');
     *
     * ------------------------------------------------------------
     *
     *    var_dump(Guardian::get());
     *
     * ------------------------------------------------------------
     *
     */

    public static function get($key = null, $fallback = "") {
        if($log = Session::get('cookie:' . self::$user, false)) {
            if( ! is_null($key)) {
                return isset($log[$key]) ? $log[$key] : $fallback;
            }
            return $log;
        }
        return $fallback;
    }

    /**
     * ============================================================
     *  GET ACCEPTED USER(S) DATA
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::ally('mecha');
     *
     * ------------------------------------------------------------
     *
     *    var_dump(Guardian::ally());
     *
     * ------------------------------------------------------------
     *
     */

    public static function ally($user = null, $fallback = false) {
        if($file = File::exist(LOG . DS . 'users.txt')) {
            $ally = array();
            foreach(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $str) {
                $s = trim($str);
                // serialized array
                if(strpos($s, 'a:') === 0) {
                    $s = unserialize($s);
                // encoded JSON array
                } else if(strpos($s, '{"') === 0) {
                    $s = json_decode($s, true);
                // Pattern 1: `user: pass (Author Name: status) email@domain.com`
                // Pattern 2: `user: pass (Author Name $status) email@domain.com`
                } else if(preg_match('#^(.*?)\:\s*(.*?)\s+\((.*?)(?:\s*\$|\:\s*)(pilot|[\w.]+)\)(?:\s+(.*?))?$#', $s, $matches)) {
                    $s = array();
                    $s['user'] = $matches[1];
                    $s['pass'] = $matches[2];
                    $s['name'] = $matches[3];
                    $s['status'] = $matches[4];
                    $s['email'] = isset($matches[5]) && ! empty($matches[5]) ? $matches[5] : false;
                } else {
                    self::abort('Broken <code>' . LOG . DS . 'users.txt</code> format.');
                }
                foreach($s as $k => $v) {
                    $v = Converter::strEval($v);
                    $s[$k . '_raw'] = Filter::colon('user:' . $k . '_raw', $v, $s);
                    $s[$k] = Filter::colon('user:' . $k, $v, $s);
                }
                $ally[$s['user']] = Filter::apply('user', $s, $s['user']);
            }
            $ally = Filter::apply('users', $ally);
            if(is_null($user)) return $ally;
            return isset($ally[$user]) ? $ally[$user] : $fallback;
        } else {
            self::abort('Missing <code>' . LOG . DS . 'users.txt</code> file.');
        }
    }

    /**
     * ============================================================
     *  GENERATE A UNIQUE TOKEN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::token();
     *
     * ------------------------------------------------------------
     *
     */

    public static function token() {
        $log = LOG . DS . 'token.' . Text::parse(self::get('user'), '->safe_file_name') . '.log';
        $token = File::open($log)->read(Session::get(self::$token, self::hash()));
        Session::set(self::$token, $token);
        return $token;
    }

    /**
     * ============================================================
     *  GENERATE A RANDOM HASH
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Form::hidden('token', Guardian::hash());
     *
     * ------------------------------------------------------------
     *
     */

    public static function hash($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    /**
     * ============================================================
     *  CHECK FOR INVALID SECURITY TOKEN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if($req = Request::post()) {
     *        Guardian::checkToken($req['token']);
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkToken($token, $kick = false) {
        if(Session::get(self::$token) === "" || Session::get(self::$token) !== $token) {
            Notify::error(Config::speak('notify_invalid_token'));
            self::reject()->kick( ! $kick ? Config::get('manager.slug') . '/login' : trim($kick, '/'));
        }
    }

    /**
     * ============================================================
     *  CHECK FOR INVALID MATH ANSWER
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::checkMath('your answer goes here...')) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkMath($answer) {
        return is_numeric($answer) && (int) $answer === (int) Session::get(self::$math);
    }

    /**
     * ============================================================
     *  CHECK FOR INVALID CAPTCHA ANSWER
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::checkCaptcha('your answer goes here...')) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkCaptcha($answer, $case_sensitive = true) {
        $answer = (string) $answer;
        $answer_key = (string) Session::get(self::$captcha);
        if( ! $case_sensitive) {
            return strtolower($answer) === strtolower($answer_key);
        }
        return $answer === $answer_key;
    }

    /**
     * ============================================================
     *  DELETE SECURITY TOKEN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::deleteToken();
     *
     * ------------------------------------------------------------
     *
     */

    public static function deleteToken() {
        File::open(LOG . DS . 'token.' . Text::parse(self::get('user'), '->safe_file_name') . '.log')->delete();
        Session::kill(self::$token);
    }

    /**
     * ============================================================
     *  INPUT VALIDATION CHECKER
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::checker('this_is_email', function($input) {
     *        return filter_var($input, FILTER_VALIDATE_EMAIL);
     *    });
     *
     * ------------------------------------------------------------
     *
     */

    public static function checker($name, $action) {
        $name = strtolower($name);
        if(strpos($name, 'is_') !== 0) $name = 'is_' . $name;
        self::$validator[get_called_class()][$name] = $action;
    }

    /**
     * ============================================================
     *  CHECK FOR INPUT VALIDATOR EXISTENCE
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if( ! Guardian::checkerExist('this_is_foo')) {
     *        Guardian::checker('this_is_foo', function($input) {
     *            ...
     *        });
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkerExist($name = null, $fallback = false) {
        $c = get_called_class();
        if(is_null($name)) {
            return isset(self::$validator[$c]) && ! empty(self::$validator[$c]) ? self::$validator[$c] : $fallback;
        }
        $name = strtolower($name);
        if(strpos($name, 'is_') !== 0) $name = 'is_' . $name;
        return isset(self::$validator[$c][$name]) ? self::$validator[$c][$name] : $fallback;
    }

    /**
     * ============================================================
     *  INPUT VALIDATION CHECK(S)
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::check('email@domain.com')->this_is_email) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     *    if(Guardian::check('email@domain.com', '->email')) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function check() {
        $arguments = func_get_args();
        $c = get_called_class();
        // Alternate function for faster checking process => `Guardian::check('foo, '->url')`
        if(count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $validator = str_replace('->', 'is_', strtolower($arguments[1]));
            unset($arguments[1]);
            return isset(self::$validator[$c][$validator]) ? call_user_func_array(self::$validator[$c][$validator], $arguments) : false;
        }
        // Default function for complete checking process => `Guardian::check('foo')->this_is_url`
        $results = array();
        if( ! isset(self::$validator[$c])) {
            self::$validator[$c] = array();
        }
        foreach(self::$validator[$c] as $name => $action) {
            $results[$name] = call_user_func_array($action, $arguments);
        }
        return (object) $results;
    }

    /**
     * ============================================================
     *  URL REDIRECTION
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::kick('manager/login');
     *
     * ------------------------------------------------------------
     *
     */

    public static function kick($path = "") {
        $path = Converter::url(File::url($path));
        $path = Filter::apply('guardian:kick', $path);
        $G = array('data' => array('url' => $path));
        Session::set('cookie:url_origin', Config::get('url_current'));
        Weapon::fire('before_kick', array($G, $G));
        header('Location: ' . $path);
        exit;
    }

    /**
     * ============================================================
     *  STORE THE POSTED DATA INTO SESSION
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Request::post()) {
     *        Guardian::memorize();
     *        // do another stuff ...
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function memorize($name = null, $value = "") {
        if(is_null($name)) {
            $name = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : array();
        }
        if(is_object($name)) {
            $name = Mecha::A($name);
        }
        if( ! is_array($name)) {
            $name = array($name => $value);
        }
        $memory = Session::get(self::$form, array());
        Session::set(self::$form, Mecha::extend($memory, $name));
    }

    /**
     * ============================================================
     *  DELETE THE STORED POST DATA
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::forget();
     *
     * ------------------------------------------------------------
     *
     */

    public static function forget($name = null) {
        if( ! is_null($name)) {
            Session::kill(self::$form . '.' . $name);
        } else {
            Session::kill(self::$form);
        }
    }

    /**
     * ============================================================
     *  SPELL THE STORED DATA
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::wayback('name');
     *
     * ------------------------------------------------------------
     *
     */

    public static function wayback($name = null, $fallback = "") {
        $memory = Session::get(self::$form);
        self::forget($name);
        return ! is_null($name) ? Mecha::GVR($memory, $name, $fallback) : $memory;
    }

    /**
     * ============================================================
     *  LOGGING IN ...
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Request::post()) {
     *        Guardian::authorize()->kick('manager/article');
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function authorize($user = 'user', $pass = 'pass', $token = 'token') {
        $config = Config::get();
        $speak = Config::speak();
        $user = isset($_POST[$user]) ? $_POST[$user] : "";
        $pass = isset($_POST[$pass]) ? $_POST[$pass] : "";
        $token = isset($_POST[$token]) ? $_POST[$token] : "";
        self::checkToken($token);
        if(trim($user) !== "" && trim($pass) !== "") {
            $author = self::ally($user);
            if($author && (string) $pass === (string) $author['pass']) {
                $token = self::token();
                $author['token'] = $token;
                $author['author'] = $author['name'];
                $author['author_raw'] = $author['name_raw'];
                if( ! $author['email']) {
                    $author['email'] = $config->author->email;
                }
                if( ! $author['email_raw']) {
                    $author['email_raw'] = $config->author->email;
                }
                unset($author['pass'], $author['pass_raw']); // remove `pass` data
                Session::set('cookie:' . self::$user, $author, 30, '/', "", false, true);
                File::write($token)->saveTo(LOG . DS . 'token.' . Text::parse($user, '->safe_file_name') . '.log', 0600);
                File::open(LOG . DS . 'users.txt')->setPermission(0600);
            } else {
                Notify::error($speak->notify_invalid_user_or_pass);
                self::kick($config->manager->slug . '/login');
            }
        } else {
            Notify::error($speak->notify_error_empty_fields);
            self::kick($config->manager->slug . '/login');
        }
        return new static;
    }

    /**
     * ============================================================
     *  LOGGING OUT ...
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if($user_is_invalid) {
     *        Guardian::reject()->kick('manager/login');
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function reject() {
        self::deleteToken();
        Session::kill('cookie:' . self::$user);
        return new static;
    }

    /**
     * ============================================================
     *  LOGGED IN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::happy()) {
     *        echo 'You are logged in.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function happy($status = null) {
        $file = LOG . DS . 'token.' . Text::parse(self::get('user'), '->safe_file_name') . '.log';
        $auth = Session::get('cookie:' . self::$user);
        $ok = isset($auth['token']) && file_exists($file) && $auth['token'] === file_get_contents($file);
        $ok = $ok && (is_null($status) || self::get('status_raw') === $status);
        return $ok;
    }

    /**
     * ============================================================
     *  SOMETHING GOES WRONG
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::abort('Configuration file not found.');
     *
     * ------------------------------------------------------------
     *
     */

    public static function abort($reason = "", $exit = true) {
        if(DEBUG && $reason) {
            $x = new Exception($reason);
            $x = explode("\n", trim($x->getTraceAsString()));
            array_pop($x);
            $html = '<div style="font:normal normal 18px/1.4 Helmet,FreeSans,Sans-Serif;background-color:#333;color:#FFA;padding:.65em .8em;margin:1px;"><p style="font-size:65%;margin-top:0;">';
            foreach(array_reverse($x) as $v) {
                $html .= '<span style="display:block;">&darr; <code style="font-family:&quot;Courier New&quot;,Courier,&quot;Nimbus Mono L&quot;,Monospace;background:none;color:inherit;text-shadow:none;text-decoration:none;">';
                $v = explode(' ', $v, 2);
                if(strpos($v[1], ROOT) === 0 && preg_match('#^(.*?)\((\d+)\):\s+(.*?)$#', $v[1], $m)) {
                    $fn = preg_replace('#^([\w\\\\]+)::(?=\w)#', '<span style="color:#86CDA7;">$1</span><span style="color:#EDCB7E;">::</span>', htmlentities($m[3]));
                    $fn = preg_replace('#(?<=\w)\((.*)\)#', '<span style="color:#EDCB7E;">()</span> <span style="font-weight:normal;font-style:italic;color:#5D6D8B;">&hellip; <span title="Function Arguments">$1</span></span>', $fn);
                    $html .= '<span title="File">' . str_replace(ROOT . DS, "", $m[1]) . '</span> <span style="color:#E59D95;" title="Line">#' . $m[2] . '</span> &hellip; <span style="font-weight:bold;color:#EDBB54;" title="Function">' . $fn . '</span>';
                } else {
                    $fn = preg_replace('#(\{(.*)\})#', '<span style="font-weight:bold;">$1</span>', $v[1]);
                    $fn = preg_replace('#(\[.*\]:?)#', '<span style="font-weight:bold;color:#D3FAFA;">$1</span>', $fn);
                    $html .= '<span>' . $fn . '</span>';
                }
                $html .= '</code></span>';
            }
            $reason = str_replace('<a ', '<a style="font:inherit;background:none;color:#F97F71;text-shadow:none;text-decoration:none;" ', $reason);
            echo $html . '</p><p style="margin:1em 0 0;">' . $reason . '</p></div>';
        }
        if($exit) exit;
    }

    /**
     * ============================================================
     *  MATH CHALLENGE
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::math();
     *
     * ------------------------------------------------------------
     *
     */

    public static function math($min = 1, $max = 10, $text = array(), $format = '%1$s %3$s %2$s') {
        $x = mt_rand($min, $max);
        $y = mt_rand($min, $max);
        $x_text = isset($text[$x]) ? $text[$x] : $x;
        $y_text = isset($text[$y]) ? $text[$y] : $y;
        if($x - $y > 0) {
            Session::set(self::$math, $x - $y);
            $o = isset($text['-']) ? $text['-'] : '&minus;';
        } else {
            Session::set(self::$math, $x + $y);
            $o = isset($text['+']) ? $text['+'] : '&plus;';
        }
        return sprintf($format, $x_text, $y_text, $o);
    }

    /**
     * ============================================================
     *  CAPTCHA IMAGE
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::captcha();
     *
     * ------------------------------------------------------------
     *
     */

    public static function captcha($bg = '333333', $color = 'FFFFAA', $width = 100, $height = 30, $padding = 0, $size = 16, $length = 7, $font = 'special-elite-regular.ttf') {
        $c = array(
            'bg' => $bg !== '333333' ? Converter::str($bg) : false,
            'color' => $color !== 'FFFFAA' ? (string) $color : false,
            'width' => $width !== 100 ? (int) $width : false,
            'height' => $height !== 30 ? (int) $height : false,
            'padding' => $padding !== 0 ? (int) $padding : false,
            'size' => $size !== 16 ? (int) $size : false,
            'length' => $length !== 7 ? (int) $length : false,
            'font' => $font !== 'special-elite-regular.ttf' ? (string) $font : false
        );
        $param = array();
        foreach($c as $k => $v) {
            if($v !== false) $param[] = $k . '=' . urlencode($v);
        }
        $param = ! empty($param) ? '?' . implode('&amp;', $param) : "";
        return '<img class="captcha" width="' . ($width + ($padding * 2)) . '" height="' . ($height + ($padding * 2)) . '" src="' . Config::get('url') . '/captcha.png' . $param . '" alt="captcha"' . ES;
    }

}