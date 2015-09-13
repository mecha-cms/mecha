<?php

class Guardian extends Base {

    public static $token = 'token';
    public static $user = 'user';
    public static $form = 'form';
    public static $math = 'math';
    public static $captcha = 'captcha';

    protected static $validators = array();

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
        $log = Session::get('cookie:' . self::$user, $fallback);
        if( ! is_null($key)) {
            return isset($log[$key]) ? $log[$key] : $fallback;
        }
        return $log;
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
            foreach(explode("\n", file_get_contents($file)) as $a) {
                // Pattern 1: `user: pass (Author Name: status) email@domain.com`
                // Pattern 2: `user: pass (Author Name @status) email@domain.com`
                preg_match('#^(.*?)\:\s*(.*?)\s+\((.*?)(?:\s*@|\:\s*)(pilot|[a-z0-9_.]+)\)(?:\s+(.*?))?$#', trim($a), $matches);
                $ally[$matches[1]] = array(
                    'pass' => self::get('status') === 'pilot' || ! self::happy() ? $matches[2] : null,
                    'author' => $matches[3],
                    'status' => $matches[4],
                    'password' => self::get('status') === 'pilot' || ! self::happy() ? $matches[2] : null, // alias for `pass`
                    'email' => isset($matches[5]) && ! empty($matches[5]) ? $matches[5] : false
                );
            }
            if(is_null($user)) {
                return $ally;
            }
            return isset($ally[$user]) ? $ally[$user] : $fallback;
        } else {
            self::abort('Missing <code>users.txt</code> file.');
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
        $file = LOG . DS . 'token.' . Text::parse(self::get('user'), '->safe_file_name') . '.log';
        $token = File::open($file)->read(sha1(uniqid(mt_rand(), true)));
        Session::set(self::$token, $token);
        return $token;
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
        if(strpos($name, 'this_is_') !== 0) $name = 'this_is_' . $name;
        self::$validators[get_called_class()][$name] = $action;
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
        if(is_null($name)) {
            return isset(self::$validators[get_called_class()]) ? self::$validators[get_called_class()] : array();
        }
        $name = strtolower($name);
        if(strpos($name, 'this_is_') !== 0) $name = 'this_is_' . $name;
        return isset(self::$validators[get_called_class()][$name]) ? self::$validators[get_called_class()][$name] : $fallback;
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
        // Alternate function for faster checking process => `Guardian::check('foo, '->url')`
        if(count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $validator = 'this_is_' . str_replace('->', "", strtolower($arguments[1]));
            unset($arguments[1]);
            return isset(self::$validators[get_called_class()][$validator]) ? call_user_func_array(self::$validators[get_called_class()][$validator], $arguments) : false;
        }
        // Default function for complete checking process => `Guardian::check('foo')->this_is_url`
        $results = array();
        if( ! isset(self::$validators[get_called_class()])) {
            self::$validators[get_called_class()] = array();
        }
        foreach(self::$validators[get_called_class()] as $name => $action) {
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
        $old = Session::get(self::$form, array());
        Guardian::memorize(array_merge(array('url_origin' => Config::get('url_current')), $old));
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

    public static function memorize($memo = null) {
        if(is_null($memo)) {
            $memo = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : array();
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

    public static function authorize($user = 'user', $pass = 'pass', $token = 'token') {
        $config = Config::get();
        $speak = Config::speak();
        $user = isset($_POST[$user]) ? $_POST[$user] : "";
        $pass = isset($_POST[$pass]) ? $_POST[$pass] : "";
        $token = isset($_POST[$token]) ? $_POST[$token] : "";
        self::checkToken($token);
        if(trim($user) !== "" && trim($pass) !== "") {
            $author = self::ally($user);
            if($author && $pass === $author['pass']) {
                $token = self::token();
                Session::set('cookie:' . self::$user, array(
                    'token' => $token,
                    'user' => $user,
                    'username' => $user, // alias for `user`
                    // 'pass' => $author['pass'],
                    // 'password' => $author['pass'], // alias for `pass`
                    'author' => $author['author'],
                    'status' => $author['status'],
                    'email' => $author['email'] ? $author['email'] : $config->author_email
                ), 30, '/', "", false, true);
                File::write($token)->saveTo(LOG . DS . 'token.' . Text::parse($user, '->safe_file_name') . '.log', 0600);
                File::open(LOG . DS . 'users.txt')->setPermission(0600);
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
        $file = LOG . DS . 'token.' . Text::parse(self::get('user'), '->safe_file_name') . '.log';
        $auth = Session::get('cookie:' . self::$user);
        return isset($auth['token']) && file_exists($file) && $auth['token'] === file_get_contents($file);
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

    public static function abort($reason = "", $stop = true) {
        if(DEBUG) {
            $id = 'guardian-' . time();
            echo $reason ? '<style>#' . $id . '{font:normal normal 18px/1.4 Helmet,FreeSans,Sans-Serif;background-color:#333;color:#FFA;padding:1em 1.2em;margin:0 0 1px}#' . $id . ' a{font:inherit;background:none;color:#F97F71;text-decoration:none}#' . $id . ' a:focus,#' . $id . ' a:hover{text-decoration:underline}</style><div id="' . $id . '">' . $reason . '</div>' : "";
            if($stop) exit;
        }
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