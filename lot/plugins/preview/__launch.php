<?php

if(
    $config->url_path === $config->manager->slug . '/article/ignite' ||
    $config->url_path === $config->manager->slug . '/page/ignite' ||
    $config->url_path === $config->manager->slug . '/comment/ignite' ||
    strpos($config->url_path, $config->manager->slug . '/article/repair/id:') === 0 ||
    strpos($config->url_path, $config->manager->slug . '/page/repair/id:') === 0 ||
    strpos($config->url_path, $config->manager->slug . '/comment/repair/id:') === 0
) {
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript(__DIR__ . DS . 'assets' . DS . 'sword' . DS . 'preview.js');
    });
    Weapon::add('tab_content_after', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'workers' . DS . 'tab-content.php';
    });
    Weapon::add('tab_content_2_before', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'workers' . DS . 'toggle.php';
    });
    Weapon::add('tab_button_after', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'workers' . DS . 'tab-button.php';
    });
}