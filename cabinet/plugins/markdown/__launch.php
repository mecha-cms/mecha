<?php


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $state = PLUGIN . DS . File::B(__DIR__) . DS . 'states' . DS;
        File::write(trim($request['abbr']))->saveTo($state . 'abbr.txt', 0600);
        File::write(trim($request['url']))->saveTo($state . 'url.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', $speak->plugin));
        Guardian::kick(File::D($config->url_current));
    }
});