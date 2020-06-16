<?php

To::_('YAML', $fn = function(array $in, string $dent = '  ', $docs = false) {
    /*
    if (extension_loaded('yaml')) {}
    */
    $yaml = function(array $data, string $dent = '  ') use(&$yaml) {
        $out = [];
        $yaml_list = function(array $data) use(&$dent, &$yaml) {
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
        $yaml_set = function(string $k, string $m, $v) {
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
    $yaml_docs = function(array $data, string $dent = '  ', $content = "\t") use(&$yaml) {
        $out = $c = "";
        if (array_key_exists($content, $data)) {
            $c = $data[$content];
            unset($data[$content]);
        }
        for ($i = 0, $count = count($data); $i < $count; ++$i) {
            $v = $yaml($data[$i], $dent);
            $out .= "---\n" . ("" !== $v ? $v . "\n" : "");
        }
        $c = trim($c, "\n");
        return $out . '...' . ($c ? "\n\n" . $c : "");
    };
    return $docs ? $yaml_docs($in, $dent, true === $docs ? "\t" : $docs) : $yaml($in, $dent);
});

// Alias
To::_('yaml', $fn);
