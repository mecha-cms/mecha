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
 *
 *    // Perform a test
 *    var_dump(Text::parse('some text'));
 *
 *    // Convert formated text into array
 *    Text::toArray("Key: value\nKey: value");
 *
 * -------------------------------------------------------
 *
 */

use \Michelf\Markdown;
use \Michelf\MarkdownExtra;

spl_autoload_register(function() {
    include PLUGIN . DS . 'markdown' . DS . 'Michelf' . DS . 'Markdown.php';
    include PLUGIN . DS . 'markdown' . DS . 'Michelf' . DS . 'MarkdownExtra.php';
});

class Text {

    private function __construct() {}
    private function __clone() {}

    /**
     * Convert Text into ASCII Characters
     * ----------------------------------
     */

    private static function text_to_ASCII($text) {
        $result = "";
        for($i = 0, $length = strlen($text); $i < $length; ++$i) {
            $result .= '&#' . ord($text[$i]) . ';';
        }
        return $result;
    }

    /**
     * Convert Nested Strings into Associative Arrays
     * ----------------------------------------------
     */

    private static function text_to_array($text, $splitter, $indent) {
        $results = array();
        $data = array();
        // Remove all comments and empty line breaks
        $validated = preg_replace(
            array(
                '#\r#',
                '#(^|\n)( *\#.[^\n]*)#',
                '#\n+#',
                '#^\#+\n#'
            ),
            array(
                "",
                '$1',
                "\n",
                ""
            ),
        $text);
        foreach(explode("\n", trim($validated)) as $line) {
            // Get depth and labels
            $depth = 0;
            $is_multi = strpos($line, $splitter) !== false;
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
     * Convert Text into Slug URL
     * --------------------------
     */

    private static function text_to_slug($text, $lower = true, $strip_underscores_and_dots = true) {
        if($lower) $text = strtolower($text);
        $text = preg_replace(
            array(
                '#[^a-z0-9-\_\.]#i',
                '#-+#',
                '#^-|-$#'
            ),
            array(
                '-',
                '-',
                ""
            ),
        strip_tags($text));
        if($strip_underscores_and_dots) {
            $text = preg_replace(array('#[\_\.]+#', '#\-+#'), '-', $text);
        }
        return ! empty($text) ? $text : '--';
    }

    /**
     * Convert Slug URL into Plain Text
     * --------------------------------
     */

    private static function slug_to_text($slug) {
        // 1. Replace `+` to ` `
        // 2. Replace `-` to ` `
        // 3. Replace `---` to `-`
        return preg_replace(
            array(
                '#-{3}#',
                '#-#',
                '# +#',
                '#``\.``#'
            ),
            array(
                '``.``',
                ' ',
                ' ',
                '-'
            ),
        urldecode($slug));
    }

    /**
     * Parser Output
     * -------------
     */

    public static function parse($input, $option = false) {
        $parser = new MarkdownExtra;
        $parser->empty_element_suffix = ">"; // HTML5 self closing tag
        $parser->table_align_class_tmpl = 'text-%%'; // Define table alignment class, example: `<td class="text-right">`
        if(is_string($input)) {
            return (object) array(
                'to_ascii' => self::text_to_ASCII($input),
                'to_encoded_url' => urlencode($input),
                'to_decoded_url' => urldecode($input),
                'to_encoded_html' => htmlentities($input, ENT_QUOTES, 'UTF-8'),
                'to_decoded_html' => html_entity_decode($input, ENT_QUOTES, 'UTF-8'),
                'to_html' => trim($parser->transform($input)),
                'to_encoded_json' => json_encode($input),
                'to_decoded_json' => ! is_null(json_decode($input, $option)) ? json_decode($input, $option) : $input,
                'to_slug' => self::text_to_slug($input),
                'to_slug_moderate' => self::text_to_slug($input, true, false),
                'to_text' => self::slug_to_text($input),
                'to_array_key' => str_replace('-', '_', self::text_to_slug($input, false))
            );
        } elseif(is_array($input) || is_object($input)) {
            return (object) array(
                'to_encoded_json' => json_encode($input)
            );
        }
        return $input;
    }

    public static function toArray($text = "", $splitter = ':', $indent = '    ') {
        if(is_array($text)) return $text;
        if(is_object($text)) return Mecha::A($text);
        return self::text_to_array($text, $splitter, $indent);
    }

    public static function toObject($text = "", $splitter = ':', $indent = '    ') {
        if(is_object($text)) return $text;
        if(is_array($text)) return Mecha::O($text);
        return Mecha::O(self::text_to_array($text, $splitter, $indent));
    }

    /**
     * Convert Formatted Text File into Page Data
     * ------------------------------------------
     */

    public static function toPage($text, $parse_contents = true, $filter_prefix = 'page:') {
        $results = array();
        $parts = explode(SEPARATOR, trim($text), 2);
        $headers = isset($parts[0]) ? explode("\n", trim($parts[0])) : array();
        $contents = isset($parts[1]) ? trim($parts[1]) : "";
        foreach($headers as $field) {
            $field = explode(':', $field, 2);
            $key = Text::parse(strtolower(trim($field[0])))->to_array_key;
            $value = Converter::strEval(trim($field[1]));
            $value = Filter::apply($filter_prefix . $key, Filter::apply($key, $value));
            $results[$key] = $value;
        }
        $results['content_raw'] = $contents;
        $contents = Filter::apply('shortcode', $contents);
        $contents = Filter::apply($filter_prefix . 'shortcode', $contents);
        if($parse_contents) {
            $results['content'] = Filter::apply('content', Text::parse($contents)->to_html);
            $results['content'] = Filter::apply($filter_prefix . 'content', $results['content']);
        } else {
            $results['content'] = Filter::apply('content', $contents);
            $results['content'] = Filter::apply($filter_prefix . 'content', $results['content']);
        }
        return $results;
    }

}