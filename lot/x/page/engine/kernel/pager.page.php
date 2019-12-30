<?php namespace Pager;

class Page extends \Pager {

    public function __construct(array $data = [], string $current = null, string $parent = null) {
        $url = $GLOBALS['url'];
        $data = \array_values($data);
        $count = \count($data);
        $parent = \rtrim($parent, '/');
        if (false !== ($i = \array_search($current, $data, true))) {
            $this->next = $i + 1 < $count ? $parent . '/' . $data[$i + 1] : null;
            $this->prev = $i - 1 > -1 ? $parent . '/' . $data[$i - 1] : null;
        }
        $this->parent = "" !== $parent && $parent !== $url . "" ? $parent : null;
    }

}