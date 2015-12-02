<?php

$post = 'article';
$response = 'comment';

Weapon::add('tab_content_1_before', function($page, $segment) use($config, $speak) {
    include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'response' . DS . 'post.php';
}, .9);

Weapon::add('tab_content_1_before', function($page, $segment) use($config, $speak) {
    include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'response' . DS . 'status.php';
}, 3.1);

Weapon::add('tab_content_1_before', function($page, $segment) use($config, $speak) {
    include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'response' . DS . 'parent.php';
}, 5.1);

require __DIR__ . DS . 'route.response.php';