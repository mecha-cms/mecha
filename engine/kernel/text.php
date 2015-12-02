<?php

class Text extends Base {

    protected static $text = "";
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
        $name = strtolower($name);
        if(strpos($name, 'to_') !== 0) $name = 'to_' . $name;
        self::$parsers[get_called_class()][$name] = $action;
        self::plug($name, $action); // add `Text::to_upper_case()` shortcut
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
        $c = get_called_class();
        if(is_null($name)) {
            return isset(self::$parsers[$c]) && ! empty(self::$parsers[$c]) ? self::$parsers[$c] : $fallback;
        }
        $name = strtolower($name);
        if(strpos($name, 'to_') !== 0) $name = 'to_' . $name;
        return isset(self::$parsers[$c][$name]) ? self::$parsers[$c][$name] : $fallback;
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
        $arguments = func_get_args();
        $c = get_called_class();
        // Alternate function for faster parsing process => `Text::parse('foo', '->html')`
        if(count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $parser = str_replace('->', 'to_', strtolower($arguments[1]));
            unset($arguments[1]);
            return isset(self::$parsers[$c][$parser]) ? call_user_func_array(self::$parsers[$c][$parser], $arguments) : $arguments[0];
        }
        // Default function for complete parsing process => `Text::parse('foo')->to_html`
        $results = array();
        if( ! isset(self::$parsers[$c])) {
            self::$parsers[$c] = array();
        }
        foreach(self::$parsers[$c] as $name => $action) {
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

    public static function toPage($text, $content = 'content', $FP = 'page:', $results = array(), $data = null) {
        $c = $content !== false ? $content : 'content';
        foreach($results as $k => $v) {
            $results[$k . '_raw'] = Filter::colon($FP . $k . '_raw', $v, $data);
            $results[$k] = Filter::colon($FP . $k, $v, $data);
        }
        if( ! $content) {
            // By file path
            if(strpos($text, ROOT) === 0 && ($buffer = File::open($text)->get(SEPARATOR)) !== false) {
                foreach(explode("\n", $buffer) as $header) {
                    $field = explode(S, $header, 2);
                    if( ! isset($field[1])) $field[1] = 'false';
                    $key = Text::parse(trim($field[0]), '->array_key', true);
                    $value = Filter::colon($FP . $key . '_raw', Converter::strEval(Converter::DS(trim($field[1]))), $data);
                    $results[$key . '_raw'] = $value;
                    $value = Filter::colon($FP . $key, $value, $data);
                    $results[$key] = $value;
                }
                unset($results['__']);
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
                        $value = Filter::colon($FP . $key . '_raw', Converter::strEval(Converter::DS(trim($field[1]))), $data);
                        $results[$key . '_raw'] = $value;
                        $value = Filter::colon($FP . $key, $value, $data);
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
                $results[$c . '_raw'] = Converter::DS(trim($text));
            } else {
                $parts = explode(SEPARATOR, trim($text), 2);
                $headers = explode("\n", trim($parts[0]));
                foreach($headers as $header) {
                    $field = explode(S, $header, 2);
                    if( ! isset($field[1])) $field[1] = 'false';
                    $key = Text::parse(trim($field[0]), '->array_key', true);
                    $value = Filter::colon($FP . $key . '_raw', Converter::strEval(Converter::DS(trim($field[1]))), $data);
                    $results[$key . '_raw'] = $value;
                    $value = Filter::colon($FP . $key, $value, $data);
                    $results[$key] = $value;
                }
                $results[$c . '_raw'] = isset($parts[1]) ? trim($parts[1]) : "";
            }
        }
        if(isset($results[$c . '_raw'])) {
            $content_extra = explode(SEPARATOR, $results[$c . '_raw']);
            if(count($content_extra) > 1) {
                $results[$c . '_raw'] = $results[$c] = array();
                foreach($content_extra as $k => $v) {
                    $v = Converter::DS(trim($v));
                    $v = Filter::colon($FP . $c . '_raw', $v, $data, $k + 1);
                    $results[$c . '_raw'][$k] = $v;
                    $v = Filter::colon($FP . 'shortcode', $v, $data, $k + 1);
                    $v = Filter::colon($FP . $c, $v, $data, $k + 1);
                    $results[$c][$k] = $v;
                }
            } else {
                $v = Converter::DS($results[$c . '_raw']);
                $v = Filter::colon($FP . $c . '_raw', $v, $data, 1);
                $results[$c . '_raw'] = $v;
                $v = Filter::colon($FP . 'shortcode', $v, $data, 1);
                $v = Filter::colon($FP . $c, $v, $data, 1);
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
        if( ! is_string($text)) return false;
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
            // No `:` ... fix it!
            if(strpos($line, $s) === false) {
                $line .= $s . $line;
            }
            while(substr($line, 0, $indent_length) === $indent) {
                $depth += 1;
                $line = rtrim(substr($line, $indent_length));
            }
            while($depth < count($data)) {
                array_pop($data);
            }
            $parts = explode($s, $line, 2);
            $data[$depth] = rtrim($parts[0]);
            $parent =& $results;
            foreach($data as $depth => $key) {
                if( ! isset($parent[$key])) {
                    $value = isset($parts[1]) && ! empty($parts[1]) ? preg_replace('#^`|`$#', "", trim($parts[1])) : array();
                    $parent[rtrim($parts[0])] = Converter::strEval($value);
                    break;
                }
                $parent =& $parent[$key];
            }
        }
        return $results;
    }

    /**
     * =====================================================================
     *  INITIALIZE THE TEXT CHECKER
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    Text::check($text)-> ...
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function check($text) {
        $arguments = is_array($text) ? $text : func_get_args();
        self::$text = func_num_args() > 1 ? $arguments : (string) $text;
        return new static;
    }

    /**
     * =====================================================================
     *  CHECK IF TEXT CONTAIN(S) `A` AND `B`
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if(Text::check($text)->has('A', 'B')) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function has($text) {
        if( ! is_array($text) && func_num_args() === 1) {
            $text = (string) $text;
            return strpos(self::$text, $text) !== false;
        } else {
            $text = is_array($text) ? $text : func_get_args();
            $text_v = 0;
            foreach($text as $v) {
                if(strpos(self::$text, $v) !== false) {
                    $text_v++;
                }
            }
            return $text_v === count($text);
        }
    }

    /**
     * =====================================================================
     *  CHECK IF TEXT CONTAIN(S) `A` OR `B`
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if(Text::check('A', 'B')->in($text)) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function in($text) {
        if(is_string(self::$text)) {
            if(is_array($text)) {
                return Mecha::walk($text)->has(self::$text);
            }
            return strpos($text, self::$text) !== false;
        }
        foreach((array) self::$text as $v) {
            if(strpos($text, $v) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * =====================================================================
     *  CHECK OFFSET OF A STRING INSIDE A TEXT
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if(Text::check($text)->offset('A')->start === 0) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public static function offset($text) {
        $output = array('start' => -1, 'end' => -1);
        if( ! is_string(self::$text)) return (object) $output;
        if(($offset = strpos(self::$text, $text)) !== false) {
            $output['start'] = $offset;
            $output['end'] = $offset + strlen($text) - 1;
        }
        return (object) $output;
    }

}