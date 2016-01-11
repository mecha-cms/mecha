<?php


/**
 * =============================================================
 *  AUTOMATIC CONFIGURATION DATA
 * =============================================================
 *
 * -- CODE: ----------------------------------------------------
 *
 *    Config::load();
 *
 * -------------------------------------------------------------
 *
 */

Config::plug('load', function() {

    // Extract the configuration file
    $config = Get::state_config();

    // Define some default variable(s)
    $config['protocol'] = $config['url_protocol'] = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://';
    $config['host'] = $config['url_host'] = $_SERVER['HTTP_HOST'];
    $config['base'] = $config['url_base'] = trim(File::url(File::D($_SERVER['SCRIPT_NAME'])), '/');
    $config['url'] = $config['url_'] = rtrim($config['protocol'] . $config['host']  . '/' . $config['base'], '/');
    $config['path'] = $config['url_path'] = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $config['base'] . '?' . trim('?' . $_SERVER['QUERY_STRING'], '/?') ? "" : preg_replace('#[?&].*$#', "", trim('?' . $_SERVER['QUERY_STRING'], '/?'));
    $config['current'] = $config['url_current'] = rtrim($config['url'] . '/' . $config['url_path'], '/');
    $config['origin'] = $config['url_origin'] = Session::get('url_origin', false);

    $config['page_title'] = $config['title'];
    $config['index_query'] = $config['tag_query'] = $config['archive_query'] = $config['search_query'] = "";
    $config['articles'] = $config['article'] = $config['pages'] = $config['page'] = $config['pagination'] = $config['cargo'] = false;

    foreach(array(ARTICLE, PAGE, COMMENT) as $folder) {
        $s = File::B($folder);
        $config['total_' . $s . 's'] = count(glob($folder . DS . '*.txt', GLOB_NOSORT));
        $config['total_' . $s . 's_backend'] = count(glob($folder . DS . '*.{txt,draft,archive,hold}', GLOB_NOSORT | GLOB_BRACE));
    }

    foreach(array(SHIELD, PLUGIN) as $folder) {
        $s = File::B($folder);
        $config['total_' . $s] = count(glob($folder . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR));
    }

    $page = '404';
    $path = $config['url_path'];
    $s = explode('/', $path);
    if($path === "") $page = 'home';
    if($path !== "" && strpos($path, '/') === false) $page = 'page';
    if($path === $config['index']['slug']) $page = 'index';
    if($s[0] === $config['index']['slug'] && isset($s[1])) $page = is_numeric($s[1]) ? 'index' : 'article';
    if(strpos($path, $config['tag']['slug'] . '/') === 0) $page = 'tag';
    if(strpos($path, $config['archive']['slug'] . '/') === 0) $page = 'archive';
    if(strpos($path, $config['search']['slug'] . '/') === 0) $page = 'search';
    if(strpos($path, $config['manager']['slug'] . '/') === 0) $page = 'manager';
    if($path === 'sitemap') $page = 'sitemap';
    if($path === 'feed') $page = 'feed';
    if($path === 'feed/rss' || strpos($path, 'feed/rss/') === 0) $page = 'rss';
    if($path === 'feed/json' || strpos($path, 'feed/json/') === 0) $page = 'json';

    // Create proper query string data
    if($path !== "") {
        array_shift($_GET);
    }

    // Loading the language file(s)
    $lang = LANGUAGE . DS . 'en_US' . DS . 'speak.txt';
    $lang_a = LANGUAGE . DS . $config['language'] . DS . 'speak.txt';
    if( ! file_exists($lang) && ! file_exists($lang_a)) {
        Guardian::abort('Language file not found.');
    }
    $lang = file_exists($lang) ? Text::toArray(File::open($lang)->read(), S, '  ') : array();
    if($config['language'] !== 'en_US') {
        $lang_a = file_exists($lang_a) ? Text::toArray(File::open($lang_a)->read(), S, '  ') : array();
        Mecha::extend($lang, $lang_a);
    }

    $config['query'] = $config['url_query'] = HTTP::query($_GET);
    $config['offset'] = isset($s[1]) && is_numeric($s[1]) ? (int) $s[1] : 1;
    $config['page_type'] = $page;
    $config['speak'] = $lang;

    Config::set($config);

});


/**
 * =============================================================
 *  GET LANGUAGE FILE TO SPEAK
 * =============================================================
 *
 * -- CODE: ----------------------------------------------------
 *
 *    echo Config::speak('home');
 *
 * -------------------------------------------------------------
 *
 *    echo Config::speak('action')->save;
 *
 * -------------------------------------------------------------
 *
 *    echo Config::speak('action.save');
 *
 * -------------------------------------------------------------
 *
 *    $speak = Config::speak();
 *
 *    echo $speak->home;
 *    echo $speak->action->save;
 *
 * -------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ---------------------------------------
 *  $key      | string | Key of language data to be called
 *  $vars     | array  | Array of value used in PHP `vsprintf()`
 *  --------- | ------ | ---------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Config::plug('speak', function($key = "", $vars = array()) {
    if( ! $key) return Config::get('speak');
    if( ! is_array($vars)) {
        $vars = array_slice(func_get_args(), 1);
    }
    $speak = Mecha::A(Config::get('speak'));
    if(strpos($key, '.') !== false) {
        $value = Mecha::GVR($speak, $key, $key);
        return vsprintf($value, $vars);
    }
    if(isset($speak[$key])) {
        return ! is_array($speak[$key]) ? vsprintf($speak[$key], $vars) : Mecha::O($speak[$key]);
    }
    return $key;
});


/**
 * =============================================================
 *  GET URL DATA
 * =============================================================
 *
 * -- CODE: ----------------------------------------------------
 *
 *    echo Config::url();
 *
 * -------------------------------------------------------------
 *
 *    echo Config::url('path');
 *
 * -------------------------------------------------------------
 *
 *    echo Config::url('current');
 *
 * -------------------------------------------------------------
 *
 *    echo Config::url('query');
 *
 * -------------------------------------------------------------
 *
 */

Config::plug('url', function($key = "", $fallback = false) {
    return Config::get('url_' . $key, $fallback);
});