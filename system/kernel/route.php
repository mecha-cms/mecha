<?php

/**
 * ====================================================================================
 *  IGNITE SPECIFIC ACTION BASED ON URL PATTERN MATCH
 * ====================================================================================
 *
 * -- CODE: ---------------------------------------------------------------------------
 *
 *    Route::accept('test/page', function() {
 *        echo 'We are in http://example.org/test/page page.
 *    });
 *
 *    Route::accept('test/page/(:num)', function($offset = 1) {
 *        echo 'We are in http://example.org/test/page/' . $offset . ' page.
 *    });
 *
 *    Route::accept('test/(:any)/(:num)', function($slug = "", $offset = 1) {
 *        echo 'We are in http://example.org/test/' . $slug . '/' . $offset . ' page.
 *    });
 *
 *    Route::accept('test/(article|page)', function($slug = "") {
 *        echo 'We are in http://example.org/test/' . $slug . ' page.
 *    });
 *
 *    Route::accept(array('test/page', 'test/page/(:num)'), function($offset = 1) {
 *        echo 'Page ' . $offset;
 *    });
 *
 * ------------------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type     | Description
 *  --------- | -------- | ------------------------------------------------------------
 *  $pattern  | string   | URL pattern to match, relative to root domain name
 *  $callback | function | Function to be executed if pattern matched with URL
 *  --------- | -------- | ------------------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Route {

    private static $routes = array();

    private function __construct() {}
    private function __clone() {}

    private static function fix($string) {
        return str_replace(
            array(':any', ':num', ':all', '/', ':'),
            array('.[^/]*', '\d+', '.*?', '\/', '\:'),
        $string);
    }

    public static function accept($patterns, $callback) {
        if(is_array($patterns)) {
            foreach($patterns as $pattern) {
                $pattern = ltrim(str_replace(Config::get('url') . '/', "", $pattern), '/');
                self::$routes['#^' . self::fix($pattern) . '$#'] = $callback;
            }
        } else {
            $pattern = ltrim(str_replace(Config::get('url') . '/', "", $patterns), '/');
            self::$routes['#^' . self::fix($pattern) . '$#'] = $callback;
        }
    }

    public static function reject($patterns, $status = null) {
        if(is_null($status)) {
            $status = $_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden';
        }
        return self::accept($patterns, function() use($status) {
            header($status);
            exit;
        });
    }

    public static function execute() {
        $url = preg_replace('#(\?|\&).*$#', "", $_SERVER['REQUEST_URI']);
        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if(strpos($url, $base) === 0) {
            $url = substr($url, strlen($base));
        }
        foreach(self::$routes as $pattern => $callback) {
            if(preg_match($pattern, trim($url, '/'), $params)) {
                array_shift($params);
                return call_user_func_array($callback, array_values($params));
            }
        }
    }

}