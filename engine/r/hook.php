<?php

function hook(...$lot) {
    return count($lot) < 2 ? Hook::get(...$lot) : Hook::set(...$lot);
}

header_register_callback(function() {
    Hook::fire('set');
});

register_shutdown_function(function() {
    // Run task(s) if any…
    if (is_file($f = ROOT . DS . 'task.php')) {
        (static function($f) {
            extract($GLOBALS, EXTR_SKIP);
            require $f;
        })($f);
    }
    // Fire!
    Hook::fire('get');
    Hook::fire('let'); // Will not work if there is an exit code declared in the previous hook(s)
});