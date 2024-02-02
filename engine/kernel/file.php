<?php

class File extends Genome {

    public $path;

    public function __construct(string $path = null) {
        if ($path && is_string($path) && 0 === strpos($path, PATH)) {
            $this->path = stream_resolve_include_path($path) ?: null;
        }
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return null;
    }

    public function __isset(string $key): bool {
        return null !== $this->__get($key);
    }

    public function __toString(): string {
        if ($path = $this->path) {
            return strtr($path, [PATH . D => ".\\", D => "\\"]);
        }
        return "";
    }

    public function _seal() {
        return $this->exist() ? fileperms($this->path) : null;
    }

    public function _size() {
        return $this->exist() ? filesize($this->path) : null;
    }

    public function URL() {
        return ($route = $this->route()) ? long($route) : null;
    }

    public function content() {
        if ($this->exist()) {
            $content = file_get_contents($this->path);
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

    public function jsonSerialize() {
        return $this->__toString() ?: null;
    }

    public function name(...$lot) {
        if ($this->exist()) {
            $path = $this->path;
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
        return $this->exist() ? new Folder(dirname($this->path)) : null;
    }

    public function route() {
        if ($this->exist()) {
            return '/' . trim(strtr($this->path, [PATH . D => '/', D => '/']), '/');
        }
        return null;
    }

    public function seal() {
        return null !== ($seal = $this->_seal()) ? substr(sprintf('%o', $seal), -4) : null;
    }

    public function size(string $unit = null, int $fix = 2, int $base = 1000) {
        if ($this->exist()) {
            $path = $this->path;
            return size(filesize($path), $unit, $fix, $base);
        }
        return null;
    }

    public function stream(?int $max = 1024): Traversable {
        yield from ($this->exist() ? stream($this->path, $max) : []);
    }

    public function time(string $format = null) {
        if ($this->exist()) {
            $time = filectime($this->path);
            return $format ? (new Time($time))($format) : $time;
        }
        return null;
    }

    public function type() {
        if ($this->exist()) {
            $type = mime_content_type($this->path);
            return false !== $type ? $type : null;
        }
        return null;
    }

    public function x() {
        if ($this->exist()) {
            $path = $this->path;
            if (false === strpos(basename($path), '.')) {
                return null;
            }
            return strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }
        return null;
    }

    public static function from(...$lot) {
        return new static(...$lot);
    }

}