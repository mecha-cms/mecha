<?php namespace Pager;

class Page extends \Pager {

    public function __construct(array $value = [], $current = null, $parent = null) {
        parent::__construct();
        $value = \array_values($value);
        $count = \count($value);
        if (false !== ($i = \array_search($current, $value, true))) {
            if ($i + 1 < $count) {
                if (\is_object($value[$i + 1])) {
                    $this->next = $value[$i + 1];
                } else if (\is_file($value[$i + 1])) {
                    $this->next = $this->page($value[$i + 1]);
                }
            }
            if ($i - 1 > -1) {
                if (\is_object($value[$i - 1])) {
                    $this->prev = $value[$i - 1];
                } else if (\is_file($value[$i - 1])) {
                    $this->prev = $this->page($value[$i - 1]);
                }
            }
        }
        if (\is_object($parent)) {
            $this->parent = $parent;
        } else if (\is_file($parent)) {
            $this->parent = $this->page($parent);
        }
    }

    public function page(string $path = null) {
        return new \Page($path);
    }

}