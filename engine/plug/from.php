<?php

// Get key and value pair…
function __from_yaml_k__($s) {
    if ((strpos($s, "'") === 0 || strpos($s, '"') === 0) && preg_match('#(\'(?:[^\'\\\]|\\\.)*\'|"(?:[^"\\\]|\\\.)*") *: +([^\n]*)#', $s, $m)) {
        $a = [t($m[1], $s[0]), $m[2]];
    } else {
        $a = explode(':', $s, 2);
    }
    $a[0] = trim($a[0]);
    // If value is an empty string, replace with `[]`
    $a[1] = isset($a[1]) && $a[1] !== "" ? trim($a[1]) : [];
    return $a;
}

// Parse array-like string…
function __from_yaml_a__($s) {
    if (!is_string($s)) {
        return $s;
    }
    if (strpos($s, '[') === 0 && substr($s, -1) === ']' || strpos($s, '{') === 0 && substr($s, -1) === '}') {
        $a = preg_split('#(\s*(?:\'(?:[^\'\\\]|\\\.)*\'|"(?:[^"\\\]|\\\.)*"|[\[\]\{\}:,])\s*)#', $s, null, PREG_SPLIT_DELIM_CAPTURE);
        $s = "";
        foreach ($a as $v) {
            if (($v = trim($v)) === "") {
                continue;
            }
            if (strpos('[]{}:,', $v) !== false || is_numeric($v) || $v === 'true' || $v === 'false' || $v === 'null') {
                // Do nothing!
            } else if (strpos($v, '"') === 0 && substr($v, -1) === '"') {
                if (json_decode($v) === null) {
                    $v = '"' . str_replace('"', '\\"', $v) . '"';
                }
            } else if (strpos($v, "'") === 0 && substr($v, -1) === "'") {
                $v = '"' . t($v, "'") . '"';
            } else {
                $v = '"' . $v . '"';
            }
            $s .= $v;
        }
        return json_decode($s, true);
    }
    return $s;
}

function __from_yaml__($in, $d = '  ', $ref = [], $e = true) {
    // Normalize white-space(s)…
    $in = trim(n($in), "\n");
    if ($in === "") {
        return [];
    }
    $key = $out = $i = [];
    $len = strlen($d);
    // Save `\:` as `\x1A`
    $in = str_replace('\\:', X, $in);
    $x = x($d);
    if (strpos($in, '&') !== false) {
        if (preg_match_all('#((?:' . $x . ')*)([^\n]+): +&(\S+)(\s*\n((?:(?:\1' . $x . '[^\n]*)?\n)+|$)| *[^\n]*)#', $in, $m)) {
            foreach ($m[3] as $k => $v) {
                $ref[$v] = __from_yaml__($m[2][$k] . ':' . $m[4][$k], $in, $ref);
            }
        }
    }
    if (strpos($in, ': ') !== false && (strpos($in, '|') !== false || strpos($in, '>') !== false)) {
        $in = preg_replace_callback('#((?:' . $x . ')*)([^\n]+): +([|>])\s*\n((?:(?:\1' . $x . '[^\n]*)?\n)+|$)#', function($m) use($d) {
            $s = trim(str_replace("\n" . $m[1] . $d, "\n", "\n" . $m[4]), "\n");
            if ($m[3] === '>') {
                // TODO
                $s = preg_replace('#(\S)\n(\S)#', '$1 $2', $s);
            }
            return $m[1] . $m[2] . ': ' . json_encode($s) . "\n";
        }, $in);
    }
    foreach (explode("\n", $in) as $v) {
        $test = trim($v);
        // Ignore empty line-break and comment(s)…
        if ($test === "" || strpos($test, '#') === 0) {
            continue;
        }
        $dent = 0;
        while (substr($v, 0, $len) === $d) {
            ++$dent;
            $v = substr($v, $len);
        }
        // Start with `- `
        if (strpos($v, '- ') === 0) {
            ++$dent;
            if (isset($i[$dent])) {
                $i[$dent] += 1;
            } else {
                $i[$dent] = 0;
            }
            $v = substr_replace($v, $i[$dent] . ': ', 0, 2);
        // TODO
        } else if ($v === '-') {
            ++$dent;
            if (isset($i[$dent])) {
                $i[$dent] += 1;
            } else {
                $i[$dent] = 0;
            }
            $v = $i[$dent] . ':';
        } else {
            $i = [];
        }
        while ($dent < count($key)) {
            array_pop($key);
        }
        $a = __from_yaml_k__(trim($v));
        // Restore `\x1A` to `:`
        $a[0] = $key[$dent] = str_replace(X, ':', $a[0]);
        if (is_string($a[1])) {
            // Ignore comment(s)…
            if (strpos($a[1], '#') === 0) {
                $a[1] = [];
            // Copy…
            } else if (strpos($a[1], '&') === 0) {
                $a[1] = strpos($a[1], ' ') !== false ? explode(' ', $a[1], 2)[1] : [];
            // Paste…
            } else if (strpos($a[1], '*') === 0 && isset($ref[substr($a[1], 1)])) {
                $a[1] = array_pop($ref[substr($a[1], 1)]);
            } else {
                $s = strpos($a[1], "'") === 0 || strpos($a[1], '"') === 0 ? $a[1] : explode('#', $a[1])[0];
                $s = trim($s);
                $s = $s === '~' ? 'null' : $s;
                $a[1] = __from_yaml_a__($e ? e($s) : $s);
            }
        }
        $parent =& $out;
        foreach ($key as $kk) {
            if (!isset($parent[$kk])) {
                $parent[$kk] = $a[1];
                break;
            }
            $parent =& $parent[$kk];
        }
    }
    return $out;
}

