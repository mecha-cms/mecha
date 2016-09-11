<?php


// Helper function(s) ...

$SS = '"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'';
$CC = '\/\*[\s\S]*?\*\/';
$CH = '<\!--[\s\S]*?-->';

function _do_detract_x($input) {
    return str_replace(array("\n", "\t", ' '), array(X . '\n', X . '\t', X . '\s'), $input);
}

function _do_detract_v($input) {
    return str_replace(array(X . '\n', X . '\t', X . '\s'), array("\n", "\t", ' '), $input);
}


/**
 * ====================================================================
 *  CONVERT ARRAY DATA INTO NESTED STRING
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    echo Converter::toText(array(
 *        'key_1' => 'Value 1',
 *        'key_2' => 'Value 2',
 *        'key_3' => 'Value 3'
 *    ));
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $input    | array  | The array of data to be converted
 *  $s        | string | Separator between array key and array value
 *  $indent   | string | Indentation as nested array data level
 *  $n        | string | Separator between data, default is `\n`
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Converter::plug('toText', function($input, $s = S, $indent = '    ', $n = "\n", $depth = 0) {
    if(is_array($input) || is_object($input)) {
        $text = "";
        foreach($input as $k => $v) {
            if( ! is_array($v) && ! is_object($v)) {
                $v = Converter::str($v);
                $v = $v !== $n && strpos($v, $n) !== false ? json_encode($v) : $v;
                $T = str_repeat($indent, $depth);
                // Line
                if($v === $n) {
                    $text .= $n;
                // Comment
                } else if(strpos($v, '#') === 0) {
                    $text .= $T . trim($v) . $n;
                // ...
                } else {
                    $text .= $T . trim($k) . $s . ' ' . $v . $n;
                }
            } else {
                $ss = Converter::toText($v, $s, $indent, $n, $depth + 1);
                $text .= $T . $k . $s . $n . $ss . $n;
            }
        }
        return str_replace($s . ' ' . $n, $s . ' ""' . $n, substr($text, 0, -strlen($n)));
    }
    return $input !== $n && strpos($input, $n) !== false ? json_encode($input) : $input;
});


/**
 * ====================================================================
 *  CONVERT NESTED STRING INTO ASSOCIATIVE ARRAY
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    echo Converter::toArray('key_1: Value 1
 *    key_2: Value 2
 *    key_3: Value 3');
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $input    | string | The string of data to be converted
 *  $s        | string | Separator between data key and data value
 *  $indent   | string | Indentation of nested string data level
 *  $n        | string | Separator between data, default is `\n`
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Converter::plug('toArray', function($input, $s = S, $indent = '    ', $n = "\n") use($SS) {
    if( ! is_string($input)) return Mecha::A($input);
    if(trim($input) === "") return array();
    $i = 0;
    $results = $data = array();
    // Normalize line-break
    $text = Converter::RN($input, $n);
    // Save `\:` as `\x1A`
    $text = str_replace('\\' . $s, X, $text);
    if(strpos($indent, "\t") === false) {
        // Force translate 1 tab to 4 space
        $text = Converter::TS($text);
    }
    $indent_length = strlen($indent);
    foreach(explode($n, $text) as $line) {
        $depth = 0;
        $line = rtrim($line);
        // Ignore comment and empty line-break
        if($line === "" || strpos($line, '#') === 0) continue;
        while(substr($line, 0, $indent_length) === $indent) {
            $depth += 1;
            $line = substr($line, $indent_length);
        }
        $line = ltrim($line);
        while($depth < count($data)) {
            array_pop($data);
        }
        // No `:` ... fix it!
        if(strpos($line, $s) === false) {
            $line = $line . $s . $line;
        // Start with `:`
        } else if($line[0] === $s) {
            $line = $i . $line;
            $i++;
        // else ...
        } else {
            $i = 0;
        }
        $part = explode($s, $line, 2);
        $v = trim($part[1]);
        // Remove inline comment(s) ...
        if($v && strpos($v, '#') !== false) {
            if($v[0] === '"' || $v[0] === "'") {
                $vv = '#(' . $SS . ')|\s*\#.*#';
                $v = preg_replace($vv, '$1', $v);
            } else {
                $v = explode('#', $v, 2);
                $v = trim($v[0]);
            }
        }
        // Restore `\x1A` as `:`
        $data[$depth] = str_replace(X, $s, trim($part[0]));
        $parent =& $results;
        foreach($data as $k) {
            if( ! isset($parent[$k])) {
                $parent[$k] = Converter::strEval($v ? $v : array());
                break;
            }
            $parent =& $parent[$k];
        }
    }
    return $results;
});


/**
 * ====================================================================
 *  HTML MINIFIER
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    Converter::detractSkeleton(file_get_contents('test.html'));
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $input    | string | The HTML string to be compressed
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

function _do_detract_skeleton_a($input) {
    return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#', function($m) {
        if(isset($m[2])) {
            // Minify inline CSS declaration(s)
            if(stripos($m[2], ' style=') !== false) {
                $m[2] = preg_replace_callback('#( style=)([\'"]?)(.*?)\2#i', function($m) {
                    return $m[1] . $m[2] . _do_detract_shell($m[3]) . $m[2];
                }, $m[2]);
            }
            return '<' . $m[1] . preg_replace(
                array(
                    // From `defer="defer"`, `defer='defer'`, `defer="true"`, `defer='true'`, `defer=""` and `defer=''` to `defer` [^1]
                    '#\s(checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped)(?:=([\'"]?)(?:true|\1)?\2)#i',
                    // Remove extra white-space(s) between HTML attribute(s) [^2]
                    '#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
                    // From `<img />` to `<img/>` [^3]
                    '#\s+\/$#'
                ),
                array(
                    // [^1]
                    ' $1',
                    // [^2]
                    ' $1$2',
                    // [^3]
                    '/'
                ),
            str_replace("\n", ' ', $m[2])) . '>';
        }
        return '<' . $m[1] . '>';
    }, $input);
}

function _do_detract_skeleton($input) {
    if( ! $input = trim($input)) return $input;
    global $CH;
    // Keep important white-space(s) after self-closing HTML tag(s)
    $input = preg_replace('#(<(?:img|input)(?:\s[^<>]*?)?\s*\/?>)\s+#i', '$1' . X . '\s', $input);
    // Create chunk(s) of HTML tag(s), ignored HTML group(s), HTML comment(s) and text
    $input = preg_split('#(' . $CH . '|<pre(?:>|\s[^<>]*?>)[\s\S]*?<\/pre>|<code(?:>|\s[^<>]*?>)[\s\S]*?<\/code>|<script(?:>|\s[^<>]*?>)[\s\S]*?<\/script>|<style(?:>|\s[^<>]*?>)[\s\S]*?<\/style>|<textarea(?:>|\s[^<>]*?>)[\s\S]*?<\/textarea>|<[^<>]+?>)#i', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $output = "";
    foreach($input as $v) {
        if($v !== ' ' && trim($v) === "") continue;
        if($v[0] === '<' && substr($v, -1) === '>') {
            if($v[1] === '!' && strpos($v, '<!--') === 0) { // HTML comment ...
                // Remove if not detected as IE comment(s) ...
                if(substr($v, -12) !== '<![endif]-->') continue;
                $output .= $v;
            } else {
                $output .= _do_detract_x(_do_detract_skeleton_a($v));
            }
        } else {
            // Force line-break with `&#10;` or `&#xa;`
            $v = str_replace(array('&#10;', '&#xA;', '&#xa;'), X . '\n', $v);
            // Force white-space with `&#32;` or `&#x20;`
            $v = str_replace(array('&#32;', '&#x20;'), X . '\s', $v);
            // Replace multiple white-space(s) with a space
            $output .= preg_replace('#\s+#', ' ', $v);
        }
    }
    // Clean up ...
    $output = preg_replace(
        array(
            // Remove two or more white-space(s) between tag [^1]
            '#>([\n\r\t]\s*|\s{2,})<#',
            // Remove white-space(s) before tag-close [^2]
            '#\s+(<\/[^\s]+?>)#'
        ),
        array(
            // [^1]
            '><',
            // [^2]
            '$1'
        ),
    $output);
    $output = _do_detract_v($output);
    // Remove white-space(s) after ignored tag-open and before ignored tag-close (except `<textarea>`)
    return preg_replace('#<(code|pre|script|style)(>|\s[^<>]*?>)\s*([\s\S]*?)\s*<\/\1>#i', '<$1$2$3</$1>', $output);
}

Converter::plug('detractSkeleton', '_do_detract_skeleton');


/**
 * ====================================================================
 *  CSS MINIFIER
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    Converter::detractShell(file_get_contents('test.css'));
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $input    | string | The CSS string to be compressed
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

function _do_detract_shell_a($input) {
    // Keep important white-space(s) in `calc()`
    if(stripos($input, 'calc(') !== false) {
        $input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', function($m) {
            return $m[1] . preg_replace('#\s+#', X . '\s', $m[2]) . ')';
        }, $input);
    }
    // Minify ...
    return preg_replace(
        array(
            // Fix case for `#foo [bar="baz"]` and `#foo :first-child` [^1]
            '#(?<![,\{\}])\s+(\[|:\w)#',
            // Fix case for `[bar="baz"] .foo` and `@media (foo: bar) and (baz: qux)` [^2]
            '#\]\s+#', '#\b\s+\(#', '#\)\s+\b#',
            // Minify HEX color code ... [^3]
            '#\#([\da-f])\1([\da-f])\2([\da-f])\3\b#i',
            // Remove white-space(s) around punctuation(s) [^4]
            '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
            // Replace zero unit(s) with `0` [^5]
            '#\b(?:0\.)?0([a-z]+\b|%)#i',
            // Replace `0.6` with `.6` [^6]
            '#\b0+\.(\d+)#',
            // Replace `:0 0`, `:0 0 0` and `:0 0 0 0` with `:0` [^7]
            '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
            // Replace `background(?:-position)?:(0|none)` with `background$1:0 0` [^8]
            '#\b(background(?:-position)?):(0|none)\b#i',
            // Replace `(border(?:-radius)?|outline):none` with `$1:0` [^9]
            '#\b(border(?:-radius)?|outline):none\b#i',
            // Remove empty selector(s) [^10]
            '#(^|[\{\}])(?:[^\{\}]+)\{\}#',
            // Remove the last semi-colon and replace multiple semi-colon(s) with a semi-colon [^11]
            '#;+([;\}])#',
            // Replace multiple white-space(s) with a space [^12]
            '#\s+#'
        ),
        array(
            // [^1]
            X . '\s$1',
            // [^2]
            ']' . X . '\s', X . '\s(', ')' . X . '\s',
            // [^3]
            '#$1$2$3',
            // [^4]
            '$1',
            // [^5]
            '0',
            // [^6]
            '.$1',
            // [^7]
            ':0',
            // [^8]
            '$1:0 0',
            // [^9]
            '$1:0',
            // [^10]
            '$1',
            // [^11]
            '$1',
            // [^12]
            ' '
        ),
    $input);
}

function _do_detract_shell($input) {
    if( ! $input = trim($input)) return $input;
    global $SS, $CC;
    // Keep important white-space(s) between comment(s)
    $input = preg_replace('#(' . $CC . ')\s+(' . $CC . ')#', '$1' . X . '\s$2', $input);
    // Create chunk(s) of string(s), comment(s) and text
    $input = preg_split('#(' . $SS . '|' . $CC . ')#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $output = "";
    foreach($input as $v) {
        if(trim($v) === "") continue;
        if(
            ($v[0] === '"' && substr($v, -1) === '"') ||
            ($v[0] === "'" && substr($v, -1) === "'") ||
            (strpos($v, '/*') === 0 && substr($v, -2) === '*/')
        ) {
            // Remove if not detected as important comment ...
            if($v[0] === '/' && strpos($v, '/*!') !== 0) continue;
            $output .= $v; // String or comment ...
        } else {
            $output .= _do_detract_shell_a($v);
        }
    }
    // Remove quote(s) where possible ...
    $output = preg_replace(
        array(
            '#(' . $CC . ')|(?<!\bcontent\:|[\s\(])([\'"])([a-z_][-\w]*?)\2#i',
            '#(' . $CC . ')|\b(url\()([\'"])([^\s]+?)\3(\))#i'
        ),
        array(
            '$1$3',
            '$1$2$4$5'
        ),
    $output);
    return _do_detract_v($output);
}

