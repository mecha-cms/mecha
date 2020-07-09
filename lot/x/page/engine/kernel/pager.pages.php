<?php namespace Pager;

class Pages extends \Pager {

    protected function link(string $base = null, int $i = null) {
        return $base . (null !== $i ? '/' . $i : "");
    }

    public function __construct(array $data = [], $chunk = [5, 0], string $parent = null) {
        parent::__construct();
        $data = \array_chunk($data, $chunk[0] ?? 5);
        $i = $chunk[1] ?? 0;
        $url = \is_file($parent) ? $this->page($parent)->url : null;
        if (isset($data[$i + 1])) {
            $next = $this->page();
            $next->link = $this->link($url, $i + 2);
            $this->next = $next;
        }
        if (isset($data[$i - 1])) {
            $prev = $this->page();
            $prev->link = $this->link($url, $i);
            $this->prev = $prev;
        }
        $base = (string) $this->base;
        $p = \trim(\State::get('path') ?? 'index', '/');
        if ($i > 0 || $url && $url !== $base . '/' . $p) {
            $parent = $this->page();
            $parent->link = $this->link($i > 0 ? $url : \dirname($url));
            $this->parent = $parent;
        }
    }

    public function page(string $path = null) {
        return new \Page($path);
    }

}
