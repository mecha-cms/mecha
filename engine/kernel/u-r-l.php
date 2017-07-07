<?php

class URL extends Genome {

    public static function long($url, $root = true) {
        if (!is_string($url)) return $url;
        $a = __url__();
        $b = false;
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $url = ltrim($url, '/');
            $b = true; // Relative to the root domain
        }
        if (
            strpos($url, '://') === false &&
            strpos($url, '//') !== 0 &&
            strpos($url, '?') !== 0 &&
            strpos($url, '&') !== 0 &&
            strpos($url, '#') !== 0 &&
            strpos($url, 'javascript:') !== 0
        ) {
            return trim(($root && $b ? $a['protocol'] . $a['host'] : $a['url']) . '/' . self::_fix($url), '/');
        }
        return self::_fix($url);
    }

    public static function short($url, $root = true) {
        $a = __url__();
        if (strpos($url, '//') === 0 && strpos($url, '//' . $a['host']) !== 0) {
            return $url; // Ignore external URL
        }
        $url = X . $url;
        if ($root) {
            return str_replace([X . $a['protocol'] . $a['host'], X . '//' . $a['host'], X], "", $url);
        }
        return ltrim(str_replace([X . $a['url'], X . '//' . rtrim($a['host'] . '/' . $a['directory'], '/'), X], "", $url), '/');
    }

    // Initial URL (without the page offset)
    public static function I($url) {
        if (strpos($url, ROOT) === 0) {
            $url = To::url($url);
        }
        return rtrim($url, '/0123456789');
    }

    protected static function _fix($s) {
        return str_replace(['\\', '/?', '/&', '/#'], ['/', '?', '&', '#'], $s);
    }

    protected $lot = [];

    public static function __callStatic($kin, $lot) {
        $a = __url__();
        if (!self::kin($kin)) {
            $fail = array_shift($lot) ?: false;
            return array_key_exists($kin, $a) ? $a[$kin] : $fail;
        }
        return parent::__callStatic($kin, $lot);
    }

    public function __construct($input = null) {
        if (isset($input)) {
            $a = parse_url($input);
            if (isset($a['path'])) {
                $a['path'] = trim($a['path'], '/');
            }
            if (isset($a['query']) && strpos($a['query'], '?') !== 0) {
                $a['query'] = '?' . str_replace('&amp;', '&', $a['query']);
            }
            if (isset($a['fragment'])) {
                if (strpos($a['fragment'], '#') !== 0) {
                    $a['fragment'] = '#' . $a['fragment'];
                }
                $a['hash'] = $a['fragment'];
            }
            unset($a['fragment']);
            $this->lot = $a;
        } else {
            $this->lot = __url__();
        }
        parent::__construct();
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return isset($this->lot[$key]) ? $this->lot[$key] : null;
    }

    public function __unset($key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return $this->lot['url'];
    }

}