<?php

if (!is_file(LOT . D . 'layout' . D . 'alert.php')) {
    Layout::set('alert', __DIR__ . D . 'engine' . D . 'r' . D . 'layout.php');
}