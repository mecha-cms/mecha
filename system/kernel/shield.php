<?php

class Shield extends Base {

    protected static $lot = array();

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
            'articles' => $config->articles,
            'article' => $config->article,
            'pages' => $config->pages,
            'page' => $config->page,
            'responses' => $config->responses,
            'response' => $config->response,
            'files' => $config->files,
            'file' => $config->file,
            'pager' => $config->pagination,
            'manager' => Guardian::happy(),
            'token' => $token,
            'messages' => Notify::read(false),
            'message' => Notify::read(false)
        );
        Session::set(Guardian::$token, $token);
        unset($config, $token);
        self::$lot = array_merge($results, self::$lot);
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
        $name = File::path($name) . (File::E($name, "") !== 'php' ? '.php' : "");
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
        } else if($path = File::exist($name)) {
            return $path;
        }
        return $fallback;
    }

    /**
     * ==========================================================
     *  DEFINE NEW SHORTCUT VARIABLE(S)
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::lot('foo', 'bar')->attach('file');
     *
     * ----------------------------------------------------------
     *
     *    Shield::lot(array(
     *        'foo' => 'bar',
     *        'baz' => 'qux'
     *    ))->attach('page');
     *
     * ----------------------------------------------------------
     *
     */

    public static function lot($key, $value = "") {
        if(is_array($key)) {
            self::$lot = array_merge(self::$lot, $key);
        } else {
            self::$lot[$key] = $value;
        }
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
     *    var_dump(Shield::info('aero'));
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
        $default = 'Title' . S . ' ' . ucwords(Text::parse($folder, '->text')) . "\n" .
                   'Author' . S . ' ' . $speak->anon . "\n" .
                   'URL' . S . ' #' . "\n" .
                   'Version' . S . ' 0.0.0' . "\n" .
                   "\n" . SEPARATOR . "\n" .
                   "\n" . Config::speak('notify_not_available', $speak->description);
        $info = Text::toPage(File::open($info)->read($default), 'content', 'shield:');
        return $array ? $info : Mecha::O($info);
    }

    /**
     * ==========================================================
     *  RENDER A PAGE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::attach('article', true, false);
     *
     * ----------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | -----------------------------------
     *  $name     | string  | Name of the shield
     *  $minify   | boolean | Minify HTML output?
     *  $cache    | boolean | Create a cache file on page visit?
     *  $expire   | integer | Define cache file expiration time
     *  --------- | ------- | -----------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function attach($name, $minify = null, $cache = false, $expire = null) {
        $config = Config::get();
        if(is_null($minify)) {
            $minify = $config->html_minifier;
        }
        $G = array('data' => array(
            'name' => $name,
            'minify' => $minify,
            'cache' => $cache,
            'expire' => $expire
        ));
        $path = false;
        $path_base = explode('-', $name, 2);
        if($_path = File::exist(self::path($name))) {
            $path = $_path;
        } else if($_path = File::exist(self::path($path_base[0]))) {
            $path = $_path;
        } else {
            Guardian::abort(Config::speak('notify_file_not_exist', '<code>' . self::path($name) . '</code>'));
        }
        $G['data']['path'] = $path;
        $q = ! empty($config->url_query) ? '.' . md5($config->url_query) : "";
        $cache_path = is_string($cache) ? $cache : CACHE . DS . str_replace(array('/', ':'), '.', $config->url_path) . $q . '.cache';
        if($G['data']['cache'] && file_exists($cache_path)) {
            if(is_null($expire) || is_int($expire) && time() - $expire < filemtime($cache_path)) {
                // Begin shield cache
                Weapon::fire('shield_cache_before', array($G, $G));
                echo Filter::apply('shield:cache', File::open($cache_path)->read());
                // Reset shield lot
                self::$lot = array();
                // End shield cache
                Weapon::fire('shield_cache_after', array($G, $G));
                exit;
            }
        }
        // Begin shield
        $out = "";
        extract(Filter::apply('shield:lot', self::cargo()));
        Weapon::fire('shield_before', array($G, $G));
        ob_start(function($content) use($minify, $path, &$out) {
            $content = Filter::apply('shield:input', $content, $path);
            $content = $minify ? Converter::detractSkeleton($content) : $content;
            $out = Filter::apply('shield:output', $content, $path);
            return $out;
        });
        require Filter::apply('shield:path', $path);
        ob_end_flush();
        // Reset shield lot
        self::$lot = array();
        // Create shield cache
        $G['data']['content'] = $out;
        if($G['data']['cache']) {
            $G['data']['cache'] = $cache_path;
            File::write($G['data']['content'])->saveTo($cache_path);
            Weapon::fire('on_cache_construct', array($G, $G));
        }
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

    public static function abort($name = '404', $minify = null, $cache = false, $expire = null) {
        HTTP::status(404);
        Config::set('page_type', '404');
        self::attach($name, $minify, $cache, $expire);
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
        $G = array('data' => array('name' => $name));
        if(is_array($fallback)) {
            self::$lot = array_merge(self::$lot, $fallback);
            $fallback = false;
        }
        $name = Filter::apply('chunk:path', self::path($name, $fallback));
        if($name) {
            extract(Filter::apply('chunk:lot', self::$lot));
            Weapon::fire('chunk_before', array($G, $G));
            if($buffer) {
                ob_start(function($content) use($name) {
                    $content = Filter::apply('chunk:input', $content, $name);
                    return Filter::apply('chunk:output', $content, $name);
                });
                require $name;
                ob_end_flush();
            } else {
                require $name;
            }
            Weapon::fire('chunk_after', array($G, $G));
        }
    }

}