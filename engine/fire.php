<?php

// Enable/disable debug mode (default is `null`)
if (defined('DEBUG')) {
    ini_set('error_log', ENGINE . DS . 'log' . DS . 'error.log');
    if (DEBUG === true) {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', true);
        ini_set('display_startup_errors', true);
        ini_set('html_errors', 1);
    } else if (DEBUG === false) {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
    }
}

// Normalize line–break
$vars = [&$_GET, &$_POST, &$_REQUEST, &$_COOKIE];
array_walk_recursive($vars, function(&$v) {
    $v = str_replace(["\r\n", "\r"], "\n", $v);
});

$f = ENGINE . DS;
d($f . 'kernel', function($w, $n) use($f) {
    $f .= 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

File::$config['extensions'] = array_unique(explode(',', FONT_X . ',' . IMAGE_X . ',' . MEDIA_X . ',' . PACKAGE_X . ',' . SCRIPT_X));

Session::ignite();
Config::ignite();

$config = new Config;
$url = new URL;

// Set default date time zone
Date::zone($config->zone);

// Must be set after date time zone set
Language::ignite();

$date = new Date;
$language = new Language;

$seeds = [
    'config' => $config,
    'date' => $date,
    'language' => $language,
    'site' => $config,
    'url' => $url,
    'u_r_l' => $url
];

// Plant…
Lot::set($seeds);

$extends = [];
foreach (g(EXTEND . DS . '*', '{index__,index}.php') as $v) {
    $extends[$v] = (float) File::open(Path::D($v) . DS . 'index.stack')->get(0, 10);
}

asort($extends);
extract($seeds);
$c = [];
foreach ($extends as $k => $v) {
    $f = Path::D($k) . DS;
    $i18n = $f . 'lot' . DS . 'language' . DS;
    if ($l = File::exist([
        $i18n . $config->language . '.page',
        $i18n . 'en-us.page'
    ])) {
        $c[$l] = filemtime($l);
    }
    $f .= 'engine' . DS;
    d($f . 'kernel', function($w, $n) use($f, $seeds) {
        $f .= 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seeds);
            require $f;
        }
    }, $seeds);
}

$id = array_sum($c);
if (Cache::expire(EXTEND, $id)) {
    $content = [];
    foreach ($c as $k => $v) {
        $i18n = new Page($k, [], 'language');
        $fn = 'From::' . __c2f__($i18n->type, '_');
        $content = array_replace_recursive($content, is_callable($fn) ? call_user_func($fn, $i18n->content) : (array) $i18n->content);
    }
    Cache::set(EXTEND, $content, $id);
} else {
    $content = Cache::get(EXTEND, []);
}

// Load extension(s)’ language…
Language::set($content);

// Load all extension(s)…
foreach (array_keys($extends) as $v) {
    call_user_func(function() use($v) {
        extract(Lot::get(null, []));
        require $v;
    });
}

// Load user language(s) from the current shield folder if any
$folder_shield = SHIELD . DS . $config->shield . DS;
$i18n = $folder_shield . 'language' . DS;
if ($l = File::exist([
    $i18n . $config->language . '.page',
    $i18n . 'en-us.page'
])) {
    $i18n = new Page($l, [], 'language');
    $fn = 'From::' . str_replace('-', '_', __c2f__($i18n->type));
    Language::set(is_callable($fn) ? call_user_func($fn, $i18n->content) : $i18n->content);
}

// Load user function(s) from the current shield folder if any
if ($fn = File::exist($folder_shield . 'index.php')) require $fn;
if ($fn = File::exist($folder_shield . 'index__.php')) require $fn;

function do_fire() {
    Route::fire();
    Shield::abort();
}

Hook::set('fire', 'do_fire')->fire('fire');