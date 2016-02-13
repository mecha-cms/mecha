<?php

Config::merge('DASHBOARD.languages.MTE', $speak->__MTE);
Config::set('MTE', 'MTE'); // Replace `HTE` with `MTE`
$_ = File::D(__DIR__) . DS . 'assets' . DS . 'sword' . DS;
Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($_) {
    echo Asset::javascript($_ . 'help.js', "", 'sword/editor.button.help.min.js');
}, 11);
Filter::add('asset:path', function($path) use($_) {
    // Replace `HTE` with `MTE`
    if($path === PLUGIN . DS . '__editor' . DS . 'assets' . DS . 'sword' . DS . 'hte.min.js') {
        return $_ . 'mte.min.js';
    }
    // Replace default `info` button with the new one
    if($path === PLUGIN . DS . '__editor-button' . DS . 'assets' . DS . 'sword' . DS . 'info.js') {
        return $_ . 'info.js';
    }
    // Replace default `table` button with the new one
    if($path === PLUGIN . DS . '__editor-button' . DS . 'assets' . DS . 'sword' . DS . 'table.js') {
        return $_ . 'table.js';
    }
    return $path;
});