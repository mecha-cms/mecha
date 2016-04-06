<?php

/**
 * =============================================================
 *  PAGE
 * =============================================================
 *
 * -- CODE: ----------------------------------------------------
 *
 *    echo Page::header(array(
 *        'Title' => 'Test Page',
 *        'Content Type' => 'HTML'
 *    ))->content('<p>Test!</p>')->put();
 *
 * -------------------------------------------------------------
 *
 */

class Page extends Base {

    public static $open = null;
    public static $bucket = array();
    public static $bucket_alt = "";

    // Remove `:` in field key
    protected static function fix($key) {
        return trim(str_replace(S, '_', $key));
    }

    // Reset the cached data
    protected static function reset() {
        self::$open = null;
        self::$bucket = array();
        self::$bucket_alt = "";
    }

    // Create the page
    protected static function create() {
        $output = "";
        foreach(self::$bucket as $key => $value) {
            $output .= $key . S . ' ' . $value . "\n";
        }
        return trim($output) !== "" ? trim($output) . (trim(self::$bucket_alt) !== "" ? "\n\n" . SEPARATOR . "\n\n" . self::$bucket_alt : "") : self::$bucket_alt;
    }

    // Create from text
    public static function text($text, $content = 'content', $FP = 'page:', $results = array(), $data = array()) {
        $c = $content !== false ? $content : 'content';
        foreach($results as $k => $v) {
            $results[$k . '_raw'] = Filter::colon($FP . $k . '_raw', $v, $data);
            $results[$k] = Filter::colon($FP . $k, $v, $data);
        }
        if( ! $content) {
            // By file path
            if(strpos($text, ROOT) === 0 && ($buffer = File::open($text)->get(SEPARATOR)) !== false) {
                Mecha::extend($results, self::__($buffer, $FP, $data));
                unset($results['__'], $results['___raw']);
            // By file content
            } else {
                $text = str_replace("\r", "", $text);
                if(strpos($text, "\n" . SEPARATOR . "\n") !== false) {
                    $parts = explode("\n" . SEPARATOR . "\n", trim($text), 2);
                    Mecha::extend($results, self::__($parts[0], $FP, $data));
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
                Mecha::extend($results, self::__($parts[0], $FP, $data));
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

    protected static function __($text, $FP, $data) {
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

    // Open the page file
    public static function open($path) {
        self::reset();
        self::$open = $path;
        $parts = explode(SEPARATOR, file_get_contents($path), 2);
        $headers = explode("\n", trim($parts[0]));
        foreach($headers as $header) {
            $field = explode(S, $header, 2);
            self::$bucket[trim($field[0])] = isset($field[1]) ? trim($field[1]) : false;
        }
        self::$bucket_alt = trim($parts[1]);
        return new static;
    }

    // Add page header or update the existing page header data
    public static function header($data = array(), $value = "") {
        if( ! is_array($data)) {
            $data = array(self::fix($data) => $value);
        }
        foreach($data as $k => $v) {
            if($v === false) {
                unset($data[self::fix($k)], self::$bucket[self::fix($k)]);
            } else {
                // Restrict user(s) from inputting the `SEPARATOR` constant
                // to prevent mistake(s) in parsing the file content
                $data[self::fix($k)] = Converter::ES($v);
            }
        }
        Mecha::extend(self::$bucket, $data);
        return new static;
    }

    // Add page content or update the existing page content
    public static function content($data = "") {
        if($data === false) {
            $data = "";
        }
        // Restrict user(s) from inputting the `SEPARATOR` constant
        // to prevent mistake(s) in parsing the file content
        $data = Converter::ES($data);
        self::$bucket_alt = trim(self::$bucket_alt) !== "" && is_null(self::$open) ? trim(self::$bucket_alt) . (trim($data) !== "" ? "\n\n" . SEPARATOR . "\n\n" . $data : "") : $data;
        return new static;
    }

    // Show page data as plain text
    public static function put() {
        $output = self::create();
        self::reset();
        return $output;
    }

    // Show page data as array
    public static function read($content = 'content', $FP = 'page:') {
        $results = self::text(self::create(), $content, $FP);
        if($content === false) {
            unset($results['content'], $results['content_raw']);
        }
        self::reset();
        return $results;
    }

    // Save the opened page
    public static function save($permission = 0600) {
        File::write(self::create())->saveTo(self::$open, $permission);
        self::reset();
    }

    // Save the generated page to ...
    public static function saveTo($path, $permission = 0600) {
        self::$open = $path;
        return self::save($permission);
    }

}