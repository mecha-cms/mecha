<?php namespace Pager;

class Pages extends \Pager {

    public function __construct(array $data = [], $chunk = [5, 0], string $parent = null) {
        parent::__construct();
        $data = \array_chunk($data, $chunk[0] ?? 5);
        $i = $chunk[1] ?? 0;
        $top = \is_file($parent) ? $this->page($parent)->url : null;
        if (isset($data[$i + 1])) {
            $next = $this->page();
            $next->link = $top . '/' . ($i + 2);
            $this->next = $next;
        }
        if (isset($data[$i - 1])) {
            $prev = $this->page();
            $prev->link = $top . '/' . $i;
            $this->prev = $prev;
        }
        $base = $this->base . "";
        $p = \trim($GLOBALS['state']->path ?? 'index', '/');
        if ($i > 0 || $top && $top !== $base . '/' . $p) {
            $parent = $this->page();
            $parent->link = $i > 0 ? $top : \dirname($top);
            $this->parent = $parent;
        }
    }

    public function page(string $path = null) {
        return new \Page($path);
    }

}
