<?php

foreach ([
    'HTML' => static function (?string $value): ?string {
        $out = htmlspecialchars_decode($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE);
        return "" !== $out ? $out : null;
    },
    'JSON' => static function ($value, $tidy = false): ?string {
        if ($tidy) {
            $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        } else {
            $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }
        $out = json_encode($value ?? "", $flags);
        return "" !== $out ? $out : null;
    },
    'URL' => static function (?string $value, $raw = false): ?string {
        $url = $GLOBALS['url'] . "";
        $value = (string) $value;
        $value = realpath($value) ?: $value;
        $value = strtr($value, [
            PATH => $url,
            strtr(PATH, D, '/') => $url,
            D => '/'
        ]);
        $out = $raw ? rawurldecode($value) : urldecode($value);
        return "" !== $out ? $out : null;
    },
    'base64' => "\\base64_encode",
    'camel' => "\\c",
    'dec' => static function (?string $value, int $pad = 4): ?string {
        $out = "";
        $value = (string) $value;
        for ($i = 0, $count = strlen($value); $i < $count; ++$i) {
            $v = ord($value[$i]);
            if ($pad) {
                $v = str_pad($v, $pad, '0', STR_PAD_LEFT);
            }
            $out .= '&#' . $v . ';';
        }
        return "" !== $out ? $out : null;
    },
    'file' => static function (?string $value, string $keep = '._'): ?string {
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
    'folder' => static function (?string $value, string $keep = '._'): ?string {
        $value = preg_split('/\s*[\\/]\s*/', $value ?? "", -1, PREG_SPLIT_NO_EMPTY);
        $n = array_pop($value);
        $out = "";
        foreach ($value as $v) {
            $out .= h($v, '-', true, $keep) . D;
        }
        $out = $out . h($n, '-', true, $keep);
        return "" !== $out ? $out : null;
    },
    'hex' => static function (?string $value, int $pad = 4): ?string {
        $out = "";
        $value = (string) $value;
        for ($i = 0, $count = strlen($value); $i < $count; ++$i) {
            $v = dechex(ord($value[$i]));
            if ($pad) {
                $v = str_pad($v, $pad, '0', STR_PAD_LEFT);
            }
            $out .= '&#x' . $v . ';';
        }
        return "" !== $out ? $out : null;
    },
    'kebab' => static function (?string $value, string $join = '-', $accent = true): ?string {
        $out = trim(h($value, $join, $accent), $join);
        return "" !== $out ? $out : null;
    },
    'key' => static function (?string $value, $accent = true): ?string {
        $out = trim(h($value, '_', $accent), '_');
        $out = $out && is_numeric($out[0]) ? '_' . $out : $out;
        return "" !== $out ? $out : null;
    },
    'lower' => "\\l",
    'pascal' => "\\p",
    'path' => static function (?string $value): ?string {
        $url = $GLOBALS['url'] . "";
        $value = strtr($value ?? "", [
            $url => PATH,
            strtr($url, '/', D) => PATH,
            '/' => D
        ]);
        $out = realpath($value) ?: $value;
        return "" !== $out ? $out : null;
    },
    'query' => static function (?array $value = []): ?string {
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
    'snake' => static function (?string $value, $accent = true): ?string {
        $out = trim(h($value, '_', $accent), '_');
        return "" !== $out ? $out : null;
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