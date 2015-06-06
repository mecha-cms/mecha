<?php


/**
 * AJAX
 * ----
 */

Route::post($config->manager->slug . '/ajax/(:any):(:any)', function($action = "", $kind = "") use($config, $speak) {
    $P = array('data' => Request::post());
    $P['kind'] = $kind;
    $the_path = strpos($kind, '/') === false ? DECK . DS . 'workers' . DS . 'unit.ajax.' . $action . '.' . $kind . '.php' : ROOT . DS . File::path($kind);
    include $the_path;
    exit;
});