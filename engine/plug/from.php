<?php

foreach ([
    'HTML' => static function (?string $value, $deep = false): ?string {
        $value = htmlspecialchars($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', !!$deep);
        return "" !== $value ? $value : null;
    },
    'JSON' => static function (?string $value, $array = false) {
        $value = json_decode($value ?? "", $array);
        return "" !== $value ? $value : null;
    },
    'base64' => static function (?string $value): ?string {
        return "" !== ($value = base64_decode($value ?? "")) ? $value : null;
    },
    'entity' => static function (?string $value): ?string {
        return "" !== ($value = html_entity_decode($value ?? "", ENT_HTML5 | ENT_QUOTES)) ? $value : null;
    },
    'link' => 'To::path',
    'path' => 'To::link',
    'query' => static function (?string $from, $eval = true, $value = true): array {
        if ("" === ($from = trim($from ?? ""))) {
            return [];
        }
        if ('?' === $from[0]) {
            $from = substr($from, 1);
        }
        $to = [];
        foreach (explode('&', $from) as $v) {
            $v = explode('=', $v, 2);
            $v[0] = urldecode($v[0]);
            if (isset($v[1])) {
                $v[1] = urldecode($v[1]);
                $v[1] = $eval ? e($v[1]) : $v[1];
            } else {
                $v[1] = $value;
            }
            $keys = explode('[', strtr($v[0], [']' => ""]));
            $r =& $to;
            while (isset($keys[1])) {
                $r[$k = "" === ($k = array_shift($keys)) ? count($r) : $k] ??= [];
                $r =& $r[$k];
            }
            $r["" === ($k = array_shift($keys)) ? count($r) : $k] = $v[1];
            ksort($r);
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
    'json' => 'JSON'
] as $k => $v) {
    From::_($k, From::_($v));
}