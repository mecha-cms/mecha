<?php


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $state = __DIR__ . DS . 'states' . DS;
        File::write(trim($request['abbr']))->saveTo($state . 'abbr.txt', 0600);
        File::write(trim($request['url']))->saveTo($state . 'url.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', $speak->plugin));
        Guardian::kick(File::D($config->url_current));
    }
});

// Editor ...
Weapon::add('shield_before', function() use($config, $speak) {
    $page = Config::get('page', array());
    $parser = isset($page->content_type_raw) ? $page->content_type_raw : $config->html_parser->active;
    if($parser === 'Markdown' || $parser === 'Markdown Extra') {
        Config::merge('DASHBOARD.languages.MTE', $speak->__MTE);
        Config::set('MTE', 'MTE');
        Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
            echo Asset::javascript(__DIR__ . DS . 'assets' . DS . 'sword' . DS . 'help.js');
        }, 11);
        Filter::add('asset:path', function($path) {
            // Replace `HTE` with `MTE`
            if($path === PLUGIN . DS . '__editor' . DS . 'assets' . DS . 'sword' . DS . 'hte.min.js') {
                return __DIR__ . DS . 'assets' . DS . 'sword' . DS . 'mte.min.js';
            }
            // Replace default `table` button with the new one
            if($path === PLUGIN . DS . '__editor-button' . DS . 'assets' . DS . 'sword' . DS . 'table.js') {
                return __DIR__ . DS . 'assets' . DS . 'sword' . DS . 'table.js';
            }
            return $path;
        });
    }
}, 1.1);