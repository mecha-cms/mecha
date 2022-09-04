<?php

$keep = false;

// This feature is available since PHP 8.1
if (!function_exists('array_is_list')) {
    $keep = true;
    function array_is_list(array $value) {
        if ([] === $value) {
            return true;
        }
        $key = -1;
        foreach ($value as $k => $v) {
            if ($k !== ++$key) {
                return false;
            }
        }
        return true;
    }
}

// This feature is available since PHP 7.3
if (!function_exists('array_key_first')) {
    $keep = true;
    function array_key_first(array $value) {
        if ($value) {
            foreach ($value as $k => $v) {
                return $k;
            }
        }
        return null;
    }
}

// This feature is available since PHP 7.3
if (!function_exists('array_key_last')) {
    $keep = true;
    function array_key_last(array $value) {
        return $value ? key(array_slice($value, -1, 1, true)) : null;
    }
}

// Delete this file if all feature(s) are available by default!
if (!$keep) {
    unlink(__FILE__);
}