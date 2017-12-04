<?php

foreach([
    'a' => function($text = "", $href = null, $target = null, $attr = [], $dent = 0) {
        $attr_o = [
            'target' => $target === true ? '_blank' : ($target === false ? null : $target)
        ];
        $attr = array_replace_recursive($attr_o, $attr);
        $attr['href'] = URL::long(str_replace('&amp;', '&', $href));
        return HTML::unite('a', $text, $attr, $dent);
    },
    'img' => function($src = null, $alt = null, $attr = [], $dent = 0) {
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
        $attr = array_replace_recursive($attr_o, $attr);
        $attr['src'] = URL::long(str_replace('&amp;', '&', $src));
        return HTML::unite('img', false, $attr, $dent);
    }
] as $k => $v) {
    HTML::_($k, $v);
}

foreach (['br', 'hr'] as $kin) {
    HTML::_($kin, function($i = 1, $attr = [], $dent = 0) use($kin) {
        return HTML::dent($dent) . str_repeat(HTML::unite($kin, false, $attr), $i);
    });
}

foreach (['ol', 'ul'] as $kin) {
    HTML::_($kin, function($list = [], $attr = [], $dent = 0) use($kin) {
        $tag = new HTML;
        $html = $tag->begin($kin, $attr, $dent) . N;
        foreach ($list as $k => $v) {
            if (is_array($v)) {
                $html .= $tag->begin('li', [], $dent + 1) . $k . N;
                $html .= call_user_func('HTML::' . $kin, $v, $attr, $dent + 2) . N;
                $html .= $tag->end('li', $dent + 1) . N;
            } else {
                $html .= HTML::unite('li', $v, [], $dent + 1) . N;
            }
        }
        return $html . $tag->end($kin, $dent);
    });
}