<?php

Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
    $assets = glob(__DIR__ . DS . 'assets' . DS . 'sword' . DS . '*.js', GLOB_NOSORT);
    echo Asset::javascript($assets);
}, 2.1);