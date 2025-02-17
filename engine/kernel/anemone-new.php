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

    public function all($fn) {}

    public function any($fn) {}

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

    public function find($fn) {}

    public function first($take = false) {}

    public function get(?string $key = null) {}

    public function getIterator(): Traversable {
        return is_array($value = $this->value) ? new ArrayIterator($value) : $value;
    }

    public function has(?string $key = null) {}

    public function index(string $key) {}

    public function is($fn, $keys = false) {}

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    public function key(int $index) {}

    public function last($take = false) {}

    public function let(?string $key = null) {}

    public function map(callable $fn) {}

    public function mitose() {
        $that = new static($this->value, $this->join);
        $that->parent = $this;
        return $that;
    }

    public function not($fn, $keys = false) {}

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
        $that = $this->mitose();
        if (is_array($value = $that->value)) {
            $value = array_reverse($value, $keys);
            $that->value = $keys ? $value : array_values($value);
            return $that;
        }
        if ($keys) {
            $stack = new SplStack;
            foreach ($value as $v) {
                $stack[] = $v;
            }
            $that->value = $stack;
            return $that;
        }
        // To preserve the stream key is more memory-efficient for now :(
        $that->value = SplFixedArray::fromArray(array_reverse(y($value)));
        return $that;
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