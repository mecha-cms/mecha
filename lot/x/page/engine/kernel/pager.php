<?php

abstract class Pager extends Genome {

    const next = '&#x25B6;';
    const parent = '&#x25C6;';
    const prev = '&#x25C0;';

    public $base;

    public $next;
    public $parent;
    public $prev;

    public function __construct() {
        $this->base = $GLOBALS['url'];
    }

    public function __toString() {
        return $this->prev(self::prev) . ' ' . $this->parent(self::parent) . ' ' . $this->next(self::next);
    }

    public function next(string $text = null) {
        $url = $this->base;
        $next = isset($this->next) ? ($this->next->link ?? $this->next->url) . $url->query . $url->hash : null;
        if (isset($text)) {
            return null !== $next ? '<a href="' . strtr($next, ['&' => '&amp;']) . '" rel="next">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $next;
    }

    public function parent(string $text = null) {
        $url = $this->base;
        $parent = isset($this->parent) ? ($this->parent->link ?? $this->parent->url) . $url->query . $url->hash : null;
        if (isset($text)) {
            return null !== $parent ? '<a href="' . strtr($parent, ['&' => '&amp;']) . '">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $parent;
    }

    public function prev(string $text = null) {
        $url = $this->base;
        $prev = isset($this->prev) ? ($this->prev->link ?? $this->prev->url) . $url->query . $url->hash : null;
        if (isset($text)) {
            return null !== $prev ? '<a href="' . strtr($prev, ['&' => '&amp;']) . '" rel="prev">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $prev;
    }

}
