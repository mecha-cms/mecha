<?php

// Run after shield lot data up-to-date
Weapon::add('shield_lot_after', function() {
    ob_start();
    $config = Config::get();
    $config->cargo = (string) $config->cargo;
    $cargo = strpos($config->cargo, ROOT) === false ? __DIR__ . DS . $config->cargo : $config->cargo;
    // No buffer for backend cargo, so `chunk:input` and `chunk:output` filter(s) won't work here
    Shield::chunk($cargo, false, false);
    $content = ob_get_clean();
    $o = (object) array(
        'title' => $config->page_title,
        'url' => "",
        'link' => "",
        'content_type' => $config->html_parser->active,
        'content' => $content
    );
    Shield::lot(array('page' => $o));
}, 1);