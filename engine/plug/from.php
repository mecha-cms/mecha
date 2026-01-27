<?php

foreach ([
    'HTML' => static function (?string $value, $deep = false): ?string {
        $r = htmlspecialchars($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', !!$deep);
        return "" !== $r ? $r : null;
    },
    'JSON' => static function (?string $value, $array = false) {
        $r = json_decode($value ?? "", $array);
        return "" !== $r ? $r : null;
    },
    'URL' => static function (?string $value, $raw = false): ?string {
        $r = $raw ? rawurlencode($value ?? "") : urlencode($value ?? "");
        return "" !== $r ? $r : null;
    },
    'base64' => static function (?string $value): ?string {
        return "" !== ($value = base64_decode($value ?? "")) ? $value : null;
    },
    'entity' => static function (?string $value): ?string {
        return "" !== ($value = html_entity_decode($value ?? "", ENT_HTML5 | ENT_QUOTES)) ? $value : null;
    },
    'query' => static function (?string $from, $eval = true, $value = true): array {
        $to = [];
        if ("" === ($from = trim($from ?? ""))) {
            return $to;
        }
        static $q;
        $q = $q ?? function (array &$to, $k, $v) {
            $k = explode('[', strtr($k, [']' => ""]));
            while (count($k) > 1) {
                if ("" === ($kk = array_shift($k))) {
                    $kk = count($to);
                }
                if (!array_key_exists($kk, $to)) {
                    $to[$kk] = [];
                }
                $to =& $to[$kk];
            }
            if ("" === ($kk = array_shift($k))) {
                $kk = count($to);
            }
            $to[$kk] = $v;
            ksort($to);
        };
        if (isset($from[0]) && '?' === $from[0]) {
            $from = substr($from, 1);
        }
        foreach (explode('&', $from) as $v) {
            $v = explode('=', $v, 2);
            $v[1] = isset($v[1]) ? urldecode($v[1]) : $value;
            $q($to, urldecode($v[0]), $eval ? e($v[1]) : $v[1]);
        }
        return $to;
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