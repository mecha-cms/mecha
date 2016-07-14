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

    // Define some variable(s) related to URL
    $config['url_scheme'] = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443) ? 'https' : 'http';
    $config['url_protocol'] = $config['url_scheme'] . '://';
    $config['url_host'] = $_SERVER['HTTP_HOST'];
    $config['url_base'] = trim(File::url(File::D($_SERVER['SCRIPT_NAME'])), '/');
    $config['url_url'] = rtrim($config['url_protocol'] . $config['url_host']  . '/' . $config['url_base'], '/');
    $o = preg_replace('#[<>"]|[?&].*$#', "", trim($_SERVER['QUERY_STRING'], '/')); // Remove HTML tag(s) and query string(s) from URL
    $config['url_path'] = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $config['url_base'] . '?' . trim($_SERVER['QUERY_STRING'], '/') ? "" : $o;
    $config['url_current'] = rtrim($config['url_url'] . '/' . $config['url_path'], '/');
    $config['url_origin'] = Session::get('cookie:url_origin', false);

    // Alias(es)
    $config['scheme'] = $config['url_scheme'];
    $config['protocol'] = $config['url_protocol'];
    $config['host'] = $config['url_host'];
    $config['base'] = $config['url_base'];
    $config['url'] = $config['url_url'];
    $config['path'] = $config['url_path'];
    $config['current'] = $config['url_current'];
    $config['origin'] = $config['url_origin'];

    // Default(s)
    $config['page_title'] = $config['title'];
    $config['index_query'] = $config['tag_query'] = $config['archive_query'] = $config['search_query'] = "";
    $config['pages'] = $config['page'] = $config['pagination'] = $config['cargo'] = false;

    $ss = array();
    $folders = array_merge(
        glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR),
        glob(RESPONSE . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR)
    );
    foreach($folders as $folder) {
        $s = File::B($folder);
        $ss[$s][0] = glob($folder . DS . '*.{archive,draft,hold,txt}', GLOB_NOSORT | GLOB_BRACE);
        $ss[$s][1] = glob($folder . DS . '*.txt', GLOB_NOSORT);
        $config[$s . 's_path'] = $ss[$s][1];
        $config['__' . $s . 's_path'] = $ss[$s][0];
        $config['total_' . $s . 's'] = count($ss[$s][1]);
        $config['__total_' . $s . 's'] = count($ss[$s][0]);
    }

    $page = '404';
    $path = $config['url_path'];
    $s = explode('/', $path);
    if($path === "") $page = ""; // home page
    if($path !== "" && strpos($path, '/') === false && strpos(implode(X, $ss['page'][1]), '_' . $path . '.') !== false) $page = 'page';
    if($path === $config['index']['slug']) $page = 'index';
    if($s[0] === $config['index']['slug'] && isset($s[1])) {
        if( ! is_numeric($s[1])) {
            $page = strpos(implode(X, $ss['article'][1]), '_' . $s[1] . '.') !== false ? 'article' : '404';
        } else {
            $page = 'index';
        }
    }
    if(strpos($path . '/', $config['tag']['slug'] . '/') === 0) $page = 'tag';
    if(strpos($path . '/', $config['archive']['slug'] . '/') === 0) $page = 'archive';
    if(strpos($path . '/', $config['search']['slug'] . '/') === 0) $page = 'search';
    if(strpos($path . '/', $config['manager']['slug'] . '/') === 0 && Plugin::exist('manager')) $page = 'manager';
    if(strpos($path . '/', 'sitemap/') === 0) $page = 'sitemap';
    if(strpos($path . '/', 'feed/') === 0) $page = 'feed';
    if(strpos($path . '/', 'feed/rss/') === 0) $page = 'rss';
    if(strpos($path . '/', 'feed/json/') === 0) $page = 'json';

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
    $lang = Converter::toArray(File::open($lang)->read(""), S, '  ');
    if($config['language'] !== 'en_US') {
        $lang_a = Converter::toArray(File::open($lang_a)->read(""), S, '  ');
        Mecha::extend($lang, $lang_a);
    }

    $config['query'] = $config['url_query'] = HTTP::query($_GET);
    $config['offset'] = isset($s[1]) && is_numeric($s[1]) ? (int) $s[1] : 1;
    $config['page_type'] = $page;
    $config['speak'] = $lang;

    $config['is'] = array(
        'post' => $page !== '404' && Mecha::walk(glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR))->has(POST . DS . $page),
        'posts' => Mecha::walk(array('index', 'tag', 'archive', 'search', ""))->has($page),
        'response' => false, // TODO: make this usable
        'responses' => false // TODO: make this usable
    );

    unset($ss, $s, $lang, $lang_a);

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

Config::plug('url', function($key = 'url', $fallback = false) {
    return Config::get('url_' . $key, $fallback);
});