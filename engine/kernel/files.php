<?php

class Files extends Anemon {

    public function getIterator() {
        $files = [];
        foreach ($this->value as $k => $v) {
            yield $k => $this->file($v);
        }
    }

    public function file(string $path): \ArrayAccess {
        return new File($path);
    }

    public function offsetGet($i) {
        return $this->file($this->value[$i] ?? null);
    }

    public function sort($sort = 1, $preserve_key = false) {
        if (is_array($sort)) {
            $value = [];
            if (isset($sort[1])) {
                foreach ($this->value as $v) {
                    $f = $this->file($v);
                    $value[$v] = $f[$sort[1]] ?? $f->{$sort[1]} ?? $sort[2] ?? null;
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
        return new static(array_keys(y(g($lot[0] ?? ROOT, $lot[1] ?? 1, $lot[2] ?? 0))));
    }

}