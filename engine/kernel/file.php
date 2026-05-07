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
        if (!is_string($path = $this->path)) {
            return null;
        }
        // Even with the `finfo` feature available, the return type value is sometimes still messed up :(
        static $fix = [
            // <https://www.rfc-editor.org/rfc/rfc4287.html#section-2>
            'atom' => 'application/atom+xml',
            // <https://www.rfc-editor.org/rfc/rfc2318.html#section-4>
            'css' => 'text/css',
            // <https://www.rfc-editor.org/rfc/rfc4180.html#section-3>
            'csv' => 'text/csv',
            'html' => 'text/html',
            // <https://www.rfc-editor.org/rfc/rfc9239.html#section-6.1>
            'js' => 'text/javascript',
            // <https://www.rfc-editor.org/rfc/rfc8259.html#section-1.2>
            'json' => 'application/json',
            // <https://www.rfc-editor.org/rfc/rfc7519.html#section-10.3.1>
            'jwt' => 'application/jwt',
            // <https://www.rfc-editor.org/rfc/rfc7763.html#section-2>
            'markdown' => 'text/markdown',
            'md' => 'text/markdown',
            // <https://www.rfc-editor.org/rfc/rfc9239.html#section-6>
            'mjs' => 'text/javascript',
            // <https://www.php.net/manual/en/install.unix.apache2.php>
            'php' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'phtm' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            // <https://www.iana.org/assignments/media-types/image/svg+xml>
            'svg' => 'image/svg+xml',
            // <https://www.iana.org/assignments/media-types/text/tab-separated-values>
            'tsv' => 'text/tab-separated-values',
            // <https://www.iana.org/assignments/media-types/text/vtt>
            'vtt' => 'text/vtt',
            // <https://www.rfc-editor.org/rfc/rfc7303.html#section-4.1>
            'xml' => 'application/xml',
            // <https://www.rfc-editor.org/rfc/rfc9512.html#section-2>
            'yaml' => 'application/yaml',
            'yml' => 'application/yaml'
        ];
        if (isset($fix[$x = $this->x() ?? 0])) {
            return $fix[$x];
        }
        if (function_exists('finfo_open')) {
            static $c = [];
            static $f;
            if (isset($c[$path])) {
                return $c[$path];
            }
            $f ??= finfo_open(FILEINFO_MIME_TYPE);
            $c[$path] = $type = finfo_file($f, $path);
        } else {
            $type = mime_content_type($path);
        }
        return is_string($type) && "" !== $type ? $type : null;
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