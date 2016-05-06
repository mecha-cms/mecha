<?php

class Date extends __ {

    /**
     * ==========================================================
     *  DATE FORMATTER
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    $input = '2014-05-30 09:22:42';
     *
     *    echo Date::format($input, 'Y/m/d');
     *
     * ----------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ------------------------------------
     *  $date     | mixed  | The date input
     *  $format   | string | The date pattern
     *  --------- | ------ | ------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function format($date, $format = 'Y-m-d H:i:s') {
        if(is_numeric($date)) return date($format, $date);
        if(substr_count($date, '-') === 5) {
            $m = explode('-', $date);
            $date = $m[0] . '-' . $m[1] . '-' . $m[2] . ' ' . $m[3] . ':' . $m[4] . ':' . $m[5];
        }
        return date($format, strtotime($date));
    }

    // Convert time to slug
    public static function slug($date) {
        if(is_string($date) && substr_count($date, '-') === 5) {
            return $date;
        }
        return self::format($date, 'Y-m-d-H-i-s');
    }

    /**
     * ==========================================================
     *  DATE AGO CALCULATOR
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    $input = '2014-05-30 09:22:42';
     *
     *    var_dump(Date::ago($input));
     *
     * ----------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | -----------------------------------
     *  $input    | mixed   | The date input
     *  $output   | string  | Optional to output single data
     *  $compact  | boolean | Remove empty leading offset(s)?
     *  --------- | ------- | -----------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function ago($input, $output = null, $compact = true) {
        $speak = Config::speak();
        $date = new DateTime();
        $date->setTimestamp((int) self::format($input, 'U'));
        $interval = $date->diff(new DateTime('now'));
        $time = $interval->format('%y.%m.%d.%h.%i.%s');
        $time = explode('.', $time);
        $time = Converter::strEval($time);
        $data = array(
            'year' => $time[0],
            'month' => $time[1],
            'day' => $time[2],
            'hour' => $time[3],
            'minute' => $time[4],
            'second' => $time[5]
        );
        if($compact) {
            foreach($data as $k => $v) {
                if($v === 0) {
                    unset($data[$k]);
                } else {
                    break;
                }
            }
        }
        $results = array();
        foreach($data as $k => $v) {
            $text = array($speak->{$k}, $speak->{$k . 's'});
            $results[$k] = $v . ' ' . ($v === 1 ? $text[0] : $text[1]);
        }
        unset($data);
        return ! is_null($output) ? $results[$output] : $results;
    }

    /**
     * ==========================================================
     *  GMT DATE FORMATTER
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    $input = '2014-05-30 09:22:42';
     *
     *    echo Date::GMT($input);
     *
     * ----------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -------------------------------------
     *  $date     | mixed | The date input
     *  --------- | ----- | -------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function GMT($date, $format = 'Y-m-d H:i:s') {
        $date_GMT = new DateTime(self::format($date, 'c'));
        $date_GMT->setTimeZone(new DateTimeZone('UTC'));
        return $date_GMT->format($format);
    }

    /**
     * ==========================================================
     *  INITIALIZE DATE TIMEZONE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Date::timezone('Asia/Jakarta');
     *
     * ----------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | -------------------------------------
     *  $zone     | string | The date timezone
     *  --------- | ------ | -------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function timezone($zone = 'Asia/Jakarta') {
        date_default_timezone_set($zone);
    }

}