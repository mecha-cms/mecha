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
        return is_string($path = $this->path) ? strtr($path, [PATH . D => ".\\", D => "\\"]) : "";
    }

    public function __unserialize(array $lot): void {
        if (is_string($path = $lot['path'] ?? 0) && 0 === strpos($path, ".\\")) {
            $lot['path'] = PATH . D . strtr(substr($path, 2), ["\\" => D]);
        }
        parent::__unserialize($lot);
    }

    public function _seal() {
        return is_string($path = $this->path) ? (fileperms($path) & 0777) : null;
    }

    public function _size() {
        if (is_string($path = $this->path)) {
            // Scan all file(s) to get the total size
            $size = 0;
            foreach (g($path, 1, true) as $k => $v) {
                $size += filesize($k);
            }
            return $size;
        }
        return null;
    }

    public function _time() {
        return is_string($path = $this->path) ? filectime($path) : null;
    }

    public function URL() {
        return ($route = $this->route()) ? long($route) : null;
    }

    public function content() {
        return null;
    }

    public function count(): int {
        return $this->path ? 1 : 0;
    }

    public function exist() {
        return $this->path ?? false;
    }

    public function name() {
        return is_string($path = $this->path) ? basename($path) : null;
    }

    public function parent() {
        return is_string($path = $this->path) ? new static(dirname($path)) : null;
    }

    public function route() {
        return is_string($path = $this->path) ? '/' . trim(strtr($path, [PATH . D => '/', D => '/']), '/') : null;
    }

    public function seal() {
        return is_int($seal = $this->_seal()) ? substr(sprintf('%o', $seal), -3) : null;
    }

    public function size(?string $unit = null, int $fix = 2, int $base = 1000) {
        return is_int($size = $this->_size()) ? size($size, $unit, $fix, $base) : null;
    }

    public function stream($x = null, $deep = 0): Traversable {
        if (is_string($path = $this->path)) {
            foreach (g($path, $x, $deep) as $k => $v) {
                yield $k => 0 === $v ? new static($k) : new File($k);
            }
        }
        return new EmptyIterator;
    }

    public function time(?string $format = null) {
        if (is_int($time = $this->_time())) {
            $t = new Time($time);
            return $format ? $t($format) : $t;
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