<?php

/**
 * ==============================================
 *  NOTIFICATION MESSAGES
 * ==============================================
 *
 * -- CODE: -------------------------------------
 *
 *    // [1]. Set
 *    Notify::error('Hi, there was an error!');
 *    Notify::info('PS: Hi again!');
 *
 *    // [2]. Get
 *    echo Notify::read();
 *
 * ----------------------------------------------
 *
 */

class Notify {

    private static $notify = 'mecha_notification';

    private static $errors = 0;

    private static $config = array(
        'icons' => array(
            'default' => '<i class="fa fa-fw fa-microphone"></i> ',
            'success' => '<i class="fa fa-fw fa-check"></i> ',
            'info' => '<i class="fa fa-fw fa-info-circle"></i> ',
            'warning' => '<i class="fa fa-fw fa-exclamation-triangle"></i> ',
            'error' => '<i class="fa fa-fw fa-times"></i> '
        )
    );

    public static function add($type = 'info', $text = "", $icon = null, $tag = 'p') {
        if(is_null($icon)) $icon = self::$config['icons']['default'];
        Session::set(self::$notify, Session::get(self::$notify) . '<' . $tag . ' class="message message-' . $type . ' cl cf">' . $icon . $text . '</' . $tag . '>');
    }

    public static function success($text = "", $icon = null, $tag = 'p') {
        if(is_null($icon)) $icon = self::$config['icons']['success'];
        self::add('success', $text, $icon, $tag);
        Guardian::forget();
    }

    public static function info($text = "", $icon = null, $tag = 'p') {
        if(is_null($icon)) $icon = self::$config['icons']['info'];
        self::add('info', $text, $icon, $tag);
    }

    public static function warning($text = "", $icon = null, $tag = 'p') {
        if(is_null($icon)) $icon = self::$config['icons']['warning'];
        self::add('warning', $text, $icon, $tag);
        Guardian::memorize();
        self::$errors++;
    }

    public static function error($text = "", $icon = null, $tag = 'p') {
        if(is_null($icon)) $icon = self::$config['icons']['error'];
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

    public static function configure($key, $value = "") {
        foreach($value as $k => $v) {
            self::$config[$key][$k] = $v;
        }
    }

}