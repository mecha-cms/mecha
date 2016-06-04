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

class Page extends __ {

    public static $open = null;
    public static $header = array();
    public static $content = array();

    protected static $i = 0;

    // Remove `:` in field key
    protected static function _x($key) {
        return trim(str_replace(S, '_', $key));
    }

    // Reset the cached data
    protected static function _reset() {
        self::$open = null;
        self::$header = array();
        self::$content = array();
        self::$i = 0;
    }

    // Create the page
    protected static function _create() {
        $header = "";
        $_ = "\n\n" . SEPARATOR . "\n\n";
        foreach(self::$header as $k => $v) {
            $header .= $k . S . ' ' . $v . "\n";
        }
        $content = implode($_, self::$content);
        return trim(substr($header, 0, -1) . $_ . $content);
    }

    // Create from text
    public static function text($text, $content = 'content', $FP = 'page:', $results = array(), $data = array()) {
        $c = $content !== false ? $content : 'content';
        $_ = SEPARATOR;
        foreach($results as $k => $v) {
            $results[$k . '_raw'] = Filter::colon($FP . $k . '_raw', $v, $data);
            $results[$k] = Filter::colon($FP . $k, $v, $data);
        }
        if( ! $content) {
            // By file path
            if(strpos($text, ROOT) === 0 && ($text = File::open($text)->get($_)) !== false) {
                $text = Filter::apply($FP . 'input', Converter::RN($text), $FP, $data);
                Mecha::extend($results, self::_header($text, $FP, $data));
            // By file content
            } else {
                $text = Filter::apply($FP . 'input', Converter::RN($text), $FP, $data);
                if(strpos($text, $_) !== false) {
                    $s = explode($_, $text, 2);
                    Mecha::extend($results, self::_header(trim($s[0]), $FP, $data));
                    if(isset($s[1]) && $s[1] !== "") {
                        $results[$c . '_raw'] = trim($s[1]);
                    }
                }
            }
        } else {
            // By file path
            if(strpos($text, ROOT) === 0 && file_exists($text)) {
                $text = file_get_contents($text);
            }
            $text = Filter::apply($FP . 'input', Converter::RN($text), $FP, $data);
            // By file content
            if($text === $_ || strpos($text, $_) === false) {
                $results[$c . '_raw'] = Converter::DS(trim($text));
            } else {
                $s = explode($_, $text, 2);
                Mecha::extend($results, self::_header(trim($s[0]), $FP, $data));
                if(isset($s[1]) && $s[1] !== "") {
                    $results[$c . '_raw'] = trim($s[1]);
                }
            }
        }
        unset($results['__'], $results['___raw']);
        Mecha::extend($data, $results);
        if(isset($results[$c . '_raw'])) {
            $content_x = explode($_, $results[$c . '_raw']);
            if(count($content_x) > 1) {
                $results[$c . '_raw'] = $results[$c] = array();
                $i = 0;
                foreach($content_x as $v) {
                    $v = Converter::DS(trim($v));
                    $v = Filter::colon($FP . $c . '_raw', $v, $data, $i + 1);
                    $results[$c . '_raw'][$i] = $v;
                    $v = Filter::colon($FP . 'shortcode', $v, $data, $i + 1);
                    $v = Filter::colon($FP . $c, $v, $data, $i + 1);
                    $results[$c][$i] = $v;
                    $i++;
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
        return Filter::apply($FP . 'output', $results, $FP, $data);
    }

    protected static function _header($text, $FP, $data) {
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
        self::_reset();
        self::$open = $path;
        $i = 0;
        $results = array();
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        foreach($lines as $k => $v) {
            if($i === 0 && $v === "") {
                continue;
            }
            if($v === SEPARATOR) {
                unset($lines[$k]);
                $i++;
                continue;
            }
            $results[$i][] = $v;
        }
        // has header data ...
        if(isset($results[0])) {
            foreach($results[0] as $v) {
                $field = explode(S, $v, 2);
                self::$header[trim($field[0])] = isset($field[1]) ? trim($field[1]) : "";
            }
            unset($results[0]);
        }
        foreach(array_values($results) as $k => $v) {
            self::$content[$k] = trim(implode("\n", $v));
        }
        return new static;
    }

    // Add page header or update the existing page header data
    public static function header($data = array(), $value = "") {
        if( ! is_array($data)) {
            $data = array(self::_x($data) => $value);
        }
        foreach($data as $k => $v) {
            $kk = self::_x($k);
            if($v === false) {
                unset($data[$kk], self::$header[$kk]);
            } else {
                // Restrict user(s) from inputting the `SEPARATOR` constant
                // to prevent mistake(s) in parsing the file content
                $data[$kk] = Converter::ES(trim($v));
            }
        }
        Mecha::extend(self::$header, $data);
        return new static;
    }

    // Add page content or update the existing page content
    public static function content($data = "", $i = null) {
        if($data === false) {
            if( ! is_null($i)) {
                unset(self::$content[$i]);
            } else {
                self::$content = array();
            }
        }
        // Restrict user(s) from inputting the `SEPARATOR` constant
        // to prevent mistake(s) in parsing the file content
        self::$content[is_null($i) ? self::$i : $i] = Converter::ES(trim($data));
        self::$i++;
        return new static;
    }

    // Show page data as plain text
    public static function put() {
        $output = self::_create();
        self::_reset();
        return $output;
    }

    // Show page data as array
    public static function read($content = 'content', $FP = 'page:') {
        if($content === false) {
            self::$content = array();
        }
        $results = self::text(self::_create(), $content, $FP);
        self::_reset();
        return $results;
    }

    // Save the opened page
    public static function save($permission = 0600) {
        File::write(self::_create())->saveTo(self::$open, $permission);
        self::_reset();
    }

    // Save the generated page to ...
    public static function saveTo($path, $permission = 0600) {
        self::$open = $path;
        return self::save($permission);
    }

}