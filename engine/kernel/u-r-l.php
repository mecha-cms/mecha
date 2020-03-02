<?php

final class URL extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    private $lot = [
        'clean' => null,
        'current' => null,
        'd' => null,
        'ground' => null,
        'hash' => null,
        'host' => null,
        'i' => null,
        'path' => null,
        'port' => null,
        'protocol' => null,
        'query' => null,
        'root' => null
    ];

    private function e($in, $t = '/') {
        $in = strtr($in, DS, '/');
        if ($t) {
            $in = trim($in, $t);
        }
        return "" !== $in ? $in : null;
    }

    private function t($in) {
        return strtr($in, [
            '/?' => '?',
            '/&' => '?',
            '/#' => '#'
        ]);
    }

    // Refresh clean URL result
    private function fireClean() {
        $this->fireRoot();
        extract($this->lot);
        $this->setClean($root . (isset($path) ? '/' . $path : ""));
    }

    // Refresh current URL result
    private function fireCurrent() {
        $this->fireClean();
        extract($this->lot);
        $this->setCurrent(
            ($ground ?? "") .
            (isset($port) ? ':' . $port : "") .
            (isset($d) ? '/' . $d : "") .
            (isset($path) ? '/' . $path : "") .
            (isset($i) ? '/' . $i : "") .
            (isset($query) ? '?' . $query : "") .
            (isset($hash) ? '#' . $hash : "")
        );
    }

    // Refresh ground URL result
    private function fireGround() {
        extract($this->lot);
        $this->setGround((isset($protocol) ? $protocol . '://' : '//') . ($host ?? "") . (isset($port) ? ':' . $port : ""));
    }

    // Refresh root URL result
    private function fireRoot() {
        $this->fireGround();
        extract($this->lot);
        $this->setRoot(($ground ?? "") . (isset($d) ? '/' . $d : ""));
    }

    private function getClean() {
        $out = $this->lot['clean'];
        return false === strpos($out, '://') ? $this->t('/' . $out) : $out;
    }

    private function getCurrent() {
        $out = $this->lot['current'];
        return false === strpos($out, '://') ? $this->t('/' . $out) : $out;
    }

    private function getD() {
        return null !== ($out = $this->lot['d']) ? '/' . $out : $out;
    }

    // private function getGround() {
    //     return $this->lot['ground'];
    // }

    private function getHash() {
        return null !== ($out = $this->lot['hash']) ? '#' . $out : $out;
    }

    // private function getHost() {
    //     return $this->lot['host'];
    // }

    private function getI() {
        return null !== ($out = $this->lot['i']) ? '/' . $out : $out;
    }

    private function getPath() {
        return null !== ($out = $this->lot['path']) ? '/' . $out : $out;
    }

    // private function getPort() {
    //     return $this->lot['port'];
    // }

    private function getProtocol() {
        return null !== ($out = $this->lot['protocol']) ? $out . '://' : $out;
    }

    private function getQuery() {
        return null !== ($out = $this->lot['query']) ? '?' . $out : $out;
    }

    // private function getRoot() {
    //     return $this->lot['root'];
    // }

    private function setClean($in) {
        $this->lot['clean'] = $this->e($in);
    }

    private function setCurrent($in) {
        $this->lot['current'] = $this->e($this->t($in));
    }

    private function setD($in) {
        $this->lot['d'] = $this->e($in);
    }

    private function setGround($in) {
        $this->lot['ground'] = $this->e($in);
    }

    private function setHash($in) {
        $this->lot['hash'] = $this->e(ltrim($in, '#'), false);
    }

    private function setHost($in) {
        $this->lot['host'] = $this->e($in);
    }

    private function setI($in) {
        $in = $this->e($in);
        $this->lot['i'] = is_numeric($in) ? (int) $in : null;
    }

    private function setPath($in) {
        $this->lot['path'] = $this->e($in);
    }

    private function setPort($in) {
        $in = $this->e($in);
        $this->lot['port'] = is_numeric($in) ? (int) $in : null;
    }

    private function setProtocol($in) {
        $this->lot['protocol'] = $this->e(explode('://', strtr($in, DS, '/'))[0]);
    }

    private function setQuery($in) {
        $this->lot['query'] = $this->e(ltrim(strtr($in, ['&amp;' => '&']), '?'), false);
    }

    private function setRoot($in) {
        $this->lot['root'] = $this->e($in);
    }

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__call($kin, $lot);
        }
        if (method_exists($this, $get = 'get' . ucfirst($kin))) {
            return $this->{$get}(...$lot);
        }
        if (array_key_exists($kin, $this->lot)) {
            return "" !== $this->lot[$kin] ? $this->lot[$kin] : null;
        }
        return null;
    }

    public function __construct(string $in = null, string $d = null, int $i = null) {
        if ($in && 0 === strpos($in, '//')) {
            $in = 'http:' . $in; // Force protocol
        }
        $out = array_replace($this->lot, parse_url($in));
        $d = $this->e($d);
        $i = $this->e((string) ($i > 0 ? $i : ""));
        $path = $this->e($out['path'] ?? "");
        if (null !== $path && is_numeric(substr($path, -1)) && preg_match('/(.*?)\/([1-9][0-9]*)$/', $path, $m)) {
            $path = $m[1];
            $i = (int) ($i ?? $m[2]); // Set page offset from path
        }
        $path = $this->e(null !== $d && null !== $path && 0 === strpos($path . '/', $d . '/') ? substr($path, strlen($d)) : $path);
        $this->setD($d);
        $this->setHash($out['fragment'] ?? "");
        $this->setHost($out['host']);
        $this->setI($i);
        $this->setPath($path);
        $this->setPort($out['port'] ?? "");
        $this->setProtocol($out['scheme'] ?? "");
        $this->setQuery($out['query'] ?? "");
        $this->fireCurrent(); // Refresh
    }

    public function __get(string $key) {
        return $this->__call($key);
    }

    // Fix case for `isset($url->key)` or `!empty($url->key)`
    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value = null) {
        if (method_exists($this, $set = 'set' . ucfirst($key))) {
            $this->{$set}($value);
        } else {
            $this->lot[$key] = $value;
        }
        $this->fireCurrent(); // Refresh
    }

    public function __toString() {
        return (string) $this->lot['root'];
    }

    public function __unset(string $key) {
        unset($this->lot[$key]);
        $this->fireCurrent(); // Refresh
    }

    public function count() {
        return count($this->lot);
    }

    // `$url->d('.')`
    public function d(string $join = '/') {
        $d = $this->lot['d'];
        return null !== $d ? $join . $d : null;
    }

    public function getIterator() {
        foreach ($this->lot as $k => $v) {
            yield $k => $this->{$k}();
        }
    }

    // `$url->hash('#!')`
    public function hash(string $join = '#') {
        $hash = $this->lot['hash'];
        return $hash ? $join . $hash : null;
    }

    // `$url->i('.', 4)`
    public function i(string $join = '/', int $j = 0) {
        $i = $this->lot['i'];
        return null !== $i ? $join . ($i + $j) : null;
    }

    public function jsonSerialize() {
        $out = [];
        foreach ($this->lot as $k => $v) {
            $out[$k] = $this->{$k}();
        }
        return $out;
    }

    public function offsetExists($i) {
        return isset($this->lot[$i]);
    }

    public function offsetGet($i) {
        return $this->lot[$i];
    }

    public function offsetSet($i, $value) {
        $this->lot[$i] = $value;
        $this->fireCurrent(); // Refresh
    }

    public function offsetUnset($i) {
        unset($this->lot[$i]);
        $this->fireCurrent(); // Refresh
    }

    // `$url->path('.')`
    public function path(string $join = '/', array $p = []) {
        $path = $this->lot['path'];
        if (!empty($p)) {
            $path = array_replace(explode('/', $path), $p);
            $path = implode($join, $path);
        } else {
            $path = strtr($path, ['/' => $join]);
        }
        return "" !== $path ? $join . $path : null;
    }

    // `$url->query('&amp;')`
    public function query(string $join = '&', array $q = []) {
        $query = $this->lot['query'] . "";
        if (!empty($q)) {
            $query = From::query($query);
            $query = array_replace_recursive($query, $q);
            return strtr(To::query($query), ['&' => $join]);
        }
        return "" !== $query ? '?' . strtr($query, ['&' => $join]) : null;
    }

}
