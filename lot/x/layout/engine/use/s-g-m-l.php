<?php

class SGML extends Genome implements \ArrayAccess, \Countable, \JsonSerializable {

    protected $lot = [
        0 => null,
        1 => "",
        2 => []
    ];

    public $state = [
        0 => ['<', '>', '/'],
        1 => ['"', '"', '=']
    ];

    public $strict = true;

    public function __construct($value = [], array $state = []) {
        $this->state = $state = array_replace_recursive($this->state, $state);
        if (is_array($value)) {
            $this->lot = array_replace_recursive($this->lot, $value);
        } else if (is_object($value) && $value instanceof self) {
            $this->lot[0] = $value[0];
            $this->lot[1] = $value[1];
            $this->lot[2] = $value[2];
        } else if (is_string($value)) {
            // Must starts with `<` and ends with `>`
            if (0 === strpos($value, $state[0][0]) && $state[0][1] === substr($value, -strlen($state[0][1]))) {
                $tag = x(implode("", $state[0]));
                $tag_open = x($state[0][0]); // `<`
                $tag_close = x($state[0][1]); // `>`
                $tag_end = x($state[0][2]); // `/`
                $attr = x(implode("", $state[1]));
                $attr_open = x($state[1][2] . $state[1][0]);
                $attr_close = x($state[1][1]);
                if (preg_match('/' . $tag_open . '([^' . $tag . $attr . '\s]+)(\s[^' . $tag_close . ']*)?(?:' . $tag_close . '((?R)|[\s\S]*?)(?:' . $tag_open . $tag_end . '(\1)' . $tag_close . ')|(?:' . $tag_end . ')' . ($this->strict ? "" : '?') . $tag_close . ')/', n($value), $m)) {
                    $this->lot = [
                        0 => $m[1],
                        1 => isset($m[4]) ? $m[3] : false,
                        2 => []
                    ];
                    $this->strict = substr($value, -strlen($end = $state[0][2] . $state[0][1])) === $end;
                    if (isset($m[2]) && preg_match_all('/\s+([^' . $attr . '\s]+)(' . $attr_open . '((?:[^' . x($state[1][0] . $state[1][1]) . '\\\]+|\\\.)*)' . $attr_close . ')?/', $m[2], $mm)) {
                        if (!empty($mm[1])) {
                            foreach ($mm[1] as $k => $v) {
                                $this->lot[2][$v] = "" === $mm[2][$k] ? true : strtr($mm[3][$k], [
                                    "\\" . $state[1][0] => $state[1][0],
                                    "\\" . $state[1][1] => $state[1][1]
                                ]);
                            }
                        }
                    }
                } else {
                    throw new \ParseError(static::class . ' parsing error: ' . $value);
                }
            } else {
                throw new \ParseError(static::class . ' parsing error: ' . $value);
            }
        }
    }

    public function __toString() {
        $lot = $this->lot;
        $state = $this->state;
        if (!isset($lot[0]) || false === $lot[0]) {
            return $lot[1] ?? "";
        }
        $out = $state[0][0] . $lot[0];
        if (!empty($lot[2])) {
            ksort($lot[2]);
            foreach ($lot[2] as $k => $v) {
                if (true === $v) {
                    $out .=  ' ' . $k;
                    continue;
                }
                if (null === $v || false === $v) {
                    continue;
                }
                $v = strtr($v, [
                    $state[1][0] => "\\" . $state[1][0],
                    $state[1][1] => "\\" . $state[1][1]
                ]);
                $out .= ' ' . $k . $state[1][2] . $state[1][0] . $v . $state[1][1];
            }
        }
        $out .= (false === $lot[1] ? ($this->strict ? $state[0][2] : "") : $state[0][1] . $lot[1] . $state[0][0] . $state[0][2] . $lot[0]) . $state[0][1];
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