<?php namespace Pager;

class Pages extends \Pager {

    public function __construct(array $value = [], $chunk = [5, 0], $parent = null) {
        parent::__construct();
        if ($parent) {
            if (\is_object($parent)) {
                $this->parent = $parent;
            } else if (\is_file($parent)) {
                $this->parent = $parent = $this->page($parent);
            }
        }
        $value = \array_chunk($value, $chunk[0] ?? 5);
        $i = $chunk[1] ?? 0;
        if (isset($value[$i + 1])) {
            if (\is_object($value[$i + 1])) {
                $this->next = $value[$i + 1];
            } else {
                $this->next = $this->to($parent, $i + 2);
            }
        }
        if (isset($value[$i - 1])) {
            if (\is_object($value[$i - 1])) {
                $this->prev = $value[$i - 1];
            } else {
                $this->prev = $this->to($parent, $i);
            }
        }
    }

    public function page(string $path = null) {
        return new \Page($path);
    }

    public function to(...$lot) {
        if (isset($lot[0]) && \is_object($lot[0])) {
            $p = clone $lot[0];
            $p->link = ($lot[0]->link ?? $lot[0]->url ?? "") . (isset($lot[1]) ? '/' . $lot[1] : "");
            return $p;
        }
        return $this->page();
    }

}