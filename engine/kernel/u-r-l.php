<?php

final class URL extends Genome {

    private $lot = [];

    private function _hash(?string $v = null) {
        if ("" === ($v = (string) $v)) {
            $v = $this->lot['hash'] ?? "";
            if ("" !== $v && is_string($v)) {
                return '#' . $v;
            }
            return null;
        }
        $this->lot['hash'] = strtr(trim($v, '#'), [
            '#' => '%23',
            '&' => '%26',
            '?' => '%3F'
        ]);
    }

    private function _host(?string $v = null) {
        if ("" === ($v = (string) $v)) {
            $v = $this->lot['host'] ?? "";
            if ("" !== $v && is_string($v)) {
                return $v;
            }
            return null;
        }
        $this->lot['host'] = strstr($v . ':', ':', true);
    }

    private function _path(?string $v = null) {
        if ("" === ($v = (string) $v)) {
            $v = $this->lot['path'] ?? "";
            if ("" !== $v && is_string($v)) {
                return '/' . $v;
            }
            return null;
        }
        $this->lot['path'] = strtr(trim($v, '/'), [
            "\\" => '/',
            '#' => '%23',
            '&' => '%26',
            '?' => '%3F'
        ]);
    }

    private function _port(?string $v = null) {
        if ("" === ($v = (string) $v)) {
            $v = $this->lot['port'] ?? "";
            if ("" !== $v && is_string($v)) {
                return ':' . $v;
            }
            return null;
        }
        $this->lot['port'] = $v;
    }

    #[Deprecated]
    private function _protocol(?string $v = null) {
        return $this->_scheme($v);
    }

    private function _query(?string $v = null) {
        if ("" === ($v = (string) $v)) {
            $v = $this->lot['query'] ?? "";
            if ("" !== $v && is_string($v)) {
                return '?' . $v;
            }
            return null;
        }
        $this->lot['query'] = strtr(trim($v, '&?'), [
            '#' => '%23',
            '?' => '%3F'
        ]);
    }

    private function _scheme(?string $v = null) {
        if ("" === ($v = (string) $v)) {
            $v = $this->lot['scheme'] ?? "";
            if ("" !== $v && is_string($v)) {
                return $v . '://';
            }
            return null;
        }
        $this->lot['scheme'] = strstr($v . ':', ':', true);
    }

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__call($kin, $lot);
        }
        if (method_exists($this, $key = '_' . $kin)) {
            return $this->{$key}(...$lot);
        }
        return null;
    }

    public function __construct(?string $value = null) {
        $value = (string) $value;
        if ($value && 0 === strpos($value, '//')) {
            $value = 'http:' . $value; // Force scheme
        }
        extract(parse_url($value), EXTR_SKIP);
        $this->_hash((string) ($fragment ?? ""));
        $this->_host((string) ($host ?? ""));
        $this->_path((string) ($path ?? ""));
        $this->_port((string) ($port ?? ""));
        $this->_query((string) ($query ?? ""));
        $this->_scheme((string) ($scheme ?? ""));
    }

    public function __get(string $key): mixed {
        if (method_exists($this, $key) && (new ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return $this->__call($key);
    }

    public function __isset(string $key): bool {
        return !!$this->__get($key);
    }

    public function __serialize(): array {
        return ['lot' => $this->lot ?? []];
    }

    public function __set(string $key, $value = null): void {
        if (method_exists($this, $key = '_' . $key)) {
            $this->{$key}($value);
        }
    }

    public function __toString(): string {
        return (string) ($this->_scheme() . $this->_host() . $this->_port());
    }

    public function __unserialize(array $lot): void {
        $this->lot = $lot['lot'] ?? [];
    }

    public function __unset(string $key): void {
        unset($this->lot[$key]);
    }

    public function count(): int {
        return count($this->lot);
    }

    public function current($query = [], $hash = true) {
        if (true === $hash) {
            $hash = $this->_hash();
        } else if (is_string($hash)) {
            $hash = $this->_hash($hash)->_hash();
        } else {
            $hash = "";
        }
        if (true === $query) {
            $query = $this->_query();
        } else if (is_array($query)) {
            $query = $this->query($query);
        } else if (is_string($query)) {
            $query = $this->_query($query)->_query();
        } else {
            $query = "";
        }
        return $this->__toString() . $this->_path() . $query . $hash;
    }

    public function getIterator(): Traversable {
        foreach ($this->lot as $k => $v) {
            yield $k => $this->{'_' . $k}();
        }
    }

    public function offsetExists($key): bool {
        return isset($this->lot[$key]);
    }

    public function offsetGet($key): mixed {
        if ('port' === $key) {
            return (int) ($this->lot[$key] ?? 0);
        }
        return $this->lot[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        $this->{'_' . $key}($value);
    }

    public function offsetUnset($key): void {
        $this->__unset($key);
    }

    public function path(array $lot = []) {
        if ($lot) {
            $path = trim($this->_path() ?? "", '/');
            return '/' . implode('/', array_replace(explode('/', $path), $lot));
        }
        return $this->_path();
    }

    public function query(array $lot = []) {
        if ($lot) {
            return To::query(array_replace_recursive(From::query($this->_query()), $lot));
        }
        return $this->_query();
    }

}