<?php

abstract class Pager extends Genome {

    const next = '&#x25B6;';
    const parent = '&#x25C6;';
    const prev = '&#x25C0;';

    public $next;
    public $parent;
    public $prev;

    public function __toString() {
        return $this->prev(self::prev) . ' ' . $this->parent(self::parent) . ' ' . $this->next(self::next);
    }

    public function next(string $text = null) {
        $url = $GLOBALS['url'];
        $next = isset($this->next) ? $this->next . $url->query . $url->hash : null;
        if (isset($text)) {
            return null !== $next ? '<a href="' . strtr($next, ['&' => '&amp;']) . '" rel="next">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $next;
    }

    public function parent(string $text = null) {
        $url = $GLOBALS['url'];
        $parent = isset($this->parent) ? $this->parent . $url->query . $url->hash : null;
        if (isset($text)) {
            return null !== $parent ? '<a href="' . strtr($parent, ['&' => '&amp;']) . '">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $parent;
    }

    public function prev(string $text = null) {
        $url = $GLOBALS['url'];
        $prev = isset($this->prev) ? $this->prev . $url->query . $url->hash : null;
        if (isset($text)) {
            return null !== $prev ? '<a href="' . strtr($prev, ['&' => '&amp;']) . '" rel="prev">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $prev;
    }

}