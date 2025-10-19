<?php

class Anemone extends Genome {

    protected $join;
    protected $lot;
    protected $parent;

    protected function _q(callable $at, $sort = -1, $keys = false, $that = null) {
        $count = $i = 0;
        $n = PHP_INT_MAX;
        $it = new class extends SplPriorityQueue {
            public $sort = -1;
            public function compare($a, $b): int {
                return -1 === ($d = $this->sort) || SORT_DESC === $d ? $a <=> $b : $b <=> $a;
            }
        };
        $it->setExtractFlags(SplPriorityQueue::EXTR_DATA);
        $it->sort = $sort;
        foreach ($this->lot as $k => $v) {
            $it->insert($v = [$v, $keys ? $k : null], [fire($at, $v, $that) ?? PHP_INT_MIN, --$n]);
            ++$count;
        }
        $r = $keys ? new ArrayIterator : new SplFixedArray($count);
        while ($it->valid()) {
            [$v, $k] = $it->extract();
            $r[$k ?? $i++] = $v;
        }
        $this->lot = $r;
        return $this;
    }

    public function __call(string $kin, array $lot = []) {
        if (property_exists($this, $kin) && (new ReflectionProperty($this, $kin))->isPublic()) {
            return $this->{$kin};
        }
        return parent::__call($kin, $lot);
    }

