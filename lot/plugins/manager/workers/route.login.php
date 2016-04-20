<?php


/**
 * Login Page
 * ----------
 *
 * [1]. manager/login
 *
 */

Route::accept($config->manager->slug . '/login', function() use($config, $speak) {
    if( ! File::exist(File::D(__DIR__) . DS . 'launch.php')) {
        Shield::abort('404-manager');
    }
    if(Guardian::happy()) {
        Guardian::kick($config->manager->slug . '/article');
    }
    Config::set(array(
        'page_title' => $speak->log_in . $config->title_separator . $config->title,
        'cargo' => 'cargo.login.php'
    ));
    include __DIR__ . DS . 'cargo.php';
    if($request = Request::post()) {
        Guardian::authorize()->kick( ! empty($request['kick']) ? $request['kick'] : $config->manager->slug . '/article');
    }
    Shield::attach('manager-login');
}, 20);


/**
 * Logout Page
 * -----------
 *
 * [1]. manager/logout
 *
 */

Route::accept($config->manager->slug . '/logout', function() use($config, $speak) {
    Notify::success(ucfirst(strtolower($speak->logged_out)) . '.');
    Guardian::reject()->kick($config->manager->slug . '/login');
}, 21);