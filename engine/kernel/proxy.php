<?php

abstract class Proxy implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Stringable {

    protected function __fire__($key) {
        static $c = [];
        if (isset($c[$k = static::class][$key])) {
            return $c[$k][$key];
        }
        if (!method_exists($this, $key)) {
            return ($c[$k][$key] = false);
        }
        if ((new ReflectionMethod($this, $key))->isPublic()) {
            return ($c[$k][$key] = true);
        }
        return ($c[$k][$key] = false);
    }

    protected function __get__($key) {
        static $c = [];
        if (isset($c[$k = static::class][$key])) {
            return $c[$k][$key];
        }
        if (!property_exists($this, $key)) {
            return ($c[$k][$key] = false);
        }
        if ((new ReflectionProperty($this, $key))->isPublic()) {
            return ($c[$k][$key] = true);
        }
        return ($c[$k][$key] = false);
    }

    protected function __has__($key) {
        return $this->__fire__($key) || $this->__get($key);
    }

    private static function _fire(string $kin, array $lot, $that = null) {
        foreach (self::__chain__() as $k) {
            if (!empty(self::$_[$k]) && array_key_exists($kin, self::$_[$k])) {
                $v = self::$_[$k][$kin];
                if (is_callable($v[0])) {
                    // Alter default function argument(s)
                    if (isset($v[1])) {
                        $lot = array_replace((array) $v[1], $lot);
                    }
                    // Limit function argument(s)
                    if (isset($v[2])) {
                        $lot = array_slice($lot, 0, $v[2]);
                    }
                    return fire($v[0], $lot, $that);
                }
                return $v[0];
            }
        }
        if (defined('TEST') && TEST) {
            $c = static::class;
            throw new BadMethodCallException('Method ' . ($that ? '$' . strtr($c, ["\\" => '__']) . '->' : $c . '::') . $kin . '() does not exist.');
        }
    }

    protected static function __chain__() {
        static $c = [];
        if (!isset($c[$k = static::class])) {
            $r = class_parents($k);
            unset($r[self::class]);
            $c[$k] = [$k, ...$r];
        }
        return $c[$k];
    }

    public function __call(string $kin, array $lot = []) {
        return self::_fire($kin, $lot, $this);
    }

    public function __clone(): void {}

    public function __construct() {}

    public function __debugInfo(): array {
        return $this->__serialize();
    }

    public function __destruct() {}

    public function __get(string $key): mixed {
        return null;
    }

    public function __invoke(): mixed {}

    public function __isset(string $key): bool {
        return false;
    }

    public function __serialize(): array {
        return get_object_vars($this);
    }

    public function __set(string $key, $value): void {}

    public function __toString(): string {
        return json_encode($this->__serialize(), JSON_UNESCAPED_UNICODE) ?: "";
    }

    public function __unserialize(array $lot): void {
        foreach ($lot as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function __unset(string $key): void {}

    public function count(): int {
        return count($this->__serialize());
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->__serialize());
    }

    public function jsonSerialize(): mixed {
        return $this->__serialize();
    }

    public function offsetExists($key): bool {
        return false;
    }

    public function offsetGet($key): mixed {
        return null;
    }

    public function offsetSet($key, $value): void {}

    public function offsetUnset($key): void {}

    public static $_ = [];

    public static function _(...$lot) {
        $c = static::class;
        $max = count($lot);
        // Get
        if (0 === $max) {
            return self::$_[$c] ?? [];
        }
        // Get
        if (1 === $max) {
            return self::$_[$c][$lot[0]] ?? null;
        }
        // Let
        if (null === $lot[1]) {
            unset(self::$_[$c][$lot[0]]);
        // Set
        } else {
            self::$_[$c][$lot[0]] = (array) $lot[1];
        }
    }

    public static function __callStatic(string $kin, array $lot = []) {
        return self::_fire($kin, $lot);
    }

    public static function __set_state(array $lot): object {
        $that = new static;
        $that->__unserialize($lot);
        return $that;
    }

}