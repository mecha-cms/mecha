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

    public static $bucket = array();
    public static $bucket_alt = "";
    public static $open = null;

    private static function fix($key) {
        return trim(str_replace(':', "", $key));
    }

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

    public static function content($data = "") {
        self::$bucket_alt = trim(self::$bucket_alt) !== "" && is_null(self::$open) ? trim(self::$bucket_alt) . (trim($data) !== "" ? "\n\n" . SEPARATOR . "\n\n" . $data : "") : $data;
        return new static;
    }

    public static function put() {
        $output = "";
        foreach(self::$bucket as $key => $value) {
            $output .= $key . ': ' . $value . "\n";
        }
        return trim($output) !== "" ? trim($output) . (trim(self::$bucket_alt) !== "" ? "\n\n" . SEPARATOR . "\n\n" . self::$bucket_alt : "") : self::$bucket_alt;
    }

    public static function read($content = 'content', $filter_prefix = 'page:') {
        $results = Text::toPage(self::put(), $content, $filter_prefix);
        if($content === false) {
            unset($results['content']);
            unset($results['content_raw']);
        }
        return $results;
    }

    public static function save($permission = 0600) {
        File::write(self::put())->saveTo(self::$open, $permission);
        self::$open = null;
    }

    public static function saveTo($path, $permission = 0600) {
        self::$open = $path;
        return self::save($permission);
    }

}