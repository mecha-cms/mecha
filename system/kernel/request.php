<?php

/**
 * ===========================================================
 *  PAGE REQUEST
 * ===========================================================
 *
 * -- CODE: --------------------------------------------------
 *
 *    if(Request::post()) {
 *        echo Request::post('name');
 *        echo Request::post('foo.bar');
 *        echo Request::post('foo.bar', 'Failed.');
 *    }
 *
 * -----------------------------------------------------------
 *
 *    if(Request::get()) { ... }
 *
 * -----------------------------------------------------------
 *
 */

class Request {

    public static function post($param = null, $fallback = false, $str_eval = true) {
        if(is_null($param)) {
            return $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST) && ! empty($_POST) ? ($str_eval ? Converter::strEval($_POST) : $_POST) : $fallback;
        }
        $output = Mecha::GVR($_POST, $param, $fallback);
        return ! empty($output) ? ($str_eval ? Converter::strEval($output) : $output) : $fallback;
    }

    public static function get($param = null, $fallback = false, $str_eval = true) {
        if(is_null($param)) {
            return $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET) && ! empty($_GET) ? ($str_eval ? Converter::strEval($_GET) : $_GET) : $fallback;
        }
        $output = Mecha::GVR($_GET, $param, $fallback);
        return ! empty($output) ? ($str_eval ? Converter::strEval($output) : $output) : $fallback;
    }

}