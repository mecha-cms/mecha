<?php

foreach([
    'HTML' => function(string $in = null) {
        return htmlspecialchars_decode($in, ENT_COMPAT | ENT_HTML5);
    },
    'JSON' => function($in = null, $tidy = false) {
        if ($tidy) {
            $i = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        } else {
            $i = JSON_UNESCAPED_UNICODE;
        }
        return json_encode($in, $i);
    },
    'URL' => function(string $in = null, $raw = false) {
        $url = $GLOBALS['url'];
        $x = strtr(ROOT, DS, '/');
        $in = realpath($in) ?: $in;
        $in = str_replace([ROOT, DS, $x], [$url, '/', $url], $in);
        return $raw ? rawurldecode($in) : urldecode($in);
    },
    'base64' => "\\base64_encode",
    'camel' => "\\c",
    'dec' => function(string $in = null, $z = false, array $f = ['&#', ';']) {
        $out = "";
        for ($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = ord($in[$i]);
            if (!$z) {
                $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            }
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'excerpt' => function(string $in = null, $html = true, $x = 200) {
        $s = w($in, $html ? 'a,abbr,b,br,cite,code,del,dfn,em,i,ins,kbd,mark,q,span,strong,sub,sup,time,u,var' : []);
        $utf8 = extension_loaded('mbstring');
        if (is_int($x)) {
            $x = [$x, '&#x2026;'];
        }
        $utf8 = extension_loaded('mbstring');
        // <https://stackoverflow.com/a/1193598/1163000>
        if ($html && (false !== strpos($s, '<') || false !== strpos($s, '&'))) {
            $out = "";
            $done = $i = 0;
            $tags = [];
            while ($done < $x[0] && preg_match('/<\/?([a-z\d:.-]+)(?:\s[^>]*)?>|&(?:[a-z\d]+|#\d+|#x[a-f\d]+);|[\x80-\xFF][\x80-\xBF]*/i', $s, $m, PREG_OFFSET_CAPTURE, $i)) {
                $tag = $m[0][0];
                $pos = $m[0][1];
                $str = substr($s, $i, $pos - $i);
                if ($done + strlen($str) > $x[0]) {
                    $out .= substr($str, 0, $x[0] - $done);
                    $done = $x[0];
                    break;
                }
                $out .= $str;
                $done += strlen($str);
                if ($done >= $x[0]) {
                    break;
                }
                if ('&' === $tag[0] || ord($tag) >= 0x80) {
                    $out .= $tag;
                    ++$done;
                } else {
                    // `tag`
                    $n = $m[1][0];
                    // `</tag>`
                    if ('/' === $tag[1]) {
                        $open = array_pop($tags);
                        assert($open === $n); // Check that tag(s) are properly nested!
                        $out .= $tag;
                    // `<tag/>`
                    } else if ('/>' === substr($tag, -2) || preg_match('/<(?:area|base|br|col|command|embed|hr|img|input|link|meta|param|source)(?:\s[^>]*)?>/i', $tag)) {
                        $out .= $tag;
                    // `<tag>`
                    } else {
                        $out .= $tag;
                        $tags[] = $n;
                    }
                }
                // Continue after the tag…
                $i = $pos + strlen($tag);
            }
            // Print rest of the text…
            if ($done < $x[0] && $i < strlen($s)) {
                $out .= substr($s, $i, $x[0] - $done);
            }
            // Close any open tag(s)…
            while ($close = array_pop($tags)) {
                $out .= '</' . $close . '>';
            }
            $out = trim(str_replace('<br>', ' ', $out));
            $s = trim(strip_tags($s));
            $t = $utf8 ? mb_strlen($s) : strlen($s);
            $out = trim($out) . ($t > $x[0] ? $x[1] : "");
            return "" !== $out ? $out : null;
        }
        $out = $utf8 ? mb_substr($s, 0, $x[0]) : substr($s, 0, $x[0]);
        $t = $utf8 ? mb_strlen($s) : strlen($s);
        $out = trim($out) . ($t > $x[0] ? $x[1] : "");
        return "" !== $out ? $out : null;
    },
    'file' => function(string $in = null) {
        $in = preg_split('/\s*[\\/]\s*/', $in, null, PREG_SPLIT_NO_EMPTY);
        $n = preg_split('/\s*[.]\s*/', array_pop($in), null, PREG_SPLIT_NO_EMPTY);
        $x = array_pop($n);
        $out = "";
        foreach ($in as $v) {
            $out .= h($v, '-', true, '_') . DS;
        }
        $out .= h(implode('.', $n), '-', true, '_.') . '.' . h($x, '-', true);
        return '.' !== $out ? $out : null;
    },
    'folder' => function(string $in = null) {
        $in = preg_split('/\s*[\\/]\s*/', $in, null, PREG_SPLIT_NO_EMPTY);
        $n = array_pop($in);
        $out = "";
        foreach ($in as $v) {
            $out .= h($v, '-', true, '_') . DS;
        }
        $out = $out . h($n, '-', true, '_');
        return "" !== $out ? $out : null;
    },
    'hex' => function(string $in = null, $z = false, array $f = ['&#x', ';']) {
        $out = "";
        for ($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = dechex(ord($in[$i]));
            if (!$z) {
                $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            }
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'kebab' => function(string $in = null, string $join = '-', $accent = true) {
        return trim(h($in, $join, $accent), $join);
    },
    'key' => function(string $in = null, $accent = true) {
        $out = trim(h($in, '_', $accent), '_');
        return $out && is_numeric($out[0]) ? '_' . $out : $out;
    },
    'lower' => "\\l",
    'pascal' => "\\p",
    'path' => function(string $in = null) {
        $url = $GLOBALS['url'];
        $x = strtr($url, '/', DS);
        $in = str_replace([$url, '/', $x], [ROOT, DS, ROOT], $in);
        return realpath($in) ?: $in;
    },
    'query' => function(array $in = null) {
        $out = [];
        $q = function(array $in, $enter) use(&$q) {
            $a = [];
            $exit = $enter ? ']' : "";
            foreach ($in as $k => $v) {
                $k = urlencode($k);
                if (is_array($v)) {
                    $a = array_merge($a, $q($v, $enter . $k . $exit . '['));
                } else {
                    $a[$enter . $k . $exit] = $v;
                }
            }
            return $a;
        };
        foreach ($q($in, "") as $k => $v) {
            // `['a' => false, 'b' => 'false', 'c' => null, 'd' => 'null']` → `b=false&d=null`
            if (!isset($v) || false === $v) {
                continue;
            }
            // `['a' => true, 'b' => 'true', 'c' => ""]` → `a&b=true&c=`
            $v = true !== $v ? '=' . urlencode(s($v)) : "";
            if ("" !== ($v = $k . $v)) {
                $out[] = $v;
            }
        }
        return $out ? '?' . implode('&', $out) : null;
    },
    'sentence' => function(string $in = null, string $tail = '.') {
        $in = trim($in);
        if (extension_loaded('mbstring')) {
            return mb_strtoupper(mb_substr($in, 0, 1)) . mb_strtolower(mb_substr($in, 1)) . $tail;
        }
        return ucfirst(strtolower($in)) . $tail;
    },
    'serial' => "\\serialize",
    'snake' => function(string $in = null, $a = true) {
        return trim(h($in, '_', $a), '_');
    },
    'text' => "\\w",
    'title' => function(string $in = null) {
        $in = w($in);
        $out = extension_loaded('mbstring') ? mb_convert_case($in, MB_CASE_TITLE) : ucwords($in);
        // Convert to abbreviation if all case(s) are in upper
        $out = u($out) === $out ? str_replace(' ', "", $out) : $out;
        return "" !== $out ? $out : null;
    },
    'upper' => "\\u"
] as $k => $v) {
    To::_($k, $v);
}

// Alias(es)…
foreach ([
    'files' => 'folder',
    'html' => 'HTML',
    'url' => 'URL'
] as $k => $v) {
    To::_($k, To::_($v));
}
