<?php

class Pages extends Anemone {

    public function getIterator(): \Traversable {
        foreach ($this->value as $k => $v) {
            yield $k => is_array($v) ? $this->page(null, $v) : $this->page($v);
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key) {
        $value = parent::offsetGet($key);
        if (is_array($value)) {
            return $this->page(null, $value);
        }
        return $this->page($value);
    }

    public function page(string $path = null, array $lot = []) {
        return new Page($path, $lot);
    }

    public function sort($sort = 1, $preserve_key = false) {
        if (count($value = $this->value) <= 1) {
            if (!$preserve_key) {
                $this->value = array_values($this->value);
            }
            return $this;
        }
        if (is_array($sort)) {
            $value = [];
            if (isset($sort[1])) {
                foreach ($this->value as $k) {
                    $f = $this->page($k);
                    if (is_string($v = $f[$sort[1]] ?? $f->{$sort[1]} ?? $sort[2] ?? null)) {
                        $v = strip_tags($v); // Ignore HTML tag(s)
                    }
                    $value[$k] = $v;
                }
            }
            -1 === $sort[0] ? arsort($value) : asort($value);
            $this->value = array_keys($value);
        } else {
            $value = $this->value;
            if ($preserve_key) {
                -1 === $sort ? arsort($value) : asort($value);
            } else {
                -1 === $sort ? rsort($value) : sort($value);
            }
            $this->value = $value;
        }
        return $this;
    }

    public static function from(...$lot) {
        if (is_array($v = reset($lot))) {
            return new static($v);
        }
        $pages = [];
        foreach (g($lot[0] ?? LOT . D . 'page', $lot[1] ?? 'page', $lot[2] ?? 0) as $k => $v) {
            if ("" === pathinfo($k, PATHINFO_FILENAME)) {
                continue; // Ignore placeholder page(s)
            }
            $pages[] = $k;
        }
        return new static($pages);
    }

}