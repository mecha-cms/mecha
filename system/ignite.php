<?php


// error_reporting(E_ALL);
// ini_set('display_errors', 1);


/**
 * Start the Session Launch
 * ------------------------
 */

session_start();


/**
 * => `http://www.php.net/manual/en/security.magicquotes.disabling.php`
 * --------------------------------------------------------------------
 */

if(get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}


/**
 * Loading Workers
 * ---------------
 */

function prepare_to_launch($workers) {
    foreach(glob(SYSTEM . DS . 'kernel' . DS . '*.php') as $workers) {
        include_once $workers;
    }
}

spl_autoload_register('prepare_to_launch');

$config = Config::get();
$speak = Config::speak();

if(File::exist(ROOT . DS . 'install.php')) {
    Guardian::kick('install.php');
}


/**
 * Set Default TimeZone Before Launch
 * ----------------------------------
 */

date_default_timezone_set($config->timezone);


/**
 * Inject Widget's CSS and JavaScript
 * ----------------------------------
 */

Weapon::add('shell_before', function() {
    echo Asset::stylesheet('cabinet/shields/widgets.css');
});

Weapon::add('sword_after', function() {
    echo Asset::script('cabinet/shields/widgets.js');
});


/**
 * Loading Plugins
 * ---------------
 */

foreach(glob(PLUGIN . DS . '*' . DS . 'launch.php') as $plugin) {
    include $plugin;
}


/**
 * Include User Defined Functions
 * ------------------------------
 */

if($function = File::exist(SHIELD . DS . $config->shield . DS . 'functions.php')) {
    include $function;
}


/**
 * Handle Shortcode in Contents
 * ----------------------------
 */

Filter::add('shortcode', function($content) use($config, $speak) {
    if($file = File::exist(STATE . DS . 'shortcodes.txt')) {
        $shortcodes = unserialize(File::open($file)->read());
    } else {
        $shortcodes = include STATE . DS . 'repair.shortcodes.php';
    }
    $regex = array();
    foreach($shortcodes as $key => $value) {
        $regex['#(?!`)' . str_replace(
            array(
                '%s'
            ),
            array(
                '(.*?)'
            ),
        preg_quote($key)) . '(?!`)#'] = $value;
    }
    if(strpos($content, '{{php}}') !== false) {
        $content = preg_replace_callback('#(?!`)\{\{php\}\}([\s\S]+?)\{\{\/php\}\}(?!`)#m', function($matches) {
            return Converter::phpEval($matches[1]);
        }, $content);
    }
    $regex['#`\{\{(.*?)\}\}`#'] = '{{$1}}'; // the escaped shortcode
    return preg_replace(array_keys($regex), array_values($regex), $content);
}, 10);


/**
 * Others
 * ------
 *
 * I'm trying to not touching the source code of the Markdown plugin at all.
 *
 * [1]. Add bordered class for tables in contents.
 * [2]. Add `rel="nofollow"` attribute in external links.
 *
 */

Filter::add('content', function($content) use($config) {
    return preg_replace(
        array(
            '#<table>#',
            '#<a href="(https?\:\/\/)(?!' . preg_quote($config->host) . ')#'
        ),
        array(
            '<table class="table-bordered">',
            '<a rel="nofollow" href="$1'
        ),
    $content);
}, 10);


/**
 * Add Global Cache Killer for Articles and Pages
 * ----------------------------------------------
 */

function kill_cache() {
    global $config;
    $root = ( ! empty($config->base) ? str_replace('/', '.', $config->base) . '.' : "");
    File::open(CACHE . DS . $root . 'sitemap.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.rss.cache.txt')->delete();
}

Weapon::add('on_article_update', 'kill_cache', 10);
Weapon::add('on_page_update', 'kill_cache', 10);


/**
 * Add Default Article and Page Footer Links
 * -----------------------------------------
 */

Weapon::add('article_footer', function($article) {
    $config = Config::get();
    $speak = Config::speak();
    if(Guardian::happy() && $config->page_type == 'manager') {
        echo '<a href="' . $config->url . '/' . $config->manager->slug . '/article/repair/id:' . $article->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/article/kill/id:' . $article->id . '">' . $speak->delete . '</a>';
    }
}, 20);

Weapon::add('page_footer', function($page) {
    $config = Config::get();
    $speak = Config::speak();
    if(Guardian::happy() && $config->page_type == 'manager') {
        echo '<a href="' . $config->url . '/' . $config->manager->slug . '/page/repair/id:' . $page->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/page/kill/id:' . $page->id . '">' . $speak->delete . '</a>';
    }
}, 20);


/**
 * Add Default Comment Footer Links
 * --------------------------------
 */

Weapon::add('comment_footer', function($comment, $article) {
    $config = Config::get();
    $speak = Config::speak();
    if(Guardian::happy()) {
        echo '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $comment->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/comment/kill/id:' . $comment->id . '">' . $speak->delete . '</a>';
    }
}, 20);