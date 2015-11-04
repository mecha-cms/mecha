<?php

class Shield extends Base {

    protected static $lot = array();

    /**
     * Default Shortcut Variable(s)
     * ----------------------------
     */

    public static function cargo() {
        $config = Config::get();
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
            'manager' => Guardian::happy()
        );
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
        $shield = false;
        $shield_base = explode('-', $name, 2);
        if($_file = File::exist(self::path($name))) {
            $shield = $_file;
        } else if($_file = File::exist(self::path($shield_base[0]))) {
            $shield = $_file;
        } else {
            Guardian::abort(Config::speak('notify_file_not_exist', '<code>' . self::path($name) . '</code>'));
        }
        $G['data']['path'] = $shield;
        $q = ! empty($config->url_query) ? '.' . md5($config->url_query) : "";
        $cache_path = is_string($cache) ? $cache : CACHE . DS . str_replace(array('/', ':'), '.', $config->url_path) . $q . '.cache';
        if($G['data']['cache'] && file_exists($cache_path)) {
            if(is_null($expire) || is_int($expire) && time() - $expire < filemtime($cache_path)) {
                // Begin shield cache
                Weapon::fire('shield_cache_before', array($G, $G));
                echo Filter::apply('shield:cache', File::open($cache_path)->read());
                // Clear session
                Guardian::forget();
                Notify::clear();
                self::$lot = array();
                // End shield cache
                Weapon::fire('shield_cache_after', array($G, $G));
                exit;
            }
        }
        // Begin shield
        $token = Guardian::token();
        $message = Notify::read();
        Session::set(Guardian::$token, $token);
        Session::set(Notify::$message, $message);
        extract(Filter::apply('shield:lot', self::lot(array(
            'token' => $token,
            'message' => $message,
            'messages' => $message
        ))->cargo()));
        Weapon::fire('shield_before', array($G, $G));
        $content_detract = "";
        ob_start(function($content) use($minify, $shield, &$content_detract) {
            $content = Filter::apply('shield:input', $content, $shield);
            $content = $minify ? Converter::detractSkeleton($content) : $content;
            $content_detract = Filter::apply('shield:output', $content, $shield);
            return $content_detract;
        });
        require Filter::apply('shield:path', $shield);
        // Clear session
        Guardian::forget();
        Notify::clear();
        self::$lot = array();
        ob_end_flush();
        // Create shield cache
        $G['data']['content'] = $content_detract;
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
     *    Shield::chunk('header', array('title' => 'Test'));
     *
     * ----------------------------------------------------------
     *
     */

    public static function chunk($name, $vars = array(), $buffer = true) {
        $G = array('data' => array('name' => $name));
        $name = Filter::apply('chunk:path', self::path($name));
        if($name) {
            extract(Filter::apply('chunk:lot', array_merge(self::cargo(), self::$lot, $vars)));
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