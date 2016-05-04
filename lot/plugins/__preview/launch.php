<?php

Weapon::add('tab_content_2_before', function($page, $segment) use($config, $speak) {
    if(isset($page->css_raw) || isset($page->js_raw)) {
        include __DIR__ . DS . 'workers' . DS . 'unit' . DS . 'form' . DS . '..._preview.php';
        Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
            echo Asset::javascript(__DIR__ . DS . 'assets' . DS . 'sword' . DS . 'preview.js');
        });
    }
}, .9);

Weapon::add('tab_button_after', function($page, $segment) use($config, $speak) {
    if(isset($page->path) && ($page->path === "" || Text::check(array(POST . DS, RESPONSE . DS))->in($page->path))) {
        include __DIR__ . DS . 'workers' . DS . 'unit' . DS . 'tab' . DS . 'button' . DS . 'preview.php';
    }
});

Weapon::add('tab_content_after', function($page, $segment) use($config, $speak) {
    if(isset($page->path) && ($page->path === "" || Text::check(array(POST . DS, RESPONSE . DS))->in($page->path))) {
        include __DIR__ . DS . 'workers' . DS . 'unit' . DS . 'tab' . DS . 'content' . DS . 'preview.php';
    }
});