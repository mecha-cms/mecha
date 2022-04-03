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
        if ($next = $this->next->link ?? $this->next->url ?? null) {
            $next .= $url->query . $url->hash;
        }
        if (isset($text)) {
            return null !== $next ? '<a href="' . htmlspecialchars($next) . '" rel="next">' . $text . '</a>' : '<a aria-disabled="true" rel="next">' . $text . '</a>';
        }
        return $next;
    }

    public function parent(string $text = null) {
        $url = $this->base;
        if ($parent = $this->parent->link ?? $this->parent->url ?? null) {
            $parent .= $url->query . $url->hash;
        }
        if (isset($text)) {
            return null !== $parent ? '<a href="' . htmlspecialchars($parent) . '">' . $text . '</a>' : '<a aria-disabled="true">' . $text . '</a>';
        }
        return $parent;
    }

    public function prev(string $text = null) {
        $url = $this->base;
        if ($prev = $this->prev->link ?? $this->prev->url ?? null) {
            $prev .= $url->query . $url->hash;
        }
        if (isset($text)) {
            return null !== $prev ? '<a href="' . htmlspecialchars($prev) . '" rel="prev">' . $text . '</a>' : '<a aria-disabled="true" rel="prev">' . $text . '</a>';
        }
        return $prev;
    }

}