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
    $config['url'] = $config['url_root'] = rtrim($config['protocol'] . $config['host']  . '/' . $config['base'], '/');
    $config['url_path'] = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $config['base'] . '?' . trim('?' . $_SERVER['QUERY_STRING'], '/?') ? "" : preg_replace('#[?&].*$#', "", trim('?' . $_SERVER['QUERY_STRING'], '/?'));
    $config['url_current'] = rtrim($config['url'] . '/' . $config['url_path'], '/');

    $config['page_title'] = $config['title'];
    $config['index_query'] = $config['tag_query'] = $config['archive_query'] = $config['search_query'] = "";
    $config['articles'] = $config['article'] = $config['pages'] = $config['page'] = $config['responses'] = $config['response'] = $config['files'] = $config['file'] = $config['pagination'] = $config['cargo'] = false;

    $config['total_articles'] = count(glob(ARTICLE . DS . '*.txt', GLOB_NOSORT));
    $config['total_pages'] = count(glob(PAGE . DS . '*.txt', GLOB_NOSORT));
    $config['total_comments'] = count(glob(RESPONSE . DS . '*.txt', GLOB_NOSORT));

    $config['total_articles_backend'] = count(glob(ARTICLE . DS . '*.{txt,draft,archive}', GLOB_NOSORT | GLOB_BRACE));
    $config['total_pages_backend'] = count(glob(PAGE . DS . '*.{txt,draft,archive}', GLOB_NOSORT | GLOB_BRACE));
    $config['total_comments_backend'] = count(glob(RESPONSE . DS . '*.{txt,hold}', GLOB_NOSORT | GLOB_BRACE));

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
    if($path === 'feed' || $path === 'feeds') $page = 'feed';
    if($path === 'feed/rss' || strpos($path, 'feed/rss/') === 0 || $path === 'feeds/rss' || strpos($path, 'feeds/rss/') === 0) $page = 'rss';
    if($path === 'feed/json' || strpos($path, 'feed/json/') === 0 || $path === 'feeds/json' || strpos($path, 'feeds/json/') === 0) $page = 'json';

    // Create a proper query string data
    if($path !== "") {
        array_shift($_GET);
    }
    $queries = array();
    foreach($_GET as $k => $v) {
        $queries[] = $k . '=' . urlencode($v);
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

    $config['url_query'] = ! empty($queries) ? '?' . implode('&', $queries) : "";
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
    if(strpos($key, 'file:') === 0) {
        $key = File::path(str_replace('file:', "", $key));
        if($file = File::exist(LANGUAGE . DS . Config::get('language') . DS . 'yapping' . DS . $key . '.txt')) {
            $wizard = Text::toPage(File::open($file)->read(), 'content', 'wizard:');
            return vsprintf($wizard['content']);
        } else if($file = File::exist(ROOT . DS . $key . '.txt')) {
            $wizard = Text::toPage(File::open($file)->read(), 'content', 'wizard:');
            return vsprintf($wizard['content']);
        }
        return "";
    }
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

Config::plug('url', function($key = 'root', $fallback = false) {
    return Config::get('url_' . $key, $fallback);
});