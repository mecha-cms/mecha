<?php

// `<meta>`
Cell::add('meta', function($name = null, $content = null, $attr = array(), $indent = 0) {
    $attr['name'] = $name;
    $attr['content'] = Cell::protect($content);
    return Cell::unit('meta', false, $attr, $indent);
});

// `<link>`
Cell::add('link', function($href = null, $rel = null, $type = null, $attr = array(), $indent = 0) {
    $attr['href'] = Converter::url($href);
    $attr['rel'] = $rel;
    $attr['type'] = $type;
    return Cell::unit('link', false, $attr, $indent);
});

// `<script>`
Cell::add('script', function($attr = array(), $content = "", $indent = 0) {
    if(is_string($attr)) {
        $attr = array('src' => Converter::url($attr));
    }
    return Cell::unit('script', $content, $attr, $indent);
});

// `<a>`
Cell::add('a', function($href = null, $content = "", $target = null, $attr = array(), $indent = 0) {
    $attr['href'] = Converter::url($href);
    $attr['target'] = $target;
    return Cell::unit('a', $content, $attr, $indent);
});

// `<img>`
Cell::add('img', function($src = null, $alt = null, $attr = array(), $indent = 0) {
    $attr['src'] = Converter::url($src);
    $attr['alt'] = Cell::protect($alt);
    return Cell::unit('img', false, $attr, $indent);
});

// `<ol>`
Cell::add('ol', function($list = array(), $attr = array(), $indent = 0) {
    $html = Cell::begin('ol', $attr, $indent) . NL;
    foreach($list as $li) {
        $html .= Cell::unit('li', $li, array(), $indent + 1) . NL;
    }
    return $html . Cell::end('ol', $indent);
});

// `<ul>`
Cell::add('ul', function($list = array(), $attr = array(), $indent = 0) {
    $html = Cell::begin('ul', $attr, $indent) . NL;
    foreach($list as $li) {
        $html .= Cell::unit('li', $li, array(), $indent + 1) . NL;
    }
    return $html . Cell::end('ul', $indent);
});