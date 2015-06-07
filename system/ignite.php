<?php if( ! defined('ROOT')) die('Rejected.');


/**
 * Error Reporting
 * ---------------
 */

if(DEBUG) {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    ini_set('error_log', SYSTEM . DS . 'log' . DS . 'errors.log');
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

foreach(glob(SYSTEM . DS . 'plug' . DS . '*.php', GLOB_NOSORT) as $plug) {
    require $plug;
}


/**
 * Start the Sessions
 * ------------------
 */

Session::start(SYSTEM . DS . 'log' . DS . 'sessions');


/**
 * Load the Configuration Data
 * ---------------------------
 */

Config::load();

$config = Config::get();
$speak = Config::speak();


/**
 * Define Allowed File Extensions
 * ------------------------------
 */

$e = explode(',', FONT_EXT . ',' . IMAGE_EXT . ',' . MEDIA_EXT . ',' . PACKAGE_EXT . ',' . SCRIPT_EXT);
File::configure('file_extension_allow', array_unique($e));


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

$plugins = Plugin::load();

for($i = 0, $count = count($plugins); $i < $count; ++$i) {
    if($plugins[$i]) {
        if( ! $language = File::exist($plugins[$i] . DS . 'languages' . DS . $config->language . DS . 'speak.txt')) {
            $language = $plugins[$i] . DS . 'languages' . DS . 'en_US' . DS . 'speak.txt';
        }
        if(File::exist($language)) {
            Config::merge('speak', Text::toArray(File::open($language)->read(), ':', '  '));
        }
        if($launch = File::exist($plugins[$i] . DS . 'launch.php')) {
            if(strpos(basename($plugins[$i]), '__') === 0) {
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

if($config->html_parser) {
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
    } else if(isset($config->page->description)) {
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