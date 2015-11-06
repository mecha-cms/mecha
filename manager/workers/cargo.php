<?php

Weapon::add('shield_before', function() {
    ob_start();
    // No buffer for backend cargo, so `chunk:input` and `chunk:output` filter(s) won't work here
    Shield::chunk(DECK . DS . 'workers' . DS . Config::get('cargo'), false, false);
    $content = ob_get_clean();
    Shield::lot('page', (object) array(
        'title' => Config::get('page_title'),
        'url' => "",
        'link' => "",
        'content' => $content
    ));
});