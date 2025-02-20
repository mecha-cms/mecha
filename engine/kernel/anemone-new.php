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
        if ($this->lazy) {
            return implode($join, y(fire($value)));
        }
        return implode($join, $value);
    }

    public function __toString(): string {
        return (string) $this->join($this->join);
    }

    public function all($fn) {
        return all($this->getIterator(), that($fn, $this));
    }

    public function any($fn) {
        return any($this->getIterator(), that($fn, $this));
    }

    public function chunk(int $chunk = 5, int $part = -1, $keys = false) {
        $that = $this->mitose();
        if ($chunk < 1) {
            return $that;
        }
        $value = $this->value;
        if ($this->lazy) {
            $that->value = function () use ($chunk, $keys, $part, $value) {
                $value = fire($value);
                if ($part > -1) {
                    foreach (new LimitIterator($value, $chunk * $part, $chunk) as $k => $v) {
                        if ($keys) {
                            yield $k => $v;
                        } else {
                            yield $v;
                        }
                    }
                } else {
                    $lot = new ArrayIterator;
                    while ($value->valid()) {
                        if ($keys) {
                            $lot[$value->key()] = $value->current();
                        } else {
                            $lot[] = $value->current();
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
        $that->value = $value = array_chunk($value, $chunk, $keys);
        if ($part > -1) {
            $that->value = $value[$part] ?? [];
        }
        return $that;
    }

    public function count(): int {
        return q($this->getIterator());
    }

    public function find($fn) {
        return find($this->getIterator(), that($fn, $this));
    }

    public function first($take = false) {
        if ($this->lazy || !$this->value) {
            return null;
        }
        if ($take) {
            return array_shift($this->value);
        }
        $first = reset($this->value);
        return false !== $first ? $first : null;
    }

    public function get(?string $key = null) {
        return get($this->getIterator(), $key);
    }

    public function getIterator(): Traversable {
        $value = $this->value;
        return $this->lazy ? fire($value) : $value;
    }

    public function has(?string $key = null) {
        return has($this->getIterator(), $key);
    }

    public function index(string $key) {
        $value = $this->value;
        if ($this->lazy) {
            $i = 0;
            foreach (fire($value) as $k => $v) {
                if ($k === $key) {
                    return $i;
                }
                ++$i;
            }
            return null;
        }
        if (!$value || !array_key_exists($key, $value)) {
            return null;
        }
        $index = array_search($key, array_keys($value));
        return false !== $index ? $index : null;
    }

    public function is($fn, $keys = false) {
        $that = $this->mitose();
        $value = $this->value;
        if ($this->lazy) {
            $that->value = function () use ($fn, $keys, $that, $value) {
                yield from is(fire($value), $fn, $keys, $that);
            };
            return $that;
        }
        $that->value = is($value, $fn, $keys, $that);
        return $that;
    }

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    public function key(int $index) {
        $value = $this->value;
        if ($this->lazy) {
            $i = 0;
            foreach (fire($value) as $k => $v) {
                if ($i === $index) {
                    return $k;
                }
                ++$i;
            }
            return null;
        }
        if (!$value || $index > count($value)) {
            return null;
        }
        $keys = array_keys($value);
        return array_key_exists($index, $keys) ? (string) $keys[$index] : null;
    }

    public function last($take = false) {
        if ($this->lazy || !$this->value) {
            return null;
        }
        if ($take) {
            return array_pop($this->value);
        }
        $last = end($this->value);
        return false !== $last ? $last : null;
    }

    public function let(?string $key = null) {
        if (isset($key)) {
            return let($this->getIterator(), $key);
        }
        $this->value = [];
        return true;
    }

    public function map(callable $fn) {
        $that = $this->mitose();
        $value = $this->value;
        if ($this->lazy) {
            $that->value = function () use ($fn, $that, $value) {
                yield from map(fire($value), $fn, $that);
            };
            return $that;
        }
        $that->value = map($value, $fn, $that);
        return $that;
    }

    public function mitose() {
        $that = new static($this->value, $this->join);
        $that->parent = $this;
        return $that;
    }

    public function not($fn, $keys = false) {
        $that = $this->mitose();
        $value = $this->value;
        if ($this->lazy) {
            $that->value = function () use ($fn, $keys, $that, $value) {
                yield from not(fire($value), $fn, $keys, $that);
            };
            return $that;
        }
        $that->value = not($value, $fn, $keys, $that);
        return $that;
    }

    public function offsetExists($key): bool {
        $value = $this->value;
        if ($this->lazy) {
            foreach (fire($value) as $k => $v) {
                if ($k === $key) {
                    return true;
                }
            }
            return false;
        }
        return isset($value[$key]);
    }

    public function offsetGet($key) {
        $value = $this->value;
        if ($this->lazy) {
            foreach (fire($value) as $k => $v) {
                if ($k === $key) {
                    return $v;
                }
            }
            return null;
        }
        return $value[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        if (!$this->lazy) {
            if (isset($key)) {
                $this->value[$key] = $value;
            } else {
                $this->value[] = $value;
            }
        }
    }

    public function offsetUnset($key): void {
        if (!$this->lazy) {
            unset($this->value[$key]);
        }
    }

    public function pluck(string $key, $value = null) {
        $that = $this->mitose();
        $v = $this->value;
        if ($this->lazy) {
            $that->value = function () use ($key, $that, $v, $value) {
                yield from pluck(fire($v), $key, $value, $that);
            };
            return $that;
        }
        $that->value = pluck($v, $key, $value, $that);
        return $that;
    }

    public function reverse($keys = false) {
        $value = $this->value;
        if ($this->lazy) {
            $this->value = function () use ($keys, $value) {
                $lot = new SplStack;
                foreach (fire($value) as $k => $v) {
                    $lot[] = $v;
                }
                if ($keys) {
                    yield from $lot;
                } else {
                    foreach ($lot as $v) {
                        yield $v;
                    }
                }
            };
            return $this;
        }
        if (count($value) < 2) {
            return $this;
        }
        $value = array_reverse($value, $keys);
        $this->value = $keys ? $value : array_values($value);
        return $this;
    }

    public function set($key, $value = null) {
        if (is_iterable($key)) {
            if ($this->lazy) {
                $this->value = function () use ($key) {
                    yield from $key;
                };
                return $this;
            }
            return ($this->value = $key);
        }
        return set($this->getIterator(), $key, $value);
    }

    public function shake($keys = false) {
        $value = $this->value;
        if ($this->lazy) {
            $that = $this;
            $this->value = function () use ($keys, $that, $value) {
                yield from shake(y(fire($value)), is_callable($keys) ? that($keys, $that) : $keys); // :(
            };
            return $this;
        }
        $this->value = shake($value, is_callable($keys) ? that($keys, $this) : $keys);
        return $this;
    }

    public function sort($sort = 1, $keys = false) {}

    public static function from(...$lot) {
        if (isset($lot[0]) && is_string($lot[0])) {
            $lot[0] = explode($lot[1] ?? ', ', $lot[0]);
        }
        return new static(...$lot);
    }

}