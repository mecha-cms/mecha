<?php

Route::over($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    $state = __DIR__ . DS . 'states' . DS;
    File::write(trim(Request::post('abbr')))->saveTo($state . 'abbr.txt', 0600);
    File::write(trim(Request::post('a')))->saveTo($state . 'a.txt', 0600);
    // Hijacking the `__editor` territory ...
    if($c_editor = Config::get('states.plugin_' . md5('__editor'))) {
        $c_editor->enableSETextHeader = Request::post('enableSETextHeader', 0);
        $c_editor->closeATXHeader = Request::post('closeATXHeader', 0);
        if($fence = Request::post('PRE')) {
            $c_editor->PRE = Converter::DW($fence);
        } else {
            unset($c_editor->PRE);
        }
        unset($_POST['enableSETextHeader'], $_POST['closeATXHeader'], $_POST['PRE']);
        File::serialize(Mecha::A($c_editor))->saveTo(PLUGIN . DS . '__editor' . DS . 'states' . DS . 'config.txt', 0600);
    }
    unset($_POST['abbr'], $_POST['a']);
});

// Editor ...
Weapon::add('shield_before', function() use($config, $speak) {
    if($c_editor = Config::get('states.plugin_' . md5('__editor'))) {
        $page = Config::get('page', array());
        $parser = isset($page->content_type_raw) ? $page->content_type_raw : $config->html_parser->active;
        if($parser === 'Markdown' || $parser === 'Markdown Extra') {
            Config::merge('DASHBOARD.languages.MTE', $speak->__MTE);
            Config::set('MTE', 'MTE'); // Replace `HTE` with `MTE`
            Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
                echo Asset::javascript(__DIR__ . DS . 'assets' . DS . 'sword' . DS . 'help.js', "", 'sword/editor.button.help.min.js');
            }, 11);
            Filter::add('asset:path', function($path) {
                // Replace `HTE` with `MTE`
                if($path === PLUGIN . DS . '__editor' . DS . 'assets' . DS . 'sword' . DS . 'hte.min.js') {
                    return __DIR__ . DS . 'assets' . DS . 'sword' . DS . 'mte.min.js';
                }
                // Replace default `info` button with the new one
                if($path === PLUGIN . DS . '__editor-button' . DS . 'assets' . DS . 'sword' . DS . 'info.js') {
                    return __DIR__ . DS . 'assets' . DS . 'sword' . DS . 'info.js';
                }
                // Replace default `table` button with the new one
                if($path === PLUGIN . DS . '__editor-button' . DS . 'assets' . DS . 'sword' . DS . 'table.js') {
                    return __DIR__ . DS . 'assets' . DS . 'sword' . DS . 'table.js';
                }
                return $path;
            });
        }
    }
}, 1.1);