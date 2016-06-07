<?php

function do_shortcode_url($data) {
    global $config;
    $s1 = isset($data[1]) ? $data[1] : "";
    $s1 = $s1 && (strpos($s1, '?') !== 0 && strpos($s1, '&') !== 0 && strpos($s1, '#') !== 0) ? '/' . ltrim($s1, '/') : $s1;
    $s = substr($s1, -2);
    return strlen($s) === 2 && strpos('/\'/"/`', $s) !== false ? $config->url . $s[1] : $config->url . $s1;
}

function do_shortcode_url_($data) {
    global $config;
    $c = Get::state_config($data[1]);
    $s1 = "";
    if(isset($data[1])) {
        $s1 = isset($c['slug']) ? $c['slug'] : $data[1];
    }
    $s2 = isset($data[2]) ? $data[2] : "";
    $s1 = $s1 ? '/' . ltrim($s1, '/') : "";
    $s2 = $s2 && (strpos($s2, '?') !== 0 && strpos($s2, '&') !== 0 && strpos($s2, '#') !== 0) ? '/' . ltrim($s2, '/') : $s2;
    $s = substr($s2, -2);
    return strlen($s) === 2 && strpos('/\'/"/`', $s) !== false ? $config->url . $s1 . $s[1] : $config->url . $s1 . $s2;
}

function do_shortcode_url_article($data) {
    $data[2] = isset($data[1]) ? $data[1] : "";
    $data[1] = 'index';
    return do_shortcode_url_($data);
}

function do_shortcode_url_page($data) { // alias for `{{url}}`
    return do_shortcode_url($data);
}

function do_shortcode_url_current($data) {
    global $config;
    $s1 = isset($data[1]) ? $data[1] : "";
    $s1 = $s1 && (strpos($s1, '?') !== 0 && strpos($s1, '&') !== 0 && strpos($s1, '#') !== 0) ? '/' . ltrim($s1, '/') : $s1;
    $s = substr($s1, -2);
    return strlen($s) === 2 && strpos('/\'/"/`', $s) !== false ? $config->url_current . $s[1] : $config->url_current . $s1;
}

function do_shortcode_asset($data) {
    global $config;
    $c = $config->url;
    $config->url = File::url(ASSET);
    $url = do_shortcode_url($data);
    $config->url = $c;
    return $url;
}