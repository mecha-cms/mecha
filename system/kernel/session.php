<?php

/**
 * ======================================================================
 *  SESSION
 * ======================================================================
 *
 * -- CODE: -------------------------------------------------------------
 *
 *    // Set
 *    Session::set('foo', 'bar');
 *
 *    // Get
 *    echo Session::get('foo'); // => `bar`
 *
 *    // Remove
 *    Session::kill('foo');
 *
 * ----------------------------------------------------------------------
 *
 */

class Session {

    public static function set($session, $value = "") {
        return Mecha::SVR($_SESSION, $session, $value);
    }

    public static function get($session = null, $fallback = "") {
        if(is_null($session)) {
            return $_SESSION;
        }
        return Mecha::GVR($_SESSION, $session, $fallback);
    }

    public static function kill($session = null) {
        if(is_null($session)) {
            return session_destroy();
        }
        return Mecha::UVR($_SESSION, $session);
    }

}