<?php

class AnemoneNew extends Genome {

    public $join;
    public $lazy;
    public $parent;
    public $value;

    public function __call(string $kin, array $lot = []) {}

    public function __construct($value = [], string $join = ', ') {
        $this->join = $join;
        $this->lazy = is_callable($this->value = $value);
    }

    public function __destruct() {
        $this->value = [];
        if ($parent = $this->parent) {
            unset($parent);
        }
    }

    public function __get(string $key) {}

    public function __invoke(string $join = ', ', $filter = true) {}

    public function __toString(): string {
        return (string) $this->join($this->join);
    }

    public function all($fn) {
        return all($this->value, that($fn, $this));
    }

    public function any($fn) {
        return any($this->value, that($fn, $this));
    }

    public function chunk(int $chunk = 5, int $part = -1, $keys = false) {
        $that = $this->mitose();
        if (is_array($value = $that->value)) {
            if ($chunk > 0) {
                $that->value = $value = array_chunk($value, $chunk, $keys);
                if ($part > -1) {
                    $that->value = $value[$part] ?? [];
                }
            }
            return $that;
        }
        $that->value = function () use ($chunk, $keys, $part, $value) {
            $v = fire($value);
            if ($part > -1) {
                foreach (new LimitIterator($v, $chunk * $part, $chunk) as $k => $v) {
                    if ($keys) {
                        yield $k => $v;
                    } else {
                        yield $v;
                    }
                }
            } else {
                $lot = new ArrayIterator;
                while ($v->valid()) {
                    if ($keys) {
                        $lot[$v->key()] = $v->current();
                    } else {
                        $lot[] = $v->current();
                    }
                    $v->next();
                    if ($chunk === $lot->count()) {
                        yield $lot;
                        $lot = new ArrayIterator;
                    }
                }
                if ($lot->count()) {
                    yield $lot;
                }
            }
        };
        return $that;
    }

    public function count(): int {
        return $this->lazy ? q(fire($this->value)) : count($this->value);
    }

    public function find($fn) {
        return find($this->value, that($fn, $this));
    }

    public function first($take = false) {}

    public function get(?string $key = null) {}

    public function getIterator(): Traversable {
        return $this->lazy ? fire($value = $this->value) : $value;
    }

    public function has(?string $key = null) {
        return has($this->lazy ? fire($value = $this->value) : $value, $key);
    }

    public function index(string $key) {}

    public function is($fn, $keys = false) {
        $that = $this->mitose();
        $that->value = is($this->lazy ? fire($value = $this->value) : $value, $fn, $keys);
        return $that;
    }

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    public function key(int $index) {}

    public function last($take = false) {}

    public function let(?string $key = null) {
        return let($this->lazy ? fire($value = $this->value) : $value, $key);
    }

    public function map(callable $fn) {
        $that = $this->mitose();
        $that->value = map($this->lazy ? fire($value = $this->value) : $value, $fn);
        return $that;
    }

    public function mitose() {
        $that = new static($this->value, $this->join);
        $that->parent = $this;
        return $that;
    }

    public function not($fn, $keys = false) {
        $that = $this->mitose();
        $that->value = not($this->lazy ? fire($value = $this->value) : $value, $fn, $keys);
        return $that;
    }

    public function offsetExists($key): bool {
        if ($this->lazy) {
            foreach (fire($this->value) as $k => $v) {
                if ($k === $key) {
                    return true;
                }
            }
            return false;
        }
        return isset($this->value[$key]);
    }

    public function offsetGet($key) {
        if ($this->lazy) {
            foreach (fire($this->value) as $k => $v) {
                if ($k === $key) {
                    return $v;
                }
            }
            return null;
        }
        return $this->value[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        if ($this->lazy) {
            // TODO
        } else {
            if (isset($key)) {
                $this->value[$key] = $value;
            } else {
                $this->value[] = $value;
            }
        }
    }

    public function offsetUnset($key): void {
        if ($this->lazy) {
            // TODO
        } else {
            unset($this->value[$key]);
        }
    }

    public function pluck(string $key, $value = null, $keys = false) {}

    public function reverse($keys = false) {
        if (count($value = $this->value) < 2) {
            return $this;
        }
        if (is_array($value)) {
            $value = array_reverse($value, $keys);
            $this->value = $keys ? $value : array_values($value);
            return $this;
        }
        $value = $this->value;
        $this->value = function () use ($keys, $value) {
            $v = fire($value);
            for (end($v); null !== ($k = key($v)); prev($v)) {
                if ($keys) {
                    yield $k => current($v);
                } else {
                    yield $v;
                }
            }
        };
        return $this;
    }

    public function set($key, $value = null) {}

    public function set($key, $value = null) {
        return set($this->lazy ? fire($v = $this->value) : $v, $key, $value);
    }

    public function shake($keys = false) {}

    public function sort($sort = 1, $keys = false) {}

    public static function from(...$lot) {
        if (isset($lot[0]) && is_string($lot[0])) {
            $lot[0] = explode($lot[1] ?? ', ', $lot[0]);
        }
        return new static(...$lot);
    }

}