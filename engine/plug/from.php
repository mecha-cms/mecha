<?php

foreach ([
    'HTML' => function(string $value = null, $deep = false) {
        return htmlspecialchars($value, ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', !!$deep);
    },
    'JSON' => function(string $value = null) {
        return json_decode($value);
    },
    'URL' => function($value = null, $raw = false) {
        return $raw ? rawurlencode($value) : urlencode($value);
    },
    'base64' => "\\base64_decode",
    'dec' => ["\\html_entity_decode", [null, ENT_QUOTES | ENT_HTML5]],
    'hex' => ["\\html_entity_decode", [null, ENT_QUOTES | ENT_HTML5]],
    'query' => function(string $value = null) {
        $out = [];
        if ("" === ($value = trim($value))) {
            return $out;
        }
        $q = static function(array &$out, $k, $v) {
            $k = explode('[', strtr($k, [']' => ""]));
            while (count($k) > 1) {
                $kk = array_shift($k);
                if (!array_key_exists($kk, $out)) {
                    $out[$kk] = [];
                }
                $out =& $out[$kk];
            }
            $out[array_shift($k)] = $v;
        };
        if (isset($value[0]) && '?' === $value[0]) {
            $value = substr($value, 1);
        }
        foreach (explode('&', $value) as $v) {
            $v = explode('=', $v, 2);
            $q($out, urldecode($v[0]), isset($v[1]) ? e(urldecode($v[1])) : true);
        }
        return $out;
    },
    'serial' => "\\unserialize"
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