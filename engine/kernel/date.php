<?php

class Date extends Genome {

    public static $zone = false;
    public static $format = [];

    public static function zone($zone = null) {
        if (!isset($zone)) return self::$zone;
        self::$zone = $zone;
        return date_default_timezone_set($zone);
    }

    public static function set($key, $fn = null) {
        self::$format[$key] = $fn;
        return new static;
    }

    public static function get($key = null, $fail = false) {
        if (isset($key)) {
            return isset(self::$format[$key]) ? self::$format[$key] : $fail;
        }
        return !empty(self::$format) ? self::$format : $fail;
    }

    public static function reset($key = null) {
        if (isset($key)) {
            unset(self::$format[$key]);
        } else {
            self::$format = [];
        }
        return new static;
    }

    protected $date = "";

    public function __construct($date = null) {
        $this->date = isset($date) ? $date : (isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());
        parent::__construct();
    }

    public function format($format = DATE_WISE) {
        $date = $this->date;
        if (is_numeric($date)) return date($format, $date);
        if (substr_count($date, '-') === 5) {
            return DateTime::createFromFormat('Y-m-d-H-i-s', $date)->format($format);
        }
        return date($format, strtotime($date));
    }

    public function extract($key = null, $fail = false) {
        global $language;
        $months_long = $language->months_long;
        $days_long = $language->days_long;
        $months_short = $language->months_short;
        $days_short = $language->days_short;
        list(
            $year,
            $year_short,
            $month,
            $day,
            $hour_24,
            $hour_12,
            $minute,
            $second,
            $AM_PM,
            $d
        ) = explode('.', $this->format('Y.y.m.d.H.h.i.s.A.w'));
        $month_long = $months_long[(int) $month - 1];
        $month_short = $months_short[(int) $month - 1];
        $day_long = $days_long[(int) $d];
        $day_short = $days_short[(int) $d];
        $AM_PM = strtoupper($AM_PM);
        $output = [
            'W3C' => $this->format('c'),
            'GMT' => $this->GMT(DATE_WISE),
            'unix' => (int) $this->format('U'),
            'slug' => $year . '-' . $month . '-' . $day . '-' . $hour_24 . '-' . $minute . '-' . $second,
            'year' => $year,
            'year_short' => $year_short,
            'month' => $month,
            'day' => $day,
            'month_long' => $month_long,
            'day_long' => $day_long,
            'month_short' => $month_short,
            'day_short' => $day_short,
            'hour' => $hour_24,
            'hour_12' => $hour_12,
            'hour_24' => $hour_24, // alias for `hour`
            'minute' => $minute,
            'second' => $second,
            'AM_PM' => $AM_PM,
            'en_us' => $day_long . ', ' . $month_long . ' ' . $day . ', ' . $year,
            'id_id' => $day_long . ', ' . $day . ' ' . $month_long . ' ' . $year,
            'F1' => $year . '/' . $month . '/' . $day . ' ' . $hour_24 . ':' . $minute . ':' . $second,
            'F2' => $year . '/' . $month . '/' . $day . ' ' . $hour_12 . ':' . $minute . ':' . $second . ' ' . $AM_PM,
            'F3' => $hour_24 . ':' . $minute,
            'F4' => $hour_12 . ':' . $minute . ' ' . $AM_PM
        ];
        if (!empty(self::$format)) {
            foreach (self::$format as $k => $v) {
                if (!is_callable($v)) continue;
                $output[$k] = call_user_func($v, $output, $language);
            }
        }
        return isset($key) ? (array_key_exists($key, $output) ? $output[$key] : $fail) : $output;
    }

    public function ago($key = null, $fail = false, $compact = true) {
        $date = new \DateTime();
        $date->setTimestamp((int) $this->format('U'));
        $interval = $date->diff(new \DateTime('now'));
        $time = $interval->format('%y.%m.%d.%h.%i.%s');
        $time = explode('.', $time);
        $data = [
            'year' => $time[0],
            'month' => $time[1],
            'day' => $time[2],
            'hour' => $time[3],
            'minute' => $time[4],
            'second' => $time[5]
        ];
        $output = [];
        foreach ($data as $k => $v) {
            if ($compact && $v === '0') continue;
            $output[$k] = e($v);
        }
        unset($data);
        return isset($key) ? (array_key_exists($key, $output) ? $output[$key] : $fail) : $output;
    }

    public function GMT($format = DATE_WISE) {
        $date_GMT = new \DateTime($this->format('c'));
        $date_GMT->setTimeZone(new \DateTimeZone('UTC'));
        return $date_GMT->format($format);
    }

    public function __set($key, $value = null) {
        return $this->set($key, $value);
    }

    public function __get($key) {
        return $this->extract($key, null);
    }

    // Fix case for `isset($date->key)` or `!empty($date->key)`
    public function __isset($key) {
        return !!$this->__get($key);
    }

    public function __unset($key) {
        $this->reset($key);
    }

    public function __toString() {
        return $this->__invoke();
    }

    public function __invoke($format = DATE_WISE) {
        return $this->format($format);
    }

}