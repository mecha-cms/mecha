<?php

$has_mb_string = extension_loaded('mbstring');

To::plug('anemon', function($input) {
    if (__is_anemon__($input)) {
        return a($input);
    }
    return json_decode($input, true);
});

To::plug('base64', 'base64_encode');
To::plug('camel', 'c');

To::plug('dec', function($input, $z = false, $f = ['&#', ';']) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = ord($input[$i]);
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= $f[0] . $s . $f[1];
    }
    return $output;
});

To::plug('file', function($input) {
    $input = explode(DS, str_replace('/', DS, $input));
    $n = explode('.', array_pop($input));
    $x = array_pop($n);
    $s = "";
    foreach ($input as $v) {
        $s .= f($v, '-', true, '\w') . DS;
    }
    return $s . f(implode('.', $n), '-', true, '\w.') . '.' . f($x, '-', true);
});

To::plug('folder', function($input) {
    $input = explode(DS, str_replace('/', DS, $input));
    $n = array_pop($input);
    $s = "";
    foreach ($input as $v) {
        $s .= f($v, '-', true, '\w') . DS;
    }
    return $s . f($n, '-', true, '\w');
});

To::plug('hex', function($input, $z = false, $f = ['&#x', ';']) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = dechex(ord($input[$i]));
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= $f[0] . $s . $f[1];
    }
    return $output;
});

To::plug('html', 'htmlspecialchars_decode');

To::plug('json', 'json_encode');

To::plug('key', function($input, $low = true) {
    $s = f($input, '_', $low);
    return is_numeric($s[0]) ? '_' . $s : $s;
});

To::plug('pascal', 'p');

To::plug('path', function($input) {
    $url = __url__();
    $s = str_replace('/', DS, $url['url']);
    return str_replace([$url['url'], '\\', '/', $s], [ROOT, DS, DS, ROOT], $input);
});

To::plug('sentence', function($input, $tail = '.') use($has_mb_string) {
    $input = trim($input);
    if ($has_mb_string) {
        return mb_strtoupper(mb_substr($input, 0, 1)) . mb_strtolower(mb_substr($input, 1)) . $tail;
    }
    return ucfirst(strtolower($input)) . $tail;
});

To::plug('slug', 'h');

To::plug('snake', function($input) {
    return h($input, '_');
});

To::plug('snippet', function($input, $html = true, $x = [200, '&#x2026;']) use($has_mb_string) {
    $s = w($input, $html ? HTML_WISE_I : []);
    $t = $has_mb_string ? mb_strlen($s) : strlen($s);
    if (is_int($x)) {
        $x = [$x, '&#x2026;'];
    }
    $s = $has_mb_string ? mb_substr($s, 0, $x[0]) : substr($s, 0, $x[0]);
    $s = str_replace('<br>', ' ', $s);
    // Remove the unclosed HTML tag(s)…
    if ($html && strpos($s, '<') !== false) {
        $s = preg_replace('#<\/[^>]*$#', "", $s); // `foo bar </a`
        $ss = '#<[^\/>]+?>([^<]*?)$#';
        while (preg_match($ss, $s)) {
            $s = preg_replace($ss, '$1', $s); // `foo bar <a href="">baz`
        }
        $s = preg_replace('#<[^>]*$#', "", $s); // `foo bar <a href=`
    }
    return trim($s) . ($t > $x[0] ? $x[1] : "");
});

To::plug('text', 'w');

To::plug('title', function($input) use($has_mb_string) {
    $input = w($input);
    if ($has_mb_string) {
        return mb_convert_case($input, MB_CASE_TITLE);
    }
    return ucwords($input);
});

To::plug('url', function($input, $raw = false) {
    $url = __url__();
    $s = str_replace(DS, '/', ROOT);
    $input = str_replace([ROOT, DS, '\\', $s], [$url['url'], '/', '/', $url['url']], $input);
    // Fix broken external URL `http://://example.com`, `http:////example.com`
    $input = str_replace(['://://', ':////'], '://', $input);
    // --ditto `http:example.com`
    if (strpos($input, $url['scheme'] . ':') === 0 && strpos($input, $url['protocol']) !== 0) {
        $input = str_replace(X . $url['scheme'] . ':', $url['protocol'], X . $input);
    }
    return $raw ? rawurldecode($input) : urldecode($input);
});

function __to_yaml__($input, $c = [], $in = '  ', $safe = false, $dent = 0) {
    $s = array_replace(Page::v, $c);
    if (__is_anemon__($input)) {
        $t = "";
        $line = __is_anemon_0__($input) && !$safe;
        $T = str_repeat($in, $dent);
        foreach ($input as $k => $v) {
            if (!__is_anemon__($v) || empty($v)) {
                if (is_array($v)) {
                    $v = '[]';
                } else if (is_object($v)) {
                    $v = '{}';
                } else if ($v === "") {
                    $v = '""';
                } else {
                    $v = s($v);
                }
                $v = $v !== $s[4] && strpos($v, $s[2]) !== false ? json_encode($v) : $v;
                // Line
                if ($v === $s[4]) {
                    $t .= $s[4];
                // Comment
                } else if (strpos($v, '#') === 0) {
                    $t .= $T . trim($v) . $s[4];
                // …
                } else {
                    $t .= $T . ($line ? $s[3] : trim($k) . $s[2]) . $v . $s[4];
                }
            } else {
                $o = __to_yaml__($v, $s, $in, $safe, $dent + 1);
                $t .= $T . $k . $s[2] . $s[4] . $o . $s[4];
            }
        }
        return rtrim($t);
    }
    return $input !== $s[4] && strpos($input, $s[2]) !== false ? json_encode($input) : $input;
}

To::plug('yaml', function(...$lot) {
    if (!__is_anemon__($lot[0])) {
        return s($lot[0]);
    }
    if (is_string($lot[0]) && Is::path($lot[0], true)) {
        $lot[0] = include $lot[0];
    }
    return call_user_func_array('__to_yaml__', $lot);
});

// Alias(es)…
To::plug('h_t_m_l', 'htmlspecialchars_decode');
To::plug('u_r_l', 'To::url');
To::plug('y_a_m_l', 'To::yaml');