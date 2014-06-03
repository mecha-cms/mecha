<?php


// error_reporting(E_ALL);
// ini_set('display_errors', 1);


/**
 * Start the session launch ...
 */

session_start();


/**
 * => `http://www.php.net/manual/en/security.magicquotes.disabling.php`
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
 * Loading workers ...
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
 * Set default timezone before launch ...
 */

date_default_timezone_set($config->timezone);


/**
 * Inject widget's CSS and JavaScript
 */

Weapon::add('shell_before', function() {
    echo Asset::stylesheet('cabinet/shields/widgets.css');
});

Weapon::add('sword_after', function() {
    echo Asset::script('cabinet/shields/widgets.js');
});


/**
 * Inject some required assets for managers
 */

if(Guardian::happy()) {
    Weapon::add('shell_after', function() use($config) {
        echo '<link href="' . $config->protocol . 'netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">';
        echo Asset::stylesheet('shell/manager.css');
    }, 10);
    Weapon::add('cargo_before', function() use($config, $speak) {
        echo '<div class="author-banner">' . $speak->welcome . ' <strong>' . Guardian::get('author') . '!</strong> &bull; <a href="' . $config->url . '/' . $config->manager->slug . '/logout">' . $speak->logout . '</a></div>';
    }, 10);
    Weapon::add('sword_after', function() {
        echo Asset::script(array(
            'manager/sword/editor/editor.min.js',
            'manager/sword/editor/mte.min.js',
            'manager/sword/zepto.min.js',
            'manager/sword/slug.js',
            'manager/sword/tab.js',
            'manager/sword/row.js'
        ));
    }, 10);
}


/**
 * Loading plugins ...
 */

foreach(glob(PLUGIN . DS . '*' . DS . 'launch.php') as $plugin) {
    include $plugin;
}


/**
 * Include user defined functions
 */

if($function = File::exist(SHIELD . DS . $config->shield . DS . 'functions.php')) {
    include $function;
}


/**
 * Handle shortcode in contents ...
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
});


/**
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
});


/**
 * Add global cache killer for posts
 */

Weapon::add('on_page_update', function() use($config) {
    $root = ( ! empty($config->base) ? str_replace('/', '.', $config->base) . '.' : "");
    File::open(CACHE . DS . $root . 'sitemap.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.rss.cache.txt')->delete();
});