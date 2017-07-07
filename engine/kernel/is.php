<?php

class Is extends Genome {

    protected static $bucket = [];

    // Initialize…
    public static function this($input) {
        self::$bucket = $input;
        return new static;
    }

    // --ditto
    public static function these($input) {
        if ($input instanceof Anemon) {
            $input = explode(X, $input->join(X));
        }
        self::$bucket = $input;
        return new static;
    }

    // Check if `self::$bucket` contains `$s`
    public static function has($s, $all = false, $x = X) {
        $input = $x . implode($x, self::$bucket) . $x;
        if (is_array($s)) {
            if (!$all) {
                foreach ($s as $v) {
                    if (strpos($input, $x . $v . $x) !== false) {
                        return true;
                    }
                }
                return false;
            } else {
                $pass = 0;
                foreach ($s as $v) {
                    if (strpos($input, $x . $v . $x) !== false) {
                        ++$pass;
                    }
                }
                return $pass === count($s);
            }
        }
        return strpos($input, $x . $s . $x) !== false;
    }

    // Check for empty string, array or object
    public static function void($x) {
        return (
            $x === "" ||
            is_string($x) && trim($x) === "" ||
            is_array($x) && empty($x) ||
            is_object($x) && empty((array) $x)
        );
    }

    // Check for IP address
    public static function ip($x) {
        return filter_var($x, FILTER_VALIDATE_IP);
    }

    public static function i_p($x) {
        return self::ip($x);
    }

    // Check for URL address
    public static function url($x) {
        return filter_var($x, FILTER_VALIDATE_URL);
    }

    public static function u_r_l($x) {
        return self::url($x);
    }

    // Check for valid local path address (whether it is exists or not)
    public static function path($x, $e = false) {
        if (!is_string($x)) return false;
        return strpos($x, ROOT) === 0 && strpos($x, "\n") === false && (!$e || file_exists($x));
    }

    // Check for email address
    public static function email($x) {
        return filter_var($x, FILTER_VALIDATE_EMAIL);
    }

    public static function e_mail($x) {
        return self::email($x);
    }

    // Check for valid boolean value
    public static function toggle($x) {
        return filter_var($x, FILTER_VALIDATE_BOOLEAN);
    }

    // Is file
    public static function F($x) {
        return is_file($x);
    }

    // Is directory
    public static function D($x) {
        return is_dir($x);
    }

    // Is equal to `$x`
    public static function eq($x) {
        return q(self::$bucket) === $x;
    }

    // Is less than `$x`
    public static function lt($x) {
        return q(self::$bucket) < $x;
    }

    // Is greater than `$x`
    public static function gt($x) {
        return q(self::$bucket) > $x;
    }

    // Is less than or equal to `$x`
    public static function lte($x) {
        return q(self::$bucket) <= $x;
    }

    // Is greater than or equal to `$x`
    public static function gte($x) {
        return q(self::$bucket) >= $x;
    }

}