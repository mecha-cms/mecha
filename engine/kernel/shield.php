<?php

class Shield extends __ {

    protected static $lot = array();

    // Compare with current version
    public static function version($info, $v = null) {
        if(is_string($info)) {
            $info = self::info($info)->version;
        } else {
            $info = (object) $info;
            $info = isset($info->version) ? $info->version : '0.0.0';
        }
        return Mecha::version($v, $info);
    }

    /**
     * Default Shortcut Variable(s)
     * ----------------------------
     */

    public static function cargo() {
        $config = Config::get();
        $token = Guardian::token();
        $results = array(
            'config' => $config,
            'speak' => $config->speak,
            'pager' => $config->pagination,
            'manager' => Guardian::happy(),
            'token' => $token,
            'messages' => Notify::read(false),
            'message' => Notify::read(false)
        );
        foreach(glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
            $v = File::B($v);
            $results[$v . 's'] = isset($config->{$v . 's'}) ? $config->{$v . 's'} : false;
            $results[$v] = isset($config->{$v}) ? $config->{$v} : false;
        }
        Session::set(Guardian::$token, $token);
        unset($config, $token);
        self::$lot = array_merge(self::$lot, $results);
        return self::$lot;
    }

    /**
     * ==========================================================
     *  GET SHIELD PATH BY ITS NAME
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    echo Shield::path('article');
     *
     * ----------------------------------------------------------
     *
     */

    public static function path($name, $fallback = false) {
        $e = File::E($name, "") !== 'php' ? '.php' : "";
        $name = File::path($name) . $e;
        // Full path, be quick!
        if(strpos($name, ROOT) === 0) {
            return File::exist($name, $fallback);
        }
        if($path = File::exist(SHIELD . DS . Config::get('shield') . DS . ltrim($name, DS))) {
            return $path;
        } else if($path = File::exist(CHUNK . DS . ltrim($name, DS))) {
            return $path;
        } else if($path = File::exist(ROOT . DS . ltrim($name, DS))) {
            return $path;
        }
        return $fallback;
    }

    /**
     * ==========================================================
     *  DEFINE/GET SHORTCUT VARIABLE(S)
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::lot(array(
     *        'foo' => 'bar',
     *        'baz' => 'qux'
     *    ))->attach('page');
     *
     * ----------------------------------------------------------
     *
     *    $foo = Shield::lot('foo');
     *
     * ----------------------------------------------------------
     *
     */

    public static function lot($key = null, $fallback = false) {
        if(is_null($key)) return self::$lot;
        if( ! is_array($key)) {
            return isset(self::$lot[$key]) ? self::$lot[$key] : $fallback;
        }
        self::$lot = array_merge(self::$lot, $key);
        return new static;
    }

    /**
     * ==========================================================
     *  UNDEFINE SHORTCUT VARIABLE(S)
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::lot($data)->apart('foo')->attach('page');
     *
     * ----------------------------------------------------------
     *
     *    Shield::lot($data)
     *          ->apart(array('foo', 'bar'))
     *          ->attach('page');
     *
     * ----------------------------------------------------------
     *
     */

    public static function apart($data) {
        $data = (array) $data;
        foreach($data as $d) {
            unset(self::$lot[$d]);
        }
        return new static;
    }

    /**
     * ==========================================================
     *  GET SHIELD INFO
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    var_dump(Shield::info('normal'));
     *
     * ----------------------------------------------------------
     *
     */

    public static function info($folder = null, $array = false) {
        $config = Config::get();
        $speak = Config::speak();
        if(is_null($folder)) {
            $folder = $config->shield;
        }
        // Check whether the localized "about" file is available
        if( ! $info = File::exist(SHIELD . DS . $folder . DS . 'about.' . $config->language . '.txt')) {
            $info = SHIELD . DS . $folder . DS . 'about.txt';
        }
        $info = Page::text(File::open($info)->read(), 'content', 'shield:', array(
            'id' => self::exist($folder) ? $folder : false,
            'title' => Text::parse($folder, '->title'),
            'author' => $speak->anon,
            'url' => '#',
            'version' => '0.0.0',
            'content' => Config::speak('notify_not_available', $speak->description)
        ));
        return $array ? $info : Mecha::O($info);
    }

