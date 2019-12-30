<?php

final class Time extends Genome {

    private static $zone;

    public $o;
    public $parent;
    public $source;

    public function ISO8601() {
        return $this->format('c');
    }

    public function __call(string $kin, array $lot = []) {
        if ($v = parent::_($kin)) {
            if (is_string($v = $v[0]) && false !== strpos($v, '%')) {
                return $this->i($v);
            }
        }
        return parent::__call($kin, $lot);
    }

    public function __construct($date) {
        if (is_numeric($date)) {
            $this->source = date('Y-m-d H:i:s', $date);
        } else if (strlen($date) >= 19 && 5 === substr_count($date, '-')) {
            $this->source = \DateTime::createFromFormat('Y-m-d-H-i-s', $date)->format('Y-m-d H:i:s');
        } else {
            $this->source = date('Y-m-d H:i:s', strtotime($date));
        }
    }

    public function __invoke(string $pattern = '%Y-%m-%d %T') {
        return $this->i($pattern);
    }

    public function __toString() {
        return (string) $this->source;
    }

    public function date() {
        return $this->format('d');
    }

    public function day($type = null) {
        return $this->i(is_string($type) ? '%A' : '%u');
    }

    public function i(string $pattern = '%Y-%m-%d %T') {
        $out = strftime($pattern, strtotime($this->source));
        // Slightly improve the performance by detecting some pattern that produces word(s)
        if (
            // ‘Sun’ through ‘Sat’ or ‘Sunday’ through ‘Saturday’
            false !== stripos($pattern, '%a') ||
            // ‘Jan’ through ‘Dec’ or ‘January’ through ‘December’
            false !== stripos($pattern, '%b') ||
            // Preferred date and time stamp based on locale
            false !== stripos($pattern, '%c') ||
            // An alias of `%b`
            false !== stripos($pattern, '%h') ||
            // ‘AM’ or ‘PM’ based on the given time
            false !== stripos($pattern, '%p')
        ) {
            // Make the date translation system to work without PHP `intl` extension
            // Assume every word(s) in the formatted date as a translate-able string
            $out = preg_replace_callback('/[a-z]\w+/i', function($m) {
                return i($m[0]);
            }, $out);
        }
        return $out;
    }

    public function format(string $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->source)); // Generic PHP date formatter
    }

    public function hour($type = null) {
        return $this->format(12 === $type ? 'h' : 'H');
    }

    public function minute() {
        return $this->format('i');
    }

    public function month($type = null) {
        return $this->i(is_string($type) ? '%B' : '%m');
    }

    // Convert date to file name
    public function name(string $join = '-') {
        return strtr($this->source, '- :', str_repeat($join, 3));
    }

    public function second() {
        return $this->format('s');
    }

    public function to(string $zone = 'UTC') {
        $date = new \DateTime($this->source);
        $date->setTimeZone(new \DateTimeZone($zone));
        if (!isset($this->o[$zone])) {
            $this->o[$zone] = new static($date->format('Y-m-d H:i:s'));
            $this->o[$zone]->parent = $this;
        }
        return $this->o[$zone];
    }

    public function year() {
        return $this->format('Y');
    }

    public static function from($in) {
        return new static($in);
    }

    public static function zone(string $zone = null) {
        if (!isset($zone)) {
            return self::$zone ?? date_default_timezone_get();
        }
        return date_default_timezone_set(self::$zone = $zone ?? date_default_timezone_get());
    }

}