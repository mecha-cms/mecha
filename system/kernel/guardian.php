<?php

class Guardian {

    public static $token = 'token';
    public static $user = 'user';
    public static $form = 'form';
    public static $math = 'math';
    public static $captcha = 'captcha';

    private static $validators = array();

    /**
     * ============================================================
     *  GET USER DETAILS
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
        $log = Session::get('cookie:' . self::$user);
        if( ! is_null($key)) {
            return isset($log[$key]) ? $log[$key] : $fallback;
        }
        return $log;
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
        $file = SYSTEM . DS . 'log' . DS . 'token.' . Text::parse(self::get('username'), '->safe_file_name') . '.log';
        $token = File::open($file)->read(sha1(uniqid(mt_rand(), true)));
        Session::set(self::$token, $token);
        return $token;
    }

    // DEPRECATED. Please use `Guardian::token()`
    public static function makeToken() {
        return self::token();
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

    public static function checkToken($token, $redirect = null) {
        if(Session::get(self::$token) === "" || Session::get(self::$token) !== $token) {
            Notify::error(Config::speak('notify_invalid_token'));
            self::reject()->kick(is_null($redirect) ? Config::get('manager')->slug . '/login' : trim($redirect, '/'));
        }
    }

    /**
     * ============================================================
     *  CHECK FOR INVALID MATH ANSWER
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::checkMath('your answer goes here')) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkMath($answer = "") {
        return is_numeric($answer) && self::check((int) $answer, '->correct', Session::get(self::$math));
    }

    /**
     * ============================================================
     *  CHECK FOR INVALID CAPTCHA ANSWER
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::checkCaptcha('your answer goes here')) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkCaptcha($answer = "", $case_sensitive = true) {
        $answer = (string) $answer;
        $answer_key = (string) Session::get(self::$captcha);
        if( ! $case_sensitive) {
            return self::check(strtolower($answer), '->correct', strtolower($answer_key));
        }
        return self::check($answer, '->correct', $answer_key);
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
        File::open(SYSTEM . DS . 'log' . DS . 'token.' . Text::parse(self::get('username'), '->safe_file_name') . '.log')->delete();
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

    public static function checker($name, $callback) {
        self::$validators[$name] = $callback;
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

    public static function checkerExist($name = null) {
        if(is_null($name)) return self::$validators;
        return isset(self::$validators[$name]);
    }

    /**
     * ============================================================
     *  INPUT VALIDATION CHECKS
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
        $results = array();
        $arguments = func_get_args();
        // Alternate function for faster checking process => `Guardian::check('foo, '->URL')`
        if(count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $validator = 'this_is_' . str_replace('->', "", $arguments[1]);
            unset($arguments[1]);
            return isset(self::$validators[$validator]) ? call_user_func_array(self::$validators[$validator], $arguments) : false;
        }
        // Default function for complete checking process => `Guardian::check('foo')->this_is_URL`
        foreach(self::$validators as $name => $callback) {
            $results[$name] = call_user_func_array($callback, $arguments);
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
        $path = trim(File::url($path), '/');
        if(strpos($path, '://') === false) {
            $path = Config::get('url') . '/' . $path;
        }
        $G = array('data' => array('url' => $path));
        Weapon::fire('before_kick', array($G));
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

    public static function memorize($memo = null) {
        if(is_null($memo)) {
            $memo = $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST : "";
        }
        if(is_object($memo)) {
            $memo = Mecha::A($memo);
        }
        Session::set(self::$form, $memo);
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

    public static function forget() {
        Session::kill(self::$form);
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
        $form = Session::get(self::$form);
        if(is_null($name)) {
            self::forget();
            return $form;
        }
        $value = Mecha::GVR($form, $name, $fallback);
        Session::kill(self::$form . '.' . $name);
        return $value;
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

    public static function authorize($username = null, $password = null, $token = null) {
        $config = Config::get();
        $speak = Config::speak();
        if(is_null($username)) {
            $username = isset($_POST['username']) ? $_POST['username'] : "";
        }
        if(is_null($password)) {
            $password = isset($_POST['password']) ? $_POST['password'] : "";
        }
        if(is_null($token)) {
            $token = isset($_POST['token']) ? $_POST['token'] : "";
        }
        $users = Text::toArray(File::open(SYSTEM . DS . 'log' . DS . 'users.txt')->read());
        $authors = array();
        foreach($users as $user => $detail) {
            preg_match('#^(.*?) +\((.*?)\:(pilot|[a-z0-9]+)\)( +(.*?))?$#', $detail, $matches);
            $authors[$user] = array(
                'password' => trim($matches[1]),
                'author' => trim($matches[2]),
                'status' => trim($matches[3]),
                'email' => isset($matches[5]) && ! empty($matches[5]) ? $matches[5] : $config->author_email
            );
        }
        self::checkToken($token);
        if(trim($username) !== "" && trim($password) !== "") {
            if(isset($authors[$username]) && $password === $authors[$username]['password']) {
                $token_o = self::token();
                Session::set('cookie:' . self::$user, array(
                    'token' => $token_o,
                    'username' => $username,
                    // 'password' => $authors[$_POST['username']]['password'],
                    'author' => $authors[$username]['author'],
                    'status' => $authors[$username]['status'],
                    'email' => $authors[$username]['email']
                ), 30, '/', "", false, true);
                File::write($token_o)->saveTo(SYSTEM . DS . 'log' . DS . 'token.' . Text::parse($username, '->safe_file_name') . '.log', 0600);
                File::open(SYSTEM . DS . 'log' . DS . 'users.txt')->setPermission(0600);
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

    public static function happy() {
        $file = SYSTEM . DS . 'log' . DS . 'token.' . Text::parse(self::get('username'), '->safe_file_name') . '.log';
        $auth = Session::get('cookie:' . self::$user);
        return isset($auth['token']) && file_exists($file) && $auth['token'] === file_get_contents($file) ? true : false;
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

    public static function abort($reasons = "") {
        echo '<div style="font:normal normal 18px/1.4 Helmet,FreeSans,Sans-Serif;background-color:#333;color:#FFA;padding:1em 1.2em">' . $reasons . '</div>';
        exit;
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

    public static function math($min = 1, $max = 10, $extra = "") {
        $x = mt_rand($min, $max);
        $y = mt_rand($min, $max);
        if($x - $y > 0) {
            $question = $x . ' - ' . $y;
            Session::set(self::$math, $x - $y);
        } else {
            $question = $x . ' + ' . $y;
            Session::set(self::$math, $x + $y);
        }
        return $question . $extra;
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

    public static function captcha($bg = '333333', $color = 'FFFFFF', $width = 100, $height = 30, $padding = 7, $size = 16, $length = 7, $font = 'special-elite-regular.ttf') {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        Session::set(self::$captcha, substr(str_shuffle($str), 0, $length));
        $params = array();
        if($bg != '333333') $params[] = $bg === false ? 'bg=false' : 'bg=' . (string) $bg;
        if($color != 'FFFFFF') $params[] = 'color=' . (string) $color;
        if($width !== 100) $params[] = 'width=' . (string) $width;
        if($height !== 30) $params[] = 'height=' . (string) $height;
        if($padding !== 7) $params[] = 'padding=' . (string) $padding;
        if($size !== 16) $params[] = 'size=' . (string) $size;
        if($length !== 7) $params[] = 'length=' . (string) $length;
        if($font != 'special-elite-regular.ttf') $params[] = 'font=' . (string) $font;
        return '<img class="captcha" width="' . $width . '" height="' . $height . '" src="' . Config::get('url') . '/captcha.png' . ( ! empty($params) ? '?' . implode('&amp;', $params) : "") . '" alt="captcha"' . ES;
    }

    /**
     * ============================================================
     *  SET HTTP RESPONSE STATUS
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::setResponseStatus(404); // 404 Not Found
     *
     * ------------------------------------------------------------
     *
     */

