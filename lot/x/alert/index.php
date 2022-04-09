<?php

// Need to load `alert` function immediately because `Alert` class usually will be called when processing the form data,
// so the auto-loader feature will not provide any benefit. By loading this function immediately, it will prevent the
// appearance of error message(s) such as that `alert` function is not (yet) defined.
function alert(...$lot) {
    return count($lot) < 2 ? Alert::get(...$lot) : Alert::set(...$lot);
}

// Set default alert layout if `.\lot\y\*\alert.php` file does not exist.
if (class_exists('Layout') && !Layout::path('alert')) {
    Layout::set('alert', __DIR__ . D . 'engine' . D . 'y' . D . 'alert.php');
}