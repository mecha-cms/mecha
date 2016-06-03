<?php


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

Date::plug('extract', function($date, $output = null) {
    $speak = Config::speak();
    $month_name = $speak->month_names;
    $day_name = $speak->day_names;
    $month_name_short = $speak->month_names_short;
    $day_name_short = $speak->day_names_short;
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
    ) = explode('.', Date::format($date, 'Y.y.m.d.H.h.i.s.A.w'));
    $month_name = $month_name[(int) $month - 1];
    $day_name = $day_name[(int) $d];
    $month_name_short = $month_name_short[(int) $month - 1];
    $day_name_short = $day_name_short[(int) $d];
    $results = array(
        'unix' => (int) Date::format($date, 'U'),
        'W3C' => Date::format($date, 'c'),
        'GMT' => Date::GMT($date, 'Y-m-d H:i:s'),
        'slug' => $year . '-' . $month . '-' . $day . '-' . $hour_24 . '-' . $minute . '-' . $second,
        'year' => $year,
        'year_short' => $year_short,
        'month' => $month,
        'day' => $day,
        'month_name' => $month_name,
        'day_name' => $day_name,
        'month_name_short' => $month_name_short,
        'day_name_short' => $day_name_short,
        'hour' => $hour_24,
        'hour_12' => $hour_12,
        'hour_24' => $hour_24, // alias for `hour`
        'minute' => $minute,
        'second' => $second,
        'year_number' => (int) $year,
        'month_number' => (int) $month,
        'day_number' => (int) $day,
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
});