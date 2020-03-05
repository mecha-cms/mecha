<?php

class Folder extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    public $exist;
    public $path;
    public $value;

    public function __construct($path = null) {
        $this->value[0] = null;
        if ($path && is_string($path) && 0 === strpos($path, ROOT)) {
            $path = strtr($path, '/', DS);
            if (!is_dir($path)) {
                mkdir($path, 0775, true); // Create an empty folder
            }
            $this->path = is_dir($path) ? (realpath($path) ?: $path) : null;
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
        if ($this->exist) {
            // Empty folder(s)
            if (0 === q(g($this->path, 1))) {
                return 0;
            }
            // Scan all file(s) to get the total size
            $size = 0;
            foreach ($this->get(1, true) as $k => $v) {
                $size += filesize($k);
            }
            return $size;
        }
        return null;
    }

    public function URL() {
        return $this->exist ? To::URL($this->path) : null;
    }

    public function content(string $path) {
        if (is_file($file = $this->path . DS . ltrim(strtr($path, '/', DS), DS))) {
            $content = file_get_contents($file);
            return false !== $content ? $content : null;
        }
        return null;
    }

    public function count() {
        // Count file(s) only
        return iterator_count($this->stream(1, true));
    }

    public function getIterator() {
        return $this->stream(null, true, true);
    }

    public function offsetExists($i) {
        return !!$this->offsetGet($i);
    }

    public function offsetGet($i) {
        return $this->__get($i);
    }

    public function offsetSet($i, $value) {}
    public function offsetUnset($i) {}

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

    public function copy(string $to, string $as = null) {
        $out = [[]];
        if ($this->exist && $path = $this->path) {
            $to .= DS . ($as ?? basename($path));
            if (!is_dir($to)) {
                mkdir($to, 0775, true);
            }
            $out[1] = [];
            foreach (g($path, null, true) as $k => $v) {
                $out[0][] = $k;
                if (1 === $v) {
                    if (is_file($f = $to . strtr($k, [$path => ""]))) {
                        // Return `false` if file already exists
                        $out[1][] = false;
                    } else {
                        if (!is_dir($d = dirname($f))) {
                            mkdir($d, 0775, true);
                        }
                        // Return `$f` on success, `null` on error
                        $out[1][] = copy($k, $f) ? $f : null;
                    }
                } else if (0 === $v) {
                    if (is_dir($f = $to . strtr($k, [$path => ""]))) {
                        // Return `false` if folder already exists
                        $out[1][] = false;
                    } else {
                        // Return `$f` on success, `null` on error
                        // Use `mkdir()` instead of `copy()`
                        // The first argument to `copy()` cannot be a directory
                        $out[1][] = mkdir($f, 0775, true) ? $f : null;
                    }
                }
            }
        }
        $this->value[1] = $out;
        return $this;
    }

    public function get($x = null, $deep = 0) {
        return y($this->stream($x, $deep));
    }

    public function has(string $path) {
        return $this->exist && false !== stream_resolve_include_path($this->path . DS . ltrim(strtr($path, '/', DS), DS));
    }

    public function jsonSerialize() {
        $out = [$this->path => 0];
        foreach ($this->stream(null, true) as $k => $v) {
            $out[$k] = $v;
        }
        return $out;
    }

    public function move(string $to, string $as = null) {
        $out = [[]];
        if ($this->exist && $path = $this->path) {
            $to .= DS . ($as ?? basename($path));
            if (!is_dir($to)) {
                mkdir($to, 0775, true);
            }
            $out[1] = [];
            foreach (g($path, null, true) as $k => $v) {
                $out[0][] = $k;
                if (1 === $v) {
                    if (is_file($f = $to . strtr($k, [$path => ""]))) {
                        // Return `false` if file already exists
                        $out[1][] = false;
                    } else {
                        if (!is_dir($d = dirname($f))) {
                            mkdir($d, 0775, true);
                        }
                        $out[1][] = rename($k, $f) ? $f : null;
                    }
                } else if (0 === $v) {
                    if (is_dir($f = $to . strtr($k, [$path => ""]))) {
                        // Return `false` if folder already exists
                        $out[1][] = false;
                    } else {
                        $out[1][] = rename($k, $f) ? $f : null;
                    }
                    // Remove empty folder
                    if (is_dir($k) && 0 === q(g($k))) {
                        rmdir($k);
                    }
                }
            }
            // Remove empty folder
            rmdir($path);
        }
        $this->value[1] = $out;
        return $this;
    }

    public function name() {
        return $this->exist ? basename($this->path) : null;
    }

    public function parent() {
        return $this->exist ? dirname($this->path) : null;
    }

    public function size(string $unit = null, int $r = 2) {
        if (null !== ($size = $this->_size())) {
            return File::sizer($size, $unit, $r);
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
        if ($this->exist) {
            $t = filectime($this->path);
            return $format ? strftime($format, $t) : $t;
        }
        return null;
    }

    public function type() {
        return null;
    }

    public function x() {
        return null;
    }

    public static function exist($path) {
        if (is_array($path)) {
            foreach ($path as $v) {
                if ($v && is_dir($v)) {
                    return realpath($v);
                }
            }
            return false;
        }
        return is_dir($path) ? realpath($path) : false;
    }

    public function let($any = true) {
        $out = [];
        if ($this->exist) {
            $path = $this->path;
            if (true === $any) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $k) {
                    $v = $k->getPathname();
                    if ($k->isDir()) {
                        $out[$v] = rmdir($v) ? 0 : null;
                    } else {
                        $out[$v] = unlink($v) ? 1 : null;
                    }
                }
                $out[$path] = rmdir($path) ? 0 : null;
            } else  {
                foreach ((array) $any as $v) {
                    $v = $path . DS . strtr($v, '/', DS);
                    if (is_file($v)) {
                        $out[$v] = unlink($v) ? 1 : null;
                    } else {
                        $out[$v] = (new static($v))->let() ? 0 : null;
                    }
                }
            }
        }
        $this->value[1] = $out;
        return $this;
    }

}