Converter::plug('detractShell', '_do_detract_shell');


/**
 * ====================================================================
 *  JAVASCRIPT MINIFIER
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    Converter::detractSword(file_get_contents('test.js'));
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $input    | string | The JavaScript string to be compressed
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

function _do_detract_sword_a($input) {
    return preg_replace(
        array(
            // Remove inline comment(s) [^1]
            '#\s*\/\/.*$#m',
            // Remove white-space(s) around punctuation(s) [^2]
            '#\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#',
            // Remove the last semi-colon and comma [^3]
            '#[;,]([\]\}])#',
            // Replace `true` with `!0` and `false` with `!1` [^4]
            '#\btrue\b#', '#\bfalse\b#', '#\breturn\s+#',
            // Replace `new Array(x)` with `[x]` ... [^5]
            '#\b(?:new\s+)?Array\((.*?)\)#', '#\b(?:new\s+)?Object\((.*?)\)#'
        ),
        array(
            // [^1]
            "",
            // [^2]
            '$1',
            // [^3]
            '$1',
            // [^4]
            '!0', '!1', 'return ',
            // [^5]
            '[$1]', '{$1}'
        ),
    $input);
}

function _do_detract_sword($input) {
    if( ! $input = trim($input)) return $input;
    // Create chunk(s) of string(s), comment(s), regex(es) and text
    global $SS, $CC;
    $input = preg_split('#(' . $SS . '|' . $CC . '|\/[^\n]+?\/(?=[.,;]|[gimuy]|$))#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $output = "";
    foreach($input as $v) {
        if(trim($v) === "") continue;
        if(
            ($v[0] === '"' && substr($v, -1) === '"') ||
            ($v[0] === "'" && substr($v, -1) === "'") ||
            ($v[0] === '/' && substr($v, -1) === '/')
        ) {
            // Remove if not detected as important comment ...
            if(strpos($v, '//') === 0 || (strpos($v, '/*') === 0 && strpos($v, '/*!') !== 0 && strpos($v, '/*@cc_on') !== 0)) continue;
            $output .= $v; // String, comment or regex ...
        } else {
            $output .= _do_detract_sword_a($v);
        }
    }
    return preg_replace(
        array(
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}` [^1]
            '#(' . $CC . ')|([\{,])([\'])(\d+|[a-z_]\w*)\3(?=:)#i',
            // From `foo['bar']` to `foo.bar` [^2]
            '#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i'
        ),
        array(
            // [^1]
            '$1$2$4',
            // [^2]
            '$1.$3'
        ),
    $output);
}

Converter::plug('detractSword', '_do_detract_sword');