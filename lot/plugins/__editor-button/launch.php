<?php

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config) {
    $s = Config::get('states.plugin_' . md5('__editor'));
    if($buttons = glob(__DIR__ . DS . 'assets' . DS . 'sword' . DS . '*.js', GLOB_NOSORT)) {
        foreach($buttons as $button) {
            $ss = File::N($button);
            if(isset($s->buttons->{$ss}) && $s->buttons->{$ss} !== 0) {
                echo Asset::javascript($button, "", 'sword/editor.button.' . $ss . '.min.js');
            }
        }
    }
}, 2.1);