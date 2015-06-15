<?php

class Text extends Base {

    protected static $parsers = array();

    /**
     * =====================================================================
     *  TEXT PARSER CREATOR
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    Text::parser('to_upper_case', function($input) {
     *        return strtoupper($input);
     *    });
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function parser($name, $action) {
        self::$parsers[get_called_class() . '::' . $name] = $action;
    }

    /**
     * =====================================================================
     *  TEXT PARSER EXISTENCE
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if( ! Text::parserExist('to_upper_case')) {
     *        Text::parser('to_upper_case', function($input) { ... });
     *    }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function parserExist($name = null, $fallback = false) {
        if(is_null($name)) return self::$parsers;
        $name = get_called_class() . '::' . $name;
        return isset(self::$parsers[$name]) ? self::$parsers[$name] : $fallback;
    }

    /**
     * =====================================================================
     *  TEXT PARSER OUTPUT
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    var_dump(Text::parse($input));
     *
     * ---------------------------------------------------------------------
     *
     *    echo Text::parse($input)->to_upper_case;
     *
     * ---------------------------------------------------------------------
     *
     *    echo Text::parse($input, '->upper_case');
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function parse() {
        $results = array();
        $arguments = func_get_args();
        // Alternate function for faster parsing process => `Text::parse('foo', '->html')`
        if(count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $parser = get_called_class() . '::to_' . str_replace('->', "", $arguments[1]);
            unset($arguments[1]);
            return isset(self::$parsers[$parser]) ? call_user_func_array(self::$parsers[$parser], $arguments) : false;
        }
        // Default function for complete parsing process => `Text::parse('foo')->to_html`
        foreach(self::$parsers as $name => $action) {
            $name = str_replace(get_called_class() . '::', "", $name);
            $results[$name] = call_user_func_array($action, $arguments);
        }
        return (object) $results;
    }

    /**
     * =====================================================================
     *  READ A TEXT FILE AS A PAGE FILE
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    var_dump(Text::toPage($path));
     *
     * ---------------------------------------------------------------------
     *
     *    var_dump(Text::toPage($content));
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function toPage($text, $parse_content = 'content', $FP = 'page:') {
        $results = array();
        $c = $parse_content !== false ? $parse_content : 'content';
        $FP = is_string($FP) && trim($FP) !== "" ? $FP : false;
        if( ! $parse_content) {
            // By file path
            if(strpos($text, ROOT) === 0 && ($buffer = File::open($text)->get(SEPARATOR)) !== false) {
                foreach(explode("\n", $buffer) as $header) {
                    $field = explode(S, $header, 2);
                    if( ! isset($field[1])) $field[1] = 'false';
                    $key = Text::parse(trim($field[0]), '->array_key', true);
                    $value = Filter::apply($key, Converter::strEval(self::DS(trim($field[1]))));
                    if($FP) {
                        $value = Filter::apply($FP . $key, $value);
                    }
                    $results[$key] = $value;
                }
            // By file content
            } else {
                $text = str_replace("\r", "", $text);
                if(strpos($text, "\n" . SEPARATOR . "\n") !== false) {
                    $parts = explode("\n" . SEPARATOR . "\n", trim($text), 2);
                    $headers = explode("\n", trim($parts[0]));
                    foreach($headers as $header) {
                        $field = explode(S, $header, 2);
                        if( ! isset($field[1])) $field[1] = 'false';
                        $key = Text::parse(trim($field[0]), '->array_key', true);
                        $value = Filter::apply($key, Converter::strEval(self::DS(trim($field[1]))));
                        if($FP) {
                            $value = Filter::apply($FP . $key, $value);
                        }
                        $results[$key] = $value;
                    }
                    $results[$c . '_raw'] = isset($parts[1]) ? trim($parts[1]) : "";
                }
            }
        } else {
            // By file path
            if(strpos($text, ROOT) === 0 && file_exists($text)) {
                $text = file_get_contents($text);
            }
            $text = str_replace("\r", "", $text);
            // By file content
            if(strpos($text, "\n" . SEPARATOR . "\n") === false) {
                $results[$c . '_raw'] = self::DS(trim($text));
            } else {
                $parts = explode(SEPARATOR, trim($text), 2);
                $headers = explode("\n", trim($parts[0]));
                foreach($headers as $header) {
                    $field = explode(S, $header, 2);
                    if( ! isset($field[1])) $field[1] = 'false';
                    $key = Text::parse(trim($field[0]), '->array_key', true);
                    $value = Filter::apply($key, Converter::strEval(self::DS(trim($field[1]))));
                    if($FP) {
                        $value = Filter::apply($FP . $key, $value);
                    }
                    $results[$key] = $value;
                }
                $results[$c . '_raw'] = isset($parts[1]) ? trim($parts[1]) : "";
            }
        }

        /**
         * NOTES: The `content_type` field is very specific and the name
         * cannot be dynamically changed as you might think that I have
         * to replace that `$results['content_type']` with `$results[$c . '_type']`.
         * The `content` field is created from the second explosion which is not
         * came from any field in the page header, but the `content_type` is created
         * purely by the field in the page header called `Content Type`.
         */

