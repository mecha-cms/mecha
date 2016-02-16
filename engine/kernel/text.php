<?php

class Text extends Base {

    protected static $texts = array();
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
        if(trim($text) === "") return array();
        $results = array();
        $data = array();
        // Normalize line-break
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        if(strpos($indent, "\t") === false) {
            // Force translate 1 tab to 4 space
            $text = str_replace("\t", '    ', $text);
        }
        $indent_length = strlen($indent);
        foreach(explode("\n", $text) as $line) {
            // Ignore comment and empty line-break
            if($line === "" || strpos($line, '#') === 0) continue;
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

    public static function toPage($text, $content = 'content', $FP = 'page:', $results = array(), $data = array()) {
        $c = $content !== false ? $content : 'content';
        foreach($results as $k => $v) {
            $results[$k . '_raw'] = Filter::colon($FP . $k . '_raw', $v, $data);
            $results[$k] = Filter::colon($FP . $k, $v, $data);
        }
        if( ! $content) {
            // By file path
            if(strpos($text, ROOT) === 0 && ($buffer = File::open($text)->get(SEPARATOR)) !== false) {
                Mecha::extend($results, self::__doParseHeaders($buffer, $FP, $data));
                unset($results['__'], $results['___raw']);
            // By file content
            } else {
                $text = str_replace("\r", "", $text);
                if(strpos($text, "\n" . SEPARATOR . "\n") !== false) {
                    $parts = explode("\n" . SEPARATOR . "\n", trim($text), 2);
                    Mecha::extend($results, self::__doParseHeaders($parts[0], $FP, $data));
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
                Mecha::extend($results, self::__doParseHeaders($parts[0], $FP, $data));
                $results[$c . '_raw'] = isset($parts[1]) ? trim($parts[1]) : "";
            }
            Mecha::extend($data, $results);
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

    protected static function __doParseHeaders($text, $FP, $data) {
        $results = array();
        $headers = explode("\n", trim($text));
        foreach($headers as $header) {
            $field = explode(S, $header, 2);
            if( ! isset($field[1])) $field[1] = 'false';
            $key = Text::parse(trim($field[0]), '->array_key', true);
            $value = Converter::DS(trim($field[1]));
            $value = Filter::colon($FP . $key . '_raw', Converter::strEval($value), $data);
            $results[$key . '_raw'] = $value;
            $value = Filter::colon($FP . $key, $value, $data);
            $results[$key] = $value;
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
        self::$texts = is_array($text) ? $text : func_get_args();
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
        $arguments = is_array($text) ? $text : func_get_args();
        if(count($arguments) === 1) {
            return strpos(self::$texts[0], $arguments[0]) !== false;
        }
        $text_v = 0;
        foreach($arguments as $v) {
            if(strpos(self::$texts[0], $v) !== false) {
                $text_v++;
            }
        }
        return $text_v === count($arguments);
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
        $arguments = is_array($text) ? $text : func_get_args();
        if(count(self::$texts) === 1) {
            if(count($arguments) === 1) {
                if($arguments[0] === "") return self::$texts[0] === "";
                if(self::$texts[0] === "") return $arguments[0] === "";
                return strpos($arguments[0], self::$texts[0]) !== false;
            }
            foreach($arguments as $v) {
                if(strpos(self::$texts[0], $v) !== false) return true;
            }
        }
        foreach(self::$texts as $v) {
            if(strpos($arguments[0], $v) !== false) return true;
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
        if(($offset = strpos(self::$texts[0], $text)) !== false) {
            $output['start'] = $offset;
            $output['end'] = $offset + strlen($text) - 1;
        }
        return (object) $output;
    }

}