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
            $output .= $key . ': ' . $value . "\n";
        }
        return trim($output) !== "" ? trim($output) . (trim(self::$bucket_alt) !== "" ? "\n\n" . SEPARATOR . "\n\n" . self::$bucket_alt : "") : self::$bucket_alt;
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
                $data[self::fix($k)] = Text::ES($v);
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
        $data = Text::ES($data);
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
        $results = Text::toPage(self::create(), $content, $FP);
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