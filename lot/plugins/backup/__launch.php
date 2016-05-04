<?php

if(Guardian::happy(1) && strpos($config->url_path . '/', $config->manager->slug . '/backup/') === 0) {
    require __DIR__ . DS . 'workers' . DS . 'route.backup.php';
}