<?php

final class URL extends Genome implements \ArrayAccess {

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

    private function e($in) {
        return "" !== $in ? $in : null;
    }

    private function setClean($in) {
        $this->lot['clean'] = $this->e(trim(strtr($in, DS, '/'), '/'));
    }

    private function setCurrent($in) {
        $this->lot['current'] = $this->e(trim(strtr($in, DS, '/'), '/'));
    }

    private function setD($in) {
        $this->lot['d'] = $this->e(trim(strtr($in, DS, '/'), '/'));
    }

    private function setGround($in) {
        $this->lot['ground'] = $this->e(trim(strtr($in, DS, '/'), '/'));
    }

    private function setHash($in) {
        $this->lot['hash'] = $this->e(ltrim($in, '#'));
    }

    private function setHost($in) {
        $this->lot['host'] = $this->e(trim(strtr($in, DS, '/'), '/'));
    }

    private function setI($in) {
        $i = trim(strtr($in, DS, '/'), '/');
        $i = is_numeric($i) ? (int) $i : null;
        $this->lot['i'] = $i <= 0 ? null : $i;
    }

    private function setPath($in) {
        $this->lot['path'] = $this->e(trim(strtr($in, DS, '/'), '/'));
    }

    private function setPort($in) {
        $this->lot['port'] = is_numeric($in) ? (int) $in : null;
    }

    private function setProtocol($in) {
        $this->lot['protocol'] = $this->e(explode('://', strtr($in, DS, '/'))[0]);
    }

    private function setQuery($in) {
        $this->lot['query'] = $this->e(ltrim(strtr($in, ['&amp;' => '&']), '?'));
    }

    private function setRoot($in) {
        $this->lot['root'] = $this->e(trim(strtr($in, DS, '/'), '/'));
    }

    // private function getClean() {
    //     return $this->lot['clean'];
    // }

    // private function getCurrent() {
    //     return $this->lot['current'];
    // }

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

    public function __construct($in = null) {
        if (is_string($in)) {
            $out = array_replace($this->lot, parse_url($in));
            $out['protocol'] = $this->e($out['scheme'] ?? "");
            $out['path'] = $this->e($out['path'] ?? "");
            $out['port'] = isset($out['port']) ? (int) $out['port'] : null;
            $out['query'] = $this->e($out['query'] ?? "");
            $out['hash'] = $this->e($out['fragment'] ?? "");
            unset($out['fragment'], $out['scheme']);
            $out['ground'] = $out['root'] = $out['protocol'] . '://' . $out['host'];
            $out['clean'] = preg_split('/[?&#]/', $in)[0];
            $out['current'] = $in;
            $this->lot = $out;
        } else if (is_array($in)) {
            foreach ($in as $k => $v) {
                if (method_exists($this, $set = 'set' . ucfirst($k))) {
                    $this->{$set}($v);
                } else {
                    $this->lot[$k] = $this->e($v);
                }
            }
        }
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
    }

    public function __toString() {
        return (string) $this->lot['root'];
    }

    public function __unset(string $key) {
        unset($this->lot[$key]);
    }

    // `$url->d('.')`
    public function d(string $join = '/') {
        $d = $this->lot['d'];
        return null !== $d ? $join . $d : null;
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

    public function offsetExists($i) {
        return isset($this->lot[$i]);
    }

    public function offsetGet($i) {
        return $this->lot[$i];
    }

    public function offsetSet($i, $value) {
        $this->lot[$i] = $value;
    }

    public function offsetUnset($i) {
        unset($this->lot[$i]);
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