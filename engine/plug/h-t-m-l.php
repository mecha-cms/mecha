<?php

HTML::plug('a', function($content = "", $href = null, $target = null, $attr = [], $dent = 0) {
    $attr_o = [
        'target' => $target === true ? '_new' : ($target === false ? null : $target)
    ];
    $attr = Anemon::extend($attr_o, $attr);
    $attr['href'] = URL::long(str_replace('&amp;', '&', $href));
    return HTML::unite('a', $content, $attr, $dent);
});

HTML::plug('img', function($src = null, $alt = null, $attr = [], $dent = 0) {
    $path = To::path($src);
    if (file_exists($path)) {
        $z = getimagesize($path);
    } else {
        $z = [null, null];
    }
    $attr_o = [
        'alt' => !isset($alt) ? "" : $alt,
        'width' => $z[0],
        'height' => $z[1]
    ];
    $attr = Anemon::extend($attr_o, $attr);
    $attr['src'] = URL::long(str_replace('&amp;', '&', $src));
    return HTML::unite('img', false, $attr, $dent);
});

foreach (['br', 'hr'] as $unit) {
    HTML::plug($unit, function($i = 1, $attr = [], $dent = 0) use($unit) {
        return HTML::dent($dent) . str_repeat(HTML::unite($unit, false, $attr), $i);
    });
}

foreach (['ol', 'ul'] as $unit) {
    HTML::plug($unit, function($list = [], $attr = [], $dent = 0) use($unit) {
        $html = HTML::begin($unit, $attr, $dent) . N;
        foreach ($list as $k => $v) {
            if (is_array($v)) {
                $html .= HTML::begin('li', [], $dent + 1) . $k . N;
                $html .= call_user_func('HTML::' . $unit, $v, $attr, $dent + 2) . N;
                $html .= HTML::end('li', $dent + 1) . N;
            } else {
                $html .= HTML::unit('li', $v, [], $dent + 1) . N;
            }
        }
        return $html . HTML::end($unit, $dent);
    });
}