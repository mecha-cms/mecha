<?php

// Set default date time zone and locale
Time::zone($state->zone);

// Alias
class_alias('Time', 'Date');

$GLOBALS['time'] = $time = new Time($_SERVER['REQUEST_TIME'] ?? time());

// Alias
$GLOBALS['date'] = $date = $time;