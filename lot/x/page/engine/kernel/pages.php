<?php

class Pages extends Anemone {

    public function getIterator() {
        foreach ($this->value as $k => $v) {
            yield $k => $this->page($v);
        }
    }

    public function offsetGet($key) {
        return $this->page(parent::offsetGet($key));
    }

    public function page(string $path) {
        return new Page($path);
    }

    public static function from(...$lot) {
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