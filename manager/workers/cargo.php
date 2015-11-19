<?php

Weapon::add('shield_lot_before', function() {
    ob_start();
    $cargo = DECK . DS . 'workers' . DS . Config::get('cargo');
    // No buffer for backend cargo, so `chunk:input` and `chunk:output` filter(s) won't work here
    Shield::chunk($cargo, false, false);
    $content = ob_get_clean();
    $o = array(
        'title' => Config::get('page_title'),
        'url' => "",
        'link' => "",
        'content' => $content
    );
    Config::set(array(
        'page' => (object) $o,
        'cargo' => $cargo
    ));
});