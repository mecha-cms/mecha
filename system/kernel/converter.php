<?php

class Converter extends Base {

    /**
     * ====================================================================
     *  CONVERT RELATIVE URL TO FULL URL
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    echo Converter::url('foo/bar');
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $input    | string | The URL path to be converted
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function url($input) {
        if(
            strpos($input, '://') === false &&
            strpos($input, '?') !== 0 &&
            strpos($input, '&') !== 0 &&
            strpos($input, '#') !== 0 &&
            strpos($input, 'javascript:') !== 0
        ) {
            return str_replace(
                array(
                    '\\',
                    '/?',
                    '/&',
                    '/#'
                ),
                array(
                    '/',
                    '?',
                    '&',
                    '#'
                ),
            trim(Config::get('url') . '/' . ltrim($input, '/'), '/'));
        }
        return $input;
    }

    /**
     * ====================================================================
     *  CONVERT RGB/RGBA COLOR STRING INTO RGBA DATA
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Converter::RGB('rgb(255, 255, 255)'));
     *
     * --------------------------------------------------------------------
     *
     *    var_dump(Converter::RGB('rgba(255, 255, 255, .5)'));
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $rgba     | string | The RGB or RGBA color string
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function RGB($rgba, $output = null) {
        if(is_string($rgba) && preg_match('#^rgba?\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})(\s*,\s*([0-9\.]+))?\s*\)$#i', $rgba, $matches)) {
            $colors = array(
                'r' => (int) $matches[1],
                'g' => (int) $matches[2],
                'b' => (int) $matches[3],
                'a' => (float) (isset($matches[5]) ? $matches[5] : 1)
            );
            return ! is_null($output) ? $colors[$output] : $colors;
        }
        return false;
    }

    /**
     * ====================================================================
     *  CONVERT HEX COLOR STRING INTO RGBA DATA
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Converter::HEX2RGB('#000'));
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $hex      | string | The HEX color string
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function HEX2RGB($hex, $output = null) {
        if(is_string($hex) && preg_match('#\#?([a-f0-9]{3,6})#i', $hex, $matches)) {
            $color = $matches[1];
            if(strlen($color) !== 3 && strlen($color) !== 6){
                return false;
            }
            if(strlen($color) === 3) {
                $color = preg_replace('#(.)#', '$1$1', $color);
            }
            $colors = array(
                'r' => (int) hexdec(substr($color, 0, 2)),
                'g' => (int) hexdec(substr($color, 2, 2)),
                'b' => (int) hexdec(substr($color, 4, 2)),
                'a' => (float) 1
            );
            return ! is_null($output) ? $colors[$output] : $colors;
        }
        return false;
    }

    /**
     * ====================================================================
     *  CONVERT RGB/RGBA COLOR STRING/ARRAY INTO HEX COLOR
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    echo Converter::RGB2HEX(255, 255, 255);
     *
     * --------------------------------------------------------------------
     *
     *    echo Converter::RGB2HEX(array(255, 255, 255));
     *
     * --------------------------------------------------------------------
     *
     *    echo Converter::RGB2HEX('rgb(255, 255, 255)');
     *
     * --------------------------------------------------------------------
     *
     *    echo Converter::RGB2HEX('rgba(255, 255, 255, .5)');
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter      | Type  | Description
     *  -------------- | ----- | ------------------------------------------
     *  $r, $g, $b, $a | mixed | The RGB or RGBA color string or array
     *  -------------- | ----- | ------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function RGB2HEX($r = null, $g = null, $b = null, $a = 1) {
        $hex = '#';
        if(is_numeric($g)) {
            $hex .= str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
            $hex .= str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
            $hex .= str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
            return $hex;
        } else {
            if(is_array($r)) {
                list($r, $g, $b) = array_values($r);
                $hex .= str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
                $hex .= str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
                $hex .= str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
                return $hex;
            } else {
                $r = (string) $r;
                if($rgba = self::RGB($r)) {
                    $hex .= str_pad(dechex($rgba['r']), 2, '0', STR_PAD_LEFT);
                    $hex .= str_pad(dechex($rgba['g']), 2, '0', STR_PAD_LEFT);
                    $hex .= str_pad(dechex($rgba['b']), 2, '0', STR_PAD_LEFT);
                    return $hex;
                }
            }
        }
        return false;
    }

    /**
     * ==========================================================================
     *  CREATE A SUMMARY FROM LONG TEXT
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $summary = Converter::curt('Very very long text...');
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------------
     *  $input    | string  | The source text
     *  $chars    | integer | The maximum length of summary
     *  $tail     | string  | Character that follows at the end of the summary
     *  --------- | ------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function curt($input, $chars = 100, $tail = '&hellip;', $charset = "") {
        $input = preg_replace(
            array(
                '#<br *\/?>|<\/.*?>#i', // New line to a single white-space
                '#<.*?>#', // Remove all HTML tag(s)
                '#[`~\#*-=+_]{2,}#', // Remove all possible raw Markdown( Extra)? pattern(s)
                '#\b([`~*_]+)(.*?)\1\b#', // --ibid
                '#<|>#', // Fix all broken HTML tag(s). Replace `<div></di` to `div/di`
                '#(\s|&nbsp;)+#', // Multiple white-space(s) to a single white-space
                '# (?=[!:;,.\/?])#' // Remove leading white-space(s) before `!:;,./?`
            ),
            array(
                ' ',
                "",
                "",
                '$2',
                "",
                ' ',
                ""
            ),
        $input);
        $charset = $charset !== "" ? $charset : Config::get('charset');
        return trim((function_exists('mb_substr') ? mb_substr($input, 0, $chars, $charset) : substr($input, 0, $chars))) . ($chars < (function_exists('mb_strlen') ? mb_strlen($input, $charset) : strlen($input)) ? $tail : "");
    }

    /**
     * ====================================================================
     *  CONVERT PHP VALUE INTO STRING
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Converter::str(1234));
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $input    | mixed | The value or array of value to be converted
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function str($input) {
        $results = $input;
        if( ! is_array($input) && ! is_object($input)) {
            if($input === TRUE) $results = 'TRUE';
            if($input === FALSE) $results = 'FALSE';
            if($input === NULL) $results = 'NULL';
            if($input === true) $results = 'true';
            if($input === false) $results = 'false';
            if($input === null) $results = 'null';
            return (string) $results;
        } else {
            $results = array();
            foreach($input as $key => $value) {
                $results[$key] = self::str($value);
            }
        }
        return $results;
    }

    /**
     * ====================================================================
     *  CONVERT STRING OF VALUE INTO THEIR APPROPRIATE TYPE/FORMAT
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Converter::strEval('1234'));
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------
     *  $input    | mixed   | String or array of string to be converted
     *  $NRT      | boolean | Translate `\n`, `\r` and `\t` ?
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function strEval($input, $NRT = true) {
        $results = $input;
        if(is_string($input) && preg_match('#^(TRUE|FALSE|NULL|true|false|null|yes|no|on|off|ok|okay)$#', $input, $matches)) {
            $results = Mecha::alter($matches[1], array(
                'TRUE' => TRUE,
                'FALSE' => FALSE,
                'NULL' => NULL,
                'true' => true,
                'false' => false,
                'null' => null,
                'yes' => true,
                'no' => false,
                'on' => true,
                'off' => false,
                'ok' => true,
                'okay' => true
            ));
        } else if(is_string($input)) {
            if( ! is_null(json_decode($input, true))) {
                $results = self::strEval(json_decode($input, true), $NRT);
            } else {
                $results = $NRT ? self::NRT_decode($input) : $input;
            }
        } else if(is_numeric($input)) {
            $results = strpos($input, '.') !== false ? (float) $input : (int) $input;
        } else if(is_array($input) || is_object($input)) {
            $results = array();
            foreach($input as $key => $value) {
                $results[$key] = self::strEval($value, $NRT);
            }
        }
        return $results;
    }

    /**
     * ====================================================================
     *  CONVERT STRING OF VALUE INTO EXECUTABLE PHP CODE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Converter::phpEval('echo 1 + 1;'));
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $input    | string | The PHP string to be converted
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function phpEval($input) {
        ob_start();
        extract(Shield::cargo()); // include default variables ...
        eval($input);
        return ob_get_clean();
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
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function toText($array, $s = S, $indent = '    ', $depth = 0, $NRT = true) {
        $results = "";
        if( ! is_array($array) && ! is_object($array)) {
            return self::str($NRT ? self::NRT_encode($array) : $array);
        }
        foreach($array as $key => $value) {
            if( ! is_array($value) && ! is_object($value)) {
                $value = self::str($NRT ? self::NRT_encode($value) : $value);
                if(is_string($key) && strpos($key, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($key) . "\n";
                } else if(is_int($key) && strpos($value, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($value) . "\n";
                } else {
                    $results .= str_repeat($indent, $depth) . trim(str_replace($s, '_', $key)) . $s . ' ' . self::str($value) . "\n";
                }
            } else {
                $results .= str_repeat($indent, $depth) . (string) $key . $s . "\n" . self::toText($value, $s, $indent, $depth + 1, $NRT) . "\n";
            }
        }
        return rtrim($results);
    }

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
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function toArray($input, $s = S, $indent = '    ') {
        return Text::toArray($input, $s, $indent);
    }

    /**
     * ====================================================================
     *  CONVERT ATTRIBUTE(S) OF ELEMENT INTO ARRAY OF DATA
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Converter::attr('<div id="foo">'));
     *
     * --------------------------------------------------------------------
     *
     *    var_dump(Converter::attr('<div id="foo">test content</div>'));
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------
     *  $input    | string  | The string of element to be converted
     *  $element  | array   | Tag open, tag close, tag separator
     *  $attr     | array   | Value open, value close, attribute separator
     *  $str_eval | boolean | Convert value with `Converter::strEval()` ?
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function attr($input, $element = array(), $attr = array(), $str_eval = true) {
        $ED = array('<', '>', ' ', '/', '[a-zA-Z0-9\-._:]+');
        $AD = array('"', '"', '=', '[a-zA-Z0-9\-._:]+');
        $E = Mecha::extend($ED, $element);
        $A = Mecha::extend($AD, $attr);
        $E0 = preg_quote($E[0], '#');
        $E1 = preg_quote($E[1], '#');
        $E2 = preg_quote($E[2], '#');
        $E3 = preg_quote($E[3], '#');
        $A0 = preg_quote($A[0], '#');
        $A1 = preg_quote($A[1], '#');
        $A2 = preg_quote($A[2], '#');
        if( ! preg_match('#^\s*(' . $E0 . ')(' . $E[4] . ')((' . $E2 . ')+(.*?))?((' . $E1 . ')([\s\S]*?)((' . $E0 . ')' . $E3 . '\2(' . $E1 . '))|(' . $E2 . ')*' . $E3 . '?(' . $E1 . '))\s*$#', $input, $M)) return false;
        $M[5] = preg_replace('#(^|(' . $E2 . ')+)(' . $A[3] . ')(' . $A2 . ')(' . $A0 . ')(' . $A1 . ')#', '$1$2$3$4$5<attr:value>$6', $M[5]);
        $results = array(
            'element' => $M[2],
            'attributes' => null,
            'content' => isset($M[8]) && $M[9] === $E[0] . $E[3] . $M[2] . $E[1] ? $M[8] : null
        );
        if(preg_match_all('#(' . $A[3] . ')((' . $A2 . ')(' . $A0 . ')(.*?)(' . $A1 . '))?(?:(' . $E2 . ')|$)#', $M[5], $A)) {
            $results['attributes'] = array();
            foreach($A[1] as $k => $v) {
                $results['attributes'][$v] = isset($A[5][$k]) && ! empty($A[5][$k]) ? (strpos($A[5][$k], '<attr:value>') === false ? $A[5][$k] : str_replace('<attr:value>', "", $A[5][$k])) : $v;
            }
        }
        return $str_eval ? self::strEval($results) : $results;
    }

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

    public static function detractSkeleton($input) {
        if(trim($input) === "") return $input;
        // Remove extra white-space(s) between HTML attribute(s)
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, $input);
        // Minify inline CSS declaration(s)
        if(strpos($input, ' style=') !== false) {
            $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
                return '<' . $matches[1] . ' style=' . $matches[2] . Converter::detractShell($matches[3]) . $matches[2];
            }, $input);
        }
        return preg_replace(
            array(

                // Remove HTML comment(s) except IE comment(s)
                '#\s*(<\!--(?=\[if\s).*?-->)\s*|\s*<\!--.*?-->\s*#s',

                // Do not remove white-space after image and
                // input tag that is followed by a tag open
                '#(<(?:img|input)(?:\/?>|\s[^<>]*?\/?>))\s+(?=\<[^\/])#s',

                // Remove a line break or two or more white-space(s) between tag(s)
                '#(<\!--.*?-->)|(>)(?:[\n\r]|\s{2,})|(?:[\n\r]|\s{2,})(<)|(>)(?:[\n\r]|\s{2,})(<)#s',

                // PROOFING ...
                // o: tag open, c: tag close, t: text
                // If `<tag> </tag>` remove white-space
                // If `</tag> <tag>` keep white-space
                // If `<tag> <tag>` remove white-space
                // If `</tag> </tag>` remove white-space
                // If `<tag>    ...</tag>` remove white-space(s)
                // If `</tag>    ...<tag>` remove white-space(s)
                // If `<tag>    ...<tag>` remove white-space(s)
                // If `</tag>    ...</tag>` remove white-space(s)
                // If `abc <tag>` keep white-space
                // If `<tag> abc` remove white-space
                // If `abc </tag>` remove white-space
                // If `</tag> abc` keep white-space
                // TODO: If `abc    ...<tag>` keep one white-space
                // If `<tag>    ...abc` remove white-space(s)
                // If `abc    ...</tag>` remove white-space(s)
                // TODO: If `</tag>    ...abc` keep one white-space
                '#(<\!--.*?-->)|(<(?:img|input)(?:\/?>|\s[^<>]*?\/?>))\s+(?!\<\/)#s', // o+t | o+o
                '#(<\!--.*?-->)|(<[^\/\s<>]+(?:>|\s[^<>]*?>))\s+(?=\<[^\/])#s', // o+o
                '#(<\!--.*?-->)|(<\/[^\/\s<>]+?>)\s+(?=\<\/)#s', // c+c
                '#(<\!--.*?-->)|(<([^\/\s<>]+)(?:>|\s[^<>]*?>))\s+(<\/\3>)#s', // o+c
                '#(<\!--.*?-->)|(<[^\/\s<>]+(?:>|\s[^<>]*?>))\s+(?!\<)#s', // o+t
                '#(<\!--.*?-->)|(?!\>)\s+(<\/[^\/\s<>]+?>)#s', // t+c
                '#(<\!--.*?-->)|(?!\>)\s+(?=\<[^\/])#s', // t+o
                '#(<\!--.*?-->)|(<\/[^\/\s<>]+?>)\s+(?!\<)#s', // c+t
                '#(<\!--.*?-->)|(\/>)\s+(?!\<)#', // o+t

                // Replace `&nbsp;&nbsp;&nbsp;` with `&nbsp; &nbsp;`
                '#(?<=&nbsp;)(&nbsp;){2}#',

                // PROOFING ...
                '#(?<=\>)&nbsp;(?!\s|&nbsp;|\<\/)#',
                '#(?<=--\>)(?:\s|&nbsp;)+(?=\<)#'

            ),
            array(
                '$1',
                '$1&nbsp;',
                '$1$2$3$4$5',
                '$1$2&nbsp;', // o+t | o+o
                '$1$2', // o+o
                '$1$2', //c+c
                '$1$2$4', // o+c
                '$1$2', // o+t
                '$1$2', // t+c
                '$1$2 ', // t+o
                '$1$2 ', // c+t
                '$1$2 ', // o+t
                ' $1',
                ' ',
                ""
            ),
        trim($input));
    }

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

    public static function detractShell($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(

                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)#s',

                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',

                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',

                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',

                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',

                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',

                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',

                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',

                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',

                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'

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
                '$1$2'
            ),
        trim($input));
    }

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

    public static function detractSword($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(

                // Remove comment(s)
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*[\n\r]*#',

                // Remove unused white-space character(s) outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',

                // Remove the last semicolon
                '#;+\}#',

                // Minify object attribute except JSON attribute. From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',

                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'

            ),
            array(
                '$1',
                '$1$2',
                '}',
                '$1$3',
                '$1.$3'
            ),
        trim($input));
    }

    // internal only (encode)
    protected static function NRT_encode($input) {
        return str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $input);
    }

    // internal only (decode)
    protected static function NRT_decode($input) {
        return str_replace(array('\n', '\r', '\t'), array("\n", "\r", "\t"), $input);
    }

}