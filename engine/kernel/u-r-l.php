<?php

final class URL extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    private $lot = [
        'hash' => null,
        'host' => null,
        'path' => null,
        'protocol' => null,
        'query' => null
    ];

    private function getHash() {
        $hash = $this->lot['hash'];
        if (null !== $hash) {
            return '#' . $hash;
        }
        return null;
    }

    private function getHost() {
        return $this->lot['host'];
    }

    private function getPath() {
        $path = $this->lot['path'];
        if (null !== $path) {
            return '/' . $path;
        }
        return null;
    }

    private function getProtocol() {
        $protocol = $this->lot['protocol'];
        if (null !== $protocol) {
            return $protocol . '://';
        }
        return null;
    }

    private function getQuery() {
        $query = $this->lot['query'];
        if (null !== $query) {
            return '?' . $query;
        }
        return null;
    }

    private function setHash(string $hash) {
        if ($hash && '#' === $hash[0]) {
            $hash = substr($hash, 1);
        }
        $hash = strtr($hash, [
            '#' => '%23',
            '&' => '%26',
            '?' => '%3F'
        ]);
        $this->lot['hash'] = "" !== $hash ? $hash : null;
    }

    private function setHost(string $host) {
        $this->lot['host'] = $host;
    }

    private function setPath(string $path) {
        $path = trim(strtr($path, [
            "\\" => '/',
            '#' => '%23',
            '&' => '%26',
            '?' => '%3F'
        ]), '/');
        $this->lot['path'] = "" !== $path ? $path : null;
    }

    private function setProtocol(string $protocol) {
        $protocol = strtok($protocol, ':');
        $this->lot['protocol'] = "" !== $protocol ? $protocol : null;
    }

    private function setQuery(string $query) {
        if ($query && ('&' === $query[0] || '?' === $query[0])) {
            $query = substr($query, 1);
        }
        $query = strtr($query, [
            '#' => '%23',
            '?' => '%3F'
        ]);
        $this->lot['query'] = "" !== $query ? $query : null;
    }

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__call($kin, $lot);
        }
        if (method_exists($this, $get = 'get' . ucfirst($kin))) {
            return $this->{$get}(...$lot);
        }
        return null;
    }

    public function __construct(string $value = null) {
        if ($value && 0 === strpos($value, '//')) {
            $value = 'http:' . $value; // Force protocol
        }
        extract(parse_url($value), EXTR_SKIP);
        $this->setHash($fragment ?? "");
        $this->setHost($host ?? "");
        $this->setPath($path ?? "");
        $this->setProtocol($scheme ?? "");
        $this->setQuery($query ?? "");
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new \ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return $this->__call($key);
    }

    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value = null) {
        if (method_exists($this, $set = 'set' . ucfirst($key))) {
            $this->{$set}($value);
        }
    }

    public function __toString() {
        return (string) ($this->getProtocol() . $this->getHost());
    }

    public function __unset(string $key) {
        if (array_key_exists($key, $this->lot)) {
            $this->lot[$key] = null;
        }
    }

    public function count(): int {
        return count($this->lot);
    }

    public function current($query = [], $hash = true) {
        if (true === $hash) {
            $hash = $this->getHash();
        } else if (is_string($hash)) {
            $hash = '#' . strtr($hash, [
                '#' => '%23',
                '&' => '%26',
                '?' => '%3F'
            ]);
        } else {
            $hash = "";
        }
        if (true === $query) {
            $query = $this->query();
        } else if (is_array($query)) {
            $query = $this->query($query);
        } else if (is_string($query)) {
            $query = '?' . strtr($query, [
                '#' => '%23',
                '?' => '%3F'
            ]);
        } else {
            $query = "";
        }
        return $this->__toString() . $this->getPath() . $query . $hash;
    }

    public function getIterator(): \Traversable {
        foreach ($this->lot as $k => $v) {
            yield $k => $this->{'get' . ucfirst($k)}();
        }
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->lot;
    }

    public function offsetExists($key): bool {
        return isset($this->lot[$key]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key) {
        return $this->lot[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        $this->{'set' . ucfirst($key)}($value);
    }

    public function offsetUnset($key): void {
        $this->__unset($key);
    }

    public function path(array $lot = []) {
        $path = $this->lot['path'] . "";
        if ($lot) {
            $path = implode('/', array_replace(explode('/', $path), $lot));
        }
        return "" !== $path ? '/' . $path : null;
    }

    public function query(array $lot = []) {
        $query = $this->lot['query'] . "";
        if ($lot) {
            return To::query(array_replace_recursive(From::query($query), $lot));
        }
        return "" !== $query ? '?' . $query : null;
    }

}