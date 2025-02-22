<?php

class Anemone extends Genome {

    /*
    protected function _q(callable $fn, $sort = -1, $keys = false) {
        $q = new class extends SplPriorityQueue {
            public $k = -1;
            public $sort = -1;
            public function compare($a, $b) {
            }
            public function current() {

            }
            public function key() {

            }
        };
        if ($keys) {
            $q->k = 0;
        }
        $q->sort = $sort;
        return $q;
    }
    */

    public $join;
    public $lazy;
    public $lot;
    public $parent;

    public function __call(string $kin, array $lot = []) {
        if (property_exists($this, $kin) && (new ReflectionProperty($this, $kin))->isPublic()) {
            return $this->{$kin};
        }
        return parent::__call($kin, $lot);
    }

    public function __construct($lot = [], string $join = ', ') {
        $this->join = $join;
        if ($this->lazy = $lot instanceof Closure) {
            $this->lot = $lot;
        } else if (is_iterable($lot)) {
            $this->lot = y($lot);
        } else {
            $this->lot = []; // Broken :(
        }
    }

    public function __destruct() {
        $this->lot = [];
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
        $lot = ($filter ? $this->is(is_callable($filter) ? $filter : function ($v, $k) {
            // Ignore value(s) that cannot be converted to string
            if (is_array($v) || is_object($v) && !method_exists($v, '__toString')) {
                return false;
            }
            // Ignore `false` and `null` value(s)
            return false !== $v && null !== $v;
        }) : $this)->lot;
        if ($this->lazy) {
            return implode($join, y(fire($lot)));
        }
        return implode($join, $lot);
    }

    public function __serialize(): array {
        $lot = get_object_vars($this);
        unset($lot['parent']);
        if (is_callable($lot['lot'])) {
            $lot['lazy'] = false;
            $lot['lot'] = y(fire($lot['lot']));
        }
        return $lot;
    }

    public function __toString(): string {
        return (string) $this->join($this->join);
    }

    public function all(callable $fn) {
        return all($this->getIterator(), $fn, $this);
    }

    public function any(callable $fn) {
        return any($this->getIterator(), $fn, $this);
    }

    public function chunk(int $chunk = 5, int $part = -1, $keys = false) {
        $that = $this->mitose();
        if ($chunk < 1) {
            return $that;
        }
        $lot = $this->lot;
        if ($this->lazy) {
            $that->lot = that(function () use ($chunk, $keys, $lot, $part) {
                $lot = fire($lot);
                if ($part > -1) {
                    foreach (new LimitIterator($lot, $chunk * $part, $chunk) as $k => $v) {
                        if ($keys) {
                            yield $k => $v;
                        } else {
                            yield $v;
                        }
                    }
                } else {
                    $it = new ArrayIterator;
                    while ($lot->valid()) {
                        if ($keys) {
                            $it[$lot->key()] = $lot->current();
                        } else {
                            $it[] = $lot->current();
                        }
                        $lot->next();
                        if ($chunk === $it->count()) {
                            yield $it;
                            $it = new ArrayIterator;
                        }
                    }
                    if ($it->count()) {
                        yield $it;
                    }
                }
            }, $that);
            return $that;
        }
        $that->lot = $lot = array_chunk($lot, $chunk, $keys);
        if ($part > -1) {
            $that->lot = $lot[$part] ?? [];
        }
        return $that;
    }

    public function count(): int {
        if ($this->lazy) {
            $count = 0;
            foreach ($this->getIterator() as $v) {
                ++$count;
            }
            return $count;
        }
        return count($this->lot);
    }

    public function find(callable $fn) {
        return find($this->getIterator(), $fn, $this);
    }

    public function first($take = false) {
        if ($this->lazy || !$this->lot) {
            return null;
        }
        if ($take) {
            return array_shift($this->lot);
        }
        $first = reset($this->lot);
        return false !== $first ? $first : null;
    }

    public function get(?string $key = null) {
        return get($this->getIterator(), $key);
    }

    public function getIterator(): Traversable {
        $lot = $this->lot;
        return $this->lazy ? fire($lot) : new ArrayIterator($lot);
    }

    public function has(?string $key = null) {
        return has($this->getIterator(), $key);
    }

    public function index(string $key) {
        $lot = $this->lot;
        if ($this->lazy) {
            $i = 0;
            foreach (fire($lot) as $k => $v) {
                if ($k === $key) {
                    return $i;
                }
                ++$i;
            }
            return null;
        }
        if (!$lot || !array_key_exists($key, $lot)) {
            return null;
        }
        $index = array_search($key, array_keys($lot));
        return false !== $index ? $index : null;
    }

