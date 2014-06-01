<?php

/**
 * ====================================================================================
 *  IGNITE SPECIFIC ACTION BASED ON URL PATTERN
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
            array(':any', ':num', '/', ':'),
            array('.[^/]*', '\d+', '\/', '\:'),
        $string);
    }

    public static function accept($patterns, $callback) {

        $url = preg_replace('#\?.*$#', "", $_SERVER['REQUEST_URI']);
        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

        if(is_array($patterns)) {
            foreach($patterns as $pattern) {
                self::$routes['#^' . self::fix($pattern) . '$#'] = $callback;
            }
        } else {
            self::$routes['#^' . self::fix($patterns) . '$#'] = $callback;
        }

        if(strpos($url, $base) === 0) {
            $url = substr($url, strlen($base));
        }

        $url = trim($url, '/');

        foreach(self::$routes as $pattern => $callback) {
            if(preg_match($pattern, $url, $params)) {
                array_shift($params);
                return call_user_func_array($callback, array_values($params));
            }
        }

    }

    public static function reject($patterns, $status = 'HTTP/1.0 403 Forbidden') {
        return self::accept($patterns, function() use($status) {
            header($status);
            exit;
        });
    }

}