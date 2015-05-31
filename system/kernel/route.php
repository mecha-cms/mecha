<?php

class Route extends Plugger {

    public static $routes = array();
    public static $routes_e = array();
    public static $routes_over = array();

    protected static function fix($string) {
        return str_replace(
            array(
                '\(',
                '\)',
                '\|',
                '\:any',
                '\:num',
                '\:all',
                '#'
            ),
            array(
                '(',
                ')',
                '|',
                '[^/]+',
                '\d+',
                '.*?',
                '\#'
            ),
        preg_quote($string, '/'));
    }

    protected static function path($pattern) {
        return trim(str_replace(Config::get('url') . '/', "", $pattern), '/');
    }

    /**
     * ===========================================================================
     *  GLOBAL URL PATTERN MATCH
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::accept('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     *    Route::accept('foo/(:num)', function($o = 1) {
     *        ...
     *    });
     *
     * ---------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type     | Description
     *  --------- | -------- | ---------------------------------------------------
     *  $pattern  | string   | URL pattern to match
     *  $fn       | function | Route function to be executed on URL pattern match
     *  $stack    | float    | Route function priority
     *  --------- | -------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function accept($pattern, $fn, $stack = 10, $action = null) {
        $url = Config::get('url');
        if(is_array($pattern)) {
            $i = 0;
            foreach($pattern as $p) {
                self::$routes[] = array(
                    'pattern' => self::path($p),
                    'fn' => $fn,
                    'stack' => (float) (( ! is_null($stack) ? $stack : 10) + $i),
                    'action' => $action
                );
                $i += .1;
            }
        } else {
            self::$routes[] = array(
                'pattern' => self::path($pattern),
                'fn' => $fn,
                'stack' => (float) ( ! is_null($stack) ? $stack : 10),
                'action' => $action
            );
        }
    }

    /**
     * ===========================================================================
     *  GET REQUEST ONLY
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::get('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function get($pattern, $fn, $stack = 10) {
        self::accept($pattern, $fn, $stack, 'GET');
    }

    /**
     * ===========================================================================
     *  POST REQUEST ONLY
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::post('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function post($pattern, $fn, $stack = 10) {
        self::accept($pattern, $fn, $stack, 'POST');
    }

    /**
     * ===========================================================================
     *  REJECT SPECIFIC URL PATTERN
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::post('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function reject($pattern, $stack = null) {
        $pattern = self::path($pattern);
        self::$routes_e[$pattern . '->' . ( ! is_null($stack) ? $stack : 10)] = 1;
        for($i = 0, $count = count(self::$routes); $i < $count; ++$i) {
            if(self::$routes[$i]['pattern'] === $pattern) {
                if( ! is_null($stack)) {
                    if(self::$routes[$i]['stack'] === (float) $stack) {
                        unset(self::$routes[$i]);
                    }
                } else {
                    unset(self::$routes[$i]);
                }
            }
        }
    }

    /**
     * ===========================================================================
     *  DO SOMETHING BEFORE THE `$pattern` ACTION
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::over('foo/bar', function() { ... });
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function over($pattern, $fn, $stack = 10) {
        $pattern = self::path($pattern);
        if( ! isset(self::$routes_over[$pattern])) {
            self::$routes_over[$pattern] = array();
        }
        self::$routes_over[$pattern][] = array(
            'fn' => $fn,
            'stack' => (float) ( ! is_null($stack) ? $stack : 10)
        );
    }

    /**
     * ===========================================================================
     *  CHECK FOR URL PATTERN MATCH
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    if(Route::is('/')) {
     *        echo 'Home sweet home...';
     *    }
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function is($pattern) {
        $pattern = self::path($pattern);
        if(strpos($pattern, '(:') === false) {
            return Config::get('url_path') === $pattern;
        }
        return preg_match('#^' . self::fix($pattern) . '$#', Config::get('url_path'));
    }

    /**
     * ===========================================================================
     *  EXECUTE THE ADDED ROUTES
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    Route::execute();
     *
     * ---------------------------------------------------------------------------
     *
     *    Route::execute('foo/(:num)', array(4)); // Re-execute this route
     *
     * ---------------------------------------------------------------------------
     *
     */

    public static function execute($pattern = null, $arguments = array(), $stack = null) {
        $routes = Mecha::eat(self::$routes)->order('ASC', 'stack')->vomit();
        if( ! is_null($pattern)) {
            $pattern = self::path($pattern);
            foreach($routes as $route) {
                if($route['pattern'] === $pattern) {
                    if( ! is_null($stack)) {
                        if((float) $route['stack'] === (float) $stack) {
                            call_user_func_array($route['fn'], $arguments);
                        }
                    } else {
                        call_user_func_array($route['fn'], $arguments);
                    }
                }
            }
        } else {
            $url = Config::get('url_path');
            foreach($routes as $route) {
                $pattern = $route['pattern'];
                // If not rejected
                if( ! isset(self::$routes_e[$pattern . '->' . $route['stack']])) {
                    // If matched with URL path
                    if(preg_match('#^' . self::fix($pattern) . '$#', $url, $arguments)) {
                        array_shift($arguments);
                        // If request method is valid || null
                        if(is_null($route['action']) || $route['action'] === $_SERVER['REQUEST_METHOD']) {
                            // Loading cargos ...
                            if(isset(self::$routes_over[$pattern]) && is_array(self::$routes_over[$pattern])) {
                                $fn = Mecha::eat(self::$routes_over[$pattern])->order('ASC', 'stack')->vomit();
                                foreach($fn as $v) {
                                    call_user_func_array($v['fn'], array_values($arguments));
                                }
                            }
                            // Passed!
                            return call_user_func_array($route['fn'], array_values($arguments));
                        }
                    }
                }
            }
        }
    }

}