    public static function setResponseStatus($status = 200) {
        $messages = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', // RFC2518
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status', // RFC4918
            208 => 'Already Reported', // RFC5842
            226 => 'IM Used', // RFC3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', // RFC2324
            422 => 'Unprocessable Entity', // RFC4918
            423 => 'Locked', // RFC4918
            424 => 'Failed Dependency', // RFC4918
            425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
            426 => 'Upgrade Required', // RFC2817
            428 => 'Precondition Required', // RFC6585
            429 => 'Too Many Requests', // RFC6585
            431 => 'Request Header Fields Too Large', // RFC6585
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates (Experimental)', // RFC2295
            507 => 'Insufficient Storage', // RFC4918
            508 => 'Loop Detected', // RFC5842
            510 => 'Not Extended', // RFC2774
            511 => 'Network Authentication Required', // RFC6585
        );
        if(isset($messages[$status])) header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status . ' ' . $messages[$status]);
    }

    /**
     * ============================================================
     *  CHECK IF PAGE IS LOADED ON A MOBILE DEVICE
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::choked()) {
     *        require 'cure/Albuterol.php';
     *        require 'cure/Flunisolide.php';
     *        require 'cure/FluticasonePropionate.php';
     *        require 'cure/Formoterol.php';
     *        require 'cure/Montelukast.php';
     *        require 'cure/Omalizumab.php';
     *        require 'cure/Salmeterol.php';
     *        require 'cure/Theophylline.php';
     *        require 'cure/Triamcinolone.php';
     *        require 'cure/Zafirlukast.php';
     *        require 'cure/Zileuton.php';
     *    } else {
     *        require 'desktop.php';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function choked($input = null, $fallback = false) {
        if(is_null($input)) $input = Get::UA();
        // http://detectmobilebrowsers.com
        return preg_match('#(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino#i', $input) || preg_match('#1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-#i', substr($input, 0, 4)) ? $input : $fallback;
    }

}