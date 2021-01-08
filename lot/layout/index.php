<?php

// Add CSS file to the `<head>` section…
if (defined('DEBUG') && DEBUG) {
    Asset::set('css/index.css', 20);
} else {
    // Serve the minified version if `DEBUG` mode is off
    Asset::set('css/index.min.css', 20);
}

// Create site link data to be used in navigation
$GLOBALS['links'] = new Anemon((function($out, $state, $url) {
    $index = LOT . DS . 'page' . strtr($state->path, '/', DS) . '.page';
    foreach (g(LOT . DS . 'page', 'page') as $k => $v) {
        // Exclude home page
        if ($k === $index) {
            continue;
        }
        $v = new Page($k);
        // Add active state
        $v->set('active', 0 === strpos($url->path . '/', '/' . $v->name . '/'));
        $out[$k] = $v;
    }
    ksort($out);
    return $out;
})([], $state, $url));

// Create site trace data to be used in navigation
$GLOBALS['traces'] = new Pages((function($out, $state, $url) {
    $chops = explode('/', trim($url->path, '/'));
    $v = LOT . DS . 'page';
    while ($chop = array_shift($chops)) {
        $v .= '/' . $chop;
        if ($file = File::exist([
            $v . '.archive',
            $v . '.page'
        ])) {
            $out[] = $file;
        }
    }
    return $out;
})([], $state, $url));
