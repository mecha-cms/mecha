<?php


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        unset($request['token']); // Remove token from request array
        $results = array();
        foreach(explode("\n", $request['content']) as $path) {
            $s = explode(' ~', $path, 2);
            if(trim($path) !== "" && trim($s[0]) !== "") {
                $results[trim($s[0])] = isset($s[1]) && trim($s[1]) !== "" ? (float) trim($s[1]) : true;
            }
        }
        unset($request['content']); // Remove content from request array
        $request['path'] = $results;
        File::serialize($request)->saveTo(__DIR__ . DS . 'states' . DS . 'config.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', $speak->plugin));
        Guardian::kick(File::D($config->url_current));
    }
});


/**
 * Cache Killer
 * ------------
 */

function do_remove_cache() {
    global $config, $c;
    foreach($c->path as $path => $expire) {
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
        } else {
            foreach(glob(CACHE . DS . $path, GLOB_NOSORT | GLOB_BRACE) as $cache) {
                File::open($cache)->delete();
            }
        }
    }
}

Weapon::add(array('on_article_update', 'on_page_update'), 'do_remove_cache', 10);