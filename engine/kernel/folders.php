<?php

class Folders extends Anemon {

    public function getIterator() {
        $files = [];
        foreach ($this->value as $k => $v) {
            yield $k => $this->folder($v);
        }
    }

    public function folder(string $path): \ArrayAccess {
        return new Folder($path);
    }

    public function offsetGet($key) {
        return $this->folder($this->value[$key] ?? null);
    }

    public function sort($sort = 1, $preserve_key = false) {
        if (is_array($sort)) {
            $value = [];
            if (isset($sort[1])) {
                foreach ($this->value as $v) {
                    $f = $this->folder($v);
                    $value[$v] = $f[$sort[1]] ?? $f->{$sort[1]} ?? $sort[2] ?? null;
                }
            }
            -1 === $sort[0] ? arsort($value) : asort($value);
            $value = array_keys($value);
        } else {
            $value = $this->value;
            if ($preserve_key) {
                -1 === $sort ? arsort($value) : asort($value);
            } else {
                -1 === $sort ? rsort($value) : sort($value);
            }
        }
        $this->value = $value;
        return $this;
    }

    public static function from(...$lot) {
        return new static(array_keys(y(g($lot[0] ?? ROOT, $lot[1] ?? 0, $lot[2] ?? 0))));
    }

}