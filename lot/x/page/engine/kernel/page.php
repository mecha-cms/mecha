<?php

class Page extends File {

    protected $c;
    protected $cache;
    protected $lot;

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin = p2f($kin))) {
            return parent::__call($kin, $lot);
        }
        if (array_key_exists($kin, $this->cache)) {
            return $this->cache[$kin];
        }
        $v = $this->offsetGet($kin) ?? $this->lot[$kin] ?? null;
        $v = Hook::fire(map($this->c, static function($v) use($kin) {
            return $v .= '.' . $kin;
        }), [$v, $lot], $this);
        if ($lot && is_callable($v) && !is_string($v)) {
            $v = call_user_func($v, ...$lot);
        }
        return ($this->cache[$kin] = $v);
    }

    public function __construct(string $path = null, array $lot = []) {
        parent::__construct($path);
        $this->cache = [];
        foreach (array_merge([$n = static::class], array_slice(class_parents($n), 0, -1, false)) as $v) {
            $this->c[] = $v = c2f($v);
            $this->lot = array_replace_recursive($this->lot ?? [], (array) State::get('x.' . $v . '.page', true), $lot);
        }
    }

    public function __get(string $key) {
        return parent::__get($key) ?? $this->__call($key);
    }

    public function __set(string $key, $value) {
        $this->offsetSet(p2f($key), $value);
    }

    public function __toString() {
        return To::page($this->lot ?? []);
    }

    public function __unset(string $key) {
        $this->offsetUnset(p2f($key));
    }

    public function ID(...$lot) {
        $t = $this->time()->format('U');
        $id = $this->__call('id', $lot) ?? ($t ? sprintf('%u', $t) : null);
        return is_string($id) && is_numeric($id) ? (int) $id : $id;
    }

    public function URL(...$lot) {
        if ($path = $this->exist()) {
            $folder = dirname($path) . D . pathinfo($path, PATHINFO_FILENAME);
            return $this->__call('url', $lot) ?? long(strtr(strtr($folder, [LOT . D . 'page' . D => '/']), D, '/'));
        }
        return null;
    }

    public function content(...$lot) {
        return $this->__call('content', $lot);
    }

    public function getIterator(): \Traversable {
        $out = [];
        if ($this->exist()) {
            $out = From::page(file_get_contents($path = $this->path), true);
            $folder = dirname($path) . D . pathinfo($path, PATHINFO_FILENAME);
            foreach (g($folder, 'data') as $k => $v) {
                $v = e(file_get_contents($k));
                if (is_string($v) && Is::JSON($v)) {
                    $v = json_decode($v, true);
                }
                $out[basename($k, '.data')] = $v;
            }
        }
        return new \ArrayIterator($out);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->exist() ? From::page(file_get_contents($this->path), true) : [];
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key) {
        if ($this->exist()) {
            $path = $this->path;
            // Prioritize data from a file…
            $folder = dirname($path) . D . pathinfo($path, PATHINFO_FILENAME);
            if (is_file($f = $folder . D . $key . '.data')) {
                $v = e(file_get_contents($f));
                if (is_string($v) && Is::JSON($v)) {
                    $v = json_decode($v, true);
                }
                return ($this->lot[$key] = $v);
            }
            // Stream page file content and make sure that property is exists before parsing
            $exist = 'content' === $key;
            foreach (stream($path) as $k => $v) {
                if (0 === $k && YAML\SOH . "\n" !== $v) {
                    break;
                }
                if (YAML\EOT . "\n" === $v) {
                    break;
                }
                if (
                    0 === strpos($v, $key . ':') ||
                    0 === strpos($v, '"' . strtr($key, ['"' => '\\"']) . '":') ||
                    0 === strpos($v, "'" . strtr($key, ["'" => "\\'"]) . "':")
                ) {
                    $exist = true;
                    break;
                }
            }
            if ($exist) {
                $lot = From::page(file_get_contents($path), true);
                $this->lot = array_replace_recursive($this->lot ?? [], $lot);
            }
        }
        return $this->lot[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        if (isset($key)) {
            $this->lot[$key] = $value;
        } else {
            $this->lot[] = $value;
        }
    }

    public function offsetUnset($key): void {
        unset($this->cache[$key], $this->lot[$key]);
    }

    public function time(string $format = null) {
        $name = parent::name();
        // Set `time` value from the page’s file name
        if (
            is_string($name) && (
                // `2017-04-21.page`
                2 === substr_count($name, '-') ||
                // `2017-04-21-14-25-00.page`
                5 === substr_count($name, '-')
            ) &&
            is_numeric(str_replace('-', "", $name)) &&
            preg_match('/^[1-9]\d{3,}-(0\d|1[0-2])-(0\d|[1-2]\d|3[0-1])(-([0-1]\d|2[0-4])(-([0-5]\d|60)){2})?$/', $name)
        ) {
            $time = new Time($name);
        // Else…
        } else {
            $time = new Time($this->offsetGet('time') ?? parent::time());
        }
        return $format ? $time($format) : $time;
    }

    public function type(...$lot) {
        return $this->__call('type', $lot) ?? 'HTML';
    }

    public static function from(...$lot) {
        if (is_array($v = reset($lot))) {
            return new static(null, $v);
        }
        return new static(...$lot);
    }

}