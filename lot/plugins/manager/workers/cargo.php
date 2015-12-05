<?php

Weapon::add('shield_lot_after', function() {
    ob_start();
    $cargo = Config::get('cargo');
    $cargo = strpos($cargo, ROOT) === false ? __DIR__ . DS . $cargo : $cargo;
    // No buffer for backend cargo, so `chunk:input` and `chunk:output` filter(s) won't work here
    Shield::chunk($cargo, false, false);
    $content = ob_get_clean();
    $o = (object) array(
        'title' => Config::get('page_title'),
        'url' => "",
        'link' => "",
        'content' => $content
    );
    Shield::lot(array('page' => $o));
});