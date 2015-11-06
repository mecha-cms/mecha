<?php

class Date extends Base {

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
        $m = explode('-', $date);
        if(strlen($date) >= 19 && count($m) === 6) {
            $date = $m[0] . '-' . $m[1] . '-' . $m[2] . ' ' . $m[3] . ':' . $m[4] . ':' . $m[5];
        }
        return date($format, strtotime($date));
    }

    /**
     * ==========================================================
     *  DATE EXTRACTOR
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    $input = '2014-05-30 09:22:42';
     *
     *    var_dump(Date::extract($input));
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

    public static function extract($date, $output = null) {
        $speak = Config::speak();
        $month_name = (array) $speak->month_names;
        $day_name = (array) $speak->day_names;
        $month_name_short = (array) $speak->month_names_short;
        $day_name_short = (array) $speak->day_names_short;
        list(
            $year,
            $year_short,
            $month,
            $day,
            $hour_24,
            $hour_12,
            $minute,
            $second,
            $AP,
            $d
        ) = explode('.', self::format($date, 'Y.y.m.d.H.h.i.s.A.w'));
        $month_name = $month_name[(int) $month - 1];
        $day_name = $day_name[(int) $d];
        $month_name_short = $month_name_short[(int) $month - 1];
        $day_name_short = $day_name_short[(int) $d];
        $results = array(
            'unix' => (int) self::format($date, 'U'),
            'W3C' => self::format($date, 'c'),
            'GMT' => self::GMT($date, 'Y-m-d H:i:s'),
            'year' => $year,
            'year_short' => $year_short,
            'month' => $month,
            'day' => $day,
            'month_number' => (int) $month,
            'day_number' => (int) $day,
            'month_name' => $month_name,
            'day_name' => $day_name,
            'month_name_short' => $month_name_short,
            'day_name_short' => $day_name_short,
            'hour' => $hour_24,
            'hour_12' => $hour_12,
            'hour_24' => $hour_24, // alias for `hour`
            'minute' => $minute,
            'second' => $second,
            'hour_number' => (int) $hour_24,
            'hour_12_number' => (int) $hour_12,
            'hour_24_number' => (int) $hour_24,
            'minute_number' => (int) $minute,
            'second_number' => (int) $second,
            'AM_PM' => $AP,
            'FORMAT_1' => $day_name . ', ' . $day . ' ' . $month_name . ' ' . $year,
            'FORMAT_2' => $day_name . ', ' . $month_name . ' ' . $day . ', ' . $year,
            'FORMAT_3' => $year . '/' . $month . '/' . $day . ' ' . $hour_24 . ':' . $minute . ':' . $second,
            'FORMAT_4' => $year . '/' . $month . '/' . $day . ' ' . $hour_12 . ':' . $minute . ':' . $second . ' ' . $AP,
            'FORMAT_5' => $hour_24 . ':' . $minute,
            'FORMAT_6' => $hour_12 . ':' . $minute . ' ' . $AP
        );
        return ! is_null($output) ? $results[$output] : $results;
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
     *  $date     | mixed   | The date input
     *  $compact  | boolean | Remove empty leading offset(s)?
     *  --------- | ------- | -----------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function ago($input, $compact = true) {
        $speak = Config::speak();
        $date = new DateTime();
        $date->setTimestamp((int) self::format($input, 'U'));
        $interval = $date->diff(new DateTime('now'));
        $time = $interval->format('%y.%m.%d.%h.%i.%s');
        $time = explode('.', $time);
        $time = Converter::strEval($time);
        $data = array(
            $speak->year . '/' . $speak->years => $time[0],
            $speak->month . '/' . $speak->months => $time[1],
            $speak->day . '/' . $speak->days => $time[2],
            $speak->hour . '/' . $speak->hours => $time[3],
            $speak->minute . '/' . $speak->minutes => $time[4],
            $speak->second . '/' . $speak->seconds => $time[5]
        );
        if($compact) {
            foreach($data as $name => $offset) {
                if($offset === 0) {
                    unset($data[$name]);
                } else {
                    break;
                }
            }
        }
        $output = array();
        foreach($data as $name => $offset) {
            $name = explode('/', $name);
            $output[strtolower($name[0])] = $offset . ' ' . ($offset === 1 ? $name[0] : $name[1]);
        }
        return $output;
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
     *  GMT DATE FORMATTER
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