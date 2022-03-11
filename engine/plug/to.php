<?php

foreach ([
    'HTML' => function(string $value = null) {
        return htmlspecialchars_decode($value, ENT_COMPAT | ENT_HTML5);
    },
    'JSON' => function($value = null, $tidy = false) {
        if ($tidy) {
            $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        } else {
            $flags = JSON_UNESCAPED_UNICODE;
        }
        return json_encode($value, $flags);
    },
    'URL' => function(string $value = null, $raw = false) {
        $url = $GLOBALS['url'] . "";
        $value = realpath($value) ?: $value;
        $value = strtr($value, [
            PATH => $url,
            strtr(PATH, D, '/') => $url,
            D => '/'
        ]);
        return $raw ? rawurldecode($value) : urldecode($value);
    },
    'base64' => "\\base64_encode",
    'dec' => function(string $value = null, $z = false, array $f = ['&#', ';']) {
        $out = "";
        for ($i = 0, $count = strlen($value); $i < $count; ++$i) {
            $s = ord($value[$i]);
            if (!$z) {
                $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            }
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'file' => function(string $value = null) {
        $value = preg_split('/\s*[\\/]\s*/', $value ?? "", -1, PREG_SPLIT_NO_EMPTY);
        $n = preg_split('/\s*[.]\s*/', array_pop($value) ?? "", -1, PREG_SPLIT_NO_EMPTY);
        $x = array_pop($n);
        $out = "";
        foreach ($value as $v) {
            $out .= h($v, '-', true, '_') . D;
        }
        $out .= h(implode('.', $n), '-', true, '_.') . '.' . h($x, '-', true);
        return '.' !== $out ? $out : null;
    },
    'folder' => function(string $value = null) {
        $value = preg_split('/\s*[\\/]\s*/', $value ?? "", -1, PREG_SPLIT_NO_EMPTY);
        $n = array_pop($value);
        $out = "";
        foreach ($value as $v) {
            $out .= h($v, '-', true, '_') . D;
        }
        $out = $out . h($n, '-', true, '_');
        return "" !== $out ? $out : null;
    },
    'hex' => function(string $value = null, $z = false, array $f = ['&#x', ';']) {
        $out = "";
        for ($i = 0, $count = strlen($value); $i < $count; ++$i) {
            $s = dechex(ord($value[$i]));
            if (!$z) {
                $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            }
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'key' => function(string $value = null, $accent = true) {
        $out = trim(h($value, '_', $accent), '_');
        return $out && is_numeric($out[0]) ? '_' . $out : $out;
    },
    'path' => function(string $value = null) {
        $url = $GLOBALS['url'] . "";
        $value = strtr($value, [
            $url => PATH,
            strtr($url, '/', D) => PATH,
            '/' => D
        ]);
        return realpath($value) ?: $value;
    },
    'query' => function(array $value = null) {
        $out = [];
        $q = static function(array $value, $enter) use(&$q) {
            $a = [];
            $exit = $enter ? ']' : "";
            foreach ($value as $k => $v) {
                $k = urlencode($k);
                if (is_array($v)) {
                    $a = array_merge($a, $q($v, $enter . $k . $exit . '['));
                } else {
                    $a[$enter . $k . $exit] = $v;
                }
            }
            return $a;
        };
        foreach ($q($value, "") as $k => $v) {
            // `['a' => false, 'b' => 'false', 'c' => null, 'd' => 'null']` → `b=false&d=null`
            if (false === $v || null === $v) {
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
    'serial' => "\\serialize"
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