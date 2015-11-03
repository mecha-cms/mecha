<?php if( ! defined('ROOT')) die('Rejected!');


/**
 * Error Reporting
 * ---------------
 */

if(DEBUG) {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    ini_set('error_log', LOG . DS . 'errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', FALSE);
    ini_set('display_startup_errors', FALSE);
}


/**
 * => `http://www.php.net/manual/en/security.magicquotes.disabling.php`
 * --------------------------------------------------------------------
 */

$gpc = array(&$_GET, &$_POST, &$_REQUEST, &$_COOKIE);

array_walk_recursive($gpc, function(&$value) {
    $value = str_replace("\r", "", $value);
    if(get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
});


/**
 * Loading Worker(s)
 * -----------------
 */

spl_autoload_register(function($worker) {
    $path = SYSTEM . DS . 'kernel' . DS . strtolower($worker) . '.php';
    if(file_exists($path)) require $path;
});


/**
 * Loading Function(s)
 * -------------------
 */

foreach(glob(SYSTEM . DS . 'plug' . DS . '*.php', GLOB_NOSORT) as $plug) {
    require $plug;
}


/**
 * Start the Session(s)
 * --------------------
 */

Session::start();


/**
 * Load the Configuration Data
 * ---------------------------
 */

Config::load();

$config = Config::get();
$speak = Config::speak();


/**
 * Define Allowed File Extension(s)
 * --------------------------------
 */

$e = explode(',', FONT_EXT . ',' . IMAGE_EXT . ',' . MEDIA_EXT . ',' . PACKAGE_EXT . ',' . SCRIPT_EXT);
File::configure('file_extension_allow', array_unique($e));


/**
 * Set Default Time Zone
 * ---------------------
 */

date_default_timezone_set($config->timezone);


/**
 * Set Page Metadata
 * -----------------
 */

Weapon::add('meta', function() {
    $config = Config::get();
    $speak = Config::speak();
    $html  = O_BEGIN . Cell::meta(null, null, array('charset' => $config->charset)) . NL;
    $html .= Cell::meta('viewport', 'width=device-width', array(), 2) . NL;
    if(isset($config->article->description)) {
        $description = strip_tags($config->article->description);
    } else if(isset($config->page->description)) {
        $description = strip_tags($config->page->description);
    } else {
        $description = strip_tags($config->description);
    }
    $html .= Cell::meta('description', $description, array(), 2) . NL;
    $html .= Cell::meta('author', strip_tags($config->author->name), array(), 2) . NL;
    echo Filter::apply('meta', $html, 1);
}, 10);

Weapon::add('meta', function() {
    $config = Config::get();
    $html  = Cell::title(strip_tags($config->page_title), array(), 2) . NL;
    $html .= Cell::_('[if IE]>' . Cell::script($config->protocol . 'html5shiv.googlecode.com/svn/trunk/html5.js') . '<![endif]', 2, "") . NL;
    echo Filter::apply('meta', $html, 2);
}, 20);

Weapon::add('meta', function() {
    $config = Config::get();
    $speak = Config::speak();
    $html  = Cell::link($config->url . '/favicon.ico', 'shortcut icon', 'image/x-icon', array(), 2) . NL;
    $html .= Cell::link($config->url_current, 'canonical', null, array(), 2) . NL;
    $html .= Cell::link($config->url . '/sitemap', 'sitemap', null, array(), 2) . NL;
    $html .= Cell::link($config->url . '/feed/rss', 'alternate', 'application/rss+xml', array(
        'title' => $speak->feeds . $config->title_separator . $config->title
    ), 2) . O_END;
    echo Filter::apply('meta', $html, 3);
}, 30);

Weapon::add('SHIPMENT_REGION_TOP', function() {
    Weapon::fire('meta');
}, 10);


/**
 * Inject Widget's CSS and JavaScript
 * ----------------------------------
 */

if($config->widget_include_css) {
    Weapon::add('shell_before', function() {
        echo Asset::stylesheet('cabinet/shields/widgets.css', "", 'shell/widgets.min.css');
    });
}

if($config->widget_include_js) {
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript('cabinet/shields/widgets.js', "", 'sword/widgets.min.js');
    });
}


