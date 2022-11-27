<?php

foreach ([
    'HTML' => static function (?string $value, $deep = false): ?string {
        $out = htmlspecialchars($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', !!$deep);
        return "" !== $out ? $out : null;
    },
    'JSON' => static function (?string $value): ?string {
        $out = json_decode($value ?? "");
        return "" !== $out ? $out : null;
    },
    'URL' => static function (?string $value, $raw = false): ?string {
        $out = $raw ? rawurlencode($value ?? "") : urlencode($value ?? "");
        return "" !== $out ? $out : null;
    },
    'base64' => static function (?string $value): ?string {
        return "" !== ($value = base64_decode($value ?? "")) ? $value : null;
    },
    'entity' => static function (?string $value): ?string {
        return "" !== ($value = html_entity_decode($value ?? "", ENT_HTML5 | ENT_QUOTES)) ? $value : null;
    },
    'query' => static function (?string $value, $eval = true): array {
        $out = [];
        if ("" === ($value = trim($value ?? ""))) {
            return $out;
        }
        $q = static function (array &$out, $k, $v) {
            $k = explode('[', strtr($k, [']' => ""]));
            while (count($k) > 1) {
                if ("" === ($kk = array_shift($k))) {
                    $kk = count($out);
                }
                if (!array_key_exists($kk, $out)) {
                    $out[$kk] = [];
                }
                $out =& $out[$kk];
            }
            if ("" === ($kk = array_shift($k))) {
                $kk = count($out);
            }
            $out[$kk] = $v;
            ksort($out);
        };
        if (isset($value[0]) && '?' === $value[0]) {
            $value = substr($value, 1);
        }
        foreach (explode('&', $value) as $v) {
            $v = explode('=', $v, 2);
            $v[1] = isset($v[1]) ? urldecode($v[1]) : true;
            $q($out, urldecode($v[0]), $eval ? e($v[1]) : $v[1]);
        }
        return $out;
    },
    'serial' => static function (?string $value): ?string {
        return "" !== ($value = unserialize($value)) ? $value : null;
    }
] as $k => $v) {
    From::_($k, $v);
}

// Alias(es)…
foreach ([
    'html' => 'HTML',
    'json' => 'JSON',
    'url' => 'URL'
] as $k => $v) {
    From::_($k, From::_($v));
}