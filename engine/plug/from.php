<?php

foreach ([
    'HTML' => function(string $in = null, $deep = false) {
        return htmlspecialchars($in, ENT_COMPAT | ENT_HTML5, 'UTF-8', !!$deep);
    },
    'JSON' => function(string $in = null) {
        return json_decode($in);
    },
    'URL' => function($in = null, $raw = false) {
        return $raw ? rawurlencode($in) : urlencode($in);
    },
    'base64' => "\\base64_decode",
    'dec' => ["\\html_entity_decode", [null, ENT_QUOTES | ENT_HTML5]],
    'hex' => ["\\html_entity_decode", [null, ENT_QUOTES | ENT_HTML5]],
    'query' => function(string $in = null) {
        $out = [];
        $q = function(array &$out, $k, $v) {
            $k = explode('[', str_replace(']', "", $k));
            while (count($k) > 1) {
                $kk = array_shift($k);
                if (!array_key_exists($kk, $out)) {
                    $out[$kk] = [];
                }
                $out =& $out[$kk];
            }
            $out[array_shift($k)] = $v;
        };
        if (isset($in[0]) && '?' === $in[0]) {
            $in = substr($in, 1);
        }
        foreach (explode('&', $in) as $v) {
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