        $parse = ! isset($results['content_type']) || $results['content_type'] === HTML_PARSER;

        if(isset($results[$c . '_raw'])) {
            $content_extra = explode(SEPARATOR, $results[$c . '_raw']);
            if(count($content_extra) > 1) {
                $results[$c . '_raw'] = $results[$c] = array();
                foreach($content_extra as $k => $v) {
                    $v = self::DS(trim($v));
                    $v = Filter::apply($c . '_raw', $v, $k + 1);
                    if($FP) {
                        $v = Filter::apply($FP . $c . '_raw', $v, $k + 1);
                    }
                    $results[$c . '_raw'][$k] = $v;
                    $v = Filter::apply('shortcode', $v, $k + 1);
                    if($FP) {
                        $v = Filter::apply($FP . 'shortcode', $v, $k + 1);
                    }
                    $vv = $parse && $parse_content ? Text::parse($v, '->html') : $v;
                    $vv = Filter::apply($c, $vv, $k + 1);
                    if($FP) {
                        $vv = Filter::apply($FP . $c, $vv, $k + 1);
                    }
                    $results[$c][$k] = $vv;
                }
            } else {
                $v = self::DS($results[$c . '_raw']);
                $v = Filter::apply($c . '_raw', $v, 1);
                if($FP) {
                    $v = Filter::apply($FP . $c . '_raw', $v, 1);
                }
                $results[$c . '_raw'] = $v;
                $v = Filter::apply('shortcode', $v, 1);
                if($FP) {
                    $v = Filter::apply($FP . 'shortcode', $v, 1);
                }
                $v = $parse && $parse_content ? Text::parse($v, '->html') : $v;
                $v = Filter::apply($c, $v, 1);
                if($FP) {
                    $v = Filter::apply($FP . $c, $v, 1);
                }
                $results[$c] = $v;
            }
        }
        return $results;
    }

    /**
     * =====================================================================
     *  READ A TEXT FILE AS AN ASSOCIATIVE ARRAY
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    var_dump(Text::toArray("key 1: value 1\nkey 2: value 2"));
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function toArray($text, $s = S, $indent = '    ') {
        if(is_array($text)) return $text;
        if(is_object($text)) return Mecha::A($text);
        $results = array();
        $data = array();
        $indent_length = strlen($indent);
        // Remove comment(s) and empty line break(s)
        $text = preg_replace(
            array(
                '#\r#',
                '#(^|\n)\s*\#[^\n]*#',
                '#\n+#',
                '#^\n+|\n+$#'
            ),
            array(
                "",
                "\n",
                "\n",
                ""
            ),
        $text);
        foreach(explode("\n", $text) as $line) {
            $depth = 0;
            $is_assoc = strpos($line, $s) > 0;
            while(substr($line, 0, $indent_length) === $indent) {
                $depth += 1;
                $line = rtrim(substr($line, $indent_length));
            }
            while($depth < count($data)) {
                array_pop($data);
            }
            if($is_assoc) {
                $parts = explode($s, $line, 2);
                $data[$depth] = rtrim($parts[0]);
            } else {
                $data[$depth] = $line;
            }
            $parent =& $results;
            foreach($data as $depth => $key) {
                if( ! isset($parent[$key])) {
                    if($is_assoc) {
                        $value = isset($parts[1]) && ! empty($parts[1]) ? preg_replace('#^`|`$#', "", trim($parts[1])) : array();
                        $parent[rtrim($parts[0])] = Converter::strEval($value);
                    } else {
                        $parent[$key] = array();
                    }
                    break;
                }
                $parent =& $parent[$key];
            }
        }
        return $results;
    }

    // Encode the bogus `SEPARATOR`s (internal only)
    public static function ES($text) {
        return str_replace(SEPARATOR, SEPARATOR_ENCODED, $text);
    }

    // Decode the encoded bogus `SEPARATOR`s (internal only)
    public static function DS($text) {
        return str_replace(SEPARATOR_ENCODED, SEPARATOR, $text);
    }

}