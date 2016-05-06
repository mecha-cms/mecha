<?php

/**
 * ==============================================
 *  NOTIFICATION MESSAGE(S)
 * ==============================================
 *
 * -- CODE: -------------------------------------
 *
 *    // Set
 *    Notify::error('Hi, there was an error!');
 *    Notify::info('PS: Hi again!');
 *
 * ----------------------------------------------
 *
 *    // Get
 *    echo Notify::read();
 *
 * ----------------------------------------------
 *
 *    // Clear
 *    echo Notify::clear();
 *
 * ----------------------------------------------
 *
 */

class Notify extends __ {

    public static $message = 'message';
    public static $errors = 0;

    public static $config = array(
        'message' => '<p class="message message-%1$s cl cf">%2$s</p>',
        'messages' => '<div class="messages p cl cf">%1$s</div>'
    );

    public static function add($kin, $text = "") {
        if(func_num_args() === 1) {
            self::add('default', $kin);
        } else {
            Session::set(self::$message, Session::get(self::$message, "") . sprintf(self::$config['message'], $kin, $text));
        }
        return new static;
    }

    public static function clear($clear_errors = true) {
        Session::kill(self::$message);
        self::$errors = $clear_errors ? 0 : self::$errors;
    }

    public static function errors() {
        return self::$errors > 0 ? self::$errors : false;
    }

    public static function read($clear_sessions = true) {
        $output = Session::get(self::$message, "") !== "" ? O_BEGIN . sprintf(self::$config['messages'], Session::get(self::$message)) . O_END : "";
        if($clear_sessions) self::clear();
        return $output;
    }

    public static function send($from, $to, $subject, $message, $FP = 'common:') {
        if(trim($to) === "" || ! Guardian::check($to, '->email')) return false;
        $header  = "MIME-Version: 1.0\n";
        $header .= "Content-Type: text/html; charset=ISO-8859-1\n";
        $header .= "From: " . $from . "\n";
        $header .= "Reply-To: " . $from . "\n";
        $header .= "Return-Path: " . $from . "\n";
        $header .= "X-Mailer: PHP/" . phpversion();
        $header = Filter::apply($FP . 'notify.email.header', $header);
        $message = Filter::apply($FP . 'notify.email.message', $message);
        return mail($to, $subject, $message, $header);
    }

}