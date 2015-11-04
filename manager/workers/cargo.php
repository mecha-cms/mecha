<?php

Weapon::add('shield_before', function() use($cargo) {
    ob_start();
    Shield::chunk(DECK . DS . 'workers' . DS . Config::get('cargo', $cargo), array(), false);
    $content = ob_get_clean();
    Shield::lot('page', (object) array(
        'title' => Config::get('page_title'),
        'url' => "",
        'link' => "",
        'content' => $content
    ));
});