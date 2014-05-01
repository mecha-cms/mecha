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
 *    echo Session::get('foo'); => `bar`
 *
 *    // Erase
 *    Session::kill('foo');
 *
 * ----------------------------------------------------------------------
 *
 */

class Session {

    public static function set($session, $value = "") {
        return $_SESSION[$session] = $value;
    }

    public static function get($session = null) {
        if(is_null($session)) {
            return $_SESSION;
        }
        return isset($_SESSION[$session]) ? $_SESSION[$session] : "";
    }

    public static function kill($session = null) {
        if(is_null($session)) {
            return session_destroy();
        }
        unset($_SESSION[$session]);
    }

}