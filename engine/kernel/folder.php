<?php

class Folder extends Genome {

    public $path;

    public function __call(string $kin, array $lot = []) {
        if (property_exists($this, $kin) && (new ReflectionProperty($this, $kin))->isPublic()) {
            return $this->{$kin};
        }
        return parent::__call($kin, $lot);
    }

    public function __construct($path = null) {
        if (is_string($path) && 0 === strpos($path, PATH)) {
            $this->path = is_dir($path = stream_resolve_include_path($path)) ? $path : null;
        }
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return $this->__call($key);
    }

    public function __isset(string $key): bool {
        return null !== $this->__get($key);
    }

    public function __serialize(): array {
        $lot = parent::__serialize();
        if (is_string($path = $lot['path'] ?? 0) && 0 !== strpos($path, ".\\")) {
            $lot['path'] = strtr($path, [PATH . D => ".\\", D => "\\"]);
        }
        return $lot;
    }

    public function __toString(): string {
        if ($path = $this->path) {
            return strtr($path, [PATH . D => ".\\", D => "\\"]);
        }
        return "";
    }

    public function __unserialize(array $lot): void {
        if (is_string($path = $lot['path'] ?? 0) && 0 === strpos($path, ".\\")) {
            $lot['path'] = PATH . D . strtr(substr($path, 2), ["\\" => D]);
        }
        parent::__unserialize($lot);
    }

    public function _seal() {
        return $this->exist() && null !== ($path = $this->path) ? fileperms($path) : null;
    }

    public function _size() {
        if ($this->exist() && null !== ($path = $this->path)) {
            // Empty folder
            if (0 === q(g($path, 1))) {
                return 0;
            }
            // Scan all file(s) to get the total size
            $size = 0;
            foreach (g($path, 1, true) as $k => $v) {
                $size += filesize($k);
            }
            return $size;
        }
        return null;
    }

    public function URL() {
        return ($route = $this->route()) ? long($route) : null;
    }

    public function content() {
        return null;
    }

    public function count(): int {
        // Count file(s) only
        return $this->exist() && null !== ($path = $this->path) ? q(g($path, 1, true)) : 0;
    }

    public function exist() {
        return $this->path ?? false;
    }

    public function getIterator(): Traversable {
        return $this->stream(null, true);
    }

    public function name() {
        return $this->exist() && null !== ($path = $this->path) ? basename($path) : null;
    }

    public function offsetExists($key): bool {
        return null !== $this->offsetGet($key);
    }

    public function offsetGet($key) {
        if ($this->exist() && null !== ($path = $this->path)) {
            $path .= D . ltrim(strtr($key, '/', D), D);
            if (is_dir($path)) {
                return new static($path);
            }
            if (is_file($path)) {
                return new File($path);
            }
        }
        return null;
    }

    public function parent() {
        return $this->exist() && null !== ($path = $this->path) ? new static(dirname($path)) : null;
    }

    public function route() {
        if ($this->exist() && null !== ($path = $this->path)) {
            return '/' . trim(strtr($path, [PATH . D => '/', D => '/']), '/');
        }
        return null;
    }

    public function seal() {
        return null !== ($seal = $this->_seal()) ? substr(sprintf('%o', $seal), -4) : null;
    }

    public function size(?string $unit = null, int $fix = 2, int $base = 1000) {
        if (null !== ($size = $this->_size())) {
            return size($size, $unit, $fix, $base);
        }
        return null;
    }

    public function stream($x = null, $deep = 0): Traversable {
        foreach (g($this->path, $x, $deep) as $k => $v) {
            yield $k => 0 === $v ? new static($k) : new File($k);
        }
    }

    public function time(?string $format = null) {
        if ($this->exist() && null !== ($path = $this->path)) {
            $time = filectime($path);
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

    public static function __set_state(array $lot): object {
        return new static($lot['path'] ?? null);
    }

    public static function from(...$lot) {
        return new static(...$lot);
    }

}