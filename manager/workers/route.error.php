<?php


/**
 * Error Log
 * ---------
 */

Route::accept($config->manager->slug . '/error', function() use($config, $speak) {
    Config::set(array(
        'page_title' => $speak->errors . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'error.php'
    ));
    Shield::define('the_content', File::open(SYSTEM . DS . 'log' . DS . 'errors.log')->read(false))->attach('manager', false);
});


/**
 * Error Log Killer
 * ----------------
 */

Route::accept($config->manager->slug . '/error/kill', function() use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    $errors = SYSTEM . DS . 'log' . DS . 'errors.log';
    $G = array('data' => array('content' => File::open($errors)->read()));
    File::open($errors)->delete();
    Weapon::fire('on_error_destruct', array($G, $G));
    Notify::success(Config::speak('notify_success_deleted', array($speak->file)));
    Guardian::kick(dirname($config->url_current));
});