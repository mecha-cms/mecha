<?php

class Elevator extends Genome {

    const HUB = '&#x25C6;';
    const NORTH = '&#x25B2;';
    const SOUTH = '&#x25BC;';
    const WEST = '&#x25C0;';
    const EAST = '&#x25B6;';

    public $config = [];

    protected $bucket = [];
    protected $NS = "";

    public function __construct($input, $chunk = 5, $index = 0, $path = true, $config = [], $NS = "") {
        $key = __c2f__(static::class, '_') . ($NS ? '.' . $NS : "");
        $c = [
            // -1: previous
            //  0: parent
            //  1: next
            'direction' => [
               '-1' => 'up',
                '0' => "",
                '1' => 'down'
            ],
            'union' => [
               '-2' => [ // not active
                    0 => 'span',
                    2 => ['href' => null]
                ],
               '-1' => [
                    0 => 'a',
                    1 => self::NORTH
                ],
                '0' => [
                    0 => 'a',
                    1 => self::HUB
                ],
                '1' => [
                    0 => 'a',
                    1 => self::SOUTH
                ]
            ],
            'input' => $input,
            'chunk' => $chunk,
            'index' => $index,
            'path' => $path
        ];
        $cc = Anemon::extend($c, $config);
        $this->config = Hook::NS($key, [$cc, $c]);
        $d = $this->config['direction'];
        global $url;
        if (isset($chunk)) {
            $input = Anemon::eat($input)->chunk($chunk);
        }
        if ($path === true) {
            $path = $url->current;
        }
        $path = rtrim($path, '/');
        $parent = Path::D($path);
        if (!isset($chunk)) {
            $i = array_search($index, $input);
            $i = isset($i) ? $i : 0;
            if ($d['-1'] !== false) $this->bucket[$d['-1']] = isset($input[$i - 1]) ? $path . '/' . $input[$i - 1] : null;
            if ($d['0'] !== false) $this->bucket[$d['0']] = $url->path !== "" ? ($path !== $url->current ? $path : $parent) : null;
            if ($d['1'] !== false) $this->bucket[$d['1']] = isset($input[$i + 1]) ? $path . '/' . $input[$i + 1] : null;
        } else {
            if ($d['-1'] !== false) $this->bucket[$d['-1']] = isset($input[$index - 1]) ? $path . '/' . $index : null;
            if ($d['0'] !== false) $this->bucket[$d['0']] = $url->path !== "" ? ($path !== $url->current ? $path : $parent) : null;
            if ($d['1'] !== false) $this->bucket[$d['1']] = isset($input[$index + 1]) ? $path . '/' . ($index + 2) : null;
        }
        $this->NS = $NS;
        $this->bucket = Hook::NS($key . '.links', [$this->bucket, $cc, $c]);
        parent::__construct();
    }

    protected function _unite($input, $alt = ['span']) {
        if (!$alt || !$input) return "";
        $input = array_replace_recursive($alt, $input);
        return call_user_func_array('HTML::unite', $input);
    }

    public function __get($key) {
        return array_key_exists($key, $this->bucket) ? $this->bucket[$key] : null;
    }

    public function __call($kin, $lot) {
        $text = array_shift($lot);
        $u = $this->config['union'];
        $d = array_search($kin, $this->config['direction']);
        if ($d !== false && ($text || $text === "")) {
            if ($text !== true) $u[$d][1] = $text;
            return isset($this->bucket[$kin]) ? $this->_unite(array_replace_recursive($u[$d], [2 => ['href' => $this->bucket[$kin]]])) : $this->_unite($u['-2'], $u[$d]);
        }
        return isset($this->bucket[$kin]) ? $this->bucket[$kin] : $text;
    }

    public function __toString() {
        global $language;
        $c = $this->config;
        $d = $c['direction'];
        $u = $c['union'];
        $html = [];
        if ($d['-1'] !== false) $html[] = $this->{$d['-1']}(true);
        if ($d['0'] !== false) $html[] = $this->{$d['0']}(true);
        if ($d['1'] !== false) $html[] = $this->{$d['1']}(true);
        return Hook::NS(__c2f__(static::class, '_') . '.' . $this->NS . '.unit', [implode(' ', $html), $language, $this->bucket]);
    }

}