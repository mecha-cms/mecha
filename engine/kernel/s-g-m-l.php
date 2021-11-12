<?php

class SGML extends Genome implements \ArrayAccess, \Countable, \JsonSerializable {

    const state = [
        0 => ['<', '>', '/'],
        1 => ['"', '"', '=']
    ];

    protected $lot = [
        0 => null,
        1 => "",
        2 => []
    ];

    public $c;
    public $strict = true;

    public static $state = self::state;

    public function __construct($lot = []) {
        $this->c = $c = array_replace_recursive(self::state, static::$state);
        if (is_array($lot)) {
            $this->lot = array_replace_recursive($this->lot, $lot);
        } else if (is_object($lot) && $lot instanceof self) {
            $this->lot[0] = $lot[0];
            $this->lot[1] = $lot[1];
            $this->lot[2] = $lot[2];
        } else if (is_string($lot)) {
            // Must starts with `<` and ends with `>`
            if (0 === strpos($lot, $c[0][0]) && $c[0][1] === substr($lot, -strlen($c[0][1]))) {
                $tag = x(implode("", $c[0]));
                $tag_open = x($c[0][0]); // `<`
                $tag_close = x($c[0][1]); // `>`
                $tag_end = x($c[0][2]); // `/`
                $attr = x(implode("", $c[1]));
                $attr_open = x($c[1][2] . $c[1][0]);
                $attr_close = x($c[1][1]);
                if (preg_match('/' . $tag_open . '([^' . $tag . $attr . '\s]+)(\s[^' . $tag_close . ']*)?(?:' . $tag_close . '((?R)|[\s\S]*?)(?:' . $tag_open . $tag_end . '(\1)' . $tag_close . ')|(?:' . $tag_end . ')' . ($this->strict ? "" : '?') . $tag_close . ')/', n($lot), $m)) {
                    $this->lot = [
                        0 => $m[1],
                        1 => isset($m[4]) ? $m[3] : false,
                        2 => []
                    ];
                    $this->strict = substr($lot, -strlen($end = $c[0][2] . $c[0][1])) === $end;
                    if (isset($m[2]) && preg_match_all('/\s+([^' . $attr . '\s]+)(' . $attr_open . '((?:[^' . x($c[1][0] . $c[1][1]) . '\\\]+|\\\.)*)' . $attr_close . ')?/', $m[2], $mm)) {
                        if (!empty($mm[1])) {
                            foreach ($mm[1] as $k => $v) {
                                $this->lot[2][$v] = "" === $mm[2][$k] ? true : strtr($mm[3][$k], [
                                    "\\" . $c[1][0] => $c[1][0],
                                    "\\" . $c[1][1] => $c[1][1]
                                ]);
                            }
                        }
                    }
                } else {
                    throw new \ParseError(static::class . ' parsing error: ' . $lot);
                }
            } else {
                throw new \ParseError(static::class . ' parsing error: ' . $lot);
            }
        }
    }

    public function __toString() {
        $c = $this->c;
        $o = $this->lot;
        if (!isset($o[0]) || false === $o[0]) {
            return $o[1] ?? "";
        }
        $out = $c[0][0] . $o[0];
        if (!empty($o[2])) {
            ksort($o[2]);
            foreach ($o[2] as $k => $v) {
                if (true === $v) {
                    $out .=  ' ' . $k;
                    continue;
                }
                if (null === $v || false === $v) {
                    continue;
                }
                $v = strtr($v, [
                    $c[1][0] => "\\" . $c[1][0],
                    $c[1][1] => "\\" . $c[1][1]
                ]);
                $out .= ' ' . $k . $c[1][2] . $c[1][0] . $v . $c[1][1];
            }
        }
        $out .= (false === $o[1] ? ($this->strict ? $c[0][2] : "") : $c[0][1] . $o[1] . $c[0][0] . $c[0][2] . $o[0]) . $c[0][1];
        return $out;
    }

    public function count() {
        return 1; // Single node is always `1`
    }

    public function jsonSerialize() {
        return $this->lot;
    }

    public function offsetExists($key) {
        if (is_numeric($key)) {
            return isset($this->lot[$key]);
        }
        return isset($this->lot[2][$key]);
    }

    public function offsetGet($key) {
        if (is_numeric($key)) {
            return $this->lot[$key] ?? null;
        }
        return $this->lot[2][$key] ?? null;
    }

    public function offsetSet($key, $value) {
        if (isset($key)) {
            if (is_numeric($key)) {
                $this->lot[$key] = $value;
            } else {
                $this->lot[2][$key] = $value;
            }
        } else {
            $this->lot[] = $value;
        }
    }

    public function offsetUnset($key) {
        if (is_numeric($key)) {
            unset($this->lot[$key]);
        } else {
            unset($this->lot[2][$key]);
        }
    }

}