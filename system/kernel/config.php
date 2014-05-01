<?php

class Config {

    protected static $bucket = array();

    private function __construct() {}
    private function __clone() {}

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
        if(is_array($key)) {
            self::$bucket = array_merge(self::$bucket, $key);
        } else {
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
     *  --------- | ------ | ---------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function get($key = null) {
        if( ! is_null($key) && ! isset(self::$bucket[$key])) {
            self::$bucket[$key] = false; // handle undefined variable
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

    public static function merge($key, $array = array()) {
        if( ! isset(self::$bucket[$key])) {
            self::$bucket[$key] = $array;
        } else {
            self::$bucket[$key] = array_merge(self::$bucket[$key], $array);
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

        $word = self::$bucket['speak'];

        if(strpos($key, 'file:') === 0 && File::exist(LANGUAGE . '/' . self::$bucket['language'] . '/yapping/' . str_replace('file:', "", $key) . '.txt')) {
            $wizard = File::open(LANGUAGE . '/' . self::$bucket['language'] . '/yapping/' . str_replace('file:', "", $key) . '.txt')->read();
            $wizard = Text::parse(Filter::apply('shortcode', $wizard))->to_html;
            return Filter::apply('content', $wizard);
        }

        if(is_null($key)) {
            return Mecha::O($word);
        } else {
            return ! is_array($word[$key]) ? vsprintf($word[$key], $vars) : $word[$key];
        }

    }

    /**
     * =============================================================
     *  INJECT ALL CONFIGURATION DATA INTO `$bucket`
     * =============================================================
     */

    public static function load() {

        // Extracting the configuration file
        $config = unserialize(File::open(STATE . '/config.txt')->read());

        // Define some default variables
        $config['protocol'] = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $config['base'] = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $config['url'] = rtrim($config['protocol'] . $_SERVER['HTTP_HOST'] . '/' . $config['base'], '/');
        $config['url_current'] = rtrim($config['url'] . '/' . preg_replace('#\?.*$#', "", trim($_SERVER['QUERY_STRING'], '/')), '/');

        $config['page_title'] = $config['title'];
        $config['page_type'] = '404';
        $config['offset'] = 1;
        $config['index_query'] = "";
        $config['tag_query'] = "";
        $config['archive_query'] = "";
        $config['search_query'] = "";
        $config['page'] = false;
        $config['pages'] = false;
        $config['pagination'] = false;
        $config['cargo'] = $config['page'] ? $config['page']->content : false;
        $config['total_articles'] = count(glob(ARTICLE . '/*.txt'));
        $config['total_pages'] = count(glob(PAGE . '/*.txt'));
        $config['total_comments'] = count(glob(RESPONSE . '/*.txt'));

        if(File::exist(LANGUAGE . '/' . $config['language'] . '/speak.txt')) {
            $config['speak'] = Text::toArray(File::open(LANGUAGE . '/' . $config['language'] . '/speak.txt')->read(), ':', '  ');
        } elseif(File::exist(LANGUAGE . '/en_US/speak.txt')) {
            $config['speak'] = Text::toArray(File::open(LANGUAGE . '/en_US/speak.txt')->read());
        } else {
            Guardian::abort('Language file not found.');
        }

        $config['speak']['months'] = explode(',', $config['speak']['months']);
        $config['speak']['days'] = explode(',', $config['speak']['days']);

        self::$bucket = $config;

    }

}

// Load here ...
Config::load();