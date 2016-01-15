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
    $attr['target'] = $target === true ? '_blank' : $target;
    return Cell::unit('a', $content, $attr, $indent);
});

// `<img>`
Cell::add('img', function($src = null, $alt = null, $attr = array(), $indent = 0) {
    $attr['src'] = Converter::url($src);
    $attr['alt'] = Cell::protect($alt);
    return Cell::unit('img', false, $attr, $indent);
});

// `<(ol|ul)>`
foreach(array('ol', 'ul') as $unit) {
    Cell::add($unit, function($list = array(), $attr = array(), $indent = 0) use($unit) {
        $html = Cell::begin($unit, $attr, $indent) . NL;
        foreach($list as $k => $v) {
            if(is_array($v)) {
                $html .= Cell::begin('li', array(), $indent + 1) . $k . NL;
                $html .= call_user_func('Cell::' . $unit, $v, $attr, $indent + 2) . NL;
                $html .= Cell::end('li', $indent + 1) . NL;
            } else {
                $html .= Cell::unit('li', $v, array(), $indent + 1) . NL;
            }
        }
        return $html . Cell::end($unit, $indent);
    });
}

// `<(hr|br)>`
foreach(array('hr', 'br') as $unit) {
    Cell::add($unit, function($repeat = 1, $attr = array(), $indent = 0) use($unit) {
        $indent = $indent ? str_repeat(TAB, $indent) : "";
        return $indent . str_repeat(Cell::unit($unit, false, $attr), $repeat);
    });
}