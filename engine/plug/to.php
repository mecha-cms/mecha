<?php

foreach ([
    'HTML' => static function (?string $value): ?string {
        $value = htmlspecialchars_decode($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE);
        return "" !== $value ? $value : null;
    },
    'JSON' => static function ($value, $dent = false): ?string {
        if ($dent) {
            $value = json_encode($value ?? "", JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if (is_int($dent)) {
                $dent = str_repeat(' ', $dent);
            }
            if (is_string($dent)) {
                $value = strtr($value, ['    ' => $dent]);
            }
            return "" !== $value ? $value : null;
        }
        return "" !== ($value = json_encode($value ?? "", JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE)) ? $value : null;
    },
    'base64' => static function (?string $value): ?string {
        return "" !== ($value = base64_encode($value ?? "")) ? $value : null;
    },
    'camel' => "\\c",
    'entity' => static function (?string $value, $hex = false, int $pad = 4): ?string {
        $r = "";
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
            $r .= '&#' . ($hex ? 'x' : "") . $v . ';';
        }
        return "" !== $r ? $r : null;
    },
    'file' => static function (?string $value, string $keep = '._'): ?string {
        if ("" === trim($value ?? "")) {
            return null;
        }
        $r = [];
        if ($value = explode('/', strtr($value, "\\", '/'))) {
            $x = strtolower(trim(pathinfo($name = array_pop($value), PATHINFO_EXTENSION)));
            $value[] = h(trim(pathinfo($name, PATHINFO_FILENAME)), '-', true, $keep) . ("" !== $x ? '.' . $x : "");
        }
        foreach ($value as $v) {
            if ("" === ($v = trim($v))) {
                continue;
            }
            if (strspn($v, '.') === strlen($v)) {
                continue;
            }
            $r[] = h($v, '-', true, $keep);
        }
        return $r ? implode(D, $r) : null;
    },
    'folder' => static function (?string $value, string $keep = '._'): ?string {
        if ("" === trim($value ?? "")) {
            return null;
        }
        $r = [];
        foreach (explode('/', strtr($value, "\\", '/')) as $v) {
            if ("" === ($v = trim($v))) {
                continue;
            }
            if (strspn($v, '.') === strlen($v)) {
                continue;
            }
            $r[] = h($v, '-', true, $keep);
        }
        return $r ? implode(D, $r) : null;
    },
    'kebab' => static function (?string $value, string $join = '-', $accent = false): ?string {
        $value = trim(h($value, $join, $accent) ?? "", $join);
        return "" !== $value ? $value : null;
    },
    'link' => static function (?string $value) {
        if (0 === strpos($value = strtr($value ?? "", '/', D), $v = PATH . D)) {
            $value = substr($value, strlen($v));
        }
        $r = [];
        foreach (explode('/', rtrim(long('/' . strtr($value, D, '/')), '/')) as $v) {
            $r[] = !empty($v) ? rawurlencode($v) : $v;
        }
        // The `http:` part
        if (!empty($r[0]) && '%3A' === substr($r[0], -3)) {
            $r[0] = substr($r[0], 0, -3) . ':';
        }
        // The `127.0.0.1:80` part
        if (!empty($r[2])) {
            $r[2] = strtr($r[2], ['%3A' => ':']);
        }
        return $r ? implode('/', $r) : null;
    },
    'lower' => "\\l",
    'pascal' => "\\p",
    'path' => static function (?string $value): ?string {
        if (0 === strpos($value = strtr($value ?? "", D, '/'), $v = long('/') . '/')) {
            $value = substr($value, strlen($v));
        }
        $r = [];
        foreach (explode(D, rtrim(PATH . D . strtr($value, '/', D), D)) as $v) {
            $r[] = !empty($v) ? rawurldecode($v) : $v;
        }
        $r = stream_resolve_include_path($r = implode(D, $r)) ?: $r;
        return "" !== $r ? $r : null;
    },
    'query' => static function (?array $value): ?string {
        if (!$value) {
            return null;
        }
        $from = [[$value, ""]];
        $to = [];
        while ($from) {
            [$value, $x] = array_pop($from);
            ksort($value);
            foreach ($value as $k => $v) {
                $k = $x . urlencode($k) . ("" !== $x ? ']' : "");
                if (is_array($v)) {
                    $from[] = [$v, $k . '['];
                    continue;
                }
                // `['a' => false, 'b' => 'false', 'c' => null, 'd' => 'null']` → `b=false&d=null`
                if (false === $v || null === $v) {
                    continue;
                }
                // `['a' => true, 'b' => 'true', 'c' => ""]` → `a&b=true&c=`
                $to[] = true === $v ? $k : $k . '=' . urlencode(s($v));
            }
        }
        return $to ? '?' . implode('&', $to) : null;
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
    'html' => 'HTML'
] as $k => $v) {
    To::_($k, To::_($v));
}