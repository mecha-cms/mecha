<?php

class Message extends Genome {

    public static $id = 'mecha.message';
    public static $x = 0;

    public static $config = [
        'message' => [
            0 => 'p',
            1 => '%{1}%',
            2 => [
                'classes' => ['container', 'block', 'message', 'message-%{0}%']
            ],
            3 => 1 // dent
        ],
        'messages' => [
            0 => 'div',
            1 => '%{0}%',
            2 => [
                'classes' => ['container', 'block', 'messages', 'p']
            ]
        ]
    ];

    public static function set(...$lot) {
        $c = __c2f__(static::class, '_');
        $count = count($lot);
        $kin = array_shift($lot);
        $text = array_shift($lot);
        $s = array_shift($lot) ?: "";
        $k = array_shift($lot) ?: false;
        $i = $c . '_' . $kin . '_' . $text;
        $o = Language::get($i, $s, $k);
        $o = $o === $i ? $text : $o;
        if ($count === 1) {
            self::set('default', $kin);
        } else {
            Session::set(self::$id, Session::get(self::$id, "") . __replace__(call_user_func_array('HTML::unite', self::$config['message']), [$kin, Hook::NS($c . '.set.' . $kin, [$o])]));
        }
        return new static;
    }

    public static function reset($error_x = true) {
        Session::reset(self::$id);
        if ($error_x) self::$x = 0;
    }

    public static function get($session_x = true) {
        $output = Hook::NS(__c2f__(static::class, '_') . '.get', [Session::get(self::$id, "") !== "" ? __replace__(call_user_func_array('HTML::unite', self::$config['messages']), Session::get(self::$id)) : ""]);
        if ($session_x) self::reset();
        return $output;
    }

    public static function send($from, $to, $subject, $message) {
        if (empty($to) || (!is_array($to) && !Is::email($to))) {
            return false;
        }
        if (is_array($to)) {
            $s = "";
            if (__is_anemon_a__($to)) {
                // ['foo@bar' => 'Foo Bar', 'baz@qux' => 'Baz Qux']
                foreach ($to as $k => $v) {
                    $s .= ', ' . $v . ' <' . $k . '>';
                }
                $to = substr($s, 2);
            } else {
                // ['foo@bar', 'baz@qux']
                $to = implode(', ', $to);
            }
        }
        $lot  = 'MIME-Version: 1.0' . N;
        $lot .= 'Content-Type: text/html; charset=ISO-8859-1' . N;
        $lot .= 'From: ' . $from . N;
        $lot .= 'Reply-To: ' . $from . N;
        $lot .= 'Return-Path: ' . $from . N;
        $lot .= 'X-Mailer: PHP/' . phpversion();
        $s = __c2f__(static::class, '_') . '.' . __FUNCTION__;
        $lot = Hook::NS($s . '.data', [$lot]);
        $data = Hook::NS($s . '.content', [$message]);
        return mail($to, $subject, $data, $lot);
    }

    public static function __callStatic($kin, $lot = []) {
        if (!self::kin($kin)) {
            array_unshift($lot, $kin);
            return call_user_func_array('self::set', $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}