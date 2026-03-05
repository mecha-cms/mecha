<?php

class Anemone extends Genome {

    protected $join;
    protected $lot;
    protected $v;

    public function __call(string $kin, array $lot = []) {
        if (property_exists($this, $kin) && (new ReflectionProperty($this, $kin))->isPublic()) {
            return $this->{$kin};
        }
        return parent::__call($kin, $lot);
    }

    public function __construct(iterable $lot = [], string $join = ', ') {
        $this->join = $join;
        $this->lot = y($lot);
    }

    public function __get(string $key): mixed {
        if (method_exists($this, $key) && (new ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return $this->__call($key);
    }

    public function __invoke(string $join = ', ', $valid = true): mixed {
        $lot = ($valid ? $this->is(is_callable($valid) ? $valid : function ($v, $k) {
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
        $z = get_object_vars($this);
        unset($z['v']);
        return $z;
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

    public function batch() {
        return new static($this->v, $this->join);
    }

    public function chunk(int $chunk = 5, int $at = -1, $keys = false) {
        $that = new static($this->lot, $this->join);
        if ($chunk < 1) {
            return $that;
        }
        $that->v = $lot = $that->lot;
        $lot = array_chunk($lot, $chunk, $keys);
        if ($at > -1) {
            $lot = $lot[$at] ?? [];
        }
        $that->lot = $lot;
        return $that;
    }

    public function count(): int {
        return count($this->lot);
    }

    public function find(callable $valid) {
        return find($this->lot, $valid, $this);
    }

    public function first($take = false) {
        if (!$this->count()) {
            return null;
        }
        return $take ? array_shift($this->lot) : first($this->lot);
    }

    public function get(?string $key = null) {
        if (!$this->count()) {
            return null;
        }
        return isset($key) ? get($this->lot, $key) : $this->lot;
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->lot);
    }

    public function has(?string $key) {
        return has($this->lot, $key);
    }

    public function index(string $key) {
        if (!$this->count() || !array_key_exists($key, $lot = $this->lot)) {
            return null;
        }
        $at = 0;
        foreach ($lot as $k => $v) {
            if ($key === $k) {
                return $at;
            }
            ++$at;
        }
        return null;
    }

    public function is(callable $valid, $keys = false) {
        return new static(is($this->lot, $valid, $keys, $this), $this->join);
    }

    public function join(string $join = ', ') {
        return $this->__invoke($join);
    }

    public function key(int $index) {
        if ($index < 0 || $index >= $this->count()) {
            return null;
        }
        $at = 0;
        foreach ($this->lot as $k => $v) {
            if ($index === $at) {
                return $k;
            }
            ++$at;
        }
        return null;
    }

    public function last($take = false) {
        if (!$this->count()) {
            return null;
        }
        return $take ? array_pop($this->lot) : last($this->lot);
    }

    public function let(?string $key = null) {
        if (isset($key)) {
            return let($this->lot, $key);
        }
        $this->lot = [];
        return true;
    }

    public function map(callable $at) {
        return new static(map($this->lot, $at, $this), $this->join);
    }

    public function not($valid, $keys = false) {
        return new static(not($this->lot, $valid, $keys, $this), $this->join);
    }

    public function offsetExists($key): bool {
        return null !== $this->offsetGet($key);
    }

    public function offsetGet($key): mixed {
        return $this->lot[$key] ?? null;
    }

    public function offsetSet($key, $value): void {
        $lot = $this->lot;
        if (isset($key)) {
            $lot[$key] = $value;
        } else {
            $lot[] = $value;
        }
        $this->lot = $lot;
    }

    public function offsetUnset($key): void {
        unset($this->lot[$key]);
    }

    public function pluck(string $key, $value = null) {
        return new static(pluck($this->lot, $key, $value, $this), $this->join);
    }

    public function reverse($keys = false) {
        return new static(array_reverse($this->lot), $this->join);
    }

    public function set($key, $value = null) {
        return set($this->lot, $key, $value);
    }

    public function shake($keys = false) {
        return new static(shake($this->lot, $keys), $this->join);
    }

    public function sort($sort = 1, $keys = false) {
        if (count($lot = $this->lot) < 2) {
            if (!$keys && $lot) {
                $this->lot = [current($lot)];
            }
            return $this;
        }
        if (is_callable($sort)) {
            $c = cue($sort, $this);
            $keys ? uasort($lot, $c) : usort($lot, $c);
            $this->lot = $lot;
            return $this;
        }
        if (is_array($sort)) {
            [$d, $k, $v] = array_replace([1, "", null], $sort);
        } else {
            $d = $sort;
            $k = $v = null;
        }
        $desc = -1 === $d || SORT_DESC === $d;
        if (null !== $k) {
            $c = is_string($k) && false !== strpos(strtr($k, ["\\." => P]), '.') ? static function ($a, $b) use ($desc, $k, $v) {
                $a = is_array($a) ? (get($a, $k) ?? $v) : $v;
                $b = is_array($b) ? (get($b, $k) ?? $v) : $v;
                $c = $a <=> $b;
                return $desc ? -$c : $c;
            } : static function ($a, $b) use ($desc, $k, $v) {
                $a = is_array($a) ? ($a[$k] ?? $v) : $v;
                $b = is_array($b) ? ($b[$k] ?? $v) : $v;
                $c = $a <=> $b;
                return $desc ? -$c : $c;
            };
            $keys ? uasort($lot, $c) : usort($lot, $c);
        } else {
            $keys ? ($desc ? arsort($lot) : asort($lot)) : ($desc ? rsort($lot) : sort($lot));
        }
        $this->lot = $lot;
        return $this;
    }

    public static function from(...$lot) {
        if (isset($lot[0]) && is_string($lot[0])) {
            $lot[0] = explode($lot[1] ?? ', ', $lot[0]);
        }
        return new static(...$lot);
    }

}