    public function is(callable $fn, $keys = false) {
        $lot = $this->lot;
        $that = $this->mitose();
        if ($this->lazy) {
            $that->lot = that(function () use ($fn, $keys, $lot) {
                foreach (is(fire($lot), $fn, $keys, $this) as $k => $v) {
                    yield $k => $v;
                }
            }, $that);
            return $that;
        }
        $that->lot = is($lot, $fn, $keys, $that);
        return $that;
    }

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    public function key(int $index) {
        $lot = $this->lot;
        if ($this->lazy) {
            $i = 0;
            foreach (fire($lot) as $k => $v) {
                if ($i === $index) {
                    return $k;
                }
                ++$i;
            }
            return null;
        }
        if (!$lot || $index > count($lot)) {
            return null;
        }
        $keys = array_keys($lot);
        return array_key_exists($index, $keys) ? (string) $keys[$index] : null;
    }

    public function last($take = false) {
        if ($this->lazy || !$this->lot) {
            return null;
        }
        if ($take) {
            return array_pop($this->lot);
        }
        $last = end($this->lot);
        return false !== $last ? $last : null;
    }

    public function let(?string $key = null) {
        if (isset($key)) {
            return let($this->getIterator(), $key);
        }
        $this->lot = [];
        return true;
    }

    public function map(callable $fn) {
        $lot = $this->lot;
        $that = $this->mitose();
        if ($this->lazy) {
            $that->lot = that(function () use ($fn, $lot) {
                foreach (map(fire($lot), $fn, $this) as $k => $v) {
                    yield $k => $v;
                }
            }, $that);
            return $that;
        }
        $that->lot = map($lot, $fn, $that);
        return $that;
    }

    public function mitose() {
        $that = new static($this->lot, $this->join);
        $that->parent = $this;
        return $that;
    }

    public function not(callable $fn, $keys = false) {
        $lot = $this->lot;
        $that = $this->mitose();
        if ($this->lazy) {
            $that->lot = that(function () use ($fn, $keys, $lot) {
                foreach (not(fire($lot), $fn, $keys, $this) as $k => $v) {
                    yield $k => $v;
                }
            }, $that);
            return $that;
        }
        $that->lot = not($lot, $fn, $keys, $that);
        return $that;
    }

    public function offsetExists($key): bool {
        $lot = $this->lot;
        if ($this->lazy) {
            foreach (fire($lot) as $k => $v) {
                if ($k === $key) {
                    return true;
                }
            }
            return false;
        }
        return isset($lot[$key]);
    }

    public function offsetGet($key) {
        $lot = $this->lot;
        if ($this->lazy) {
            foreach (fire($lot) as $k => $v) {
                if ($k === $key) {
                    return $v;
                }
            }
            return null;
        }
        return $lot[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        if (!$this->lazy) {
            if (isset($key)) {
                $this->lot[$key] = $value;
            } else {
                $this->lot[] = $value;
            }
        }
    }

    public function offsetUnset($key): void {
        if (!$this->lazy) {
            unset($this->lot[$key]);
        }
    }

    public function pluck(string $key, $value = null) {
        $lot = $this->lot;
        $that = $this->mitose();
        if ($this->lazy) {
            $that->lot = that(function () use ($key, $lot, $value) {
                foreach (pluck(fire($lot), $key, $value, $this) as $k => $v) {
                    yield $k => $v;
                }
            }, $that);
            return $that;
        }
        $that->lot = pluck($lot, $key, $value, $that);
        return $that;
    }

    public function rank(callable $fn, $keys = false) {
        return $this->vote($fn, $keys)->reverse($keys);
    }

    public function reverse($keys = false) {
        $lot = $this->lot;
        if ($this->lazy) {
            $this->lot = that(function () use ($keys, $lot) {
                $r = new SplDoublyLinkedList;
                $r->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE | SplDoublyLinkedList::IT_MODE_LIFO);
                foreach (fire($lot) as $k => $v) {
                    $r->push($keys ? [$k, $v] : $v);
                }
                foreach ($r as $v) {
                    if ($keys) {
                        yield $v[0] => $v[1];
                    } else {
                        yield $v;
                    }
                }
            }, $this);
            return $this;
        }
        if (count($lot) < 2) {
            return $this;
        }
        $lot = array_reverse($lot, $keys);
        $this->lot = $keys ? $lot : array_values($lot);
        return $this;
    }

