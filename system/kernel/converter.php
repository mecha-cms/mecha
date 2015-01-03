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
        $results = false;
        if(is_string($input) && preg_match('#^(true|false|yes|no|on|off|null|ok|okay|TRUE|FALSE|NULL)$#', $input, $matches)) {
            switch($matches[1]) {
                case 'true': $results = true; break;
                case 'false': $results = false; break;
                case 'yes': $results = true; break;
                case 'no': $results = false; break;
                case 'on': $results = true; break;
                case 'off': $results = false; break;
                case 'null': $results = null; break;
                case 'ok': $results = true; break;
                case 'okay': $results = true; break;
                case 'TRUE': $results = TRUE; break;
                case 'FALSE': $results = FALSE; break;
                case 'NULL': $results = NULL; break;
            }
        } elseif(is_string($input) && ! is_null(json_decode($input, true))) {
            $results = self::strEval(json_decode($input, true));
        } elseif(is_numeric($input)) {
            $results = strpos($input, '.') !== false ? (float) $input : (int) $input;
        } elseif(is_array($input)) {
            $results = array();
            foreach($input as $key => $value) {
                $results[$key] = self::strEval($value);
            }
        } else {
            $results = $input;
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
     *  $input    | string  | The string element to be converted
     *  $element  | array   | Tag open, tag close, tag separator
     *  $attr     | array   | Value open, value close, attribute separator
     *  $str_eval | boolean | Convert value with `Converter::strEval()` ?
     *  --------- | ------- | ---------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function attr($input, $element = array('<', '>', ' '), $attr = array('"', '"', '='), $str_eval = true) {
        if( ! preg_match('#^' . preg_quote($element[0], '#') . '([a-zA-Z0-9\-\:\_]+)?(' . preg_quote($element[2], '#') . '(.*?))?\/?' . preg_quote($element[1], '#') . '(([\s\S]*?)(' . preg_quote($element[0], '#') . '\/\1' . preg_quote($element[1], '#') . '))?$#m', $input, $matches)) {
            return false;
        }
        $name = $matches[1];
        $attributes = null;
        if(isset($matches[3])) {
            $matches[3] = preg_replace('#' . $element[2] . ($attr[1] !== "" ? '([^' . preg_quote($attr[1], '#') . ']+)' : '([^' . preg_quote($element[2], '#') . ']+)') . $attr[2] . '#', '<attr:separator>$1' . $attr[2], trim($matches[3], $element[2]));
            if( ! empty($matches[3])) {
                $attributes = array();
                $parts = explode('<attr:separator>', $matches[3]);
                foreach($parts as $part) {
                    $part = explode($attr[2], $part, 2);
                    $attributes[trim($part[0], $element[2])] = isset($part[1]) ? preg_replace('#^' . preg_quote($attr[0], '#') . '|' . preg_quote($attr[1], '#') . '$#', "", $part[1]) : "";
                }
                unset($attributes[""]); // Remove empty array keys
            }
        }
        return array(
            'element' => ! empty($name) ? $name : null,
            'attributes' => $str_eval ? self::strEval($attributes) : $attributes,
            'content' => isset($matches[6]) ? $matches[5] : null
        );
    }

    // HTML Minifier
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

    // CSS Minifier => http://ideone.com/Q5USEF + improvement(s)
    public static function detractShell($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|/\*(?>.*?\*/)#s', // Remove comments
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+-]|\s*!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                '#([\s:])(0)(px|em|%|in|cm|mm|pc|pt|ex)#', // Replace `0(px|em|%|in|cm|mm|pc|pt|ex)` with `0`
                '#:0 0 0 0([;\}])#', // Replace `:0 0 0 0` with `:0`
                '#background-position:0([;\}])#', // Replace `background-position:0` with `background-position:0 0`
                '#(:|\s)0+\\.(\d+)#' // Replace `0.6` to `.6`, but only when preceded by `:` or a white-space
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1$2',
                ':0$1',
                'background-position:0 0$1',
                '$1.$2'
            ),
        $input);
    }

    // JavaScript Minifier
    public static function detractSword($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                '#\/\*([\s\S]+?)\*\/|(?<!:)\/\/.*([\n\r]+|$)#', // Remove comments
                '#(^|[\n\r])\s*#', // Remove space and new-line characters at the beginning of line
                '#(?| *(".*?"|\'.*?\'|(?<=[=\(\s])\/.*?\/[igm]*[.,;\s]) *| *([+-=\/%(){}\[\]<>|&?!:;,]) *)#s', // Remove unused space characters outside the string and regex
                '#;\}#', // Remove the last semicolon
                '#$#',
                '#;+$#'
            ),
            array(
                "",
                "",
                '$1',
                '}',
                ';',
                ';'
            ),
        $input);
    }

}