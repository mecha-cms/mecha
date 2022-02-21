<?php

class Folder extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    public $path;

    public function __construct(string $path = null) {
        if ($path && is_string($path) && 0 === strpos($path, PATH)) {
            $this->path = stream_resolve_include_path($path) ?: null;
        }
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new \ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return null;
    }

    public function __isset(string $key) {
        return null !== $this->__get($key);
    }

    public function __toString() {
        return $this->exist() ? $this->path : "";
    }

    public function _seal() {
        return $this->exist() ? fileperms($this->path) : null;
    }

    public function _size() {
        if ($this->exist()) {
            // Empty folder
            if (0 === q(g($this->path, 1))) {
                return 0;
            }
            // Scan all file(s) to get the total size
            $size = 0;
            foreach (g($this->path, 1, true) as $k => $v) {
                $size += filesize($k);
            }
            return $size;
        }
        return null;
    }

    public function URL() {
        return $this->exist() ? To::URL($this->path) : null;
    }

    public function content(string $path) {
        return null;
    }

    public function count(): int {
        // Count file(s) only
        return $this->exist() ? q(g($this->path, 1, true)) : 0;
    }

    public function exist() {
        return !!$this->path;
    }

    public function getIterator(): \Traversable {
        return $this->stream(null, true, true);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        $out = [$this->path => 0];
        foreach ($this->stream(null, true) as $k => $v) {
            $out[$k] = $v;
        }
        return $out;
    }

    public function offsetExists($key): bool {
        return !!$this->offsetGet($key);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key) {
        return $this->__get($key);
    }

    public function offsetSet($key, $value): void {}
    public function offsetUnset($key): void {}

    public function seal() {
        return null !== ($seal = $this->_seal()) ? substr(sprintf('%o', $seal), -4) : null;
    }

    public function name() {
        return $this->exist() ? basename($this->path) : null;
    }

    public function size(string $unit = null, int $round = 2) {
        if (null !== ($size = $this->_size())) {
            return size($size, $unit, $round);
        }
        return null;
    }

    public function stream($x = null, $deep = 0, $content = false): \Generator {
        if ($content) {
            foreach (g($this->path, $x, $deep) as $k => $v) {
                yield $k => 0 === $v ? [] : file_get_contents($k);
            }
        } else {
            yield from g($this->path, $x, $deep);
        }
    }

    public function time(string $format = null) {
        if ($this->exist()) {
            $time = filectime($this->path);
            return $format ? (new Time($time))($format) : $time;
        }
        return null;
    }

    public function type() {
        return null;
    }

    public function x() {
        return null;
    }

}