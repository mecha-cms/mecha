<?php

class File extends Genome {

    public $path;

    public function __call(string $kin, array $lot = []) {
        if (property_exists($this, $kin) && (new ReflectionProperty($this, $kin))->isPublic()) {
            return $this->{$kin};
        }
        return parent::__call($kin, $lot);
    }

    public function __construct(?string $path = null) {
        if ($path && is_string($path) && 0 === strpos($path, PATH)) {
            $this->path = is_file($path = stream_resolve_include_path($path)) ? $path : null;
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
        return $this->exist() && null !== ($path = $this->path) ? filesize($path) : null;
    }

    public function URL() {
        return ($route = $this->route()) ? long($route) : null;
    }

    public function content() {
        if ($this->exist() && null !== ($path = $this->path)) {
            $content = file_get_contents($path);
            return false !== $content ? $content : null;
        }
        return null;
    }

    public function count(): int {
        if ($this->exist()) {
            $count = 0;
            foreach ($this->stream(null) as $v) {
                ++$count;
            }
            return $count;
        }
        return 0;
    }

    public function exist() {
        return $this->path ?? false;
    }

    public function getIterator(): Traversable {
        return $this->stream(null);
    }

    public function name(...$lot) {
        if ($this->exist() && null !== ($path = $this->path)) {
            if (true === ($x = array_shift($lot) ?? false)) {
                return basename($path);
            }
            return pathinfo($path, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
        }
        return null;
    }

    public function offsetExists($key): bool {
        return null !== $this->offsetGet($key);
    }

    public function offsetGet($key) {
        foreach ($this->stream(null) as $k => $v) {
            if ($key === $k) {
                return $v;
            }
        }
        return null;
    }

    public function parent() {
        return $this->exist() && null !== ($path = $this->path) ? new Folder(dirname($path)) : null;
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
        if ($this->exist() && null !== ($path = $this->path)) {
            return size(filesize($path), $unit, $fix, $base);
        }
        return null;
    }

    public function stream(?int $max = 1024): Traversable {
        yield from ($this->exist() && null !== ($path = $this->path) ? stream($path, $max) : []);
    }

    public function time(?string $format = null) {
        if ($this->exist() && null !== ($path = $this->path)) {
            $time = filectime($path);
            return $format ? (new Time($time))($format) : $time;
        }
        return null;
    }

    public function type() {
        if ($this->exist() && null !== ($path = $this->path)) {
            $type = mime_content_type($path);
            return false !== $type ? $type : null;
        }
        return null;
    }

    public function x() {
        if ($this->exist() && null !== ($path = $this->path)) {
            if (false === strpos(basename($path), '.')) {
                return null;
            }
            return strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }
        return null;
    }

    public static function __set_state(array $lot): object {
        return new static($lot['path'] ?? null);
    }

    public static function from(...$lot) {
        return new static(...$lot);
    }

}