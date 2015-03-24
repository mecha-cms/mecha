<?php

class Converter {

    /**
     * ====================================================================
     *  CONVERT RGB/RGBA COLOR STRING INTO RGBA DATA
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Converter::RGB('rgb(255, 255, 255)'));
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
        if(is_string($rgba) && preg_match('#^rgba?\(([0-9]{1,3}) *, *([0-9]{1,3}) *, *([0-9]{1,3})( *, *([0-9\.]+))?\)$#i', $rgba, $matches)) {
            $colors = array(
                'r' => (float) $matches[1],
                'g' => (float) $matches[2],
                'b' => (float) $matches[3],
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
            if(strlen($color) == 3) {
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
     *    echo Converter::RGB2HEX(array(255, 255, 255));
     *
     *    echo Converter::RGB2HEX('rgb(255, 255, 255)');
     *
     *    echo Converter::RGB2HEX('rgba(255, 255, 255, .5)');
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter      | Type   | Description
     *  -------------- | ------ | -----------------------------------------
     *  $r, $g, $b, $a | mixed  | The RGB or RGBA color string or array
     *  -------------- | ------ | -----------------------------------------
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
     *  Parameter   | Type    | Description
     *  ----------- | ------- | -------------------------------------------------
     *  $input      | string  | The source text
     *  $chars      | integer | The maximum length of summary
     *  $tail       | string  | Character that follows at the end of the summary
     *  ----------- | ------- | -------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function curt($input, $chars = 100, $tail = '&hellip;') {
        $input = preg_replace(
            array(
                '#<br *\/?>|<\/(div|p)>#', // New line to a single white-space
                '#(\s|&nbsp;)+#', // Multiple white-spaces to a single white-space
                '#<.*?>#', // Remove all HTML tags
                '#^[`~\#*-=+]{2,}#m', // Remove all possible raw Markdown( Extra)? patterns
                '#<|>#' // Fix all broken HTML tags. Replace `<div></di` to `div/di`
            ),
            array(
                ' ',
                ' ',
                "",
                "",
                ""
            ),
        trim($input));
        return trim((function_exists('mb_substr') ? mb_substr($input, 0, $chars, 'UTF-8') : substr($input, 0, $chars)) . ($chars < strlen($input) ? $tail : ""));
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
            foreach(Mecha::A($input) as $key => $value) {
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
        } elseif(is_string($input) && ! is_null(json_decode($input, true))) {
            $results = self::strEval(json_decode($input, true));
        } elseif(is_string($input) && ! preg_match('#^".*?"$#', $input)) {
            $results = str_replace(array('\n', '\r', '\t'), array("\n", "\r", "\t"), $input);
        } elseif(is_numeric($input)) {
            $results = strpos($input, '.') !== false ? (float) $input : (int) $input;
        } elseif(is_array($input)) {
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
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
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

    public static function toText($array, $s = ':', $indent = '    ', $depth = 0) {
        $results = "";
        if( ! is_array($array) && ! is_object($array)) {
            return false;
        }
        foreach($array as $key => $value) {
            if( ! is_array($value) && ! is_object($value)) {
                $value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
                if(is_string($key) && trim($key) === "") { // Line break
                    $results .= "\n";
                } elseif(is_string($key) && strpos($key, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($key) . "\n";
                } elseif(is_int($key) && strpos($value, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($value) . "\n";
                } else {
                    $results .= str_repeat($indent, $depth) . trim(str_replace($s, "", $key)) . $s . ' ' . self::str($value) . "\n";
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

    public static function toArray($input, $s = ':', $indent = '    ') {
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
        $element_d = array('<', '>', ' ', '/');
        $attr_d = array('"', '"', '=');
        $element = Mecha::extend($element_d, $element);
        $attr = Mecha::extend($attr_d, $attr);
        $e0 = preg_quote($element[0], '#');
        $e1 = preg_quote($element[1], '#');
        $e2 = preg_quote($element[2], '#');
        $e3 = preg_quote($element[3], '#');
        $a0 = preg_quote($attr[0], '#');
        $a1 = preg_quote($attr[1], '#');
        $a2 = preg_quote($attr[2], '#');
        if( ! preg_match('#^(' . $e0 . ')([a-z0-9\-._:]+)((' . $e2 . ')+(.*?))?((' . $e1 . ')([\s\S]*?)((' . $e0 . ')' . $e3 . '\2(' . $e1 . '))|(' . $e2 . ')*' . $e3 . '?(' . $e1 . '))$#im', $input, $matches)) return false;
        $matches[5] = preg_replace('#(^|(' . $e2 . ')+)([a-z0-9\-]+)(' . $a2 . ')(' . $a0 . ')(' . $a1 . ')#i', '$1$2$3$4$5<attr:value>$6', $matches[5]);
        $results = array(
            'element' => $matches[2],
            'attributes' => null,
            'content' => isset($matches[8]) && $matches[9] == $element[0] . $element[3] . $matches[2] . $element[1] ? $matches[8] : null
        );
        if(preg_match_all('#([a-z0-9\-]+)((' . $a2 . ')(' . $a0 . ')(.*?)(' . $a1 . '))?(?:(' . $e2 . ')|$)#i', $matches[5], $attrs)) {
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
        return preg_replace(
            array(
                '#<\!--(?!\[if)([\s\S]+?)-->#s', // Remove HTML comments except IE comments
                '#>[^\S ]+#s',
                '#[^\S ]+<#s',
                '#>\s{2,}<#s'
            ),
            array(
                "",
                '>',
                '<',
                '><'
            ),
        $input);
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
        $input_parts = preg_split('#(\/\*\![\s\S]*?\*\/)#', $input, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $results = "";
        foreach($input_parts as $s) {
            $results .= (strpos($s, '/*!') === 0 ? $s : preg_replace(
                array(
                    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|/\*(?>.*?\*/)#s', // Remove comments
                    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                    '#([\s:])(0)(cm|em|ex|in|mm|pc|pt|px|%)#', // Replace `0(cm|em|ex|in|mm|pc|pt|px|%)` with `0`
                    '#:0 0 0 0([;\}])#', // Replace `:0 0 0 0` with `:0`
                    '#background-position:0([;\}])#', // Replace `background-position:0` with `background-position:0 0`
                    '#([\s:])0+\.(\d+)#' // Replace `0.6` to `.6`, but only when preceded by `:` or a white-space
                ),
                array(
                    '$1',
                    '$1$2$3$4$5$6$7',
                    '$1$2',
                    ':0$1',
                    'background-position:0 0$1',
                    '$1.$2'
                ),
            $s)) . "\n";
        }
        return trim($results);
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
        $input_parts = preg_split('#(\/\*\![\s\S]*?\*\/|\/\*\s*@cc_on[\s\S]*?@\s*\*\/)#', $input, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $results = "";
        foreach($input_parts as $s) {
            $results .= (strpos($s, '/*!') === 0 || strpos($s, '@cc_on') !== false ? $s : preg_replace(
                array(
                    '#\/\*([\s\S]*?)\*\/|(?<!:)\/\/.*([\n\r]+|$)#', // Remove comments
                    '#(?|\s*(".*?"|\'.*?\'|(?<=[\(=\s])\/.*?\/[gimuy]*(?=[.,;\s]))\s*|\s*([+-=\/%(){}\[\]<>|&?!:;.,])\s*)#s', // Remove unused white-space characters outside the string and regex
                    '#;+\}#' // Remove the last semicolon
                ),
                array(
                    "",
                    '$1',
                    '}'
                ),
            $s)) . "\n";
        }
        return trim($results);
    }

}