/**
 * Loading Plugin(s)
 * -----------------
 */

$plugins = Plugin::load();

for($i = 0, $count = count($plugins); $i < $count; ++$i) {
    if(isset($plugins[$i]) && $plugins[$i] !== false) {
        $plugins[$i] = PLUGIN . DS . $plugins[$i];
        if( ! $language = File::exist($plugins[$i] . DS . 'languages' . DS . $config->language . DS . 'speak.txt')) {
            $language = $plugins[$i] . DS . 'languages' . DS . 'en_US' . DS . 'speak.txt';
        }
        if(File::exist($language)) {
            Config::merge('speak', Text::toArray(File::open($language)->read(), ':', '  '));
        }
        if($launch = File::exist($plugins[$i] . DS . 'launch.php')) {
            if(strpos(File::B($plugins[$i]), '__') === 0) {
                if(Guardian::happy() && $config->page_type === 'manager') {
                    include $launch; // backend
                }
            } else {
                include $launch; // frontend
            }
        }
        if($launch = File::exist($plugins[$i] . DS . '__launch.php')) {
            if(Guardian::happy() && $config->page_type === 'manager') {
                include $launch; // backend
            }
        }
    }
}


/**
 * Check the Plugin(s) Order
 * -------------------------
 */

// var_dump($plugins); exit;


/**
 * Include User Defined Function(s)
 * --------------------------------
 */

if($function = File::exist(SHIELD . DS . $config->shield . DS . 'functions.php')) {
    include $function;
}


/**
 * Handle Shortcode(s) in Content
 * ------------------------------
 */

Filter::add('shortcode', function($content) use($config, $speak) {
    if(strpos($content, '{{') === false) return $content;
    foreach(Get::state_shortcode() as $key => $value) {
        $key = preg_quote($key, '#');
        // %[a,b,c]: options ... accept `a`, `b`, or `c`
        if(strpos($key, '%\\[') !== false) {
            $key = preg_replace_callback('#%\\\\\[(.*?)\\\\\]#', function($matches) {
                return '(' . str_replace(',', '|', $matches[1]) . ')';
            }, $key);
        }
        // %s: accept any values without line breaks
        // %S: accept any values with/without line breaks
        // %i: accept integer numbers
        // %f: accept float numbers
        // %b: accept boolean values
        $key = str_replace(
            array('%s', '%S', '%i', '%f', '%b'),
            array('(.+?)', '([\s\S]+?)', '(\d+?)', '((?:\d*\.)?\d+?)', '\b(TRUE|FALSE|YES|NO|ON|OFF|true|false|yes|no|on|off|1|0)\b'),
        $key);
        $content = preg_replace('#(?<!`)' . $key . '(?!`)#', Converter::WD($value), $content);
    }
    if(strpos($content, '{{php}}') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{php\}\}(?!`)([\s\S]*?)(?<!`)\{\{\/php\}\}(?!`)#', function($matches) {
            return Converter::phpEval($matches[1]);
        }, $content);
    }
    return $content;
}, 20);

// YOU ARE HERE! -- You can specify your own shortcode priority to be greater
// than the default shortcode file priority, but lesser than the shortcode
// deactivation priority by determining the shortcode priority between 20 - 30

Filter::add('shortcode', function($content) {
    if(strpos($content, '`{{') === false) return $content;
    return str_replace(array('`{{', '}}`'), array('{{', '}}'), $content);
}, 30);


/**
 * Other(s)
 * --------
 *
 * I'm trying to not touching the source code of the Markdown plugin at all.
 *
 * [1]. Add bordered class for tables in content.
 * [2]. Add `rel="nofollow"` attribute in external links.
 *
 */

if($config->html_parser !== false && $config->html_parser !== 'HTML') {
    Filter::add('content', function($content) use($config) {
        return preg_replace(
            array(
                '#<table>#',
                '#<a href="(?!' . preg_quote($config->url, '/') . '|javascript:|[\.\/\?\#])#'
            ),
            array(
                '<table class="table-bordered table-full-width">',
                '<a rel="nofollow" href="'
            ),
        $content);
        return $content;
    }, 20);
}