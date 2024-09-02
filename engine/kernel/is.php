<?php

final class Is extends Genome {

    private function __construct() {}

    public static function __callStatic(string $kin, array $lot = []) {
        return parent::_($kin) ? parent::__callStatic($kin, $lot) : null;
    }

    // Check for IP address
    public static function IP($value) {
        return false !== filter_var($value, FILTER_VALIDATE_IP);
    }

    // Check for JSON pattern
    public static function JSON($value) {
        if (!is_string($value) || "" === ($value = trim($value))) {
            return false;
        }
        return json_validate($value);
    }

    // Check for URL address
    public static function URL($value) {
        return false !== filter_var($value, FILTER_VALIDATE_URL);
    }

    // Check for email address
    public static function email($value) {
        return false !== filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    // Check for valid file name
    public static function file($value) {
        return is_string($value) && strlen($value) <= 260 && is_file($value);
    }

    // Check for valid folder name
    public static function folder($value) {
        return is_string($value) && strlen($value) <= 260 && is_dir($value);
    }

    // Check for valid local path address (whether it is exists or not)
    public static function path($value, $exist = false) {
        if (!is_string($value)) {
            return false;
        }
        return 0 === strpos($value, PATH) && false === strpos($value, "\n") && (!$exist || stream_resolve_include_path($value));
    }

    // Check for valid serial string <https://github.com/WordPress/wordpress-develop/blob/5.9/src/wp-includes/functions.php#L660-L716>
    public static function serial($value) {
        // If it isn’t a string, it isn’t serialized
        if (!is_string($value)) {
            return false;
        }
        $value = trim($value);
        if ('N;' === $value) {
            return true;
        }
        if (strlen($value) < 4) {
            return false;
        }
        if (':' !== $value[1]) {
            return false;
        }
        if (false === strpos(';}', substr($value, -1))) {
            return false;
        }
        $v = $value[0];
        if ('s' === $v && false === strpos($value, '"')) {
            return false;
        }
        if (false !== strpos('Oa', $v)) {
            return (bool) preg_match('/^' . $v . ':[0-9]+:/s', $value);
        }
        if (false !== strpos('bdi', $v)) {
            return (bool) preg_match('/^' . $v . ':[0-9.E+-]+;$/', $value);
        }
        return false;
    }

    // Check for valid boolean value
    public static function toggle($value) {
        return null !== filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    // Check for empty string, array or object
    public static function void($value) {
        if ($value instanceof Traversable) {
            $value->rewind();
            return !$value->valid();
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