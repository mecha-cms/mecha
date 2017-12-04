<?php

class URL extends Genome {

    public static function long($url, $root = true) {
        if (!is_string($url)) return $url;
        $a = $GLOBALS['URL'];
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
            return trim(($root && $b ? $a['protocol'] . $a['host'] : $a['url']) . '/' . self::__($url), '/');
        }
        return self::__($url);
    }

    public static function short($url, $root = true) {
        $a = $GLOBALS['URL'];
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
        if (strpos($url, ROOT) === 0 || strpos($url, DS) !== false) {
            $url = To::url($url);
        }
        return rtrim(rtrim(rtrim($url, '0123456789'), '-'), '/');
    }

    protected static function __($s) {
        return str_replace(['\\', '/?', '/&', '/#'], ['/', '?', '&', '#'], $s);
    }

    protected $lot = [];

    public static function __callStatic($kin, $lot = []) {
        if (self::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $a = $GLOBALS['URL'];
        $fail = array_shift($lot) ?: false;
        return array_key_exists($kin, $a) ? $a[$kin] : $fail;
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
            $this->lot = $GLOBALS['URL'];
        }
        parent::__construct();
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return isset($this->lot[$key]) ? $this->lot[$key] : null;
    }

    // Fix case for `isset($url->key)` or `!empty($url->key)`
    public function __isset($key) {
        return !!$this->__get($key);
    }

    public function __unset($key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return $this->lot['url'];
    }

}