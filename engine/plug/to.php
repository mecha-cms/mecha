<?php

function __to_query__($array, $key) {
    $out = [];
    $s = $key ? '%5D' : "";
    foreach ($array as $k => $v) {
        $k = urlencode($k);
        if (is_array($v)) {
            $out = array_merge($out, __to_query__($v, $key . $k . $s . '%5B'));
        } else {
            $out[$key . $k . $s] = $v;
        }
    }
    return $out;
}

function __to_yaml__($in, $d = '  ', $safe = false, $dent = 0) {
    if (__is_anemon__($in)) {
        $out = "";
        $li = __is_anemon_0__($in) && !$safe; // is numeric array?
        $t = str_repeat($d, $dent);
        foreach ($in as $k => $v) {
            if (strpos($k, ':') !== false) {
                $k = '"' . $k . '"';
            }
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
                $v = strpos($v, "\n") !== false ? "|\n" . $t . $d . str_replace("\n", "\n" . $t . $d, $v) : $v;
                // Comment…
                if (strpos($v, '#') === 0) {
                    $out .= $t . trim($v) . "\n";
                // …
                } else {
                    $out .= $t . ($li ? '- ' : trim($k) . ': ') . $v . "\n";
                }
            } else {
                $out .= $t . $k . ":\n" . __to_yaml__($v, $d, $safe, $dent + 1) . "\n";
            }
        }
        return rtrim($out, "\n");
    }
    return strpos($in, ': ') === false ? json_encode($in) : $in;
}

foreach([
    'anemon' => function($in) {
        return (array) (is_array($in) || is_object($in) ? a($in) : json_decode($in, true));
    },
    'base64' => 'base64_encode',
    'camel' => 'c',
    'dec' => function($in, $z = false, $f = ['&#', ';']) {
        $out = "";
        for($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = ord($in[$i]);
            if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'file' => function($in) {
        $in = explode(DS, str_replace('/', DS, $in));
        $n = explode('.', array_pop($in));
        $x = array_pop($n);
        $s = "";
        foreach ($in as $v) {
            $s .= f($v, '-', true, '\w') . DS;
        }
        return $s . f(implode('.', $n), '-', true, '\w.') . '.' . f($x, '-', true);
    },
    'folder' => function($in) {
        $in = explode(DS, str_replace('/', DS, $in));
        $n = array_pop($in);
        $s = "";
        foreach ($in as $v) {
            $s .= f($v, '-', true, '\w') . DS;
        }
        return $s . f($n, '-', true, '\w');
    },
    'hex' => function($in, $z = false, $f = ['&#x', ';']) {
        $out = "";
        for($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = dechex(ord($in[$i]));
            if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'html' => 'htmlspecialchars_decode',
    'json' => 'json_encode',
    'key' => function($in, $low = true) {
        $s = f($in, '_', $low);
        return is_numeric($s[0]) ? '_' . $s : $s;
    },
    'pascal' => 'p',
    'path' => function($in) {
        $u = $GLOBALS['URL'];
        $s = str_replace('/', DS, $u['url']);
        return str_replace([$u['url'], '\\', '/', $s], [ROOT, DS, DS, ROOT], $in);
    },
    'query' => function($in, $c = []) {
        $c = array_replace(['?', '&', '=', ""], $c);
        foreach (__to_query__($in, "") as $k => $v) {
            if ($v === false) continue; // `['a' => 'false', 'b' => false]` → `a=false`
            $v = $v !== true ? $c[2] . urlencode(s($v)) : ""; // `['a' => 'true', 'b' => true]` → `a=true&b`
            $out[] = $k . $v; // `['a' => 'null', 'b' => null]` → `a=null&b=null`
        }
        return !empty($out) ? $c[0] . implode($c[1], $out) . $c[1] : "";
    },
    'sentence' => function($in, $tail = '.') {
        $in = trim($in);
        if (extension_loaded('mbstring')) {
            return mb_strtoupper(mb_substr($in, 0, 1)) . mb_strtolower(mb_substr($in, 1)) . $tail;
        }
        return ucfirst(strtolower($in)) . $tail;
    },
    'slug' => 'h',
    'snake' => function($in) {
        return h($in, '_');
    },
    'snippet' => function($in, $html = true, $x = [200, '&#x2026;']) {
        $s = w($in, $html ? HTML_WISE_I : []);
        $t = extension_loaded('mbstring') ? mb_strlen($s) : strlen($s);
        if (is_int($x)) {
            $x = [$x, '&#x2026;'];
        }
        $s = extension_loaded('mbstring') ? mb_substr($s, 0, $x[0]) : substr($s, 0, $x[0]);
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
    },
    'text' => 'w',
    'title' => function($in) {
        $in = w($in);
        if (extension_loaded('mbstring')) {
            return mb_convert_case($in, MB_CASE_TITLE);
        }
        return ucwords($in);
    },
    'url' => function($in, $raw = false) {
        $u = $GLOBALS['URL'];
        $s = str_replace(DS, '/', ROOT);
        $in = str_replace([ROOT, DS, '\\', $s], [$u['url'], '/', '/', $u['url']], $in);
        return $raw ? rawurldecode($in) : urldecode($in);
    },
    'yaml' => function(...$lot) {
        if (!__is_anemon__($lot[0])) {
            return s($lot[0]);
        }
        if (is_string($lot[0]) && Is::path($lot[0], true)) {
            $lot[0] = include $lot[0];
        }
        return call_user_func_array('__to_yaml__', $lot);
    }
] as $k => $v) {
    To::_($k, $v);
}

// Alias(es)…
foreach ([
    'h_t_m_l' => 'html',
    'u_r_l' => 'url',
    'y_a_m_l' => 'yaml'
] as $k => $v) {
    To::_($k, To::_($v));
}