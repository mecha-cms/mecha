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
    $d = DECK . DS . 'workers' . DS . 'repair.state.config.php';
    $config = file_exists($d) ? include $d : array();
    if($file = Get::state_config()) {
        Mecha::extend($config, $file);
    }

    // Define some default variables
    $config['protocol'] = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $config['host'] = $_SERVER['HTTP_HOST'];
    $config['base'] = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $config['url'] = rtrim($config['protocol'] . $config['host']  . '/' . $config['base'], '/');
    $config['url_path'] = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $config['base'] . '?' . trim($_SERVER['QUERY_STRING'], '/') ? "" : preg_replace('#[?&].*$#', "", trim($_SERVER['QUERY_STRING'], '/'));
    $config['url_current'] = rtrim($config['url'] . '/' . $config['url_path'], '/');

    $config['page_title'] = $config['title'];
    $config['offset'] = 1;
    $config['index_query'] = $config['tag_query'] = $config['archive_query'] = $config['search_query'] = "";
    $config['articles'] = $config['article'] = $config['pages'] = $config['page'] = $config['responses'] = $config['response'] = $config['files'] = $config['file'] = $config['pagination'] = $config['cargo'] = false;

    $config['total_articles'] = count(glob(ARTICLE . DS . '*.txt'));
    $config['total_pages'] = count(glob(PAGE . DS . '*.txt'));
    $config['total_comments'] = count(glob(RESPONSE . DS . '*.txt'));

    $config['total_articles_backend'] = count(glob(ARTICLE . DS . '*.{txt,draft,archive}', GLOB_BRACE));
    $config['total_pages_backend'] = count(glob(PAGE . DS . '*.{txt,draft,archive}', GLOB_BRACE));
    $config['total_comments_backend'] = count(glob(RESPONSE . DS . '*.{txt,hold}', GLOB_BRACE));

    $page = '404';
    $url = $config['url'];
    $current = $config['url_current'];
    if($current === $url) $page = 'home';
    if(strpos($current, $url . '/') === 0) $page = 'page';
    if($current . '/' === $url . '/' . $config['index']['slug'] . '/') $page = 'index';
    if(strpos($current, $url . '/' . $config['index']['slug'] . '/') === 0) $page = 'article';
    if(strpos($current, $url . '/' . $config['tag']['slug'] . '/') === 0) $page = 'tag';
    if(strpos($current, $url . '/' . $config['archive']['slug'] . '/') === 0) $page = 'archive';
    if(strpos($current, $url . '/' . $config['search']['slug'] . '/') === 0) $page = 'search';
    if(strpos($current, $url . '/' . $config['manager']['slug'] . '/') === 0) $page = 'manager';
    if(strpos($current . '/', $url . '/sitemap/') === 0) $page = 'sitemap';
    if(strpos($current . '/', $url . '/feed/') === 0 || strpos($current . '/', $url . '/feeds/') === 0) $page = 'feed';
    if(strpos($current . '/', $url . '/feed/rss/') === 0 || strpos($current . '/', $url . '/feeds/rss/') === 0) $page = 'rss';
    if(strpos($current . '/', $url . '/feed/json/') === 0 || strpos($current . '/', $url . '/feeds/json/') === 0) $page = 'json';

    // Create a proper query string data
    if($page !== 'home') {
        array_shift($_GET);
    }
    $queries = array();
    foreach($_GET as $k => $v) {
        $queries[] = $k . '=' . $v;
    }
    $config['url_query'] = ! empty($queries) ? '?' . implode('&', $queries) : "";

    // Loading the language files
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

Config::plug('speak', function($key = null, $vars = array('NULL')) {
    if(is_null($key)) return Config::get('speak');
    $speak = Mecha::A(Config::get('speak'));
    $fallback = $key;
    if(strpos($key, 'file:') === 0) {
        $key = File::path(str_replace('file:', "", $key));
        if($file = File::exist(LANGUAGE . DS . Config::get('language') . DS . 'yapping' . DS . $key . '.txt')) {
            $wizard = Text::toPage(File::open($file)->read(), 'content', 'wizard:');
            return $wizard['content'];
        } else if($file = File::exist(ROOT . DS . $key . '.txt')) {
            $wizard = Text::toPage(File::open($file)->read(), 'content', 'wizard:');
            return $wizard['content'];
        }
        return "";
    }
    if( ! is_array($vars)) {
        $vars = array_slice(func_get_args(), 1);
    }
    if(strpos($key, '.') !== false) {
        $value = Mecha::GVR($speak, $key, $fallback);
        return vsprintf($value, $vars);
    }
    return isset($speak[$key]) ? vsprintf($speak[$key], $vars) : vsprintf($fallback, $vars);
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

Config::plug('url', function($key = 'url', $fallback = false) {
    if($key !== 'url') {
        $key = 'url_' . $key;
    }
    return Config::get($key, $fallback);
});