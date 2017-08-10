<?php

define('PLUGIN', __DIR__ . DS . 'lot' . DS . 'worker');

call_user_func(function() {
    $plugins = [];
    $seeds = Lot::get(null, []);
    foreach (g(PLUGIN . DS . '*', '{index__,index,__index}.php') as $v) {
        $plugins[$v] = (float) File::open(Path::D($v) . DS . 'index.stack')->get(0, 10);
    }
    asort($plugins);
    extract($seeds);
    Config::set('+plugin', $plugins);
    $c = [];
    foreach ($plugins as $k => $v) {
        $f = Path::D($k) . DS;
        $i18n = $f . 'lot' . DS . 'language' . DS;
        if ($l = File::exist([
            $i18n . $config->language . '.page',
            $i18n . 'en-us.page'
        ])) {
            $c[$l] = filemtime($l);
        }
        if (Path::B($k) !== '__index.php') {
            $f .= 'engine' . DS;
            d($f . 'kernel', function($w, $n) use($f, $seeds) {
                $f .= 'plug' . DS . $n . '.php';
                if (file_exists($f)) {
                    extract($seeds);
                    require $f;
                }
            }, $seeds);
        }
    }
    $id = array_sum($c);
    if (Cache::expire(PLUGIN, $id)) {
        $content = [];
        foreach ($c as $k => $v) {
            $i18n = new Page($k, [], 'language');
            $fn = 'From::' . __c2f__($i18n->type, '_');
            $content = array_replace_recursive($content, is_callable($fn) ? call_user_func($fn, $i18n->content) : From::yaml($i18n->content));
        }
        Cache::set(PLUGIN, $content, $id);
    } else {
        $content = Cache::get(PLUGIN, []);
    }
    Language::set($content);
    foreach (array_keys($plugins) as $v) {
        if (Path::B($v) !== '__index.php') {
            require $v;
        }
    }
});