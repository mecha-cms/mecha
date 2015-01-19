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
 *    Route::execute(); // Execute the added routes
 *
 *    Route::execute('article/(:any)', array('foo')); // Re-execute this route
 *
 * ------------------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type     | Description
 *  --------- | -------- | ------------------------------------------------------------
 *  $pattern  | string   | URL pattern to match, relative to root domain name
 *  $fn       | function | Route function to be executed if pattern matched with URL
 *  $stack    | float    | Route function priority
 *  --------- | -------- | ------------------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Route {

    private static $routes = array();

    private static function fix($string) {
        return str_replace(
            array(':any', ':num', ':all', '/', ':'),
            array('[^/]+', '[0-9]+', '.*?', '\/', '\:'),
        $string);
    }

    public static function accept($patterns, $fn, $stack = 10) {
        if(is_array($patterns)) {
            $i = 0;
            foreach($patterns as $pattern) {
                $pattern = ltrim(str_replace(Config::get('url') . '/', "", $pattern), '/');
                self::$routes[] = array(
                    'pattern' => $pattern,
                    'fn' => $fn,
                    'stack' => (float) (( ! is_null($stack) ? $stack : 10) + $i)
                );
                $i += .1;
            }
        } else {
            $pattern = ltrim(str_replace(Config::get('url') . '/', "", $patterns), '/');
            self::$routes[] = array(
                'pattern' => $pattern,
                'fn' => $fn,
                'stack' => (float) ( ! is_null($stack) ? $stack : 10)
            );
        }
    }

    public static function reject($patterns, $status = 403) {
        return self::accept($patterns, function() use($status) {
            Guardian::setResponseStatus($status);
            exit;
        });
    }

    public static function execute($pattern = null, $params = array(), $stack = null) {
        if( ! is_null($pattern)) {
            foreach(self::$routes as $route) {
                if($route['pattern'] == $pattern) {
                    if( ! is_null($stack)) {
                        if((float) $route['stack'] == (float) $stack) {
                            call_user_func_array($route['fn'], $params);
                        }
                    } else {
                        call_user_func_array($route['fn'], $params);
                    }
                }
            }
        } else {
            $url = Config::get('url_path');
            $routes = Mecha::eat(self::$routes)->order('ASC', 'stack')->vomit();
            foreach($routes as $route) {
                if(preg_match('#^' . self::fix($route['pattern']) . '$#', $url, $params)) {
                    array_shift($params);
                    Weapon::fire('before_route_function_call', array($url, $route, array_values($params)));
                    return call_user_func_array($route['fn'], array_values($params));
                }
            }
        }
    }

}