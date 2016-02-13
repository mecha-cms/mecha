<?php

Route::over($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    // Convert pattern to array ...
    $predef = array();
    if($a = trim(Request::post('a'))) {
        foreach(explode("\n", $a) as $v) {
            if(preg_match('#^\s*\[\s*(.*?)\s*\]: *<?\s*(.*?)\s*>? +(?:[\'"\(])\s*(.*?)\s*(?:[\)"\'])\s*$#', $v, $matches)) {
                $predef[0][$matches[1]] = $matches[2];
                $predef[1][$matches[1]] = $matches[3];
            }
        }
        $_POST['predef_urls'] = $predef[0];
        $_POST['predef_titles'] = $predef[1];
    }
    if($abbr = trim(Request::post('abbr'))) {
        foreach(explode("\n", $abbr) as $v) {
            if(preg_match('#^\s*\*\[\s*(.*?)\s*\]: *(.*?)\s*$#', $v, $matches)) {
                $predef[2][$matches[1]] = $matches[2];
            }
        }
        $_POST['predef_abbr'] = $predef[2];
    }
    $_POST['no_markup'] = Request::post('no_markup', false);
    $_POST['no_entities'] = Request::post('no_entities', false);
    $_POST['code_attr_on_pre'] = Request::post('code_attr_on_pre', false);
    unset($_POST['abbr'], $_POST['a'], $predef);
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