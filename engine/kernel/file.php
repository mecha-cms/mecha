<?php

class File extends Proxy {

    public $path;

    public function __call(string $kin, array $lot = []) {
        return $this->__get__($kin) ? $this->{$kin} : parent::__call($kin, $lot);
    }

    public function __construct($path = null) {
        if (is_string($path = path($path)) && 0 === strpos($path, PATH . D)) {
            $this->path = is_file($path) ? $path : null;
        }
    }

    public function __get(string $key): mixed {
        return $this->__fire__($key) ? $this->{$key}() : $this->__call($key);
    }

    public function __isset(string $key): bool {
        return null !== $this->__get($key);
    }

    public function __serialize(): array {
        $lot = parent::__serialize();
        if (is_string($path = $lot['path'] ?? 0) && 0 === strpos($path, PATH . D)) {
            $lot['path'] = '.' . strtr(substr($path, strlen(PATH)), [D => "\\"]);
        }
        return $lot;
    }

    public function __toString(): string {
        if (is_string($path = $this->path)) {
            if (0 === strpos($path, PATH . D)) {
                return '.' . strtr(substr($path, strlen(PATH)), [D => "\\"]);
            }
        }
        return "";
    }

    public function __unserialize(array $lot): void {
        if (is_string($path = $lot['path'] ?? 0) && 0 === strpos($path, ".\\")) {
            $lot['path'] = PATH . strtr(substr($path, 1), ["\\" => D]);
        }
        parent::__unserialize($lot);
    }

    public function _seal() {
        return is_string($path = $this->path) ? (fileperms($path) & 0777) : null;
    }

    public function _size() {
        return is_string($path = $this->path) ? filesize($path) : null;
    }

    public function _time() {
        return is_string($path = $this->path) ? filectime($path) : null;
    }

    public function ID() {
        return ($route = $this->route()) ? sprintf('%u', crc32($route)) : null;
    }

    public function content() {
        if (is_string($path = $this->path) && false !== ($content = file_get_contents($path))) {
            return $content;
        }
        return null;
    }

    public function count(): int {
        return $this->path ? 1 : 0;
    }

    public function exist() {
        return $this->path ?? false;
    }

    public function link() {
        return ($route = $this->route()) ? new Link(long($route)) : null;
    }

    public function name(...$lot) {
        if (is_string($path = $this->path)) {
            if (true === ($x = array_shift($lot) ?? false)) {
                return basename($path);
            }
            return pathinfo($path, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
        }
        return null;
    }

    public function parent() {
        return is_string($path = $this->path) ? new Folder(dirname($path)) : null;
    }

    public function route() {
        return is_string($path = $this->path) ? '/' . strtr(rawurlencode(trim(strtr($path, [PATH . D => '/', D => '/']), '/')), ['%2F' => '/']) : null;
    }

    public function seal() {
        return is_int($seal = $this->_seal()) ? substr(sprintf('%o', $seal), -3) : null;
    }

    public function size(?string $unit = null, int $fix = 2, int $base = 1000) {
        return is_int($size = $this->_size()) ? size($size, $unit, $fix, $base) : null;
    }

    public function stream(?int $max = 1024): Traversable {
        return is_string($path = $this->path) ? stream($path, $max) : new EmptyIterator;
    }

    public function time(?string $pattern = null) {
        if (is_int($time = $this->_time())) {
            $t = new Time($time);
            return $pattern ? $t($pattern) : $t;
        }
        return null;
    }

    public function type() {
        return is_string($path = $this->path) && false !== ($type = mime_content_type($path)) ? $type : null;
    }

    public function x() {
        if (is_string($path = $this->path)) {
            return false !== strpos(basename($path), '.') ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
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