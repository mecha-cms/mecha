<?php

class Date {

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
        if(preg_match('#^([0-9]{4,})\-([0-9]{2})\-([0-9]{2})-([0-9]{2})\-([0-9]{2})\-([0-9]{2})$#', $date, $m)) {
            $date = $m[1] . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4] . ':' . $m[5] . ':' . $m[6];
        }
        return is_numeric($date) ? date($format, $date) : date($format, strtotime($date));
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

    public static function extract($date) {
        $speak = Config::speak();
        list($year, $month, $day, $hour, $minute, $second) = explode('.', self::format($date, 'Y.m.d.H.i.s'));
        $month_names = (array) $speak->months;
        $day_names = (array) $speak->days;
        $date_GMT = new DateTime(self::format($date, 'c'));
        $date_GMT->setTimeZone(new DateTimeZone('UTC'));
        return array(
            'unix' => (int) self::format($date, 'U'),
            'W3C' => self::format($date, 'c'),
            'GMT' => $date_GMT->format('Y-m-d H:i:s'),
            'year' => $year,
            'month' => $month_names[(int) $month - 1],
            'day' => $day_names[self::format($date, 'w')],
            'month_number' => $month,
            'day_number' => $day,
            'hour' => $hour,
            'minute' => $minute,
            'second' => $second
        );
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
     *  $simplify | boolean | Remove empty leading offset(s)?
     *  --------- | ------- | -----------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function ago($input, $simplify = true) {
        $speak = Config::speak();
        $date = new DateTime();
        $date->setTimestamp((int) self::format($input, 'U'));
        $interval = $date->diff(new DateTime('now'));
        $time = $interval->format('%y.%m.%d.%h.%i.%s');
        $time = Converter::strEval(explode('.', $time));
        $data = array(
            $speak->year . '/' . $speak->year_p => $time[0],
            $speak->month . '/' . $speak->month_p => $time[1],
            $speak->day . '/' . $speak->day_p => $time[2],
            $speak->hour . '/' . $speak->hour_p => $time[3],
            $speak->minute . '/' . $speak->minute_p => $time[4],
            $speak->second . '/' . $speak->second_p => $time[5]
        );
        if($simplify) {
            foreach($data as $name => $offset) {
                if($data[$name] === 0) {
                    unset($data[$name]);
                } else {
                    break;
                }
            }
        }
        $output = array();
        foreach($data as $name => $offset) {
            $name = explode('/', $name);
            $output[strtolower($name[0])] = $offset . ' ' . ($offset > 1 ? $name[1] : $name[0]);
        }
        return $output;
    }

}