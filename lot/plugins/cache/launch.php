<?php

$cache_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();

$route_cache = false;
if(isset($cache_config['path'][$config->url_path])) {
    $route_cache = $cache_config['path'][$config->url_path];
} else {
    foreach($cache_config['path'] as $path => $exp) {
        if(Route::is($path)) {
            $route_cache = $exp;
            break;
        }
    }
}

if($route_cache !== false) {
    Weapon::add('shield_before', function() use($config, $route_cache) {
        $q = ! empty($config->url_query) ? '.' . md5($config->url_query) : "";
        $cache = CACHE . DS . str_replace(array('/', ':'), '.', $config->url_path) . $q . '.cache';
        if(file_exists($cache) && ($route_cache === true || time() - ($route_cache * 60 * 60) < filemtime($cache))) {
            $content = file_get_contents($cache);
            $content = Filter::apply('cache:input', $content);
            $content = Filter::apply('cache:output', $content);
            echo $content;
            exit;
        }
        Weapon::add('shield_after', function($G) use($cache) {
            $G['data']['cache'] = $cache;
            File::write($G['data']['content'] . '<!-- cached: ' . date('Y-m-d H:i:s') . ' -->')->saveTo($cache);
            Weapon::fire('on_cache_construct', array($G, $G));
        });
    });
}