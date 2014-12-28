<?php

Route::accept($config->manager->slug . '/plugin/' . basename(__DIR__) . '/update', function() use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort(); // [1]
    }

    if(Request::post()) {

        Guardian::checkToken(Request::post('token')); // [2]

        File::write('test!')->saveTo(PLUGIN . DS . basename(__DIR__) . DS . 'states' . DS . 'test.txt', 0600);

        Notify::success('Plugin updated.'); // [3]

        Guardian::kick(dirname($config->url_current)); // [4]

    }

});