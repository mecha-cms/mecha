<?php

abstract class Genome implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Stringable {

    protected static function _chains($k) {
        static $c = [];
        if (!isset($c[$k])) {
            $r = class_parents($k);
            unset($r[self::class]);
            $c[$k] = [$k, ...$r];
        }
        return $c[$k];
    }

    protected static function _hasOwnMethod($key, $that) {
        static $c = [];
        if (isset($c[$k = get_class($that)][$key])) {
            return true;
        }
        if (!method_exists($that, $key)) {
            return false;
        }
        // if ((new ReflectionMethod($that, $key))->isPublic()) {
        if (is_callable([$that, $key])) {
            return ($c[$k][$key] = true);
        }
        return false;
    }

    protected static function _hasOwnProperty($key, $that) {
        static $c = [];
        if (isset($c[$k = get_class($that)][$key])) {
            return true;
        }
        if (!property_exists($that, $key)) {
            return false;
        }
        if ((new ReflectionProperty($that, $key))->isPublic()) {
            return ($c[$k][$key] = true);
        }
        return false;
    }

    public function __call(string $kin, array $lot = []) {
        foreach (self::_chains($c = static::class) as $k) {
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
                    return fire($v[0], $lot, $this);
                }
                return $v[0];
            }
        }
        if (defined('TEST') && TEST) {
            throw new BadMethodCallException('Method $' . strtr($c, ["\\" => '__']) . '->' . $kin . '() does not exist.');
        }
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
        foreach (self::_chains($c = static::class) as $k) {
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
                    return fire($v[0], $lot);
                }
                return $v[0];
            }
        }
        if (defined('TEST') && TEST) {
            throw new BadMethodCallException('Method ' . $c . '::' . $kin . '() does not exist.');
        }
    }

    public static function __set_state(array $lot): object {
        $that = new static;
        $that->__unserialize($lot);
        return $that;
    }

}