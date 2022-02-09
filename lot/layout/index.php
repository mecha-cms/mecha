<?php

// Add CSS file to the `<head>` section…
if (defined('TEST') && TEST) {
    Asset::set('index.css', 20);
} else {
    // Serve the minified version if `TEST` mode is off
    Asset::set('index.min.css', 20);
}

// Create site link data to be used in navigation
$GLOBALS['links'] = new Anemone((static function($links, $state, $url) {
    $index = LOT . D . 'page' . D . trim(strtr($state->route, '/', D), D) . '.page';
    $path = $url->path . '/';
    foreach (g(LOT . D . 'page', 'page') as $k => $v) {
        // Exclude home page
        if ($k === $index) {
            continue;
        }
        $v = new Page($k);
        // Add current state
        $v->current = 0 === strpos($path, '/' . $v->name . '/');
        $links[$k] = $v;
    }
    ksort($links);
    return $links;
})([], $state, $url));

// Create site trace data to be used in navigation
$GLOBALS['traces'] = new Pages((static function($traces, $state, $url) {
    $chops = explode('/', trim($url->path ?? "", '/'));
    $v = LOT . D . 'page';
    while ($chop = array_shift($chops)) {
        $v .= D . $chop;
        if ($file = exist([
            $v . '.archive',
            $v . '.page'
        ], 1)) {
            $traces[] = $file;
        }
    }
    return $traces;
})([], $state, $url));