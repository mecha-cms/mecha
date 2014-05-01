<?php

$config = Config::get();
$speak = Config::speak();

Route::accept($config->manager->slug . '/plugin/empty/update', function() use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort(); // [1]
    }

    if(Request::post()) {

        Guardian::checkToken(Request::post('token')); // [2]

        File::write('test!')->saveTo(ROOT . '/test-plugin.txt');

        Notify::success('Plugin updated.'); // [3]

        Guardian::kick(dirname($config->url_current));

    }

});