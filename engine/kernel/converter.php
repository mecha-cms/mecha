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
        if(is_string($rgba) && preg_match('#^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(\s*,\s*([\d\.]+))?\s*\)$#i', $rgba, $matches)) {
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
            if(strlen($color) !== 3 && strlen($color) !== 6) {
                return false;
            }
            if(strlen($color) === 3) {
                $color = preg_replace('#(.)#', '$1$1', $color);
            }
            $s = str_split($color, 2);
            $colors = array(
                'r' => (int) hexdec($s[0]),
                'g' => (int) hexdec($s[1]),
                'b' => (int) hexdec($s[2]),
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
        $s = is_array($r) || is_string($r) && is_null($g) ? $r : func_get_args();
        if(is_string($s) && $s = self::RGB($s)) {
            $s = array_values($s);
        }
        if($s) {
            $hex .= str_pad(dechex($s[0]), 2, '0', STR_PAD_LEFT);
            $hex .= str_pad(dechex($s[1]), 2, '0', STR_PAD_LEFT);
            $hex .= str_pad(dechex($s[2]), 2, '0', STR_PAD_LEFT);
            return strtoupper($hex);
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
        if(is_array($input) || is_object($input)) {
            foreach($input as &$v) {
                $v = self::str($v);
            }
            unset($v);
            return $input;
        } else if($input === true) {
            return 'true';
        } else if($input === false) {
            return 'false';
        } else if($input === null) {
            return 'null';
        }
        return (string) $input;
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
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function strEval($input) {
        if(is_string($input)) {
            if($input === "") return $input;
            if(is_numeric($input)) {
                return strpos($input, '.') !== false ? (float) $input : (int) $input;
            } else if(Guardian::check($input, '->json') && $v = json_decode($input, true)) {
                return is_array($v) ? self::strEval($v) : $v;
            } else if($input[0] === '"' && substr($input, -1) === '"' || $input[0] === "'" && substr($input, -1) === "'") {
                return substr(substr($input, 1), 0, -1);
            }
            return Mecha::alter($input, array(
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
            ));
        } else if(is_array($input) || is_object($input)) {
            foreach($input as &$v) {
                $v = self::strEval($v);
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
        return str_replace(array("\n", "\r", "\s", "\t", "\v"), array('\n', '\r', '\s', '\t', '\v'), $text);
    }

    // Decode the encoded white-space(s) (internal only)
    public static function DW($text) {
        return str_replace(array('\n', '\r', '\s', '\t', '\v'), array("\n", "\r", "\s", "\t", "\v"), $text);
    }

}