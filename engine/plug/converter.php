<?php


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

Converter::plug('toArray', function($input, $s = S, $indent = '    ', $n = "\n") {
    if( ! is_string($input)) return Mecha::A($input);
    if(trim($input) === "") return array();
    $i = 0;
    $results = $data = array();
    // Normalize line-break
    $text = Converter::RN($input, $n);
    // Save `\:` as `\x1A`
    $text = str_replace('\\' . $s, "\x1A", $text);
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
            if(strpos($v, '"') === 0 || strpos($v, "'") === 0) {
                $vv = '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\s*\#.*#';
                $v = preg_replace($vv, '$1', $v);
            } else {
                $v = explode('#', $v, 2);
                $v = trim($v[0]);
            }
        }
        // Restore `\x1A` as `:`
        $data[$depth] = str_replace("\x1A", $s, trim($part[0]));
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

Converter::plug('detractSkeleton', function($input) {
    if(trim($input) === "") return $input;
    // Remove extra white-space(s) between HTML attribute(s)
    $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
        return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
    }, Converter::RN($input));
    // Minify inline CSS declaration(s)
    if(strpos($input, ' style=') !== false) {
        $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
            return '<' . $matches[1] . ' style=' . $matches[2] . Converter::detractShell($matches[3]) . $matches[2];
        }, $input);
    }
    return preg_replace(
        array(
            // t = text
            // o = tag open
            // c = tag close
            // Keep important white-space(s) after self-closing HTML tag(s)
            '#<(img|input)(>| .*?>)#s',
            // Remove a line break and two or more white-space(s) between tag(s)
            '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
            '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
            '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
            '#<(img|input)(>| .*?>)<\/\1\x1A>#s', // reset previous fix
            '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
            // Force line-break with `&#10;` or `&#xa;`
            '#&\#(?:10|xa);#i',
            // Force white-space with `&#32;` or `&#x20;`
            '#&\#(?:32|x20);#i',
            // Remove HTML comment(s) except IE comment(s)
            '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
        ),
        array(
            "<$1$2</$1\x1A>",
            '$1$2$3',
            '$1$2$3',
            '$1$2$3$4$5',
            '$1$2$3$4$5$6$7',
            '$1$2$3',
            '<$1$2',
            '$1 ',
            "\n",
            ' ',
            ""
        ),
    $input);
});


/**
 * ====================================================================
 *  CSS MINIFIER
 * ====================================================================
 *
 *  => http://ideone.com/Q5USEF + improvement(s)
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

Converter::plug('detractShell', function($input) {
    if(trim($input) === "") return $input;
    // Force white-space(s) in `calc()`
    if(strpos($input, 'calc(') !== false) {
        $input = preg_replace_callback('#(?<=[\s:])calc\(\s*(.*?)\s*\)#', function($matches) {
            return 'calc(' . preg_replace('#\s+#', "\x1A", $matches[1]) . ')';
        }, $input);
    }
    return preg_replace(
        array(
            // Remove comment(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
            // Remove unused white-space(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
            // Replace `:0 0 0 0` with `:0`
            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
            // Replace `background-position:0` with `background-position:0 0`
            '#(background-position):0(?=[;\}])#si',
            // Replace `0.6` with `.6`, but only when preceded by a white-space or `=`, `:`, `,`, `(`, `-`
            '#(?<=[\s=:,\(\-]|&\#32;)0+\.(\d+)#s',
            // Minify string value
            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][-\w]*?)\2(?=[\s\{\}\];,])#si',
            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
            // Minify HEX color code
            '#(?<=[\s=:,\(]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
            // Replace `(border|outline):none` with `(border|outline):0`
            '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
            // Remove empty selector(s)
            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
            '#\x1A#'
        ),
        array(
            '$1',
            '$1$2$3$4$5$6$7',
            '$1',
            ':0',
            '$1:0 0',
            '.$1',
            '$1$3',
            '$1$2$4$5',
            '$1$2$3',
            '$1:0',
            '$1$2',
            ' '
        ),
    $input);
});


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

Converter::plug('detractSword', function($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
            // Remove white-space(s) outside the string and regex
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
            // Remove the last semicolon
            '#;+\}#',
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
            '#([\{,])([\'])(\d+|[a-z_]\w*)\2(?=\:)#i',
            // --ibid. From `foo['bar']` to `foo.bar`
            '#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i',
            // Replace `true` with `!0`
            '#(?<=return |[=:,\(\[])true\b#',
            // Replace `false` with `!1`
            '#(?<=return |[=:,\(\[])false\b#',
            // Clean up ...
            '#\s*(\/\*|\*\/)\s*#'
        ),
        array(
            '$1',
            '$1$2',
            '}',
            '$1$3',
            '$1.$3',
            '!0',
            '!1',
            '$1'
        ),
    $input);
});