<?php

/**
 * ======================================================================
 *  DATE
 * ======================================================================
 *
 * -- CODE: -------------------------------------------------------------
 *
 *    echo Date::format($input, 'Y/m/d');
 *    var_dump(Date::extract($input));
 *
 * ----------------------------------------------------------------------
 *
 */

class Date {

    public static function format($date, $format = 'Y-m-d H:i:s') {

        if(preg_match('#^([0-9]{4,})\-([0-9]{2})\-([0-9]{2})-([0-9]{2})\-([0-9]{2})\-([0-9]{2})$#', $date, $m)) {
            $date = $m[1] . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4] . ':' . $m[5] . ':' . $m[6];
        }

        return preg_match('#^\d+$#', (string) $date) ? date($format, $date) : date($format, strtotime($date));

    }

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

}