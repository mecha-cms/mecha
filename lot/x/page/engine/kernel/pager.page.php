<?php namespace Pager;

class Page extends \Pager {

    protected function page(string $path = null) {
        return new \Page($path);
    }

    public function __construct(array $data = [], $current = null, $parent = null) {
        parent::__construct();
        $data = \array_values($data);
        $count = \count($data);
        if (false !== ($i = \array_search($current, $data, true))) {
            if ($i + 1 < $count) {
                if (\is_object($data[$i + 1])) {
                    $this->next = $data[$i + 1];
                } else if (\is_file($data[$i + 1])) {
                    $this->next = $this->page($data[$i + 1]);
                }
            }
            if ($i - 1 > -1) {
                if (\is_object($data[$i - 1])) {
                    $this->prev = $data[$i - 1];
                } else if (\is_file($data[$i - 1])) {
                    $this->prev = $this->page($data[$i - 1]);
                }
            }
        }
        if (\is_object($parent)) {
            $this->parent = $parent;
        } else if (\is_file($parent)) {
            $this->parent = $this->page($parent);
        }
    }

}
