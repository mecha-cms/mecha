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