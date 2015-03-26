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

class Page {

    public static $open = null;
    public static $bucket = array();
    public static $bucket_alt = "";

    private static function fix($key) {
        return trim(str_replace(':', "", $key));
    }

    // Open the page file
    public static function open($path) {
        self::$bucket = array();
        self::$open = $path;
        $parts = explode(SEPARATOR, file_get_contents($path), 2);
        $headers = explode("\n", trim($parts[0]));
        foreach($headers as $header) {
            $field = explode(':', $header, 2);
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
                unset($data[self::fix($k)]);
            }
        }
        Mecha::extend(self::$bucket, $data);
        return new static;
    }

    // Add page content or update the existing page content
    public static function content($data = "") {
        self::$bucket_alt = trim(self::$bucket_alt) !== "" && is_null(self::$open) ? trim(self::$bucket_alt) . (trim($data) !== "" ? "\n\n" . SEPARATOR . "\n\n" . $data : "") : $data;
        return new static;
    }

    // Show page data as plain text
    public static function put() {
        $output = "";
        foreach(self::$bucket as $key => $value) {
            $output .= $key . ': ' . $value . "\n";
        }
        return trim($output) !== "" ? trim($output) . (trim(self::$bucket_alt) !== "" ? "\n\n" . SEPARATOR . "\n\n" . self::$bucket_alt : "") : self::$bucket_alt;
    }

    // Show page data as object
    public static function read($content = 'content', $FP = 'page:') {
        $results = Text::toPage(self::put(), $content, $FP);
        if($content === false) {
            unset($results['content']);
            unset($results['content_raw']);
        }
        return $results;
    }

    // Save the opened page
    public static function save($permission = 0600) {
        File::write(self::put())->saveTo(self::$open, $permission);
        self::$open = null;
    }

    // Save the generated page to ...
    public static function saveTo($path, $permission = 0600) {
        self::$open = $path;
        return self::save($permission);
    }

}