<?php

class Get extends Genome {

    public static function ip($fail = false) {
        $for = 'HTTP_X_FORWARDED_FOR';
        if (array_key_exists($for, $_SERVER) && !empty($_SERVER[$for])) {
            if (strpos($_SERVER[$for], ',') > 0) {
                $ip = explode(',', $_SERVER[$for]);
                $ip = trim($ip[0]);
            } else {
                $ip = $_SERVER[$for];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return Is::ip($ip) ? $ip : $fail;
    }

    public static function i_p($fail = false) {
        return self::ip($fail);
    }

    public static function ua($fail = false) {
        return !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : $fail;
    }

    public static function u_a($fail = false) {
        return self::ua($fail);
    }

}