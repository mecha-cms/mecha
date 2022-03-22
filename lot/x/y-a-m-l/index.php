<?php

// <https://github.com/mecha-cms/mecha/issues/94>
define("YAML\\SOH", '---');
define("YAML\\ETB", '---');
define("YAML\\EOT", '...');

From::_('YAML', $plug = function(string $value, string $dent = '  ', $docs = false, $eval = true) {
    /*
    if (extension_loaded('yaml')) {
        $value = explode("\n...\n", $value, 2);
        $out = yaml_parse(trim($value[0], "\n"), -1);
        if (false !== $docs) {
            $out[true === $docs ? "\t" : $docs] = trim($value[1] ?? "", "\n");
        }
        return $docs ? $out : ($out[0] ?? []);
    }
    */
    $yaml = static function(string $value, string $dent = '  ', $eval = true) use(&$yaml) {
        // Break into structure(s)
        $yaml_select = static function(string $value) {
            $out = [];
            $s = $n = null;
            foreach (explode("\n", $value) as $v) {
                if ('#' === substr($vv = trim($v), 0, 1)) {
                    continue; // Remove comment(s)
                }
                if ($v && ' ' !== $v[0] && 0 !== strpos($v, '- ') && '-' !== $vv) {
                    if (null !== $s) {
                        $out[] = rtrim($s);
                    }
                    $s = $v;
                } else {
                    $s .= $n ? ' ' . ltrim($v) : "\n" . $v;
                }
                $n = '-' === $vv;
            }
            $out[] = rtrim($s);
            return $out;
        };
        $yaml_set = static function(&$out, string $value, string $dent, $eval) use(&$yaml) {
            // Folded-style string
            $yaml_block = static function(string $value) {
                $out = "";
                $e = false; // Previous is empty
                $x = false; // Has back-slash at the end of string
                foreach (explode("\n", $value) as $k => $v) {
                    $t = trim($v);
                    if ("" === $t) {
                        $out .= "\n";
                    } else if (!$e && !$x) {
                        $out .= ' ';
                    }
                    if ("" !== $t && "\\" === substr($t, -1)) {
                        $out .= ltrim(substr($v, 0, -1));
                    } else if ("" !== $t) {
                        $out .= $t;
                    }
                    if ("" === $t) {
                        $e = true;
                        $x = false;
                    } else if ("\\" === substr($t, -1)) {
                        $e = false;
                        $x = true;
                    } else {
                        $e = $x = false;
                    }
                }
                return trim($out);
            };
            $yaml_eval = static function($value) use(&$yaml_eval) {
                if (is_array($value)) {
                    foreach ($value as &$v) {
                        $v = $yaml_eval($v);
                    }
                    unset($v);
                    return $value;
                }
                if ($value && ('"' === $value[0] && '"' === substr($value, -1) || "'" === $value[0] && "'" === substr($value, -1))) {
                    return substr(strtr($value, [
                        "\\\"" => '"',
                        "\\'" => "'"
                    ]), 1, -1);
                }
                return e($value, ['~' => null]);
            };
            // Get key and value pair(s)
            $yaml_break = static function(string $value) use(&$yaml_eval) {
                $value = trim($value, "\n");
                if (0 === strpos($value, '"') || 0 === strpos($value, "'")) {
                    $q = $value[0];
                    if (preg_match('/^(' . $q . '(?:[^' . $q . '\\\]|\\\.)*' . $q . ')\s*(:[ \n])([\s\S]*)$/', $value, $m)) {
                        array_shift($m);
                        $m[0] = substr(strtr($m[0], [
                            "\\\"" => '"',
                            "\\'" => "'"
                        ]), 1, -1);
                        return $m;
                    }
                } else if (
                    false !== strpos($value, ':') &&
                    0 !== strpos($value, '- ') &&
                    false === strpos('[{', $value[0])
                ) {
                    $m = explode(':', $value, 2);
                    $m[0] = trim($m[0]);
                    if (false !== strpos($m[1], '#')) {
                        $m[1] = preg_replace('/^\s*#.*$/m', "", $m[1]);
                    }
                    $m[2] = ltrim(rtrim($m[1] ?? ""), "\n");
                    $m[1] = ':' . ($m[1][0] ?? "");
                    return $m;
                }
                $out = strstr($value, '#', true);
                return [false, false, trim(false !== $out ? $out : $value)];
            };
            $yaml_list = static function(string $value, string $dent, $eval) use(&$yaml, &$yaml_break, &$yaml_eval, &$yaml_pull, &$yaml_value) {
                $out = [];
                $value = $yaml_pull($value, '  ' /* hard-coded */);
                foreach (explode("\n- ", substr($value, 2)) as $v) {
                    $v = strtr($v, ["\n  " => "\n"]);
                    list($k, $m) = $yaml_break($v);
                    if (false === $m) {
                        $v = $yaml_value($v);
                        $out[] = $eval ? $yaml_eval($v) : $v;
                    } else {
                        $out[] = $yaml($v, $dent, $eval);
                    }
                }
                return $out;
            };
            // Dedent from `$dent`
            $yaml_pull = static function(string $value, string $dent) {
                if (0 === strpos($value, $dent)) {
                    return strtr(substr($value, strlen($dent)), ["\n" . $dent => "\n"]);
                }
                return $value;
            };
            // Parse flow-style collection(s)
            $yaml_span = static function(string $value, $eval) use(&$yaml_eval) {
                $out = "";
                // Validate to JSON
                foreach (preg_split('/\s*("(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'|[\[\]\{\}:,])\s*/', $value, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $v) {
                    $out .= false !== strpos('[]{}:,', $v) ? $v : json_encode($v);
                }
                $out = json_decode($out, true) ?? $value;
                return $eval ? $yaml_eval($out) : $out;
            };
            // Remove comment(s)
            $yaml_value = static function(string $value) {
                $value = trim($value);
                if (0 === strpos($value, '"') || 0 === strpos($value, "'")) {
                    $q = $value[0];
                    if (preg_match('/^(' . $q . '(?:[^' . $q . '\\\]|\\\.)*' . $q . ')(\s*#.*)?$/', $value, $m)) {
                        return $m[1];
                    }
                }
                $out = strstr($value, '#', true);
                return trim(false !== $out ? $out : $value);
            };
            list($k, $m, $v) = $yaml_break($value);
            if (false === $k && false === $m && "" !== $v) {
                if (
                    '[' === $v[0] && ']' === substr($v, -1) ||
                    '{' === $v[0] && '}' === substr($v, -1)
                ) {
                    $out = $yaml_span($v, $eval);
                    return;
                }
            }
            $vv = $yaml_pull($v, $dent);
            // Get first token
            $t = substr(trim($vv), 0, 1);
            // A literal-style or folded-style scalar value
            if ('|' === $t || '>' === $t) {
                $vv = $yaml_pull(ltrim(substr(ltrim($vv), 1), "\n"), $dent);
                $out[$k] = '>' === $t ? $yaml_block($vv) : $vv;
            // Maybe a YAML collection(s)
            } else if (":\n" === $m) {
                // Sequence
                if (0 === strpos($vv, '- ')) {
                    // Indented sequence
                    if (0 === strpos($v, $dent . '-')) {
                        $v = $vv;
                    }
                    $out[$k] = $yaml_list($v, $dent, $eval);
                // Else
                } else {
                    $out[$k] = "" !== $vv ? $yaml($vv, $dent, $eval) : [];
                }
            } else {
                $vv = $yaml_value($vv);
                if (0 === strpos($vv, '- ')) {
                    $out = $yaml_list($vv, $dent, $eval);
                    return;
                }
                if ("" === $vv) {
                    $vv = null; // Empty value means `null`
                } else if ('[]' === $vv || '{}' === $vv) {
                    $vv = []; // Empty array
                } else if (
                    $vv && (
                        '[' === $vv[0] && ']' === substr($vv, -1) ||
                        '{' === $vv[0] && '}' === substr($vv, -1)
                    )
                ) {
                    // Use native JSON parser where possible
                    $vv = json_decode($vv, true) ?? $yaml_span($vv, $eval);
                } else if ($eval) {
                    $vv = $yaml_eval($vv);
                }
                $out[$k] = $vv;
            }
        };
        $out = [];
        // Normalize line-break
        $value = trim(n($value));
        if ("" === $value) {
            return $out; // Empty array
        }
        foreach ($yaml_select($value) as $v) {
            "" !== $v && $yaml_set($out, $v, $dent, $eval);
        }
        return $out;
    };
    $yaml_docs = static function(string $value, string $dent = '  ', $eval = true, $content = "\t") use(&$yaml) {
        $docs = [];
        // Normalize line-break
        $value = trim(n($value));
        // Remove the first separator
        $value = 0 === strpos($value, YAML\SOH) && '-' !== substr($value, 3, 1) ? preg_replace('/^' . x(YAML\SOH) . '\s*/', "", $value) : $value;
        // Skip any string after `...`
        $parts = explode("\n" . YAML\EOT . "\n", trim($value) . "\n", 2);
        foreach (explode("\n" . YAML\ETB, $parts[0]) as $v) {
            $docs[] = $yaml(trim($v), $dent, $eval);
        }
        // Take the rest of the YAML stream just in case you need it!
        if (isset($parts[1])) {
            // We use tab character as array key placeholder because based on the specification,
            // this character should not be written in a YAML document, so it will be impossible
            // that, there will be a YAML key denoted by a human using a tab character.
            //
            // <https://yaml.org/spec/1.2/spec.html#id2777534>
            $docs[$content] = trim($parts[1], "\n");
        }
        return $docs;
    };
    return $docs ? $yaml_docs($value, $dent, $eval, true === $docs ? "\t" : $docs) : $yaml($value, $dent, $eval);
});

