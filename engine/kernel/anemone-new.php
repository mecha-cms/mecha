<?php

class AnemoneNew extends Genome {

    public $join;
    public $parent;
    public $value;

    public function __call(string $kin, array $lot = []) {}

    public function __construct(iterable $value = [], string $join = ', ') {
        $this->join = $join;
        if (is_array($value) || is_object($value) && $value instanceof stdClass) {
            $value = (array) $value;
            $this->value = array_is_list($value) ? SplFixedArray::fromArray($value, false) : $value;
        } else {
            $this->value = $value;
        }
    }

    public function __destruct() {
        $this->lot = [];
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
        return all($this->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn);
    }

    public function any($fn) {
        return any($this->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn);
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
        // <https://gist.github.com/shadowhand/9b402c62f520c7219c30566422f93bd5>
        $k = -1;
        $that->value = new SplFixedArray(ceil(($n = count($value)) / $chunk));
        foreach ($value as $v) {
            if (0 === ($i = (int) ++$k % $chunk)) {
                $that->value[$k / $chunk] = $fix = new SplFixedArray($chunk);
            }
            $fix[$i] = $v;
        }
        if (($rest = (int) $n % $chunk) > 0) {
            $fix->setSize($rest);
        }
        if ($part > -1) {
            $that->value = $that->value[$part] ?? new SplFixedArray(0);
        }
        return $that;
    }

    public function count(): int {
        return count($this->value);
    }

    public function find($fn) {
        return find($this->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn);
    }

    public function first($take = false) {}

    public function get(?string $key = null) {}

    public function getIterator(): Traversable {
        return is_array($value = $this->value) ? new ArrayIterator($value) : $value;
    }

    public function has(?string $key = null) {}

    public function index(string $key) {}

    public function is($fn, $keys = false) {
        $that = $this->mitose();
        $that->value = is($that->value, is_callable($fn) ? Closure::fromCallable($fn)->bindTo($this) : $fn, $keys);
        return $that;
    }

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    public function key(int $index) {}

    public function last($take = false) {}

    public function let(?string $key = null) {}

    public function map(callable $fn) {
        $that = $this->mitose();
        $that->value = map($that->value, Closure::fromCallable($fn)->bindTo($this));
        return $that;
    }

    public function mitose() {
        $that = new static($this->value, $this->join);
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
        $list = new SplDoublyLinkedList;
        $list->setIteratorMode(SplDoublyLinkedList::IT_MODE_KEEP | SplDoublyLinkedList::IT_MODE_LIFO);
        foreach ($value as $k => $v) {
            $list[] = $v;
        }
        $this->value = $list;
        // Another instance of `SplDoublyLinkedList` just to sort the key(s) :(
        if (!$keys) {
            $list = new SplDoublyLinkedList;
            $list->setIteratorMode(SplDoublyLinkedList::IT_MODE_KEEP | SplDoublyLinkedList::IT_MODE_FIFO);
            $value = $this->value;
            $value->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE | SplDoublyLinkedList::IT_MODE_LIFO);
            foreach ($value as $k => $v) {
                $list[] = $v;
            }
            $this->value = $list;
        }
        return $this;
    }

    public function set($key, $value = null) {}

    public function shake($keys = false) {}

    public function sort($sort = 1, $keys = false) {}

    public static function from(...$lot) {
        if (isset($lot[0]) && is_string($lot[0])) {
            $lot[0] = explode($lot[1] ?? ', ', $lot[0]);
        }
        return new static(...$lot);
    }

}