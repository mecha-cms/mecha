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

foreach(glob(SYSTEM . DS . 'kernel' . DS . '*.php') as $workers) {
    spl_autoload_register(function() use($workers) {
        include_once $workers;
    });
}

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