To::_('YAML', $plug = function(array $value, string $dent = '  ', $docs = false) {
    /*
    if (extension_loaded('yaml')) {}
    */
    $yaml = static function(array $data, string $dent = '  ') use(&$yaml) {
        $out = [];
        $yaml_list = static function(array $data) use(&$dent, &$yaml) {
            $out = [];
            foreach ($data as $v) {
                if (is_array($v)) {
                    if (!empty($v)) {
                        $out[] = '- ' . strtr($yaml($v, $dent), ["\n" => "\n  "]);
                    } else {
                        $out[] = '- []'; // Empty array
                    }
                } else {
                    $out[] = '- ' . s($v, ['null' => '~']);
                }
            }
            return implode("\n", $out);
        };
        $yaml_set = static function(string $k, string $m, $v) {
            // Check for safe key pattern, otherwise, wrap it with quote
            if ("" !== $k && (is_numeric($k) || (ctype_alnum($k) && !is_numeric($k[0])) || preg_match('/^[a-z][a-z\d]*(?:[_-]+[a-z\d]+)*$/i', $k))) {
            } else {
                $k = "'" . strtr($k, ["'" => "\\\'"]) . "'";
            }
            return $k . $m . s($v, ['null' => '~']);
        };
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                // Sequence array?
                if (array_keys($v) === range(0, count($v) - 1)) {
                    $out[] = $yaml_set($k, ":\n", $yaml_list($v));
                } else {
                    $out[] = $yaml_set($k, ":\n", $dent . strtr($yaml($v, $dent), ["\n" => "\n" . $dent]));
                }
            } else {
                if (is_string($v)) {
                    if (false !== strpos($v, "\n")) {
                        $v = "|\n" . $dent . strtr($v, [
                            "\n" => "\n" . $dent,
                            "\n" . $dent . "\n" => "\n\n"
                        ]);
                    } else if (strlen($v) > 120) {
                        $v = ">\n" . $dent . wordwrap($v, 120, "\n" . $dent);
                    } else if (is_numeric($v) || $v !== strtr($v, "!#%&*,-:<=>?@[\\]{|}", '-------------------')) {
                        $v = "'" . $v . "'";
                    }
                }
                $out[] = $yaml_set($k, ': ', $v, $dent);
            }
        }
        return implode("\n", $out);
    };
    $yaml_docs = static function(array $data, string $dent = '  ', $content = "\t") use(&$yaml) {
        $out = $c = "";
        if (array_key_exists($content, $data)) {
            $c = $data[$content] ?? "";
            unset($data[$content]);
        }
        for ($i = 0, $count = count($data); $i < $count; ++$i) {
            $v = $yaml($data[$i], $dent);
            $out .= (0 === $i ? YAML\SOH : YAML\ETB) . "\n" . ("" !== $v ? $v . "\n" : "");
        }
        $c = trim($c, "\n");
        return $out . YAML\EOT . ($c ? "\n\n" . $c : "");
    };
    return $docs ? $yaml_docs($value, $dent, true === $docs ? "\t" : $docs) : $yaml($value, $dent);
});

// Alias
From::_('yaml', $plug);
To::_('yaml', $plug);