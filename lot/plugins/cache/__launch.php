<?php


/**
 * Plugin Updater
 * --------------
 */

Route::over($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    $_POST['path'] = array();
    foreach(explode("\n", Request::post('content')) as $path) {
        $s = explode(' ~', $path, 2);
        if(trim($path) !== "" && trim($s[0]) !== "") {
            $_POST['path'][trim($s[0])] = isset($s[1]) && trim($s[1]) !== "" ? (float) trim($s[1]) : true;
        }
    }
    unset($_POST['content']);
});


/**
 * Cache Killer
 * ------------
 */

function do_remove_cache() {
    global $config;
    $c_cache = $config->states->{'plugin_' . md5(File::B(__DIR__))};
    foreach($c_cache->path as $path => $expire) {
        $path = str_replace(
            array(
                '(:any)',
                '(:num)',
                '(:all)',
                '(',
                ')',
                '|',
                '/',
                ':'
            ),
            array(
                '*',
                '[0-9]*',
                '*',
                '{',
                '}',
                ',',
                '.',
                '.'
            ),
        $path) . '.cache';
        if($cache = File::exist(CACHE . DS . $path)) {
            File::open($cache)->delete();
        } else if($caches = glob(CACHE . DS . $path, GLOB_NOSORT | GLOB_BRACE)) {
            foreach($caches as $cache) {
                File::open($cache)->delete();
            }
        }
    }
}

$hooks = Mecha::walk(glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR), function($v) {
    return 'on_' . File::B($v) . '_update';
});

Weapon::add($hooks, 'do_remove_cache', 10);