    public function __construct(iterable $lot = [], string $join = ', ') {
        $this->join = $join;
        $this->lot = is_array($lot) && array_is_list($lot) ? SplFixedArray::fromArray($lot, false) : $lot;
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
            // Ignore value(s) that cannot be converted to a string
            if (is_array($v) || is_object($v) && !method_exists($v, '__toString')) {
                return false;
            }
            // Ignore `false` and `null` value(s)
            return false !== $v && null !== $v;
        }) : $this)->lot;
        return implode($join, y($lot));
    }

    public function __serialize(): array {
        $lot = get_object_vars($this);
        $lot['lot'] = y($lot['lot'] ?? []);
        unset($lot['parent']);
        return $lot;
    }

    public function __toString(): string {
        return (string) $this->join($this->join);
    }

    public function all($valid) {
        return all($this->lot, $valid, $this);
    }

    public function any($valid) {
        return any($this->lot, $valid, $this);
    }

    public function chunk(int $chunk = 5, int $part = -1, $keys = false) {
        $that = $this->mitose();
        if ($chunk < 1) {
            return $that;
        }
        if (is_array($lot = $this->lot)) {
            $that->lot = $lot = array_chunk($lot, $chunk, $keys);
            if ($part > -1) {
                $that->lot = $lot[$part] ?? [];
            }
            return $that;
        }
        $it = new SplFixedArray(ceil(($q = $this->count()) / $chunk));
        $k = -1;
        foreach ($lot as $v) {
            if (0 === ($i = (int) ++$k % $chunk)) {
                $it[$k / $chunk] = $fix = new SplFixedArray($chunk);
            }
            $fix[$i] = $v;
        }
        if (($rest = (int) $q % $chunk) > 0) {
            $fix->setSize($rest);
        }
        if ($part > -1) {
            $it = $it[$part] ?? new SplFixedArray(0);
        }
        $that->lot = $it;
        return $that;
    }

    public function count(): int {
        return q($this->lot);
    }

    public function find(callable $valid) {
        return find($this->lot, $valid, $this);
    }

    public function first($take = false) {
        if (!$this->count()) {
            return null;
        }
        if (is_array($lot = $this->lot)) {
            if ($take) {
                return array_shift($this->lot);
            }
            return false !== ($v = reset($lot)) ? $v : null;
        }
        // `SplDoublyLinkedList`
        // `SplQueue`
        // `SplStack`
        if ($lot instanceof SplDoublyLinkedList) {
            return $lot->{$take ? 'shift' : 'top'}();
        }
        // `SplFixedArray`
        if ($lot instanceof SplFixedArray) {
            $v = $lot->offsetGet(0) ?? null;
            if ($take) {
                $r = new SplFixedArray($size = $lot->getSize() - 1);
                for ($i = 0; $i < $size; ++$i) {
                    $r->offsetSet($i, $lot->offsetGet($i + 1));
                }
                $this->lot = $r;
            }
            return $v;
        }
        // `SplHeap`
        // `SplMaxHeap`
        // `SplMinHeap`
        // `SplPriorityQueue`
        if ($lot instanceof SplHeap) {
            return $lot->top(); // Read only :(
        }
        foreach ($lot as $k => $v) {
            if ($take) {
                unset($this->lot[$k]);
            }
            return $v;
        }
        return null;
    }

    public function get(?string $key = null) {
        if (isset($key) && !$this->count()) {
            return null;
        }
        if (is_array($lot = $this->lot)) {
            return isset($key) ? get($lot, $key) : $lot;
        }
        // `SplDoublyLinkedList`
        // `SplFixedArray`
        // `SplQueue`
        // `SplStack`
        if ($lot instanceof ArrayAccess) {
            return isset($key) ? ($lot->offsetGet($key) ?? null) : $lot;
        }
        // `SplHeap`
        // `SplMaxHeap`
        // `SplMinHeap`
        // `SplPriorityQueue`
        if ($lot instanceof SplHeap) {
            // A “heap” is not a key-value storage. There is only one useful access method —the `top()` method— to
            // access the top item. The only accepted key is `0` because it is identical to the first item in the array.
            if (0 === (int) $key) {
                return $lot->top();
            }
        }
        return null;
    }

    public function getIterator(): Traversable {
        return is_array($lot = $this->lot) ? new ArrayIterator($lot) : $lot;
    }

    public function has(?string $key = null) {
        if (!$this->count()) {
            return false;
        }
        if (is_array($lot = $this->lot)) {
            return has($lot, $key);
        }
        // `SplDoublyLinkedList`
        // `SplFixedArray`
        // `SplQueue`
        // `SplStack`
        if ($lot instanceof ArrayAccess) {
            return $lot->offsetExists($key);
        }
        // `SplHeap`
        // `SplMaxHeap`
        // `SplMinHeap`
        // `SplPriorityQueue`
        if ($lot instanceof SplHeap) {
            if (0 === (int) $key) {
                return !!$lot->top();
            }
        }
        return false;
    }

    public function index(string $key) {
        if (!$this->count()) {
            return null;
        }
        if (is_array($lot = $this->lot)) {
            if (!array_key_exists($key, $lot)) {
                return null;
            }
            $index = array_search($key, array_keys($lot));
            return false !== $index ? $index : null;
        }
        $i = 0;
        foreach ($lot as $k => $v) {
            if ($k === $key) {
                return $i;
            }
            ++$i;
        }
        return null;
    }

    public function is(callable $valid, $keys = false) {
        $that = $this->mitose();
        $that->lot = is($this->lot, $valid, $keys, $that);
        return $that;
    }

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    public function key(int $index) {
        if (!($q = $this->count()) || $index > $q) {
            return null;
        }
        if (is_array($lot = $this->lot)) {
            $keys = array_keys($lot);
            return array_key_exists($index, $keys) ? (string) $keys[$index] : null;
        }
        $i = 0;
        foreach (fire($lot) as $k => $v) {
            if ($i === $index) {
                return $k;
            }
            ++$i;
        }
        return null;
    }

    public function last($take = false) {
        if (!$q = $this->count()) {
            return null;
        }
        if (is_array($lot = $this->lot)) {
            if ($take) {
                return array_pop($this->lot);
            }
            return false !== ($v = end($lot)) ? $v : null;
        }
        // `SplDoublyLinkedList`
        // `SplQueue`
        // `SplStack`
        if ($lot instanceof SplDoublyLinkedList) {
            return $lot->{$take ? 'pop' : 'bottom'}();
        }
        // `SplFixedArray`
        if ($lot instanceof SplFixedArray) {
            $v = $lot->offsetGet($size = $lot->getSize() - 1) ?? null;
            if ($take) {
                $lot->setSize($size);
            }
            return $v;
        }
        // `SplHeap`
        // `SplMaxHeap`
        // `SplMinHeap`
        // `SplPriorityQueue`
        if ($lot instanceof SplHeap) {
            return null; // There is no “bottom” in a “heap” :(
        }
        $index = 0;
        foreach ($lot as $k => $v) {
            if ($q === ++$index) {
                if ($take) {
                    unset($this->lot[$k]);
                }
                return $v;
            }
        }
        return null;
    }

    public function let(?string $key = null) {
        if (isset($key)) {
            if (is_array($lot = $this->lot)) {
                return let($this->lot, $key);
            }
            // `SplDoublyLinkedList`
            // `SplFixedArray`
            // `SplQueue`
            // `SplStack`
            if ($lot instanceof ArrayAccess) {
                if ($lot instanceof SplFixedArray) {
                    $count = $lot->count();
                    $key = (int) $key;
                    if ($key < 0 || $key >= $count) {
                        return false;
                    }
                    $k = 0;
                    $r = new SplFixedArray($count - 1);
                    for ($i = 0; $i < $count; ++$i) {
                        if ($key === $i) {
                            continue;
                        }
                        $r->offsetSet($k++, $lot->offsetGet($i));
                    }
                    $this->lot = $r;
                    return true;
                }
                return $lot->offsetUnset($key);
            }
            return false;
        }
        $this->lot = [];
        return true;
    }

    public function map(callable $at) {
        $that = $this->mitose();
        $that->lot = map($this->lot, $at, $that);
        return $that;
    }

    public function mitose() {
        $that = new static($this->lot, $this->join);
        $that->parent = $this;
        return $that;
    }

    public function not($valid, $keys = false) {
        $that = $this->mitose();
        $that->lot = not($this->lot, $valid, $keys, $that);
        return $that;
    }

    public function offsetExists($key): bool {
        if (is_array($lot = $this->lot)) {
            return isset($lot[$key]);
        }
        if ($lot instanceof ArrayAccess) {
            return $lot->offsetExists($key);
        }
        foreach ($lot as $k => $v) {
            if ($k === $key) {
                return true;
            }
        }
        return false;
    }

    public function offsetGet($key) {
        if (is_array($lot = $this->lot)) {
            return $lot[$key] ?? null;
        }
        if ($lot instanceof ArrayAccess) {
            return $lot->offsetGet($key);
        }
        foreach ($lot as $k => $v) {
            if ($k === $key) {
                return $v;
            }
        }
        return null;
    }

    public function offsetSet($key, $value): void {
        if (is_array($lot = $this->lot)) {
            if (isset($key)) {
                $lot[$key] = $value;
            } else {
                $lot[] = $value;
            }
        } else if ($lot instanceof ArrayAccess) {
            if (isset($key)) {
                $lot->offsetSet($key, $value);
            } else {
                if ($lot instanceof SplFixedArray) {
                    $lot->setSize(($key = $lot->getSize()) + 1);
                }
                $lot->offsetSet($key, $value);
            }
        }
        $this->lot = $lot;
    }

    public function offsetUnset($key): void {
        if (is_array($lot = $this->lot)) {
            unset($lot[$key]);
        } else if ($lot instanceof ArrayAccess) {
            $lot->offsetUnset($key);
        }
        $this->lot = $lot;
    }

    public function parent() {
        return $this->parent;
    }

    public function pluck(string $key, $value = null) {
        $that = $this->mitose();
        $that->lot = pluck($this->lot, $key, $value, $that);
        return $that;
    }

    public function rank(callable $at, $keys = false) {
        return $this->_q($at, 1, $keys, $this);
    }

    public function reverse($keys = false) {
        if (is_array($lot = $this->lot)) {
            if (count($lot) < 2) {
                return $this;
            }
            $lot = array_reverse($lot, $keys);
            $this->lot = $keys ? $lot : array_values($lot);
            return $this;
        }
        return $this->_q(function ($v, $k) {
            return 1;
        }, 1, $keys, $this);
    }

    public function set($key, $value = null) {
        if (is_array($lot = $this->lot)) {
            return set($this->lot, $key, $value);
        }
        // `SplDoublyLinkedList`
        // `SplFixedArray`
        // `SplQueue`
        // `SplStack`
        if ($lot instanceof ArrayAccess) {
            return $lot->offsetSet($key, $value);
        }
        // `SplHeap`
        // `SplMaxHeap`
        // `SplMinHeap`
        // `SplPriorityQueue`
        if ($lot instanceof SplHeap) {
            return null;
        }
        return null;
    }

    public function shake($keys = false) {
        $this->lot = shake(y($this->lot), $keys, $this);
        return $this;
    }

    public function sort($sort = 1, $keys = false) {
        if (is_array($lot = $this->lot)) {
            if (count($lot) < 2) {
                if (!$keys && $lot) {
                    $this->lot = [reset($lot)];
                }
                return $this;
            }
            if (is_callable($sort)) {
                $test = cue($sort, $this);
                $keys ? uasort($this->lot, $test) : usort($this->lot, $test);
                return $this;
            }
            if (is_array($sort) && false !== ($key = $sort[1] ?? false)) {
                $d = reset($sort);
                $dot = false !== strpos(strtr($key, ["\\." => \P]), '.');
                $test = -1 === $d || SORT_DESC === $d ? static function ($a, $b) use ($dot, $key) {
                    if (!is_array($b) || !is_array($a)) {
                        return 0;
                    }
                    if (($dot && null === get($b, $key) && null === get($a, $key)) || !isset($b[$key]) && !isset($a[$key])) {
                        return 0;
                    }
                    if (($dot && null === get($b, $key)) || !isset($b[$key])) {
                        return 1;
                    }
                    if (($dot && null === get($a, $key)) || !isset($a[$key])) {
                        return -1;
                    }
                    return $dot ? get($b, $key) <=> get($a, $key) : $b[$key] <=> $a[$key];
                } : static function ($a, $b) use ($dot, $key) {
                    if (!is_array($a) || !is_array($b)) {
                        return 0;
                    }
                    if (($dot && null === get($a, $key) && null === get($b, $key)) || !isset($a[$key]) && !isset($b[$key])) {
                        return 0;
                    }
                    if (($dot && null === get($a, $key)) || !isset($a[$key])) {
                        return 1;
                    }
                    if (($dot && null === get($b, $key)) || !isset($b[$key])) {
                        return -1;
                    }
                    return $dot ? get($a, $key) <=> get($b, $key) : $a[$key] <=> $b[$key];
                };
                if (array_key_exists(2, $sort)) {
                    if ($dot) {
                        foreach ($lot as &$v) {
                            if (is_array($v) && null === get($v, $key)) {
                                set($v, $key, $sort[2]);
                            }
                        }
                    } else {
                        foreach ($lot as &$v) {
                            if (is_array($v) && !isset($v[$key])) {
                                $v[$key] = $sort[2];
                            }
                        }
                    }
                    unset($v);
                }
                $keys ? uasort($lot, $test) : usort($lot, $test);
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
        if (is_array($sort) && false !== ($key = $sort[1] ?? false)) {
            $dot = false !== strpos(strtr($key, ["\\." => \P]), '.');
            $value = array_key_exists(2, $sort) ? $sort[2] : null;
            return $this->_q(function ($v, $k) use ($dot, $key, $value) {
                if ($dot && is_array($v)) {
                    return get($v, $key) ?? $value;
                }
                return $v[$key] ?? $value;
            }, $sort[0], $keys, $this);
        }
        $d = is_array($sort) ? reset($sort) : $sort;
        $lot = y($lot); // :(
        if ($keys) {
            -1 === $d || SORT_DESC === $d ? arsort($lot) : asort($lot);
        } else {
            -1 === $d || SORT_DESC === $d ? rsort($lot) : sort($lot);
        }
        $this->lot = $lot;
        return $this;
    }

    public function vote(callable $at, $keys = false) {
        return $this->_q($at, -1, $keys, $this);
    }

    public static function from(...$lot) {
        if (isset($lot[0]) && is_string($lot[0])) {
            $lot[0] = explode($lot[1] ?? ', ', $lot[0]);
        }
        return new static(...$lot);
    }

}