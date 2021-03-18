<?php

final class Is extends Genome {

    // Check for IP address
    public static function IP($value) {
        return filter_var($value, FILTER_VALIDATE_IP);
    }

    // Check for URL address
    public static function URL($value) {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        return parent::_($kin) ? parent::__callStatic($kin, $lot) : null;
    }

    // Check for email address
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    // Check for valid file name
    public static function file($value) {
        return is_string($value) && strlen($value) <= 260 && is_file($value);
    }

    // Check for valid folder name
    public static function files($value) {
        return is_string($value) && strlen($value) <= 260 && is_dir($value);
    }

    // Alias for `files`
    public static function folder($value) {
        return self::files($value);
    }

    // Check for valid local path address (whether it is exists or not)
    public static function path($value, $exist = false) {
        if (!is_string($value)) {
            return false;
        }
        return 0 === strpos($value, ROOT) && false === strpos($value, "\n") && (!$exist || stream_resolve_include_path($value));
    }

    // Check for valid boolean value
    public static function toggle($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    // Check for empty string, array or object
    public static function void($value) {
        if ($value instanceof \Traversable) {
            return 0 === \iterator_count($value);
        }
        // `0` integer and `0` string is not considered void
        return (
            "" === $value ||
            false === $value ||
            null === $value ||
            is_string($value) && "" === trim($value) ||
            is_array($value) && empty($value) ||
            is_object($value) && empty((array) $value)
        );
    }

}
