<?php

class HTML extends XML {

    protected $raw = [
        'script' => 1,
        'style' => 1,
        'textarea' => 1
    ];

    // <https://www.w3.org/TR/2011/WD-html-markup-20110113/syntax.html#void-element>
    protected $void = [
        'area' => 1,
        'base' => 1,
        'br' => 1,
        'col' => 1,
        'command' => 1,
        'embed' => 1,
        'hr' => 1,
        'img' => 1,
        'input' => 1,
        'keygen' => 1,
        'link' => 1,
        'meta' => 1,
        'param' => 1,
        'source' => 1,
        'track' => 1,
        'wbr' => 1
    ];

    public function __construct($value = [], $deep = false, $strict = null) {
        parent::__construct($value, $deep, $strict ?? (is_string($value) && '/>' === substr($value, -2)));
    }

}