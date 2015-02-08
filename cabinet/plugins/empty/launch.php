<?php

Route::accept($config->manager->slug . '/plugin/' . basename(__DIR__) . '/update', function() use($config, $speak) {
    if( ! Guardian::happy()) {
        Shield::abort(); // [1]
    }
    if($request = Request::post()) {
        Guardian::checkToken($request['token']); // [2]
        File::write('test!')->saveTo(PLUGIN . DS . basename(__DIR__) . DS . 'states' . DS . 'config.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', array($speak->plugin))); // [3]
        Guardian::kick(dirname($config->url_current)); // [4]
    }
});