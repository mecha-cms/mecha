<?php

$post = 'page';
$response = 'comment';

// Repair
if(strpos($config->url_path, '/id:') !== false) {
    Weapon::add('tab_button_before', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'unit' . DS . 'tab' . DS . 'button' . DS . 'new.php';
    }, .9);
    Weapon::add('tab_content_1_before', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'date.hidden.php';
    }, .9);
}

require __DIR__ . DS . 'route.post.php';