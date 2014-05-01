<?php

/**
 * ===========================================================
 *  PAGE REQUEST
 * ===========================================================
 *
 * -- CODE: --------------------------------------------------
 *
 *    [1]. if(Request::post()) {
 *             echo Request::post('name');
 *             echo Request::post('foo.bar');
 *             echo Request::post('foo.bar', 'Failed.');
 *         }
 *
 *    [2]. if(Request::get()) { ... }
 *
 * -----------------------------------------------------------
 *
 */

class Request {

    public static function post($param = null, $fallback = false, $type = 'POST') {
        if(is_null($param)) {
            if($type == 'POST') {
                return $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST) && ! empty($_POST) ? $_POST : $fallback;
            } else {
                return $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET) && ! empty($_GET) ? $_GET : $fallback;
            }
        }
        return Mecha::eat($type == 'POST' ? $_POST : $_GET)->vomit($param, $fallback);
    }

    public static function get($param = null, $fallback = false) {
        return self::post($param, $fallback, 'GET');
    }

}