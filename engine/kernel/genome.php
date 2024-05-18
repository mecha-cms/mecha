<?php

abstract class Genome implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Serializable, Stringable {

    public function __call(string $kin, array $lot = []) {
        foreach (array_merge([$n = static::class], array_slice(class_parents($n), 0, -1, false)) as $c) {
            if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
                $v = self::$_[$c][$kin];
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
            if (defined('TEST') && TEST) {
                throw new BadMethodCallException('Method $' . basename($c) . '->' . $kin . '() does not exist.');
            }
        }
    }

    #[ReturnTypeWillChange]
    public function __clone() {}

    public function __construct() {}

    public function __debugInfo(): array {
        return $this->__serialize();
    }

    public function __destruct() {}

    #[ReturnTypeWillChange]
    public function __get(string $key) {
        return null;
    }

    #[ReturnTypeWillChange]
    public function __invoke() {}

    public function __isset(string $key): bool {
        return false;
    }

    public function __serialize(): array {
        return get_object_vars($this);
    }

    public function __set(string $key, $value): void {}

    public function __toString(): string {
        return json_encode($this->__serialize());
    }

    #[ReturnTypeWillChange]
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

    #[ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->__serialize();
    }

    public function offsetExists($key): bool {
        return false;
    }

    #[ReturnTypeWillChange]
    public function offsetGet($key) {
        return null;
    }

    public function offsetSet($key, $value): void {}

    public function offsetUnset($key): void {}

    // PHP Serializable < 7.4
    #[ReturnTypeWillChange]
    public function serialize() {
        return serialize($this->__serialize());
    }

    // PHP Serializable < 7.4
    #[ReturnTypeWillChange]
    public function unserialize($lot) {
        $this->__unserialize(unserialize($lot));
    }

    public static $_ = [];

    public static function _(...$lot) {
        $c = static::class;
        // Get
        if (0 === count($lot)) {
            return self::$_[$c] ?? [];
        }
        // Get
        if (1 === count($lot)) {
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
        foreach (array_merge([$n = static::class], array_slice(class_parents($n), 0, -1, false)) as $c) {
            if (isset(self::$_[$c]) && array_key_exists($kin, self::$_[$c])) {
                $r = self::$_[$c][$kin];
                if (is_callable($r[0])) {
                    // Alter default function argument(s)
                    if (isset($r[1])) {
                        $lot = array_replace((array) $r[1], $lot);
                    }
                    // Limit function argument(s)
                    if (isset($r[2])) {
                        $lot = array_slice($lot, 0, $r[2]);
                    }
                    return fire($r[0], $lot);
                }
                return $r[0];
            }
            if (defined('TEST') && TEST) {
                throw new BadMethodCallException('Method ' . $c . '::' . $kin . '() does not exist.');
            }
        }
    }

    public static function __set_state(array $lot): object {
        return (new static)->__unserialize($lot);
    }

}