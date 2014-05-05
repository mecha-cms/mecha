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
    foreach(glob(SYSTEM . '/kernel/*.php') as $workers) {
        include_once $workers;
    }
}

spl_autoload_register('prepare_to_launch');

$config = Config::get();
$speak = Config::speak();

if(File::exist(ROOT . '/install.php')) {
    Guardian::kick('install.php');
}

/**
 * Handling for missing files
 */
if( ! File::exist(STATE . '/fields.txt')) {
    File::write(serialize(array()))->saveTo(STATE . '/fields.txt');
}
if( ! File::exist(STATE . '/tags.txt')) {
    File::write(serialize(include STATE . '/repair.tags.php'))->saveTo(STATE . '/tags.txt');
}
if( ! File::exist(STATE . '/menus.txt')) {
    File::write("Home: /\nAbout: /about")->saveTo(STATE . '/menus.txt');
}
if( ! File::exist(STATE . '/shortcodes.txt')) {
    File::write(serialize(include STATE . '/repair.shortcodes.php'))->saveTo(STATE . '/shortcodes.txt');
}
if( ! File::exist(STATE . '/config.txt')) {
    File::write(serialize(include STATE . '/repair.config.php'))->saveTo(STATE . '/config.txt');
    Guardian::kick($config->url_current);
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
 * Include user defined functions
 */
if(File::exist(SHIELD . '/' . $config->shield . '/functions.php')) {
    include SHIELD . '/' . $config->shield . '/functions.php';
}

/**
 * Inject some required asset for managers
 */
if(Guardian::happy()) {
    Weapon::add('shell_after', function() use($config) {
        echo '<link href="' . $config->protocol . 'netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">';
        echo Asset::stylesheet('shell/manager.css');
    });
    Weapon::add('cargo_before', function() use($config, $speak) {
        echo '<div class="author-banner">' . $speak->welcome . ' <strong>' . Guardian::get('author') . '!</strong> &bull; <a href="' . $config->url . '/' . $config->manager->slug . '/logout">' . $speak->logout . '</a></div>';
    });
    Weapon::add('sword_after', function() {
        echo Asset::script(array(
            'manager/sword/editor/editor.min.js',
            'manager/sword/editor/mte.min.js',
            'manager/sword/markdown/markdown.js',
            'manager/sword/zepto.min.js',
            'manager/sword/slug.js',
            'manager/sword/tab.js',
            'manager/sword/row.js'
        ));
    });
}

/**
 * Loading plugins ...
 */
foreach(glob(PLUGIN . '/*/launch.php') as $plugin) {
    include $plugin;
}

/**
 * Handle shortcode in contents ...
 */
Filter::add('shortcode', function($content) {
    $regex = array();
    foreach(unserialize(File::open(STATE . '/shortcodes.txt')->read()) as $key => $value) {
        $regex['#(?!`)' . str_replace(
            array(
                '%s'
            ),
            array(
                '(.*?)'
            ),
        preg_quote($key)) . '(?!`)#'] = $value;
    }
    $regex['#`\{\{(.*?)\}\}`#'] = '{{$1}}'; // the escaped shortcode
    return preg_replace(array_keys($regex), array_values($regex), $content);

});

/**
 * I'm trying to not touching the source code of the Markdown plugin at all.
 *
 * [1]. Add bordered class for tables in page and comment.
 * [2]. Add `rel="nofollow"` attribute in comment links.
 */
Filter::add('content', function($content) {
    return str_replace('<table>', '<table class="table-bordered">', $content);
});
Filter::add('comment', function($comment) {
    return str_replace(array(
        '<a href="',
        '<table>'
    ),
    array(
        '<a rel="nofollow" href="',
        '<table class="table-bordered">'
    ), $comment);
});

/**
 * Add global cache killer for posts
 */
Weapon::add('on_page_update', function() use($config) {
    $root = ( ! empty($config->base) ? str_replace('/', '.', $config->base) . '.' : "");
    File::open(CACHE . '/' . $root . 'sitemap.cache.txt')->delete();
    File::open(CACHE . '/' . $root . 'feeds.cache.txt')->delete();
    File::open(CACHE . '/' . $root . 'feeds.rss.cache.txt')->delete();
});