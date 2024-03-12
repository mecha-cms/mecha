<?php

foreach ([
    'HTML' => static function (?string $value): ?string {
        $value = htmlspecialchars_decode($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE);
        return "" !== $value ? $value : null;
    },
    'JSON' => static function ($value, $dent = false): ?string {
        if ($dent) {
            $value = json_encode($value ?? "", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if (is_int($dent)) {
                $dent = str_repeat(' ', $dent);
            }
            if (is_string($dent)) {
                $value = strtr($value, ['    ' => $dent]);
            }
            return "" !== $value ? $value : null;
        }
        return "" !== ($value = json_encode($value ?? "", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ? $value : null;
    },
    'URL' => static function (?string $value, $raw = false): ?string {
        $url = lot('url') . "";
        $value = (string) $value;
        $value = stream_resolve_include_path($value) ?: $value;
        $value = strtr($value, [
            PATH => $url,
            strtr(PATH, D, '/') => $url,
            D => '/'
        ]);
        $value = $raw ? rawurldecode($value) : urldecode($value);
        return "" !== $value ? $value : null;
    },
    'base64' => static function (?string $value): ?string {
        return "" !== ($value = base64_encode($value ?? "")) ? $value : null;
    },
    'camel' => "\\c",
    'entity' => static function (?string $value, $hex = false, int $pad = 4): ?string {
        $out = "";
        $utf8 = extension_loaded('mbstring');
        $value = (string) $value;
        $value = $utf8 ? mb_str_split($value, 1, 'UTF-8') : str_split($value);
        for ($i = 0, $count = count($value); $i < $count; ++$i) {
            $v = $utf8 ? mb_ord($value[$i], 'UTF-8') : ord($value[$i]);
            if ($hex) {
                $v = dechex($v);
            }
            if ($pad) {
                $v = str_pad($v, $pad, '0', STR_PAD_LEFT);
            }
            $out .= '&#' . ($hex ? 'x' : "") . $v . ';';
        }
        return "" !== $out ? $out : null;
    },
    'file' => static function (?string $value, string $keep = '._'): ?string {
        if ("" === trim($value ?? "")) {
            return null;
        }
        // Trim white-space(s) around `DIRECTORY_SEPARATOR`
        $value = preg_replace('/\s*[' . ($v = "\\\\\/") . ']\s*/', D, $value);
        // Make sure file extension uses lower-case(s)
        $value = preg_replace_callback('/\s*[.]\s*([a-z\d]+)$/i', static function ($m) {
            return '.' . strtolower($m[1]);
        }, $value);
        // Convert to safe file path
        $value = strtr(h($value, '-', true, $keep . $v) ?? "", ["\\" => D, '/' => D]);
        // Convert `\.\` and `\..\` to `\`
        $value = strtr($value, [D . '.' . D => D, D . '..' . D => D]);
        return "" !== trim($value, '.') ? $value : null;
    },
    'folder' => static function (?string $value, string $keep = '._'): ?string {
        if ("" === trim($value ?? "")) {
            return null;
        }
        // Trim white-space(s) around `DIRECTORY_SEPARATOR`
        $value = preg_replace('/\s*[' . ($v = "\\\\\/") . ']\s*/', D, $value);
        // Convert to safe folder path
        $value = strtr(h($value, '-', true, $keep . $v) ?? "", ["\\" => D, '/' => D]);
        // Convert `\.\` and `\..\` to `\`
        $value = strtr($value, [D . '.' . D => D, D . '..' . D => D]);
        return "" !== trim($value, '.') ? $value : null;
    },
    'kebab' => static function (?string $value, string $join = '-', $accent = false): ?string {
        $value = trim(h($value, $join, $accent) ?? "", $join);
        return "" !== $value ? $value : null;
    },
    'lower' => "\\l",
    'pascal' => "\\p",
    'path' => static function (?string $value): ?string {
        $url = lot('url') . "";
        $value = strtr($value ?? "", [
            $url => PATH,
            strtr($url, '/', D) => PATH,
            '/' => D
        ]);
        $value = stream_resolve_include_path($value) ?: $value;
        return "" !== $value ? $value : null;
    },
    'query' => static function (?array $value): ?string {
        $out = [];
        if (!$value) {
            return null;
        }
        $q = static function (array $value, $enter) use (&$q) {
            $a = [];
            $exit = $enter ? ']' : "";
            ksort($value);
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
    'serial' => static function ($value): ?string {
        return "" !== ($value = serialize($value)) ? $value : null;
    },
    'snake' => static function (?string $value, $accent = false): ?string {
        $value = trim(h($value, '_', $accent) ?? "", '_');
        return "" !== $value ? $value : null;
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