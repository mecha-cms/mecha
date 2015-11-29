<?php


/**
 * AJAX
 * ----
 */

Route::post($config->manager->slug . '/ajax/(:any):(:all)', function($action = "", $kind = "") use($config, $speak) {
    $segment = 'ajax';
    $P = array('data' => Request::post());
    $P['data']['action'] = $action;
    include ROOT . DS . File::path($kind) . '.php';
    exit;
});