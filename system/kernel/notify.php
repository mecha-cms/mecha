<?php

/**
 * ============================================
 *  NOTIFICATION MESSAGES
 * ============================================
 *
 * -- CODE: -----------------------------------
 *
 *    // Set
 *    Notify::error('Hi, there was an error!');
 *    Notify::info('PS: Hi again!');
 *
 *    // Get
 *    echo Notify::read();
 *
 * --------------------------------------------
 *
 */

class Notify {

    private static $notify = 'mecha_notification';
    private static $errors = 0;

    public static function add($type = 'info', $text = "", $icon = '<i class="fa fa-microphone"></i> ', $tag = 'p') {
        Session::set(self::$notify, Session::get(self::$notify) . '<' . $tag . ' class="message message-' . $type . ' cl cf">' . $icon . $text . '</' . $tag . '>');
    }

    public static function success($text = "", $icon = '<i class="fa fa-check"></i> ', $tag = 'p') {
        self::add('success', $text, $icon, $tag);
        Guardian::forget();
    }

    public static function info($text = "", $icon = '<i class="fa fa-info-circle"></i> ', $tag = 'p') {
        self::add('info', $text, $icon, $tag);
    }

    public static function warning($text = "", $icon = '<i class="fa fa-exclamation-triangle"></i> ', $tag = 'p') {
        self::add('warning', $text, $icon, $tag);
        Guardian::memorize();
        self::$errors++;
    }

    public static function error($text = "", $icon = '<i class="fa fa-times"></i> ', $tag = 'p') {
        self::add('error', $text, $icon, $tag);
        Guardian::memorize();
        self::$errors++;
    }

    public static function errors() {
        return self::$errors > 0 ? self::$errors : false;
    }

    public static function read() {
        $results = Session::get(self::$notify) !== "" ? '<div class="messages">' . Session::get(self::$notify) . '</div>' : "";
        self::clear();
        return $results;
    }

    public static function clear() {
        Session::set(self::$notify, "");
    }

}