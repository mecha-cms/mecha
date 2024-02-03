<?php

final class Time extends Genome {

    public $parent;
    public $value;
    public $z;

    public function __construct($value = null) {
        if (is_numeric($value)) {
            $this->value = date('Y-m-d H:i:s', (int) $value);
        } else if (is_string($value) && strspn($value, '-0123456789') >= 19 && 5 === substr_count($value, '-')) {
            if ($date = DateTime::createFromFormat('Y-m-d-H-i-s', $value)) {
                $this->value = $date->format('Y-m-d H:i:s');
            } else {
                $this->value = date('Y-m-d H:i:s', 0);
            }
        } else {
            $this->value = date('Y-m-d H:i:s', $value ? strtotime($value) : null);
        }
    }

    public function __get(string $key) {
        if ($v = parent::_($key)) {
            if (is_string($v = $v[0]) && false !== strpos($v, '%')) {
                return $this->i($v);
            }
            return parent::__call($key);
        }
        return method_exists($this, $key) ? $this->{$key}() : null;
    }

    public function __invoke(string $pattern = '%Y-%m-%d %T') {
        return $this->i($pattern);
    }

    public function __serialize(): array {
        return ['value' => $this->value];
    }

    public function __toString(): string {
        return (string) $this->value;
    }

    public function __unserialize(array $lot): void {
        $this->value = $lot['value'] ?? date('Y-m-d H:i:s', $value ? strtotime($value) : null);
    }

    public function date() {
        return $this->format('d');
    }

    public function day($type = null) {
        if (is_array($type)) {
            return $type[$day = $this->format('l')] ?? $day;
        }
        if (is_string($type)) {
            return $this->i('%A'); // The day name, translated
        }
        return str_pad($this->format('w'), 2, '0', STR_PAD_LEFT);
    }

    public function i(string $pattern = '%Y-%m-%d %T') {
        // Can no longer use `strfmtime()` function because this function will be removed in PHP 9.0.0 :(
        // Here, we use `strfmtime()` format in the user input, but use the `date()` function internally.
        // <https://php.watch/versions/8.1/strftime-gmstrftime-deprecated>
        // <https://www.php.net/manual/en/function.date.php>
        // <https://www.php.net/manual/en/function.strftime.php>
        $out = date(strtr($pattern, [
            '%%' => '%',
            '%A' => 'l',
            '%B' => 'F',
            '%D' => 'm/d/y',
            '%F' => 'Y-m-d',
            '%H' => 'H',
            '%I' => 'h',
            '%M' => 'i',
            '%P' => 'a',
            '%R' => 'H:i',
            '%S' => 's',
            '%T' => 'H:i:s',
            '%W' => 'W',
            '%X' => 'H:i:s',
            '%Y' => 'Y',
            '%Z' => 'T',
            '%a' => 'D',
            '%b' => 'M',
            '%c' => 'D M j H:i:s Y',
            '%d' => 'd',
            '%e' => 'j',
            '%h' => 'M',
            '%k' => 'G',
            '%l' => 'g',
            '%m' => 'm',
            '%n' => "\n",
            '%p' => 'A',
            '%r' => 'h:i:s A',
            '%s' => 'U',
            '%t' => "\t",
            '%u' => 'N',
            '%w' => 'w',
            '%x' => 'm/d/y',
            '%y' => 'y',
            '%z' => 'O'
        ]), strtotime($this->value));
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
            $out = preg_replace_callback('/[a-z]\w+/i', static function ($m) {
                return i($m[0]);
            }, $out);
        }
        return $out;
    }

    public function format(string $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->value)); // Generic PHP date formatter
    }

    public function hour($type = null) {
        return $this->format(12 === $type ? 'h' : 'H');
    }

    public function minute() {
        return $this->format('i');
    }

    public function month($type = null) {
        if (is_array($type)) {
            return $type[$month = $this->format('F')] ?? $month;
        }
        if (is_string($type)) {
            return $this->i('%B'); // The month name, translated
        }
        return $this->format('m');
    }

    // Convert date to file name
    public function name(string $join = '-') {
        return strtr($this->value, '- :', str_repeat($join, 3));
    }

    public function second() {
        return $this->format('s');
    }

    public function to(string $zone = null, string $modify = null) {
        $date = new DateTime($this->value);
        $date->setTimeZone(new DateTimeZone($zone = $zone ?? zone()));
        if (isset($modify)) {
            $date->modify($modify);
        }
        if (!isset($this->z[$zone])) {
            $this->z[$zone] = new static($date->format('Y-m-d H:i:s'));
            $this->z[$zone]->parent = $this;
        }
        return $this->z[$zone];
    }

    public function year() {
        return $this->format('Y');
    }

    public static function __set_state(array $lot): object {
        $that = new static($lot['value'] ?? null);
        if ($zone = $lot['zone'] ?? 0) {
            return $that->to($zone);
        }
        return $that;
    }

    public static function from(...$lot) {
        return new static(...$lot);
    }

}