    /**
     * ==========================================================
     *  RENDER A PAGE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::attach('article');
     *
     * ----------------------------------------------------------
     *
     */

    public static function attach($name, $fallback = false, $buffer = true) {
        $path__ = File::path($name);
        $s = explode('-', File::N($name), 2);
        $G = array('data' => array('name' => $name, 'name_base' => $s[0]));
        if(strpos($path__, ROOT) === 0 && file_exists($path__) && is_file($path__)) {
            // do nothing ...
        } else {
            if($_path = File::exist(self::path($path__, $fallback))) {
                $path__ = $_path;
            } else if($_path = File::exist(self::path($s[0], $fallback))) {
                $path__ = $_path;
            } else {
                Guardian::abort(Config::speak('notify_file_not_exist', '<code>' . $path__ . '</code>'));
            }
        }
        $lot__ = self::cargo();
        $path__ = Filter::apply('shield:path', $path__);
        $G['data']['lot'] = $lot__;
        $G['data']['path'] = $path__;
        $G['data']['path_base'] = $s[0];
        $out = "";
        // Begin shield
        Weapon::fire('shield_lot_before', array($G, $G));
        extract(Filter::apply('shield:lot', $lot__));
        Weapon::fire('shield_lot_after', array($G, $G));
        Weapon::fire('shield_before', array($G, $G));
        if($buffer) {
            ob_start(function($content) use($path__, &$out) {
                $content = Filter::apply('shield:input', $content, $path__);
                $out = Filter::apply('shield:output', $content, $path__);
                return $out;
            });
            require $path__;
            ob_end_flush();
        } else {
            require $path__;
        }
        $G['data']['content'] = $out;
        // Reset shield lot
        self::$lot = array();
        // End shield
        Weapon::fire('shield_after', array($G, $G));
        exit;
    }

    /**
     * ==========================================================
     *  RENDER A 404 PAGE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::abort();
     *
     * ----------------------------------------------------------
     *
     *    Shield::abort('404-custom');
     *
     * ----------------------------------------------------------
     *
     */

    public static function abort($name = '404', $fallback = false, $buffer = true) {
        $s = explode('-', $name, 2);
        $s = is_numeric($s[0]) ? $s[0] : '404';
        Config::set('page_type', $s);
        HTTP::status((int) $s);
        self::attach($name, $fallback, $buffer);
    }

    /**
     * ==========================================================
     *  RENDER A SHIELD CHUNK
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::chunk('header');
     *
     * ----------------------------------------------------------
     *
     *    Shield::chunk('header', array('title' => 'Yo!'));
     *
     * ----------------------------------------------------------
     *
     */

    public static function chunk($name, $fallback = false, $buffer = true) {
        $path__ = File::path($name);
        $G = array('data' => array('name' => $name));
        if(is_array($fallback)) {
            self::$lot = array_merge(self::$lot, $fallback);
            $fallback = false;
        }
        $path__ = Filter::apply('chunk:path', self::path($path__, $fallback));
        $G['data']['lot'] = self::$lot;
        $G['data']['path'] = $path__;
        $out = "";
        if($path__) {
            // Begin chunk
            Weapon::fire('chunk_lot_before', array($G, $G));
            extract(Filter::apply('chunk:lot', self::$lot));
            Weapon::fire('chunk_lot_after', array($G, $G));
            Weapon::fire('chunk_before', array($G, $G));
            if($buffer) {
                ob_start(function($content) use($path__, &$out) {
                    $content = Filter::apply('chunk:input', $content, $path__);
                    $out = Filter::apply('chunk:output', $content, $path__);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            $G['data']['content'] = $out;
            // End chunk
            Weapon::fire('chunk_after', array($G, $G));
        }
    }

    /**
     * ==========================================================
     *  CHECK IF SHIELD ALREADY EXIST
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    if($path = Shield::exist('normal')) { ... }
     *
     * ----------------------------------------------------------
     *
     */

    public static function exist($name, $fallback = false) {
        $name = SHIELD . DS . $name;
        return file_exists($name) && is_dir($name) ? $name : $fallback;
    }

}