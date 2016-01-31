<?php


/**
 * Error Log
 * ---------
 */

Route::accept($config->manager->slug . '/error', function() use($config, $speak) {
    Config::set(array(
        'page_title' => $speak->errors . $config->title_separator . $config->manager->title,
        'cargo' => 'cargo.error.php'
    ));
    Shield::lot(array(
        'segment' => 'error',
        'content' => File::open(ini_get('error_log'))->read(false)
    ))->attach('manager');
});


/**
 * Error Log Killer
 * ----------------
 */

Route::accept($config->manager->slug . '/error/do:kill', function() use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $errors = LOG . DS . 'errors.log';
    $G = array('data' => array('content' => File::open($errors)->read()));
    File::open($errors)->delete();
    Weapon::fire('on_error_destruct', array($G, $G));
    Notify::success(Config::speak('notify_success_deleted', $speak->file));
    Guardian::kick(File::D($config->url_current));
});