<?php

From::plug('base64', 'base64_decode');
From::plug('dec', 'html_entity_decode');
From::plug('hex', 'html_entity_decode');
From::plug('html', 'htmlspecialchars');

From::plug('json', function($input) {
    if (__is_anemon__($input)) {
        return (object) o($input);
    }
    return json_decode($input);
});

From::plug('url', function($input, $raw = false) {
    return $raw ? rawurlencode($input) : urlencode($input);
});

// Parse array–like string…
function __from_yaml_a__($s) {
    if (!is_string($s)) return $s;
    if (strpos($s, '[') === 0 && substr($s, -1) === ']') {
        return preg_split('#\s*,\s*#', trim(t($s, '[', ']')));
    } else if (strpos($s, '{') === 0 && substr($s, -1) === '}') {
        return $s; // TODO
    }
    return $s;
}

// Remove inline comment(s)…
function __from_yaml_c__($s, $q) {
    if (!is_string($s)) return $s;
    if ($s && strpos($s, '#') !== false) {
        if ($s[0] === '"' || $s[0] === "'") {
            $s = preg_replace('/(' . implode('|', $q) . ')|\s*#.*/', '$1', $s);
        } else {
            $s = explode('#', $s, 2);
            $s = trim($s[0]);
        }
    }
    return $s;
}

// Get key and value part…
function __from_yaml_k__($li, $s, $q) {
    if ($li[0] === '"' && preg_match('/^(' . $q[0] . ')\s*' . x($s[2]) . '(.*?)$/', $li, $part)) {
        array_shift($part);
        $part[0] = t($part[0], '"');
    } else if ($li[0] === "'" && preg_match('/^(' . $q[1] . ')\s*' . x($s[2]) . '(.*?)$/', $li, $part)) {
        array_shift($part);
        $part[0] = t($part[0], "'");
    } else {
        $part = explode($s[2], $li, 2);
    }
    return $part;
}

function __from_yaml__($input, $c = [], $in = '  ') {
    if (!is_string($input)) {
        return a($input);
    }
    if (!trim($input)) return [];
    $s = array_replace(Page::v, $c);
    $q = ['"(?:[^"\\\]|\\\.)*"', '\'(?:[^\'\\\]|\\\.)*\''];
    $i = 0;
    $output = $data = [];
    // Normalize white–space(s)
    $input = trim(n($input, $in), $s[4]);
    // Save `\: ` as `\x1A`
    $input = str_replace('\\' . $s[2], X, $input);
    // Check if it is a flat YAML data, if so, optimize it!
    if (strpos($input, $in) !== 0 && strpos($input, $s[4] . $in) === false) {
        foreach (explode($s[4], $input) as $li) {
            // Ignore comment and empty line–break
            if ($li === "" || strpos($li, '#') === 0) continue;
            // Start with `- `
            if (strpos($li, $s[3]) === 0) {
                $li = $i . $s[2] . substr($li, strlen($s[3]));
                ++$i;
            }
            // No `: ` … fix it!
            if (strpos($li, $s[2]) === false) {
                $li = $li . $s[2] . $li;
            }
            $part = __from_yaml_k__($li, $s, $q);
            // Restore `\x1A` as `: `
            $k = str_replace(X, $s[2], trim($part[0]));
            // If value is an empty string, replace with an `[]`
            $v = isset($part[1]) && trim($part[1]) !== "" ? trim($part[1]) : [];
            $v = __from_yaml_c__($v, $q);
            $output[$k] = e(__from_yaml_a__($v));
        }
        return $output;
    }
    // Else, should be a complex YAML data…
    $len = strlen($in);
    foreach (explode($s[4], $input) as $li) {
        $dent = 0;
        $li = rtrim($li);
        // Ignore comment and empty line–break
        if ($li === "" || strpos($li, '#') === 0) continue;
        while (substr($li, 0, $len) === $in) {
            $dent += 1;
            $li = substr($li, $len);
        }
        $li = ltrim($li) . ' ';
        while ($dent < count($data)) {
            array_pop($data);
        }
        // Start with `- `
        if (strpos($li, $s[3]) === 0) {
            $li = $i . $s[2] . substr($li, strlen($s[3]));
            ++$i;
        } else {
            $i = 0;
        }
        // No `: ` … fix it!
        if (strpos($li, $s[2]) === false) {
            $li = $li . $s[2] . $li;
        }
        $part = __from_yaml_k__($li, $s, $q);
        // Restore `\x1A` as `: `
        $k = str_replace(X, $s[2], trim($part[0]));
        $v = isset($part[1]) && trim($part[1]) !== "" ? trim($part[1]) : [];
        $v = __from_yaml_c__($v, $q);
        $data[$dent] = $k;
        $parent =& $output;
        foreach ($data as $k) {
            if (!isset($parent[$k])) {
                $parent[$k] = e(__from_yaml_a__($v));
                break;
            }
            $parent =& $parent[$k];
        }
    }
    return $output;
}

From::plug('yaml', function(...$lot) {
    if (__is_anemon__($lot[0])) {
        return a($lot[0]);
    }
    if (Is::path($lot[0], true)) {
        $lot[0] = file_get_contents($lot[0]);
    }
    $s = Page::v;
    $lot[0] = str_replace([X . $s[0], $s[1] . X, X], "", X . $lot[0] . X);
    return call_user_func_array('__from_yaml__', $lot);
});

// Alias(es)…
From::plug('h_t_m_l', 'htmlspecialchars');
From::plug('j_s_o_n', 'From::json');
From::plug('u_r_l', 'From::url');
From::plug('y_a_m_l', 'From::yaml');