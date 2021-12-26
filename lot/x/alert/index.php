<?php

// Need to load `alert` function immediately because `Alert` class usually will be called when processing the
// form data, so the auto-loader feature will not provide any benefit. By loading this function immediately,
// it will prevent the appearance of error message(s) such as that `alert` function is not defined.
function alert(...$lot) {
    return count($lot) < 2 ? Alert::get(...$lot) : Alert::set(...$lot);
}

// Set default alert layout if `.\lot\layout\alert.php` file does not exist.
if (!is_file(LOT . D . 'layout' . D . 'alert.php')) {
    Layout::set('alert', __DIR__ . D . 'lot' . D . 'layout' . D . 'alert.php');
}