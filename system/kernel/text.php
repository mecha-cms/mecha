<?php

/**
 * =======================================================
 *  TEXT PARSERS
 * =======================================================
 *
 * -- CODE: ----------------------------------------------
 *
 *    // Basic parser
 *    echo Text::parse('some text')->to_slug;
 *    echo Text::parse('some text', $foo, $bar)->to_slug;
 *
 *    // Fast parser
 *    echo Text::parse('some text', '->slug');
 *    echo Text::parse('some text', '->slug', $foo, $bar);
 *
 *    // Perform a test
 *    var_dump(Text::parse('some text'));
 *
 *    // Convert text into array
 *    Text::toArray("Key 1: Value 1\nKey 2: Value 2");
 *
 * -------------------------------------------------------
 *
 */

class Text {

    private static $parsers = array();

    /**
     * Convert Nested String into Associative Arrays
     * ---------------------------------------------
     */

    private static function text_to_array($text, $splitter, $indent) {
        $results = array();
        $data = array();
        // Remove all comments and empty line breaks
        $validated = preg_replace(
            array(
                '#\r#',
                '#(^|\n)( *\#[^\n]*)#',
                '#\n+#'
            ),
            array(
                "",
                '$1',
                "\n"
            ),
        $text);
        foreach(explode("\n", trim($validated)) as $line) {
            // Get depth and labels
            $depth = 0;
            $is_multi = strpos($line, $splitter) !== false && strpos($line, $splitter) > 0;
            while(substr($line, 0, strlen($indent)) === $indent) {
                $depth += 1;
                $line = rtrim(substr($line, strlen($indent)));
            }
            // Truncate paths if needed
            while($depth < count($data)) {
                array_pop($data);
            }
            // Keep lines (at depth)
            if($is_multi) {
                $parts = explode($splitter, $line, 2);
                $data[$depth] = rtrim($parts[0]);
            } else {
                $data[$depth] = $line;
            }
            // Traverse paths and add labels to result
            $parent =& $results;
            foreach($data as $depth => $key) {
                if( ! isset($parent[$key])) {
                    if($is_multi) {
                        $values = isset($parts[1]) && ! empty($parts[1]) ? preg_replace('#^`|`$#', "", trim($parts[1])) : array();
                        $parent[rtrim($parts[0])] = Converter::strEval($values);
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

    /**
     * Parser Creator
     * --------------
     */

    public static function parser($name, $callback) {
        self::$parsers[$name] = $callback;
    }

    /**
     * Parser Existence
     * ----------------
     */

    public static function parserExist($name = null) {
        if(is_null($name)) return self::$parsers;
        return isset(self::$parsers[$name]);
    }

    /**
     * Parser Outputs
     * --------------
     */

    public static function parse() {
        $results = array();
        $arguments = func_get_args();
        // Alternate function for faster parsing process => `Text::parse('foo', '->html')`
        if(count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $parser = 'to_' . str_replace('->', "", $arguments[1]);
            unset($arguments[1]);
            return isset(self::$parsers[$parser]) ? call_user_func_array(self::$parsers[$parser], $arguments) : false;
        }
        // Default function for complete parsing process => `Text::parse('foo')->to_html`
        foreach(self::$parsers as $name => $callback) {
            $results[$name] = call_user_func_array($callback, $arguments);
        }
        return (object) $results;
    }

    public static function toArray($text, $splitter = ':', $indent = '    ') {
        if(is_array($text)) return $text;
        if(is_object($text)) return Mecha::A($text);
        return self::text_to_array($text, $splitter, $indent);
    }

    public static function toObject($text, $splitter = ':', $indent = '    ') {
        if(is_object($text)) return $text;
        if(is_array($text)) return Mecha::O($text);
        return Mecha::O(self::text_to_array($text, $splitter, $indent));
    }

    /**
     * Convert Text File into Page Data
     * --------------------------------
     */

    public static function toPage($text, $content = true, $filter_prefix = 'page:', $content_field = 'content') {
        $results = array();
        $c = $content_field;
        if(strpos($text, ROOT) === 0 && $handle = fopen($text, 'r')) { // By file path
            $by_path = true;
            while(($buffer = fgets($handle, 4096)) !== false) {
                if(trim($buffer) === "" || trim($buffer) == SEPARATOR) {
                    fclose($handle);
                    break;
                }
                $field = explode(':', $buffer, 2);
                if( ! isset($field[1])) $field[1] = "";
                $key = Text::parse(strtolower(trim($field[0])), '->array_key');
                $value = Filter::apply($key, Converter::strEval(self::DS(trim($field[1]))));
                if(is_string($filter_prefix) && trim($filter_prefix) !== "") {
                    $value = Filter::apply($filter_prefix . $key, $value);
                }
                $results[$key] = $value;
            }
        } else { // By file content
            $by_path = false;
            if(strpos(str_replace("\r", "", $text), "\n" . SEPARATOR . "\n") !== false) {
                $parts = explode(SEPARATOR, trim($text), 2);
                $headers = explode("\n", trim($parts[0]));
                foreach($headers as $field) {
                    $field = explode(':', $field, 2);
                    if( ! isset($field[1])) $field[1] = "";
                    $key = Text::parse(strtolower(trim($field[0])), '->array_key');
                    $value = Filter::apply($key, Converter::strEval(self::DS(trim($field[1]))));
                    if(is_string($filter_prefix) && trim($filter_prefix) !== "") {
                        $value = Filter::apply($filter_prefix . $key, $value);
                    }
                    $results[$key] = $value;
                }
                $results[$c . '_raw'] = $results[$c] = isset($parts[1]) ? self::DS(trim($parts[1])) : "";
            } else {
                $results[$c . '_raw'] = $results[$c] = self::DS(trim($text));
            }
        }
        if($content) {
            if($by_path) {
                $text = File::open($text)->read();
                $parts = explode(SEPARATOR, trim($text), 2);
                $results[$c . '_raw'] = isset($parts[1]) ? self::DS(trim($parts[1])) : "";
            }
            $parse_content = ! isset($results['content_type']) || (isset($results['content_type']) && $results['content_type'] === HTML_PARSER);

            /**
             * NOTES: The `content_type` field is very specific and the name
             * cannot be dynamically changed as you might think that I have
             * to replace that `$results['content_type']` with `$results[$c . '_type']`.
             * The `content` field is created from the second explosion which is not
             * came from any field in the page header, but the `content_type` is created
             * purely by the field in the page header called `Content Type`.
             */

            $contents = Filter::apply('shortcode', $results[$c . '_raw']);
            if(is_string($filter_prefix) && trim($filter_prefix) !== "") {
                $contents = Filter::apply($filter_prefix . 'shortcode', $contents);
            }
            $results[$c] = Filter::apply($c, $parse_content ? Text::parse($contents, '->html') : $contents);
            if(is_string($filter_prefix) && trim($filter_prefix) !== "") {
                $results[$c] = Filter::apply($filter_prefix . $c, $results[$c]);
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