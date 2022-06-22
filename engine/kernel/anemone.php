<?php

class Anemone extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    public $join;
    public $lot;
    public $parent;
    public $value;

    public function __call(string $kin, array $lot = []) {
        if (property_exists($this, $kin) && (new \ReflectionProperty($this, $kin))->isPublic()) {
            return $this->{$kin};
        }
        return parent::__call($kin, $lot);
    }

    public function __construct(iterable $value = [], string $join = ', ') {
        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }
        $this->lot = $this->value = $value;
        $this->join = $join;
    }

    public function __destruct() {
        $this->lot = $this->value = [];
        if ($this->parent) {
            unset($this->parent);
        }
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new \ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return $this->__call($key);
    }

    public function __invoke(string $join = ', ', $filter = true) {
        $value = $filter ? $this->is(static function($v, $k) {
            // Ignore `null`, `false` and item with key prefixed by a `_`
            return isset($v) && false !== $v && 0 !== strpos($k, '_');
        })->value : $this->value;
        foreach ($value as $k => $v) {
            // Ignore value(s) that cannot be converted to string
            if (is_array($v) || (is_object($v) && !method_exists($v, '__toString'))) {
                unset($value[$k]);
            }
        }
        return implode($join, $value);
    }

    public function __toString() {
        return (string) $this->__invoke($this->join);
    }

    public function any($fn = null) {
        return any($this->value, $fn);
    }

    public function chunk(int $chunk = 5, int $index = -1, $preserve_key = false) {
        $that = $this->mitose();
        $that->value = array_chunk($that->value, $chunk, $preserve_key);
        if (-1 !== $index) {
            $that->value = $that->value[$index] ?? [];
        }
        return $that;
    }

    public function count(): int {
        return count($this->value);
    }

    public function find(callable $fn = null) {
        return find($this->value, $fn);
    }

    public function first($take = false) {
        return $take ? array_shift($this->value) : reset($this->value);
    }

    public function get(string $key = null) {
        return isset($key) ? get($this->value, $key) : $this->value;
    }

    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->value);
    }

    public function has(string $key = null) {
        return isset($key) ? has($this->value, $key) : !empty($this->value);
    }

    public function index(string $key) {
        $index = array_search($key, array_keys($this->value));
        return false !== $index ? $index : null;
    }

    public function is($fn = null) {
        $that = $this->mitose();
        $that->value = is($that->value, $fn);
        return $that;
    }

    public function join(string $join = ', ') {
        return implode($join, $this->value);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->value;
    }

    public function key(int $index) {
        $array = array_keys($this->value);
        return array_key_exists($index, $array) ? $array[$index] : null;
    }

    public function last($take = false) {
        return $take ? array_pop($this->value) : end($this->value);
    }

    public function let(string $key) {
        let($this->value, $key);
        return $this;
    }

    public function lot(array $value = [], $over = false) {
        $this->value = $this->lot = $over ? array_replace_recursive($this->lot, $value) : $value;
        return $this;
    }

    public function map(callable $fn) {
        $that = $this->mitose();
        $that->value = map($that->value, $fn);
        return $that;
    }

    public function mitose() {
        $that = new static($this->value, $this->join);
        $that->lot = $this->lot;
        $that->parent = $this;
        return $that;
    }

    public function not($fn = null) {
        $that = $this->mitose();
        $that->value = not($that->value, $fn);
        return $that;
    }

    public function offsetExists($key): bool {
        return isset($this->value[$key]);
    }

    #[\ReturnTypeWillChange]
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

    public function pluck(string $key, $value = null) {
        $that = $this->mitose();
        $that->value = pluck($that->value, $key, $value);
        return $that;
    }

    public function reverse() {
        $that = $this->mitose();
        $that->value = array_reverse($that->value);
        return $that;
    }

    public function set(string $key, $value) {
        set($this->value, $key, $value);
        return $this;
    }

    public function shake($preserve_key = true) {
        $this->value = shake($this->value, $preserve_key);
        return $this;
    }

    // Sort array value: `1` for “asc” and `-1` for “desc”
    public function sort($sort = 1, $preserve_key = false) {
        if (count($value = $this->value) <= 1) {
            if (!$preserve_key) {
                $this->value = array_values($this->value);
            }
            return $this;
        }
        if (is_callable($sort)) {
            $preserve_key ? uasort($value, $sort) : usort($value, $sort);
        } else if (is_array($sort)) {
            $i = $sort[0];
            if (isset($sort[1])) {
                $key = $sort[1];
                $fn = -1 === $i ? static function($a, $b) use($key) {
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
                } : static function($a, $b) use($key) {
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
                $preserve_key ? uasort($value, $fn) : usort($value, $fn);
            } else {
                if ($preserve_key) {
                    -1 === $i ? arsort($value) : asort($value);
                } else {
                    -1 === $i ? rsort($value) : sort($value);
                }
            }
        } else {
            if ($preserve_key) {
                -1 === $sort ? arsort($value) : asort($value);
            } else {
                -1 === $sort ? rsort($value) : sort($value);
            }
        }
        $this->value = $value;
        return $this;
    }

    public static function from(...$lot) {
        return new static(...$lot);
    }

}