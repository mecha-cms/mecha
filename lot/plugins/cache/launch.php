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
        $time = file_exists($cache) ? filemtime($cache) : false;
        if($time !== false && ($route_cache === true || time() - ($route_cache * 60 * 60) < $time)) {
            $content = file_get_contents($cache);
            if(strpos($content, '<?xml ') === 0 || strpos($content, '</html>') !== false) {
                $content .= '<!-- cached: ' . date('Y-m-d H:i:s', $time) . ' -->';
            }
            $content = Filter::apply('cache:input', $content);
            $content = Filter::apply('cache:output', $content);
            echo $content;
            exit;
        }
        Weapon::add('shield_after', function($G) use($cache) {
            $G['data']['cache'] = $cache;
            File::write($G['data']['content'])->saveTo($cache);
            Weapon::fire('on_cache_construct', array($G, $G));
        });
    });
}