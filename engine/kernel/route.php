<?php

class Route extends Genome {

    private $lot = [
        'hash' => null,
        'path' => null,
        'query' => null
    ];

    private $r;

    protected function _hash($value) {
        $this->lot['hash'] = is_string($value) && "" !== ($value = trim($value, '#')) ? $value : null;
    }

    protected function _path($value) {
        if (is_array($value) && $value) {
            // …
        } else if (is_string($value) && "" !== ($value = trim($value, '/'))) {
            $value = explode('/', $value);
        }
        foreach ($value as &$v) {
            $v = strtr($v, [
                "\\" => '%5C',
                '#' => '%23',
                '&' => '%26',
                '/' => '%2F',
                '?' => '%3F'
            ]);
        }
        unset($v);
        $path = array_replace($this->lot['path'] ?? [], $value);
        $this->lot['path'] = $path ?: null;
    }

    protected function _query($value) {
        if (is_array($value) && $value) {
            // …
        } else if (is_string($value) && "" !== ($value = trim($value, '&?'))) {
            $value = From::query($value);
        }
        $query = array_replace_recursive($this->lot['query'] ?? [], $value);
        $this->lot['query'] = $query ?: null;
    }

    public function __construct(?string $r = null, array $lot = []) {
        $this->lot = array_replace_recursive($this->lot, $lot);
        $this->r = (string) ($r ?? "");
    }

    public function __toString(): string {
        return (string) ($this->r . $this->query . $this->hash);
    }

    public function hash() {
        if (is_string($hash = $this->lot['hash'])) {
            return '#' . $hash;
        }
        return null;
    }

    public function path(array $lot = []) {
        if ("" !== ($path = implode('/', array_replace($this->lot['path'] ?? [], $lot)))) {
            return '/' . $path;
        }
        return null;
    }

    public function query(array $lot = []) {
        if ($query = To::query(array_replace_recursive($this->lot['query'] ?? [], $lot))) {
            return $query;
        }
        return null;
    }

}