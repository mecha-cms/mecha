<?php

class State extends Genome implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable {

    protected static $lot = [];

    public function __call(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__call($kin, $lot);
        }
        return self::__callStatic($kin, $lot);
    }

    public function __construct(array $value = []) {
        self::$lot[static::class] = $value;
    }

    public function __get(string $key) {
        if (parent::_($key)) {
            return $this->__call($key);
        }
        return self::get(p2f($key));
    }

    public function __invoke(...$lot) {
        return count($lot) < 2 ? self::get(...$lot) : self::set(...$lot);
    }

    public function __isset(string $key) {
        return !!$this->__get($key);
    }

    public function __set(string $key, $value = null) {
        return self::set(p2f($key), $value);
    }

    public function __toString(): string {
        return json_encode(self::get());
    }

    public function __unset(string $key) {
        self::let(p2f($key));
    }

    public function count(): int {
        return count(self::$lot[static::class] ?? []);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator(self::$lot[static::class] ?? []);
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize() {
        return self::$lot[static::class] ?? [];
    }

    public function offsetExists($key): bool {
        return null !== self::offsetGet($key);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($key) {
        return self::$lot[static::class][$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        $c = static::class;
        if (isset($key)) {
            self::$lot[$c][$key] = $value;
        } else {
            self::$lot[$c][] = $value;
        }
    }

    public function offsetUnset($key): void {
        unset(self::$lot[static::class][$key]);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        $kin = p2f($kin); // `fooBar_baz` → `foo-bar_baz`
        if ($lot) {
            $value = self::get($kin);
            // Asynchronous value with closure
            if ($value instanceof Closure) {
                return fire($value, $lot, null, static::class);
            }
            // Asynchronous value with class instance
            if (is_callable($value) && !is_string($value)) {
                return call_user_func($value, ...$lot);
            }
            // Else, static value
            return self::get($kin . '.' . $lot[0], !empty($lot[1]));
        }
        return self::get($kin);
    }

    public static function get($key = null, $array = false) {
        $c = static::class;
        if ($key && is_array($key)) {
            $out = [];
            // `State::get(['a', 'b', 'c'])`
            if (array_is_list($key)) {
                foreach ($key as $k) {
                    $out[] = self::get($k, $array);
                }
            // `State::get(['a' => null, 'b' => null, 'c' => null])`
            } else {
                foreach ($key as $k => $v) {
                    $out[$k] = self::get($k, $array) ?? $v;
                }
            }
            return $array ? $out : o($out);
        }
        // `State::get('a')`
        if (isset($key) && !is_array($key)) {
            $out = self::$lot[$c] ?? [];
            $out = get($out, (string) $key);
            return $array ? $out : o($out);
        }
        // `State::get()`
        $out = self::$lot[$c] ?? [];
        return $array ? $out : o($out);
    }

    public static function let($key = null) {
        $c = static::class;
        if ($key && is_array($key)) {
            // `State::let(['a', 'b', 'c'])`
            if (array_is_list($key)) {
                foreach ($key as $k) {
                    self::let($k);
                }
            // `State::let(['a' => null, 'b' => null, 'c' => null])`
            } else {
                foreach ($key as $k => $v) {
                    if ($v === self::get($k)) {
                        self::let($k);
                    }
                }
            }
        // `State::let('a')`
        } else if (isset($key) && !is_array($key)) {
            let(self::$lot[$c], (string) $key);
        // `State::let()`
        } else {
            self::$lot[$c] = [];
        }
    }

    public static function set($key, $value = null) {
        $c = static::class;
        $in = [];
        if (is_array($key)) {
            if (array_is_list($key)) {
                foreach ($key as $k) {
                    set($in, $k, $value);
                }
            } else {
                foreach ($key as $k => $v) {
                    set($in, $k, $v);
                }
            }
        } else {
            set($in, $key, $value);
        }
        $out = self::$lot[$c] ?? [];
        self::$lot[$c] = array_replace_recursive($out, $in);
    }

}