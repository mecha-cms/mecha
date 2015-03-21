<?php if( ! defined('ROOT')) die('Rejected.');


// error_reporting(E_ALL);
// ini_set('display_errors', 1);

ini_set('error_log', SYSTEM . DS . 'log' . DS . 'errors.log');
ini_set('session.gc_probability', 1);


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
 * Loading Workers
 * ---------------
 */

spl_autoload_register(function($worker) {
    $path = SYSTEM . DS . 'kernel' . DS . strtolower($worker) . '.php';
    if(file_exists($path)) require $path;
});


/**
 * Loading Functions
 * -----------------
 */

foreach(glob(SYSTEM . DS . 'fn.*.php') as $fn) {
    require $fn;
}

// internal only
define('SEPARATOR_ENCODED', Text::parse(SEPARATOR, '->ascii'));


/**
 * Start the Sessions
 * ------------------
 */

session_save_path(SYSTEM . DS . 'log' . DS . 'sessions');
session_start();


/**
 * Load the Configuration Data
 * ---------------------------
 */

Config::load();

$config = Config::get();
$speak = Config::speak();


/**
 * Create Proper Query String Data
 * -------------------------------
 */

if($config->page_type != 'home') {
    array_shift($_GET);
}

$queries = array();
foreach($_GET as $k => $v) {
    $queries[] = $k . '=' . $v;
}

$config->url_query = ! empty($queries) ? '?' . implode('&', $queries) : "";
Config::set('url_query', $config->url_query);


/**
 * First Installation
 * ------------------
 */

if(File::exist(ROOT . DS . 'install.php')) {
    Guardian::kick($config->url . '/install.php');
}


/**
 * Set Default Time Zone Before Launch
 * -----------------------------------
 */

date_default_timezone_set($config->timezone);


/**
 * Inject Widget's CSS and JavaScript
 * ----------------------------------
 */

if($config->widget_include_css) {
    Weapon::add('shell_before', function() {
        echo Asset::stylesheet('cabinet/shields/widgets.css', "", 'widgets.min.css');
    });
}

if($config->widget_include_js) {
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript('cabinet/shields/widgets.js', "", 'widgets.min.js');
    });
}


/**
 * Loading Plugins
 * ---------------
 */

if($plugins_order = File::exist(CACHE . DS . 'plugins.order.cache')) {
    $plugins = File::open($plugins_order)->unserialize();
} else {
    $plugins = array();
    $plugins_list = glob(PLUGIN . DS . '*' . DS . 'launch.php');
    $plugins_payload = count($plugins_list);
    sort($plugins_list);
    for($i = 0; $i < $plugins_payload; ++$i) {
        $plugins[] = false; // $plugins[] = '-- ' . dirname($plugins_list[$i]);
    }
    for($j = 0; $j < $plugins_payload; ++$j) {
        if($overtake = File::exist(dirname($plugins_list[$j]) . DS . '__overtake.txt')) {
            $to_index = ((int) file_get_contents($overtake)) - 1;
            if($to_index < 0) $to_index = 0;
            if($to_index > $plugins_payload - 1) $to_index = $plugins_payload - 1;
            array_splice($plugins, $to_index, 0, array(dirname($plugins_list[$j])));
        } else {
            $plugins[$j] = dirname($plugins_list[$j]);
        }
    }
    File::serialize($plugins)->saveTo(CACHE . DS . 'plugins.order.cache');
}

for($k = 0, $plugins_launched = count($plugins); $k < $plugins_launched; ++$k) {
    if($plugins[$k]) {
        if( ! $language = File::exist($plugins[$k] . DS . 'languages' . DS . $config->language . DS . 'speak.txt')) {
            $language = $plugins[$k] . DS . 'languages' . DS . 'en_US' . DS . 'speak.txt';
        }
        if(File::exist($language)) {
            Config::merge('speak', Text::toArray(File::open($language)->read(), ':', '  '));
        }
        if($launch = File::exist($plugins[$k] . DS . 'launch.php')) {
            include $launch;
        }
    }
}


/**
 * Check the Plugins Order
 * -----------------------
 */

// var_dump($plugins); exit;


/**
 * Include User Defined Functions
 * ------------------------------
 */

