<?php

function route(...$lot) {
    return count($lot) < 2 ? Route::get(...$lot) : Route::set(...$lot);
}

// Load all route(s)…
Hook::set('get', 'Route::start', 1000);
