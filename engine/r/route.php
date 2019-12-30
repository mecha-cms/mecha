<?php

function route(...$v) {
    return count($v) < 2 ? Route::get(...$v) : Route::set(...$v);
}

// Load all route(s)…
Hook::set('get', 'Route::start', 1000);