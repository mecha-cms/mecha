<?php

class Anemon extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    public $i = 0;
    public $lot = [];
    public $parent = null;
    public $join = "";
    public $value = [];

    public function __construct(iterable $array = [], string $join = ', ') {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }
        $this->lot = $this->value = $array;
        $this->join = $join;
    }

    public function __invoke(string $join = ', ', $filter = true) {
        $value = $filter ? $this->is(function($v, $k) {
            // Ignore `null` and `false` value and all item(s) with key prefixed by a `_`
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

    // Insert `$value` after current element
    public function after($value, $key = null) {
        $i = b($this->i + 1, [0, $this->count()]);
        $this->value = array_slice($this->value, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->value, $i, null, true);
        return $this;
    }

    public function any($fn = null) {
        return any($this->value, $fn);
    }

    // Insert `$value` to the end of array
    public function append($value, $key = null) {
        $this->i = count($v = $this->value) + 1;
        if (isset($key)) {
            $v += [$key => $value];
        } else {
            $v[] = $value;
        }
        $this->value = $v;
        return $this;
    }

    // Insert `$value` before current element
    public function before($value, $key = null) {
        $i = b($this->i, [0, $this->count()]);
        $this->value = array_slice($this->value, 0, $i, true) + [$key ?? $i => $value] + array_slice($this->value, $i, null, true);
        return $this;
    }

    // Generate chunk(s) of array
    public function chunk(int $chunk = 5, int $i = -1, $preserve_key = false) {
        $clone = $this->mitose();
        $clone->value = array_chunk($clone->value, $chunk, $preserve_key);
        if (-1 !== $i) {
            $clone->value = $clone->value[$clone->i = $i] ?? [];
        }
        return $clone;
    }

    public function count() {
        return count($this->value);
    }

    // Get current array value
    public function current() {
        $current = array_values($this->value);
        return $current[$this->i] ?? null;
    }

    public function find(callable $fn = null) {
        return find($this->value, $fn);
    }

    // Get first array value
    public function first($take = false) {
        return $take ? array_shift($this->value) : reset($this->value);
    }

    public function get(string $key = null) {
        return isset($key) ? get($this->value, $key) : $this->value;
    }

    public function getIterator() {
        return new \ArrayIterator($this->value);
    }

    public function has(string $value = "", string $join = P) {
        return has($this->value, $value, $join);
    }

    // Get position by array key
    public function index(string $key) {
        $i = array_search($key, array_keys($this->value));
        return false !== $i ? $i : null;
    }

    public function is($fn = null) {
        $clone = $this->mitose();
        $clone->value = is($clone->value, $fn);
        return $clone;
    }

    public function join(string $join = ', ') {
        return implode($join, $this->value);
    }

    public function jsonSerialize() {
        return $this->value;
    }

    // Get array key by position
    public function key(int $i) {
        $array = array_keys($this->value);
        return array_key_exists($i, $array) ? $array[$i] : null;
    }

    // Get last array value
    public function last($take = false) {
        return $take ? array_pop($this->value) : end($this->value);
    }

    public function let(string $key) {
        let($this->value, $key);
        return $this;
    }

    public function lot(array $lot = [], $over = false) {
        $this->value = $this->lot = $over ? array_replace_recursive($this->lot, $lot) : $lot;
        return $this;
    }

    public function map(callable $fn) {
        $clone = $this->mitose();
        $clone->value = map($clone->value, $fn);
        return $clone;
    }

    // Clone the current instance
    public function mitose() {
        $clone = new static($this->value, $this->join);
        $clone->lot = $this->lot;
        $clone->parent = $this;
        return $clone;
    }

    // Move to next array index
    public function next(int $skip = 0) {
        $this->i = b($this->i + 1 + $skip, [0, $this->count() - 1]);
        return $this;
    }

    public function not($fn = null) {
        $clone = $this->mitose();
        $clone->value = not($clone->value, $fn);
        return $clone;
    }

    public function offsetExists($i) {
        return isset($this->value[$i]);
    }

    public function offsetGet($i) {
        return $this->value[$i] ?? null;
    }

    public function offsetSet($i, $value) {
        if (isset($i)) {
            $this->value[$i] = $value;
        } else {
            $this->value[] = $value;
        }
    }

    public function offsetUnset($i) {
        unset($this->value[$i]);
    }

    public function pluck(string $key, $or = null) {
        $clone = $this->mitose();
        $clone->value = pluck($clone->value, $key, $or);
        return $clone;
    }

    // Insert `$value` to the start of array
    public function prepend($value, $key = null) {
        $this->i = 0;
        $v = $this->value;
        if (isset($key)) {
            $v = [$key => $value] + $v;
        } else {
            array_unshift($v, $value);
        }
        $this->value = $v;
        return $this;
    }

    // Move to previous array index
    public function prev(int $skip = 0) {
        $this->i = b($this->i - 1 - $skip, [0, $this->count() - 1]);
        return $this;
    }

    public function reverse() {
        $this->value = array_reverse($this->value);
        return $this;
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
        $value = $this->value;
        if (is_callable($sort)) {
            $preserve_key ? uasort($value, $sort) : usort($value, $sort);
        } else if (is_array($sort)) {
            $i = $sort[0];
            if (isset($sort[1])) {
                $key = $sort[1];
                $fn = -1 === $i ? function($a, $b) use($key) {
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
                } : function($a, $b) use($key) {
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

    // Move to `$i` array
    public function to($i) {
        $this->i = is_int($i) ? $i : ($this->index($i) ?? $i);
        return $this;
    }

    // Move to the first array
    public function toFirst() {
        $this->i = 0;
        return $this;
    }

    // Move to the last array
    public function toLast() {
        $this->i = $this->count() - 1;
        return $this;
    }

    // Set current element value as `$value`
    public function value($value) {
        $i = 0;
        foreach ($this->value as $k => $v) {
            if ($i === $this->i) {
                $this->value[$k] = $value;
                break;
            }
            ++$i;
        }
        return $this;
    }

    public static function from(...$lot) {
        return new static(...$lot);
    }

}
