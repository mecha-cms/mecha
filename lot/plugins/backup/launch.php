<?php

if(Guardian::happy(1)) {
    Config::merge('manager_menu', array(
        $speak->backup => array(
            'icon' => 'life-ring',
            'url' => $config->manager->slug . '/backup',
            'stack' => 9.12
        )
    ));
}