<?php namespace Pager;

class Page extends \Pager {

    public function __construct(array $data = [], string $current = null, string $parent = null) {
        parent::__construct();
        $data = \array_values($data);
        $count = \count($data);
        if (false !== ($i = \array_search($current, $data, true))) {
            $this->next = $i + 1 < $count && \is_file($data[$i + 1]) ? $this->page($data[$i + 1]) : null;
            $this->prev = $i - 1 > -1 && \is_file($data[$i - 1]) ? $this->page($data[$i - 1]) : null;
        }
        $p = \trim(\State::get('path') ?? 'index', '/');
        $index = \File::exist([
            \LOT . \DS . 'page' . \DS . $p . '.archive',
            \LOT . \DS . 'page' . \DS . $p . '.page'
        ]);
        $this->parent = $parent && $parent !== $index && \is_file($parent) ? $this->page($parent) : null;
    }

    public function page(string $path = null) {
        return new \Page($path);
    }

}
