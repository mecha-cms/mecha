<?php


/**
 * Backend Route(s)
 * ----------------
 *
 * Load the routes.
 *
 */

Weapon::add('routes_before', function() use($config, $speak, $segment) {
    // loading cargo ...
    if($config->page_type === 'manager') {
        Config::set('cargo', 'cargo.' . $segment . '.php');
        include __DIR__ . DS . 'workers' . DS . 'cargo.php';
    }
    if($detour = File::exist(__DIR__ . DS . 'workers' . DS . 'route.' . $segment . '.php')) {
        require $detour;
    }
});