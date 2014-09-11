<?php

class Config {

    protected static $bucket = array();

    /**
     * =============================================================
     *  REGISTER NEW VARIABLE(S)
     * =============================================================
     *
     * -- CODE: ----------------------------------------------------
     *
     *    Config::set('foo', 'bar');
     *
     *    Config::set(array(
     *        'a' => 1,
     *        'b' => 2
     *    ));
     *
     * -------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------
     *  $key      | string | Key of variable to call
     *  $key      | array  | Array of variable's key and value
     *  $value    | mixed  | The value of your variable key
     *  --------- | ------ | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function set($key, $value = "") {
        if(is_object($key)) {
            $key = Mecha::A($key);
        }
        $cargo = array();
        if(is_array($key)) {
            foreach($key as $k => $v) {
                Mecha::SVR($cargo, $k, $v);
            }
            self::$bucket = array_replace_recursive(self::$bucket, $cargo);
        } else {
            Mecha::SVR($cargo, $key, $value);
            self::$bucket[$key] = $value;
        }
    }

    /**
     * =============================================================
     *  GET CONFIGURATION VALUE BY ITS KEY
     * =============================================================
     *
     * -- CODE: ----------------------------------------------------
     *
     *    [1]. echo Config::get('url');
     *
     *    [2]. echo Config::get('index')->slug;
     *
     *         $config = Config::get();
     *
     *    [3]. echo $config->url;
     *
     *    [4]. echo $config->index->slug;
     *
     * -------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------
     *  $key      | string | Key of variable to call
     *  $fallback | mixed  | Fallback value if key does not exist
     *  --------- | ------ | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function get($key = null, $fallback = false) {
        if(is_string($key) && strpos($key, '.') !== false) {
            $output = Mecha::GVR(self::$bucket, $key, $fallback);
            return is_array($output) ? Mecha::O($output) : $output;
        }
        if( ! is_null($key) && ! isset(self::$bucket[$key])) {
            self::$bucket[$key] = $fallback;
        }
        return ! is_null($key) ? Mecha::O(self::$bucket[$key]) : Mecha::O(self::$bucket);
    }

    /**
     * =============================================================
     *  MERGE MORE ARRAY TO SPECIFIC CONFIGURATION ITEM
     * =============================================================
     *
     * -- CODE: ----------------------------------------------------
     *
     *    Config::merge('speak', array('cute' => 'manis'));
     *
     * -------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------
     *  $key      | string | Key of data to be infected
     *  $array    | array  | The data you want to use to infect
     *  --------- | ------ | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function merge($key, $value = array()) {
        if(is_object($value)) {
            $value = Mecha::A($value);
        }
        if( ! isset(self::$bucket[$key])) {
            if(strpos($key, '.') !== false) {
                Mecha::SVR(self::$bucket, $key, $value);
            } else {
                self::$bucket[$key] = $value;
            }
        } else {
            if(strpos($key, '.') !== false) {
                $cargo = array();
                Mecha::SVR($cargo, $key, $value);
                self::$bucket = array_merge_recursive(self::$bucket, $cargo);
            } else {
                self::$bucket[$key] = array_merge_recursive(self::$bucket[$key], $value);
            }
        }
    }

    /**
     * =============================================================
     *  GET LANGUAGE FILE TO SPEAK
     * =============================================================
     *
     * -- CODE: ----------------------------------------------------
     *
     *    [1]. echo Config::speak('home');
     *
     *    [2]. echo Config::speak('action')->save;
     *
     *         $speak = Config::speak();
     *
     *    [3]. echo $speak->home;
     *
     *    [4]. echo $speak->action->save;
     *
     * -------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------
     *  $key      | string | Key of word variable to call
     *  $vars     | array  | Array of value used in PHP `vsprintf()`
     *  --------- | ------ | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function speak($key = null, $vars = array()) {
        $words = self::$bucket['speak'];
        if(strpos($key, 'file:') === 0) {
            if($file = File::exist(LANGUAGE . DS . self::$bucket['language'] . DS . 'yapping' . DS . str_replace('file:', "", $key) . '.txt')) {
                $wizard = File::open($file)->read();
                $wizard = Text::parse(Filter::apply('wizard:shortcode', Filter::apply('shortcode', $wizard)))->to_html;
                return Filter::apply('wizard:content', Filter::apply('content', $wizard));
            } else {
                $wizard = File::open(ROOT . DS . str_replace(array('file:', '\\', '/'), array("", DS, DS), $key) . '.txt')->read();
                $wizard = Text::parse(Filter::apply('wizard:shortcode', Filter::apply('shortcode', $wizard)))->to_html;
                return Filter::apply('wizard:content', Filter::apply('content', $wizard));
            }
        }
        if(is_null($key)) {
            return Mecha::O($words);
        }
        if(strpos($key, '.') !== false) {
            $value = Mecha::GVR($words, $key, false);
            return $value ? vsprintf($value, $vars) : "";
        }
        return ! is_array($words[$key]) ? vsprintf($words[$key], $vars) : Mecha::O($words[$key]);
    }

    /**
     * =============================================================
     *  INJECT ALL CONFIGURATION DATA INTO `$bucket`
     * =============================================================
     *
     * -- CODE: ----------------------------------------------------
     *
     *    Config::load();
     *
     * -------------------------------------------------------------
     *
     */

    public static function load() {

        // Extract the configuration file
        $config = include STATE . DS . 'repair.config.php';
        if($file = File::exist(STATE . DS . 'config.txt')) {
            $config = array_replace_recursive($config, File::open($file)->unserialize());
        }

        // Define some default variables
        $config['protocol'] = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $config['host'] = $_SERVER['HTTP_HOST'];
        $config['base'] = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $config['url'] = rtrim($config['protocol'] . $config['host']  . '/' . $config['base'], '/');
        $config['url_current'] = rtrim($config['url'] . '/' . preg_replace('#(\?|\&).*$#', "", trim($_SERVER['QUERY_STRING'], '/')), '/');

        $config['page_title'] = $config['title'];
        $config['page_type'] = '404';
        $config['offset'] = 1;
        $config['index_query'] = "";
        $config['tag_query'] = "";
        $config['archive_query'] = "";
        $config['search_query'] = "";
        $config['articles'] = $config['article'] = false;
        $config['pages'] = $config['page'] = false;
        $config['responses'] = $config['response'] = false;
        $config['files'] = $config['file'] = false;
        $config['pagination'] = false;
        $config['cargo'] = false;

        $config['total_articles'] = count(glob(ARTICLE . DS . '*.txt'));
        $config['total_pages'] = count(glob(PAGE . DS . '*.txt'));
        $config['total_comments'] = count(glob(RESPONSE . DS . '*.txt'));

        $config['total_articles_backend'] = count(glob(ARTICLE . DS . '*.{txt,draft}', GLOB_BRACE));
        $config['total_pages_backend'] = count(glob(PAGE . DS . '*.{txt,draft}', GLOB_BRACE));
        $config['total_comments_backend'] = count(glob(RESPONSE . DS . '*.{txt,hold}', GLOB_BRACE));

        if($file = File::exist(LANGUAGE . DS . $config['language'] . DS . 'speak.txt')) {
            $config['speak'] = Text::toArray(File::open($file)->read(), ':', '  ');
        } elseif($file = File::exist(LANGUAGE . DS . 'en_US' . DS . 'speak.txt')) {
            $config['speak'] = Text::toArray(File::open($file)->read(), ':', '  ');
        } else {
            Guardian::abort('Language file not found.');
        }

        self::$bucket = $config;

    }

}