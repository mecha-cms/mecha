<?php

class Converter extends __ {

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
        if( ! is_string($input)) return $input;
        $config = Config::get();
        // relative to root domain
        if(strpos($input, '/') === 0 && strpos($input, '//') !== 0) {
            return trim($config->protocol . $config->host . '/' . ltrim($input, '/'), '/');
        }
        if(
            strpos($input, '://') === false &&
            strpos($input, '//') !== 0 &&
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
            trim($config->url . '/' . $input, '/'));
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
     *  $suffix   | string  | Character that follows at the end of the summary
     *  --------- | ------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function curt($input, $chars = 100, $suffix = '&hellip;', $charset = "") {
        $input = Text::parse(str_replace('<br' . ES, ' ', $input), '->text');
        $charset = $charset ? $charset : Config::get('charset');
        return trim((function_exists('mb_substr') ? mb_substr($input, 0, $chars, $charset) : substr($input, 0, $chars))) . ($chars < (function_exists('mb_strlen') ? mb_strlen($input, $charset) : strlen($input)) ? $suffix : "");
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
        if( ! is_array($input) && ! is_object($input)) {
            if($input === true) {
                $input = 'true';
            } else if($input === false) {
                $input = 'false';
            } else if($input === null) {
                $input = 'null';
            }
            return (string) $input;
        } else {
            foreach($input as &$v) {
                $v = self::str($v);
            }
            unset($v);
        }
        return $input;
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
        if(is_numeric($input)) {
            $input = strpos($input, '.') !== false ? (float) $input : (int) $input;
        } else if(is_string($input)) {
            if((strpos($input, '{"') === 0 || strpos($input, '[') === 0 || strpos($input, '"') === 0) && ! is_null(json_decode($input, true))) {
                $input = json_decode($input, true);
                if(is_array($input)) {
                    $input = self::strEval($input, $NRT);
                }
            } else if($input && ($input[0] === '"' && substr($input, -1) === '"' || $input[0] === "'" && substr($input, -1) === "'")) {
                $input = substr(substr($input, 1), 0, -1);
            } else {
                $input = Mecha::alter($input, array(
                    'TRUE' => true,
                    'FALSE' => false,
                    'NULL' => null,
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    'yes' => true,
                    'no' => false,
                    'on' => true,
                    'off' => false
                ), $NRT ? self::DW($input) : $input);
            }
        } else if(is_array($input) || is_object($input)) {
            foreach($input as &$v) {
                $v = self::strEval($v, $NRT);
            }
            unset($v);
        }
        return $input;
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

    public static function phpEval($input, $vars = array()) {
        ob_start();
        extract($vars);
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
     *  $n        | string | Separator between data, default is `\n`
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function toText($array, $s = S, $indent = '    ', $n = "\n", $depth = 0, $NRT = true) {
        $results = "";
        if( ! is_array($array) && ! is_object($array)) {
            return self::str($NRT ? self::EW($array) : $array);
        }
        foreach($array as $key => $value) {
            if( ! is_array($value) && ! is_object($value)) {
                $value = $NRT ? self::EW(self::str($value)) : self::str($value);
                if(is_int($key) && $value === 'null') { // Empty line-break
                    $results .= $n;
                } else if(is_string($key) && strpos($key, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($key) . $n;
                } else if(is_int($key) && strpos($value, '#') === 0) { // Comment
                    $results .= str_repeat($indent, $depth) . trim($value) . $n;
                } else {
                    $results .= str_repeat($indent, $depth) . trim(str_replace($s, '_', $key)) . $s . ' ' . $value . $n;
                }
            } else {
                $results .= str_repeat($indent, $depth) . (string) $key . $s . $n . self::toText($value, $s, $indent, $n, $depth + 1, $NRT) . $n;
            }
        }
        return rtrim($results, $n);
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
     *  $n        | string | Separator between data, default is `\n`
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function toArray($input, $s = S, $indent = '    ', $n = "\n") {
        if( ! is_string($input)) return Mecha::A($input);
        if(trim($input) === "") return array();
        $results = $data = array();
        // Normalize line-break
        $text = self::RN($input, $n);
        if(strpos($indent, "\t") === false) {
            // Force translate 1 tab to 4 space
            $text = self::TS($text);
        }
        $indent_length = strlen($indent);
        foreach(explode($n, $text) as $line) {
            // Ignore comment and empty line-break
            if(trim($line) === "" || strpos($line, '#') === 0) continue;
            $depth = 0;
            while(substr($line, 0, $indent_length) === $indent) {
                $depth += 1;
                $line = rtrim(substr($line, $indent_length));
            }
            while($depth < count($data)) {
                array_pop($data);
            }
            // No `:` ... fix it!
            if(strpos($line, $s) === false) {
                $line = $line . $s . $line;
            }
            $parts = explode($s, $line, 2);
            $data[$depth] = rtrim($parts[0]);
            $parent =& $results;
            foreach($data as $i => $key) {
                if( ! isset($parent[$key])) {
                    $value = isset($parts[1]) && ! empty($parts[1]) ? trim($parts[1]) : array();
                    $parent[rtrim($parts[0])] = Converter::strEval($value);
                    break;
                }
                $parent =& $parent[$key];
            }
        }
        return $results;
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
     *  $element  | array   | Tag open, tag close, tag separator, tag end
     *  $attr     | array   | Value open, value close, attribute separator
     *  $str_eval | boolean | Convert value with `Converter::strEval()` ?
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function attr($input, $element = array(), $attr = array(), $str_eval = true) {
        $ED = array('<', '>', ' ', '/', '[\w\-.:]+');
        $AD = array('"', '"', '=', '[\w\-.:]+');
        $E = Mecha::extend($ED, $element);
        $A = Mecha::extend($AD, $attr);
        $E0 = preg_quote($E[0], '#');
        $E1 = preg_quote($E[1], '#');
        $E2 = preg_quote($E[2], '#');
        $E3 = preg_quote($E[3], '#');
        $A0 = preg_quote($A[0], '#');
        $A1 = preg_quote($A[1], '#');
        $A2 = preg_quote($A[2], '#');
        $results = array('element' => null, 'attributes' => null, 'content' => null);
        if( ! preg_match('#^\s*' . $E0 . '(' . $E[4] . ')(?:' . $E2 . '*' . $E3 . '?' . $E1 . '|(' . $E2 . '+.*?)' . $E2 . '*' . $E3 . '?' . $E1 . ')(([\s\S]*?)' . $E0 . $E3 . '\1' . $E1 . ')?\s*$#s', $input, $s)) return false;
        $results['element'] = $s[1];
        $results['content'] = isset($s[4]) ? $s[4] : null;
        if( ! empty($s[2])) {
            if(preg_match_all('#' . $E2 . '+(' . $A[3] . '+)(?:' . $A2 . $A0 . '([\s\S]*?)' . $A1 . ')?#s', $s[2], $ss)) {
                $results['attributes'] = array();
                foreach($ss[1] as $k => $v) {
                    $vv = $str_eval ? Converter::strEval($ss[2][$k]) : $ss[2][$k];
                    if($vv === "" && strpos($ss[0][$k], $A[2] . $A[0] . $A[1]) === false) {
                        $vv = $v;
                    }
                    $results['attributes'][$v] = $vv;
                }
            }
        }
        return $results;
    }

    // Normalize line-break(s) (internal only)
    public static function RN($text, $n = "\n") {
        return str_replace(array("\r\n", "\r"), $n, $text);
    }

    // Reverse ...
    public static function NR($text, $r = "\r") {
        return str_replace(array("\n", "\r\n"), $r, $text);
    }

    // Tab to space
    public static function TS($text, $s = '    ') {
        return str_replace("\t", $s, $text);
    }

    // Space to tab
    public static function ST($text, $s = '    ') {
        return str_replace($s, "\t", $text);
    }

    // Encode the bogus `SEPARATOR`s (internal only)
    public static function ES($text) {
        return str_replace(SEPARATOR, Text::parse(SEPARATOR, '->ascii'), $text);
    }

    // Decode the encoded bogus `SEPARATOR`s (internal only)
    public static function DS($text) {
        return str_replace(Text::parse(SEPARATOR, '->ascii'), SEPARATOR, $text);
    }

    // Encode white-space(s) (internal only)
    public static function EW($text) {
        return str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $text);
    }

    // Decode the encoded white-space(s) (internal only)
    public static function DW($text) {
        return str_replace(array('\n', '\r', '\t'), array("\n", "\r", "\t"), $text);
    }

}