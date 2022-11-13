<?php

foreach ([
    'HTML' => static function (string $value = null) {
        return htmlspecialchars_decode($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE);
    },
    'JSON' => static function ($value = null, $tidy = false) {
        if ($tidy) {
            $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        } else {
            $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }
        return json_encode($value ?? "", $flags);
    },
    'URL' => static function (string $value = null, $raw = false) {
        $url = $GLOBALS['url'] . "";
        $value = $value ?? "";
        $value = realpath($value) ?: $value;
        $value = strtr($value, [
            PATH => $url,
            strtr(PATH, D, '/') => $url,
            D => '/'
        ]);
        return $raw ? rawurldecode($value) : urldecode($value);
    },
    'base64' => "\\base64_encode",
    'camel' => "\\c",
    'dec' => static function (string $value = null, int $pad = 4) {
        $out = "";
        $value = $value ?? "";
        for ($i = 0, $count = strlen($value); $i < $count; ++$i) {
            $v = ord($value[$i]);
            if ($pad) {
                $v = str_pad($v, $pad, '0', STR_PAD_LEFT);
            }
            $out .= '&#' . $v . ';';
        }
        return $out;
    },
    'file' => static function (string $value = null, string $keep = '._') {
        $value = preg_split('/\s*[\\/]\s*/', $value ?? "", -1, PREG_SPLIT_NO_EMPTY);
        $n = preg_split('/\s*[.]\s*/', array_pop($value) ?? "", -1, PREG_SPLIT_NO_EMPTY);
        $x = array_pop($n);
        $out = "";
        foreach ($value as $v) {
            $out .= h($v, '-', true, $keep) . D;
        }
        $out .= h(implode('.', $n), '-', true, $keep) . '.' . h($x, '-', true);
        return '.' !== $out ? $out : null;
    },
    'folder' => static function (string $value = null, string $keep = '._') {
        $value = preg_split('/\s*[\\/]\s*/', $value ?? "", -1, PREG_SPLIT_NO_EMPTY);
        $n = array_pop($value);
        $out = "";
        foreach ($value as $v) {
            $out .= h($v, '-', true, $keep) . D;
        }
        $out = $out . h($n, '-', true, $keep);
        return "" !== $out ? $out : null;
    },
    'hex' => static function (string $value = null, int $pad = 4) {
        $out = "";
        $value = $value ?? "";
        for ($i = 0, $count = strlen($value); $i < $count; ++$i) {
            $v = dechex(ord($value[$i]));
            if ($pad) {
                $v = str_pad($v, $pad, '0', STR_PAD_LEFT);
            }
            $out .= '&#x' . $v . ';';
        }
        return $out;
    },
    'kebab' => static function (string $value = null, string $join = '-', $accent = true) {
        return trim(h($value, $join, $accent), $join);
    },
    'key' => static function (string $value = null, $accent = true) {
        $out = trim(h($value, '_', $accent), '_');
        return $out && is_numeric($out[0]) ? '_' . $out : $out;
    },
    'lower' => "\\l",
    'pascal' => "\\p",
    'path' => static function (string $value = null) {
        $url = $GLOBALS['url'] . "";
        $value = strtr($value ?? "", [
            $url => PATH,
            strtr($url, '/', D) => PATH,
            '/' => D
        ]);
        return realpath($value) ?: $value;
    },
    'query' => static function (array $value = null) {
        $out = [];
        if (!$value) {
            return null;
        }
        $q = static function (array $value, $enter) use (&$q) {
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
    'serial' => "\\serialize",
    'snake' => static function (string $value = null, $accent = true) {
        return trim(h($value, '_', $accent), '_');
    },
    'text' => "\\w",
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