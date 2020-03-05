<?php

class File extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    const state = [
        // Range of allowed file size(s)
        'size' => [0, 2097152],
        // List of allowed file type(s)
        'type' => [
            'application/javascript' => 1,
            'application/json' => 1,
            'application/xml' => 1,
            'image/gif' => 1,
            'image/jpeg' => 1,
            'image/png' => 1,
            'inode/x-empty' => 1, // Empty file
            'text/css' => 1,
            'text/html' => 1,
            'text/javascript' => 1,
            'text/plain' => 1,
            'text/xml' => 1
        ],
        // List of allowed file extension(s)
        'x' => [
            'css' => 1,
            'gif' => 1,
            'htm' => 1,
            'html' => 1,
            'jpe' => 1,
            'jpg' => 1,
            'jpeg' => 1,
            'js' => 1,
            'json' => 1,
            'log' => 1,
            'png' => 1,
            'txt' => 1,
            'xml' => 1
        ]
    ];

    public $exist;
    public $path;
    public $value;

    public function __construct($path = null) {
        $this->value[0] = "";
        if ($path && is_string($path) && 0 === strpos($path, ROOT)) {
            $path = strtr($path, '/', DS);
            if (!is_file($path)) {
                if (!is_dir($d = dirname($path))) {
                    mkdir($d, 0775, true);
                }
                @touch($path); // Create an empty file
            }
            $this->path = is_file($path) ? (realpath($path) ?: $path) : null;
        }
        $this->exist = !!$this->path;
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new \ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return null;
    }

    public function __toString() {
        return $this->exist ? $this->path : "";
    }

    public function _seal() {
        return $this->exist ? fileperms($this->path) : null;
    }

    public function _size() {
        return $this->exist ? filesize($this->path) : null;
    }

    public function URL() {
        return $this->exist ? To::URL($this->path) : null;
    }

    public function content() {
        if ($this->exist) {
            $content = file_get_contents($this->path);
            return false !== $content ? $content : null;
        }
        return null;
    }

    public function copy(string $to, string $as = null) {
        $out = [null];
        if ($this->exist && $path = $this->path) {
            $out[0] = $path;
            if (is_file($f = $to . DS . ($as ?? basename($path)))) {
                // Return `false` if file already exists
                $out[1] = false;
            } else {
                if (!is_dir($d = dirname($f))) {
                    mkdir($d, 0775, true);
                }
                // Return `$f` on success, `null` on error
                $out[1] = copy($path, $f) ? $f : null;
            }
        }
        $this->value[1] = $out;
        return $this;
    }

    public function count() {
        return $this->exist ? 1 : 0;
    }

    public function get(...$lot) {
        $i = $lot[0] ?? 0;
        if ($this->exist) {
            $out = null;
            foreach ($this->stream() as $k => $v) {
                if ($k === $i) {
                    $out = $v;
                    break;
                }
            }
            return $out;
        }
        return null;
    }

    public function getIterator() {
        return $this->stream();
    }

    public function jsonSerialize() {
        return [$this->path => 1];
    }

    public function let() {
        if ($this->exist) {
            // Return `$path` on success, `null` on error
            $out = unlink($path = $this->path) ? $path : null;
        } else {
            // Return `false` if file does not exist
            $out = false;
        }
        $this->value[1] = $out;
        return $this;
    }

    public function move(string $to, string $as = null) {
        $out = [null];
        if ($this->exist && $path = $this->path) {
            $out[0] = $path;
            if (is_file($f = $to . DS . ($as ?? basename($path)))) {
                // Return `false` if file already exists
                $out[1] = false;
            } else {
                if (!is_dir($d = dirname($f))) {
                    mkdir($d, 0775, true);
                }
                // Return `$f` on success, `null` on error
                $out[1] = rename($path, $f) ? $f : null;
            }
        }
        $this->value[1] = $out;
        return $this;
    }

    public function name($x = false) {
        if ($this->exist && $path = $this->path) {
            if (true === $x) {
                return basename($path);
            }
            return pathinfo($path, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
        }
        return null;
    }

    public function offsetExists($i) {
        return !!$this->offsetGet($i);
    }

    public function offsetGet($i) {
        return $this->__get($i);
    }

    public function offsetSet($i, $value) {}
    public function offsetUnset($i) {}

    public function parent() {
        if ($this->exist) {
            $parent = dirname($this->path);
            return '.' !== $parent ? $parent : null;
        }
        return null;
    }

    public function save($seal = null) {
        $out = false; // Return `false` if `$this` is just a placeholder
        if ($path = $this->path) {
            if (isset($seal)) {
                $this->seal($seal);
            }
            // Return `$path` on success, `null` on error
            $out = file_put_contents($path, $this->value[0]) ? $path : null;
        } else if (defined('DEBUG') && DEBUG) {
            $c = static::class;
            throw new \Exception('Please provide a file path even if it does not exist. Example: `new ' . $c . '(\'' . ROOT . DS . c2f($c) . '.txt\')`');
        }
        $this->value[1] = $out;
        return $this;
    }

    public function seal($i = null) {
        if (isset($i)) {
            if ($this->exist) {
                $i = is_string($i) ? octdec($i) : $i;
                // Return `$i` on success, `null` on error
                $this->value[1] = @chmod($this->path, $i) ? $i : null;
            } else {
                // Return `false` if file does not exist
                $this->value[1] = false;
            }
            return $this;
        }
        return null !== ($i = $this->_seal()) ? substr(sprintf('%o', $i), -4) : null;
    }

    public function set(...$lot) {
        $this->value[0] = $lot[0] ?? "";
        return $this;
    }

    public function size(string $unit = null, int $r = 2) {
        if ($this->exist && is_file($path = $this->path)) {
            return self::sizer(filesize($path), $unit, $r);
        }
        return null;
    }

    public function stream() {
        return $this->exist ? stream($this->path) : yield from [];
    }

    public function time(string $format = null) {
        if ($this->exist) {
            $t = filectime($this->path);
            return $format ? (new Time($t))($format) : $t;
        }
        return null;
    }

    public function type() {
        return $this->exist ? mime_content_type($this->path) : null;
    }

    public function x() {
        if ($this->exist) {
            $path = $this->path;
            if (false === strpos($path, '.')) {
                return null;
            }
            $x = pathinfo($path, PATHINFO_EXTENSION);
            return $x ? strtolower($x) : null;
        }
        return false; // Return `false` if file does not exist
    }

    public static $state = self::state;

    public static function exist($path) {
        if (is_array($path)) {
            foreach ($path as $v) {
                if ($v && is_file($v)) {
                    return realpath($v);
                }
            }
            return false;
        }
        return is_file($path) ? realpath($path) : false;
    }

    public static function pull($path, string $name = null) {
        if (is_string($path) && is_file($path)) {
            http_response_code(200);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . ($name ?? basename($path)) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
        http_response_code(404);
        exit;
    }

    public static function push(array $blob, string $folder = ROOT) {
        if (!empty($blob['error'])) {
            return $blob['error']; // Return the error code
        }
        $folder = strtr($folder, '/', DS);
        if (is_file($path = $folder . DS . $blob['name'])) {
            return false; // Return `false` if file already exists
        }
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }
        if (move_uploaded_file($blob['tmp_name'], $path)) {
            return $path; // Return `$path` on success
        }
        return null; // Return `null` on error
    }

    public static function sizer(float $size, string $unit = null, int $r = 2) {
        $i = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        $u = $unit ? array_search($unit, $x) : ($size > 0 ? floor($i) : 0);
        $out = round($size / pow(1024, $u), $r);
        return $out < 0 ? null : trim($out . ' ' . $x[$u]);
    }

}