foreach ([
    'base64' => 'base64_decode',
    'dec' => 'html_entity_decode',
    'hex' => 'html_entity_decode',
    'html' => 'htmlspecialchars',
    'json' => function($in) {
        if (__is_anemon__($in)) {
            return (object) o($in);
        }
        return json_decode($in);
    },
    'query' => function($in, $c = []) {
        $c = array_replace(['?', '&', '=', ""], $c);
        if (!is_string($in)) {
            return [];
        }
        $out = [];
        foreach (explode($c[1], t($in, $c[0], $c[3])) as $v) {
            $q = explode($c[2], $v, 2);
            $q[0] = urldecode($q[0]);
            if (isset($q[1])) {
                $q[1] = urldecode($q[1]);
                // `a=TRUE&b` → `['a' => 'true', 'b' => true]`
                // `a=true&b` → `['a' => 'true', 'b' => true]`
                $q[1] = e($q[1] === 'TRUE' || $q[1] === 'true' ? '"true"' : $q[1]);
            } else {
                $q[1] = true;
            }
            Anemon::set($out, str_replace(']', "", $q[0]), $q[1], '[');
        }
        return $out;
    },
    'url' => function($in, $raw = false) {
        return $raw ? rawurlencode($in) : urlencode($in);
    },
    'yaml' => function(...$lot) {
        if (__is_anemon__($lot[0])) {
            return a($lot[0]);
        }
        if (Is::path($lot[0], true)) {
            $lot[0] = file_get_contents($lot[0]);
        }
        if (strpos($lot[0] = n($lot[0]), "---\n") === 0) {
            $out = [];
            $lot[0] = str_replace([X . "---\n", "\n..." . X, X], "", X . $lot[0] . X);
            foreach (explode("\n---\n", $lot[0]) as $v) {
                $out[] = call_user_func_array('__from_yaml__', $lot);
            }
            return $out;
        }
        return call_user_func_array('__from_yaml__', $lot);
    }
] as $k => $v) {
    From::_($k, $v);
}

// Alias(es)…
foreach ([
    'h_t_m_l' => 'html',
    'j_s_o_n' => 'json',
    'u_r_l' => 'url',
    'y_a_m_l' => 'yaml'
] as $k => $v) {
    From::_($k, From::_($v));
}