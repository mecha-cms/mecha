<?php

Route::over($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    // Convert pattern to array ...
    $predef = array();
    if($links = trim(Request::post('links'))) {
        foreach(explode("\n", $links) as $v) {
            if(preg_match('#^\s*\[\s*(.*?)\s*\]: *<?\s*(.*?)\s*>? +(?:[\'"\(])\s*(.*?)\s*(?:[\)"\'])\s*$#', $v, $matches)) {
                $predef[$matches[1]] = array(
                    'url' => $matches[2],
                    'title' => $matches[3]
                );
            }
        }
        $_POST['links'] = $predef;
    }
    $predef = array();
    if($abbreviations = trim(Request::post('abbreviations'))) {
        foreach(explode("\n", $abbreviations) as $v) {
            if(preg_match('#^\s*\*\[\s*(.*?)\s*\]: *(.*?)\s*$#', $v, $matches)) {
                $predef[$matches[1]] = $matches[2];
            }
        }
        $_POST['abbreviations'] = $predef;
    }
    unset($predef);
    // Hijacking the `__editor` territory ...
    if($c_editor = Config::get('states.plugin_' . md5('__editor'))) {
        include __DIR__ . DS . 'workers' . DS . '__editor.hijack.php';
    }
});

// Editor ...
Weapon::add('shield_before', function() use($config, $speak) {
    if($c_editor = Config::get('states.plugin_' . md5('__editor'))) {
        $s = Config::get('page', array());
        $parser = isset($s->content_type_raw) ? $s->content_type_raw : $config->html_parser->active;
        if($parser === 'Markdown') {
            // Altering the `__editor` territory ...
            include __DIR__ . DS . 'workers' . DS . '__editor.alter.php';
        }
    }
}, 1.1);