if($function = File::exist(SHIELD . DS . $config->shield . DS . 'functions.php')) {
    include $function;
}


/**
 * Handle Shortcodes in Content
 * ----------------------------
 */

Filter::add('shortcode', function($content) use($config, $speak) {
    if(strpos($content, '{{') === false) return $content;
    $d = DECK . DS . 'workers' . DS . 'repair.state.shortcode.php';
    $shortcodes = file_exists($d) ? include $d : array();
    if($file = Get::state_shortcode()) {
        foreach($file as $key => $value) {
            unset($shortcodes[$key]);
        }
        $shortcodes = array_merge($shortcodes, $file);
    }
    $regex = array();
    foreach($shortcodes as $key => $value) {
        $regex['#(?<!`)' . str_replace(
            array(
                '%s'
            ),
            array(
                '(.*?)'
            ),
        preg_quote($key, '#')) . '(?!`)#'] = $value;
    }
    $content = preg_replace(array_keys($regex), array_values($regex), $content);
    if(strpos($content, '{{php}}') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{php\}\}(?!`)([\s\S]*?)(?<!`)\{\{\/php\}\}(?!`)#', function($matches) {
            return Converter::phpEval($matches[1]);
        }, $content);
    }
    return preg_replace('#`\{\{(.*?)\}\}`#', '{{$1}}', $content);
}, 20);


/**
 * Others
 * ------
 *
 * I'm trying to not touching the source code of the Markdown plugin at all.
 *
 * [1]. Add bordered class for tables in content.
 * [2]. Add `rel="nofollow"` attribute in external links.
 *
 */

Filter::add('content', function($content) use($config) {
    if($config->html_parser) {
        return preg_replace(
            array(
                '#<table>#',
                '#<a href="(?!' . preg_quote($config->url, '/') . '|[\.\/\?\#])#'
            ),
            array(
                '<table class="table-bordered table-full-width">',
                '<a rel="nofollow" href="'
            ),
        $content);
    }
    return $content;
}, 20);


/**
 * Set Page Metadata
 * -----------------
 */

Weapon::add('meta', function() {
    $config = Config::get();
    $speak = Config::speak();
    $html  = O_BEGIN . '<meta charset="' . $config->charset . '"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<meta name="viewport" content="width=device-width"' . ES . NL;
    if(isset($config->article->description)) {
        $html .= str_repeat(TAB, 2) . '<meta name="description" content="' . strip_tags($config->article->description) . '"' . ES . NL;
    } elseif(isset($config->page->description)) {
        $html .= str_repeat(TAB, 2) . '<meta name="description" content="' . strip_tags($config->page->description) . '"' . ES . NL;
    } else {
        $html .= str_repeat(TAB, 2) . '<meta name="description" content="' . strip_tags($config->description) . '"' . ES . NL;
    }
    $html .= str_repeat(TAB, 2) . '<meta name="author" content="' . $config->author . '"' . ES . NL;
    echo Filter::apply('meta', $html, 1);
}, 10);

Weapon::add('meta', function() {
    $config = Config::get();
    $html  = str_repeat(TAB, 2) . '<title>' . strip_tags($config->page_title) . '</title>' . NL;
    $html .= str_repeat(TAB, 2) . '<!--[if IE]><script src="' . $config->protocol . 'html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->' . NL;
    echo Filter::apply('meta', $html, 2);
}, 20);

Weapon::add('meta', function() {
    $config = Config::get();
    $speak = Config::speak();
    $html  = str_repeat(TAB, 2) . '<link href="' . $config->url . '/favicon.ico" rel="shortcut icon" type="image/x-icon"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<link href="' . $config->url_current . '" rel="canonical"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<link href="' . $config->url . '/sitemap" rel="sitemap"' . ES . NL;
    $html .= str_repeat(TAB, 2) . '<link href="' . $config->url . '/feed/rss" rel="alternate" type="application/rss+xml" title="' . $speak->feeds . $config->title_separator . $config->title . '"' . ES . O_END;
    echo Filter::apply('meta', $html, 3);
}, 30);

Weapon::add('SHIPMENT_REGION_TOP', function() {
    Weapon::fire('meta');
}, 10);