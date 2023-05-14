<?php

class Anemone extends Genome implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable {

    public $join;
    public $lot;
    public $parent;
    public $value;

    public function __call(string $kin, array $lot = []) {
        if (property_exists($this, $kin) && (new ReflectionProperty($this, $kin))->isPublic()) {
            return $this->{$kin};
        }
        return parent::__call($kin, $lot);
    }

    public function __construct(iterable $value = [], string $join = ', ') {
        $this->lot = $this->value = y($value);
        $this->join = $join;
    }

    public function __destruct() {
        $this->lot = $this->value = [];
        if ($this->parent) {
            unset($this->parent);
        }
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return $this->__call($key);
    }

    public function __invoke(string $join = ', ', $filter = true) {
        $value = ($filter ? $this->is(is_callable($filter) ? $filter : function ($v, $k) {
            // Ignore value(s) that cannot be converted to string
            if (is_array($v) || is_object($v) && !method_exists($v, '__toString')) {
                return false;
            }
            // Ignore `false` and `null` value(s)
            return false !== $v && null !== $v;
        }) : $this)->value;
        return implode($join, $value);
    }

    public function __toString(): string {
        return (string) $this->__invoke($this->join);
    }

    public function all($fn) {
        return all($this->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn);
    }
    public function any($fn) {
        return any($this->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn);
    }

    public function chunk(int $chunk = 5, int $part = -1, $keys = false) {
        $that = $this->mitose();
        if ($chunk > 0) {
            $that->value = array_chunk($that->value, $chunk, $keys);
            if ($part > -1) {
                $that->value = $that->value[$part] ?? [];
            }
        }
        return $that;
    }

    public function count(): int {
        return count($this->value);
    }

    public function find($fn) {
        return find($this->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn);
    }

    public function first($take = false) {
        if (!$this->value) {
            return null;
        }
        if ($take) {
            return array_shift($this->value);
        }
        $first = reset($this->value);
        return false !== $first ? $first : null;
    }

    public function get(string $key = null) {
        return isset($key) ? get($this->value, $key) : $this->value;
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->value);
    }

    public function has(string $key = null) {
        return isset($key) ? has($this->value, $key) : !empty($this->value);
    }

    public function index(string $key) {
        if (!$this->value || !array_key_exists($key, $this->value)) {
            return null;
        }
        $index = array_search($key, array_keys($this->value));
        return false !== $index ? $index : null;
    }

    public function is($fn, $keys = false) {
        $that = $this->mitose();
        $that->value = is($that->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn, $keys);
        return $that;
    }

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->value;
    }

    public function key(int $index) {
        if (!$this->value || $index > count($this->value)) {
            return null;
        }
        $keys = array_keys($this->value);
        return array_key_exists($index, $keys) ? (string) $keys[$index] : null;
    }

    public function last($take = false) {
        if (!$this->value) {
            return null;
        }
        if ($take) {
            return array_pop($this->value);
        }
        $last = end($this->value);
        return false !== $last ? $last : null;
    }

    public function let(string $key = null) {
        if (isset($key)) {
            return let($this->value, $key);
        }
        $this->value = [];
        return true;
    }

    public function map(callable $fn) {
        $that = $this->mitose();
        $that->value = map($that->value, Closure::fromCallable($fn)->bindTo($this));
        return $that;
    }

    public function mitose() {
        $that = new static($this->value, $this->join);
        $that->lot = $this->lot;
        $that->parent = $this;
        return $that;
    }

    public function not($fn, $keys = false) {
        $that = $this->mitose();
        $that->value = not($that->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn, $keys);
        return $that;
    }

    public function offsetExists($key): bool {
        return isset($this->value[$key]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($key) {
        return $this->value[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        if (isset($key)) {
            $this->value[$key] = $value;
        } else {
            $this->value[] = $value;
        }
    }

    public function offsetUnset($key): void {
        unset($this->value[$key]);
    }

    public function pluck(string $key, $value = null, $keys = false) {
        $that = $this->mitose();
        $that->value = pluck($that->value, $key, $value, $keys);
        return $that;
    }

    public function reverse($keys = false) {
        $value = array_reverse($this->value, $keys);
        $this->value = $keys ? $value : array_values($value);
        return $this;
    }

    public function set($key, $value = null) {
        if (is_iterable($key)) {
            // `$key` as `$values`
            return ($this->value = y($key));
        }
        return set($this->value, $key, $value);
    }

    public function shake($keys = false) {
        $this->value = shake($this->value, is_callable($keys) ? Closure::fromCallable($keys)->bindTo($this) : $keys);
        return $this;
    }

    // Sort array value: `+1` or `1` for “asc” and `-1` for “desc”
    public function sort($sort = 1, $keys = false) {
        if (count($value = $this->value) <= 1) {
            if (!$keys) {
                $this->value = array_values($this->value);
            }
            return $this;
        }
        if (is_callable($sort)) {
            $sort = Closure::fromCallable($sort)->bindTo($this);
            $keys ? uasort($value, $sort) : usort($value, $sort);
        } else if (is_array($sort)) {
            $i = $sort[0];
            if (isset($sort[1])) {
                $key = $sort[1];
                $fn = -1 === $i ? static function ($a, $b) use ($key) {
                    if (!is_array($a) || !is_array($b)) {
                        return 0;
                    }
                    if (!isset($a[$key]) && !isset($b[$key])) {
                        return 0;
                    }
                    if (!isset($b[$key])) {
                        return 1;
                    }
                    if (!isset($a[$key])) {
                        return -1;
                    }
                    return $b[$key] <=> $a[$key];
                } : static function ($a, $b) use ($key) {
                    if (!is_array($a) || !is_array($b)) {
                        return 0;
                    }
                    if (!isset($a[$key]) && !isset($b[$key])) {
                        return 0;
                    }
                    if (!isset($a[$key])) {
                        return 1;
                    }
                    if (!isset($b[$key])) {
                        return -1;
                    }
                    return $a[$key] <=> $b[$key];
                };
                if (array_key_exists(2, $sort)) {
                    foreach ($value as &$v) {
                        if (is_array($v) && !isset($v[$key])) {
                            $v[$key] = $sort[2];
                        }
                    }
                    unset($v);
                }
                $keys ? uasort($value, $fn) : usort($value, $fn);
            } else {
                if ($keys) {
                    -1 === $i ? arsort($value) : asort($value);
                } else {
                    -1 === $i ? rsort($value) : sort($value);
                }
            }
        } else {
            if ($keys) {
                -1 === $sort ? arsort($value) : asort($value);
            } else {
                -1 === $sort ? rsort($value) : sort($value);
            }
        }
        $this->value = $value;
        return $this;
    }

    public static function from(...$lot) {
        if (isset($lot[0]) && is_string($lot[0])) {
            $lot[0] = explode($lot[1] ?? ', ', $lot[0]);
        }
        return new static(...$lot);
    }

}