    public function set($key, $value = null) {
        if (is_iterable($key)) {
            if ($this->lazy) {
                $this->lot = that(function () use ($key) {
                    yield from $key;
                }, $this);
                return $this;
            }
            return ($this->lot = $key);
        }
        return set($this->getIterator(), $key, $value);
    }

    public function shake($keys = false) {
        $lot = $this->lot;
        if ($this->lazy) {
            $this->lot = that(function () use ($keys, $lot) {
                foreach (shake(y(fire($lot)), $keys, $this) as $k => $v) {
                    yield $k => $v;
                }
            }, $this);
            return $this;
        }
        $this->lot = shake($lot, $keys, $this);
        return $this;
    }

    public function sort($sort = 1, $keys = false) {
        $lot = $this->lot;
        if (is_callable($sort)) {
            $fn = that($sort, $this);
            if ($this->lazy) {
                $this->lot = that(function () use ($fn, $keys, $lot) {
                    $lot = y(fire($lot)); // :(
                    $keys ? uasort($lot, $fn) : usort($lot, $fn);
                    foreach ($lot as $k => $v) {
                        yield $k => $v;
                    }
                }, $this);
            } else {
                $keys ? uasort($this->lot, $fn) : usort($this->lot, $fn);
            }
            return $this;
        }
        if ($this->lazy) {
            if (is_array($sort) && false !== ($key = $sort[1] ?? false)) {
                $d = $sort[0];
                $this->{-1 === $d || SORT_DESC === $d ? 'vote' : 'rank'}(function ($v) use ($key) {
                    return $v[$key] ?? null;
                }, $keys);
                return $this;
            }
            $this->lot = that(function () use ($lot, $sort) {
                $d = is_array($sort) ? reset($sort) : $sort;
                $lot = y(fire($lot)); // :(
                if ($keys) {
                    -1 === $d || SORT_DESC === $d ? arsort($lot) : asort($lot);
                } else {
                    -1 === $d || SORT_DESC === $d ? rsort($lot) : sort($lot);
                }
                yield from $lot;
            }, $this);
            return $this;
        }
        if (count($lot) < 2) {
            if (!$keys && $lot) {
                $this->lot = [reset($lot)];
            }
            return $this;
        }
        if (is_array($sort) && false !== ($key = $sort[1] ?? false)) {
            $d = $sort[0];
            $fn = -1 === $d || SORT_DESC === $d ? static function ($a, $b) use ($key) {
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
                foreach ($lot as &$v) {
                    if (is_array($v) && !isset($v[$key])) {
                        $v[$key] = $sort[2];
                    }
                }
                unset($v);
            }
            $keys ? uasort($lot, $fn) : usort($lot, $fn);
        } else {
            $d = is_array($sort) ? reset($sort) : $sort;
            if ($keys) {
                -1 === $d || SORT_DESC === $d ? arsort($lot) : asort($lot);
            } else {
                -1 === $d || SORT_DESC === $d ? rsort($lot) : sort($lot);
            }
        }
        $this->lot = $lot;
        return $this;
    }

    public function vote(callable $fn, $keys = false) {
        $fn = that($fn, $this);
        $lot = $this->getIterator();
        $max = PHP_INT_MAX;
        $q = new SplPriorityQueue;
        $q->setExtractFlags(SplPriorityQueue::EXTR_DATA);
        foreach ($lot as $k => $v) {
            $stack = call_user_func($fn, $v, $k) ?? PHP_INT_MIN;
            $q->insert($keys ? [$k, $v] : $v, [$stack, $max--]);
        }
        if ($this->lazy) {
            $this->lot = that(function () use ($keys, $q) {
                while (!$q->isEmpty()) {
                    $v = $q->extract();
                    if ($keys) {
                        yield $v[0] => $v[1];
                    } else {
                        yield $v;
                    }
                }
            }, $this);
            unset($v);
            return $this;
        }
        $lot = [];
        while (!$q->isEmpty()) {
            $v = $q->extract();
            if ($keys) {
                $lot[$v[0]] = $v[1];
            } else {
                $lot[] = $v;
            }
        }
        $this->lot = $lot;
        unset($q);
        return $this;
    }

    public static function from(...$lot) {
        if (isset($lot[0]) && is_string($lot[0])) {
            $lot[0] = explode($lot[1] ?? ', ', $lot[0]);
        }
        return new static(...$lot);
    }

}