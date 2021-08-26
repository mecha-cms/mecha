<?php

class Pages extends Files {

    // Inherit to `Files::file()`
    public function file(string $path): \ArrayAccess {
        return $this->page($path);
    }

    public function page(string $path) {
        return new Page($path);
    }

    // Inherit to `Files::from()`
    public static function from(...$lot) {
        $pages = [];
        foreach (g($lot[0] ?? LOT . DS . 'page', $lot[1] ?? 'page', $lot[2] ?? 0) as $k => $v) {
            if ("" === pathinfo($k, PATHINFO_FILENAME)) {
                continue; // Ignore placeholder page(s)
            }
            $pages[] = $k;
        }
        return new static($pages);
    }

}