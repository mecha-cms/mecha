<?php

class Page extends File {

    protected $a;
    protected $h;
    protected $lot;
    protected $read;

    protected function _get(string $kin, array $lot = []) {
        $v = $this->offsetGet($kin) ?? $this->a[$kin] ?? null;
        if (empty($this->read[$kin]) || 2 !== $this->read[$kin]) {
            // Do the hook once!
            $v = Hook::fire(map($this->h, function($v) use($kin) {
                return $v .= '.' . $kin;
            }), [$v, $lot], $this);
            if ($lot && is_callable($v) && !is_string($v)) {
                $v = call_user_func($v, ...$lot);
            }
            // Done hook
            $this->read[$kin] = 2;
            // Set again to be used later…
            $this->lot[$kin] = $v;
        }
        return $v;
    }

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin = p2f($kin))) {
            return parent::__call($kin, $lot);
        }
        return $this->_get($kin, $lot);
    }

    public function __construct(string $path = null, array $lot = []) {
        $this->h = [$c = c2f(self::class)];
        // Set pre-defined page property
        $this->a = array_replace_recursive((array) State::get('x.' . $c . '.page', true), $lot);
        parent::__construct($path);
    }

    // Inherit to `File::__get()`
    public function __get(string $key) {
        return parent::__get($key) ?? $this->__call($key);
    }

    public function __set(string $key, $value) {
        $this->offsetSet(p2f($key), $value);
    }

    // Inherit to `File::__toString()`
    public function __toString() {
        return To::page($this->lot ?? []);
    }

    public function ID(...$lot) {
        return $this->_get('id', $lot) ?? (($t = $this->time()->format('U')) ? sprintf('%u', $t) : null);
    }

    // Inherit to `File::URL()`
    public function URL(...$lot) {
        if ($this->exist) {
            return $this->_get('url', $lot) ?? trim($GLOBALS['url'] . '/' . Path::R(Path::F($this->path), LOT . DS . 'page', '/'), '/');
        }
        return null;
    }

    public function content(...$lot) {
        return $this->_get('content', $lot);
    }

    // Inherit to `File::get()`
    public function get(...$lot) {
        if (isset($lot[0]) && is_array($lot[0])) {
            $out = [];
            foreach ($lot[0] as $k => $v) {
                // `$page->get(['foo.bar' => 0])`
                if (false !== strpos($k, '.')) {
                    $kk = explode('.', $k, 2);
                    if (is_array($vv = $this->_get($kk[0]))) {
                        $out[$k] = get($vv, $kk[1]) ?? $v;
                        continue;
                    }
                }
                $out[$k] = $this->_get($k) ?? $v;
            }
            return $out;
        }
        // `$page->get('foo.bar')`
        if (isset($lot[0]) && is_string($lot[0])) {
            if (false !== strpos($lot[0], '.')) {
                $k = explode('.', $lot[0], 2);
                if (is_array($v = $this->_get($k[0]))) {
                    return get($v, $k[1]);
                }
            }
            return $this->_get($lot[0]);
        }
        return null;
    }

    // Inherit to `File::getIterator()`
    public function getIterator() {
        $out = [];
        if ($this->exist) {
            $out = From::page(file_get_contents($path = $this->path), true);
            foreach (g(Path::F($path), 'data') as $k => $v) {
                $out[basename($k, '.data')] = e(file_get_contents($k));
            }
        }
        return new \ArrayIterator($out);
    }

    // Inherit to `File::jsonSerialize()`
    public function jsonSerialize() {
        return $this->exist ? From::page(file_get_contents($this->path), true) : [];
    }

    // Inherit to `File::offsetGet()`
    public function offsetGet($i) {
        // Read once!
        if ($this->exist && empty($this->read[$i])) {
            // Prioritize data from a file…
            if (is_file($f = Path::F($this->path) . DS . $i . '.data')) {
                $this->read[$i] = 1; // Done read
                return ($this->lot[$i] = a(e(file_get_contents($f))));
            }
            // Stream page file content and make sure that property is exists before parsing
            $exist = 'content' === $i;
            foreach (stream($path = $this->path) as $k => $v) {
                if (0 === $k && "---\n" !== $v) {
                    break;
                }
                if ("...\n" === $v) {
                    break;
                }
                if (
                    0 === strpos($v, $i . ':') ||
                    0 === strpos($v, '"' . strtr($i, ['"' => "\\\""]) . '":') ||
                    0 === strpos($v, "'" . strtr($i, ["'" => "\\'"]) . "':")
                ) {
                    $exist = true;
                    break;
                }
            }
            if ($exist) {
                $any = From::page(file_get_contents($path), true);
                foreach ($any as $k => $v) {
                    $this->read[$k] = 1; // Done read
                }
                $this->lot = array_replace_recursive($this->lot ?? [], $any);
            }
            $this->lot = array_replace_recursive($this->a ?? [], $this->lot ?? []);
        }
        return $this->lot[$i] ?? null;
    }

    // Inherit to `File::offsetSet()`
    public function offsetSet($i, $value) {
        if (isset($i)) {
            $this->lot[$i] = $value;
        } else {
            $this->lot[] = $value;
        }
    }

    // Inherit to `File::offsetUnset()`
    public function offsetUnset($i) {
        unset($this->lot[$i]);
    }

    public function save($seal = null) {
        $data = $this->lot ?? [];
        $data = j($data, $this->a);
        $this->value[0] = To::page($data);
        return parent::save($seal);
    }

    // Inherit to `File::set()`
    public function set(...$lot) {
        if (!$this->exist) {
            $this->lot = [];
        }
        if (isset($lot[0])) {
            if (is_array($lot[0])) {
                foreach ($lot[0] as $k => $v) {
                    if (!isset($v) || false === $v) {
                        unset($this->lot[$k]);
                        continue;
                    }
                    $this->lot[$k] = $v;
                }
            } else if (array_key_exists(1, $lot)) {
                if (!isset($lot[1]) || false === $lot[1]) {
                    unset($this->lot[$lot[0]]);
                } else {
                    $this->lot[$lot[0]] = $lot[1];
                }
            } else {
                // `$page->set('<p>abcdef</p>')`
                $this->lot['content'] = $lot[0];
            }
        } else if (!isset($lot[0]) || false === $lot[0]) {
            unset($this->lot['content']);
        }
        return $this;
    }

    // Inherit to `File::time()`
    public function time(string $format = null) {
        $n = parent::name();
        // Set `time` value from the page’s file name
        if (
            is_string($n) && (
                // `2017-04-21.page`
                2 === substr_count($n, '-') ||
                // `2017-04-21-14-25-00.page`
                5 === substr_count($n, '-')
            ) &&
            is_numeric(str_replace('-', "", $n)) &&
            preg_match('/^[1-9]\d{3,}-(0\d|1[0-2])-(0\d|[1-2]\d|3[0-1])(-([0-1]\d|2[0-4])(-([0-5]\d|60)){2})?$/', $n)
        ) {
            $time = new Time($n);
        // Else…
        } else {
            $time = new Time($this->offsetGet('time') ?? parent::time());
        }
        return $format ? $time($format) : $time;
    }

    // Inherit to `File::type()`
    public function type(...$lot) {
        return $this->_get('type', $lot) ?? 'text/html';
    }

}
