<?php

class Converter extends Plugger {

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
        if(is_string($rgba) && preg_match('#^rgba?\( *([0-9]{1,3}) *, *([0-9]{1,3}) *, *([0-9]{1,3})( *, *([0-9\.]+))? *\)$#i', $rgba, $matches)) {
            $colors = array(
                'r' => (int) $matches[1],
                'g' => (int) $matches[2],
                'b' => (int) $matches[3],
                'a' => isset($matches[5]) ? (float) $matches[5] : 1
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
                'a' => 1
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
                '#<.*?>#', // Remove all HTML tags
                '#[`~\#*-=+_]{2,}#', // Remove all possible raw Markdown( Extra)? patterns
                '#\b([`~*_]+)(.*?)\1\b#', // --ibid
                '#<|>#', // Fix all broken HTML tags. Replace `<div></di` to `div/di`
                '#(\s|&nbsp;)+#', // Multiple white-spaces to a single white-space
                '# (?=[!:;,.\/?])#' // Remove leading white-spaces before `!:;,./?`
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
        return trim((function_exists('mb_substr') ? mb_substr($input, 0, $chars, ($charset !== "" ? $charset : Config::get('charset'))) : substr($input, 0, $chars))) . ($chars < strlen($input) ? $tail : "");
    }

    /**
     * ====================================================================
     *  CONVERT PHP VALUE INTO STRING
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $input    | mixed  | The value or array of value to be converted
     *  --------- | ------ | ----------------------------------------------
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
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $input    | mixed  | The string or array of string to be converted
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function strEval($input) {
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
        } else if(is_string($input) && ! is_null(json_decode($input, true))) {
            $results = self::strEval(json_decode($input, true));
        } else if(is_string($input) && ! preg_match('#^".*?"$#', $input)) {
            $results = str_replace(array('\n', '\r', '\t'), array("\n", "\r", "\t"), $input);
        } else if(is_numeric($input)) {
            $results = strpos($input, '.') !== false ? (float) $input : (int) $input;
        } else if(is_array($input) || is_object($input)) {
            $results = array();
            foreach($input as $key => $value) {
                $results[$key] = self::strEval($value);
            }
        }
        return $results;
    }

    /**
     * ====================================================================
     *  CONVERT STRING OF VALUE INTO EXECUTABLE PHP CODE
     * ====================================================================
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
        eval($input);
        return ob_get_clean();
    }

    /**
     * ====================================================================
     *  CONVERT ARRAY DATA INTO NESTED STRING
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------
     *  $input    | array   | The array of data to be converted
     *  $s        | string  | Separator between array key and array value
     *  $indent   | string  | Indentation as nested array data level
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function toText($array, $s = S, $indent = '    ', $depth = 0) {
        $results = "";
        if( ! is_array($array) && ! is_object($array)) {
            return self::str($array);
        }
        foreach($array as $key => $value) {
            if( ! is_array($value) && ! is_object($value)) {
                $value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), self::str($value));
                if(is_string($key) && strpos($value, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($key) . "\n";
                } else if(is_int($key) && strpos($value, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($value) . "\n";
                } else {
                    $results .= str_repeat($indent, $depth) . trim(str_replace($s, '_', $key)) . $s . ' ' . self::str($value) . "\n";
                }
            } else {
                $results .= str_repeat($indent, $depth) . (string) $key . $s . "\n" . self::toText($value, $s, $indent, $depth + 1) . "\n";
            }
        }
        return rtrim($results);
    }

    /**
     * ====================================================================
     *  CONVERT NESTED STRING INTO ASSOCIATIVE ARRAY
     * ====================================================================
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
     *  CONVERT ATTRIBUTES OF ELEMENT INTO ARRAY OF DATA
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
        $element_d = array('<', '>', ' ', '/', '[a-zA-Z0-9\-._:]+');
        $attr_d = array('"', '"', '=', '[a-zA-Z0-9\-._:]+');
        $element = Mecha::extend($element_d, $element);
        $attr = Mecha::extend($attr_d, $attr);
        $e0 = preg_quote($element[0], '#');
        $e1 = preg_quote($element[1], '#');
        $e2 = preg_quote($element[2], '#');
        $e3 = preg_quote($element[3], '#');
        $a0 = preg_quote($attr[0], '#');
        $a1 = preg_quote($attr[1], '#');
        $a2 = preg_quote($attr[2], '#');
        if( ! preg_match('#^\s*(' . $e0 . ')(' . $element[4] . ')((' . $e2 . ')+(.*?))?((' . $e1 . ')([\s\S]*?)((' . $e0 . ')' . $e3 . '\2(' . $e1 . '))|(' . $e2 . ')*' . $e3 . '?(' . $e1 . '))\s*$#', $input, $matches)) return false;
        $matches[5] = preg_replace('#(^|(' . $e2 . ')+)(' . $attr[3] . ')(' . $a2 . ')(' . $a0 . ')(' . $a1 . ')#', '$1$2$3$4$5<attr:value>$6', $matches[5]);
        $results = array(
            'element' => $matches[2],
            'attributes' => null,
            'content' => isset($matches[8]) && $matches[9] == $element[0] . $element[3] . $matches[2] . $element[1] ? $matches[8] : null
        );
        if(preg_match_all('#(' . $attr[3] . ')((' . $a2 . ')(' . $a0 . ')(.*?)(' . $a1 . '))?(?:(' . $e2 . ')|$)#', $matches[5], $attrs)) {
            $results['attributes'] = array();
            foreach($attrs[1] as $i => $attr) {
                $results['attributes'][$attr] = isset($attrs[5][$i]) && ! empty($attrs[5][$i]) ? (strpos($attrs[5][$i], '<attr:value>') === false ? $attrs[5][$i] : str_replace('<attr:value>', "", $attrs[5][$i])) : $attr;
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
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------
     *  $input    | string  | The HTML string to be compressed
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function detractSkeleton($input) {
        if(trim($input) === "") return $input;
        // Remove extra white-spaces between HTML attributes
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, $input);
        // Minify inline CSS declarations
        if(strpos($input, ' style=') !== false) {
            $input = preg_replace_callback('#\s+style=([\'"]?)(.*?)\1#s', function($matches) {
                return ' style=' . $matches[1] . Converter::detractShell($matches[2]) . $matches[1];
            }, $input);
        }
        return preg_replace(
            array(

                // Remove HTML comments except IE comments
                '#\s*(<\!--(?=\[if).*?-->)\s*|\s*<\!--.*?-->\s*#s',

                // Do not remove white-space after image and
                // input tag that is followed by a tag open
                '#(<(?:img|input)(?:\/?>|\s[^<>]*?\/?>))\s+(?=\<[^\/])#s',

                // Remove two or more white-spaces between tags
                '#(<\!--.*?-->)|(>)\s{2,}|\s{2,}(<)|(>)\s{2,}(<)#s',

                // Proofing ...
                // o: tag open, c: tag close, t: text
                // If `<tag> </tag>` remove white-space
                // If `</tag> <tag>` keep white-space
                // If `<tag> <tag>` remove white-space
                // If `</tag> </tag>` remove white-space
                // If `<tag>    ...</tag>` remove white-spaces
                // If `</tag>    ...<tag>` remove white-spaces
                // If `<tag>    ...<tag>` remove white-spaces
                // If `</tag>    ...</tag>` remove white-spaces
                // If `abc <tag>` keep white-space
                // If `<tag> abc` remove white-space
                // If `abc </tag>` remove white-space
                // If `</tag> abc` keep white-space
                // If `abc    ...<tag>` keep one white-space
                // If `<tag>    ...abc` remove white-spaces
                // If `abc    ...</tag>` remove white-spaces
                // If `</tag>    ...abc` keep one white-space
                // If `abc    ...<tag>` keep one white-space
                // If `abc    ...</tag>` remove white-spaces
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

                // Proofing ...
                '#(?<=\>)&nbsp;(?!\s|&nbsp;)#',
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
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------
     *  $input    | string  | The CSS string to be compressed
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function detractShell($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(

                // Remove comments
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)#s',

                // Remove unused white-spaces
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',

                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[:\s])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#s',

                // Replace `:0 0 0 0` with `:0`
                '#:0 0 0 0(?=[;\}]|\!important)#s',

                // Replace `background-position:0` with `background-position:0 0`
                '#background-position:0(?=[;\}])#s',

                // Replace `0.6` with `.6`, but only when preceded by `:`, `-`, `,` or a white-space
                '#(?<=[:\-,\s])0+\.(\d+)#s',

                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',

                // Minify HEX color code
                '#(?<=[:\-,\s]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',

                // Remove empty selectors
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#si'

            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                'background-position:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
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
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------
     *  $input    | string  | The JavaScript string to be compressed
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function detractSword($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // '#(?<!\\\)\\\\\"#',
                // '#(?<!\\\)\\\\\'#',

                // Remove comments
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*\s*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*[\n\r]*#',

                // Remove unused white-space characters outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\.,;]|[gimuy]))|\s*([+\-\=\/%\(\)\{\}\[\]<>\|&\?\!\:;\.,])\s*#s',

                // Remove the last semicolon
                '#;+\}#',

                // Replace `true` with `!0`
                // '#\btrue\b#',

                // Replace `false` with `!1`
                // '#\bfalse\b#',

                // Minify object attribute except JSON attribute. From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',

                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'

            ),
            array(
                // '\\u0022',
                // '\\u0027',
                '$1',
                '$1$2',
                '}',
                // '!0',
                // '!1',
                '$1$3',
                '$1.$3'
            ),
        trim($input));
    }

}