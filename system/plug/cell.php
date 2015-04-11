<?php

// `<meta>`
Cell::add('meta', function($name = null, $content = null, $attr = array(), $indent = "") {
    $attr['name'] = $name;
    $attr['content'] = $content;
    return Cell::unit('meta', false, $attr, $indent);
});

// `<link>`
Cell::add('link', function($href = null, $rel = null, $type = null, $attr = array(), $indent = "") {
    $attr['href'] = $href;
    $attr['rel'] = $rel;
    $attr['type'] = $type;
    return Cell::unit('link', false, $attr, $indent);
});

// `<script>`
Cell::add('script', function($attr = array(), $content = "", $indent = "") {
    if( ! is_array($attr)) {
        $attr = array('src' => $attr);
    }
    return Cell::unit('script', $content, $attr, $indent);
});

// `<a>`
Cell::add('a', function($href = null, $content = "", $target = null, $attr = array(), $indent = "") {
    $attr['href'] = $href;
    $attr['target'] = $target;
    return Cell::unit('a', $content, $attr, $indent);
});

// `<img>`
Cell::add('img', function($src = null, $alt = null, $attr = array(), $indent = "") {
    $attr['src'] = $src;
    $attr['alt'] = $alt;
    return Cell::unit('img', false, $attr, $indent);
});

// `<ol>`
Cell::add('ol', function($list = array(), $attr = array(), $indent = "") {
    $html = Cell::begin('ol', $attr, $indent) . NL;
    foreach($list as $li) {
        $html .= Cell::unit('li', $li, array(), $indent . TAB) . NL;
    }
    return $html . Cell::end('ol', $indent);
});

// `<ul>`
Cell::add('ul', function($list = array(), $attr = array(), $indent = "") {
    $html = Cell::begin('ul', $attr, $indent) . NL;
    foreach($list as $li) {
        $html .= Cell::unit('li', $li, array(), $indent . TAB) . NL;
    }
    return $html . Cell::end('ul', $indent);
});