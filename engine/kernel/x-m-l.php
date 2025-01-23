<?php

class XML extends Genome {

    protected $lot = [
        0 => null,
        1 => null,
        2 => []
    ];

    protected $c = [];
    protected $void = [];

    protected function apart(string $value, $deep = false) {
        $i = -1;
        $lot = [];
        $stack = 0;
        $void = array_keys(array_filter($this->void));
        foreach (apart($value, $void) as $v) {
            if ($stack > 0) {
                if (2 === $v[1]) {
                    $stack += '/' === $v[0][1] ? -1 : 1;
                }
                $lot[$i][0] .= $v[0];
                continue;
            }
            if (2 === $v[1]) {
                $stack += '/' === $v[0][1] ? -1 : 1;
            }
            $lot[++$i] = [$v[0], $v[1], strlen($v[0])];
        }
        foreach ($lot as &$v) {
            // <https://www.w3.org/TR/xml#sec-starttags>
            if (1 === $v[1] && false === strpos('!?', $v[0][1]) || 2 === $v[1]) {
                $n = strtok(substr($start = substr($v[0], 0, $v[2]), 1, -1), " \n\r\t/");
                $r = substr($v[0], $v[2]);
                if (2 === $v[1] && '</' . $n . '>' === substr($r, -(strlen($n) + 3))) {
                    $r = substr($r, 0, -(strlen($n) + 3));
                    $v = [$n, $deep ? (2 === $v[1] ? $this->apart($r, empty($this->c[$n]) ? $deep : false) : false) : $r, $this->pair(trim(substr($start, strlen($n) + 1, -1), '/'))];
                    continue;
                }
                $v = [$n, false, $this->pair(trim(substr($start, strlen($n) + 1, -1), '/'))];
                continue;
            }
            // <https://www.w3.org/TR/xml#sec-cdata-sect>
            // <https://www.w3.org/TR/xml#sec-comments>
            // <https://www.w3.org/TR/xml#sec-prolog-dtd>
            if (1 === $v[1] && false !== strpos('!?', $v[0][1])) {
                $v = [null, $v[0], []];
                continue;
            }
            // <https://www.w3.org/TR/xml#charsets>
            // <https://www.w3.org/TR/xml#sec-references>
            $v = $v[0];
        }
        unset($v);
        return $lot;
    }

    protected function pair(string $value) {
        $lot = [];
        foreach (pair($value) as $k => $v) {
            if ($v && is_string($v)) {
                $lot[$k] = htmlspecialchars_decode($v, ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE);
                continue;
            }
            $lot[$k] = $v;
        }
        return $lot;
    }

    public $deep;
    public $strict;

    public function __construct($value = [], $deep = false, $strict = true) {
        $this->deep = $deep;
        $this->strict = $strict;
        if (is_array($value)) {
            $this->lot = array_replace_recursive($this->lot, $value);
        } else if (is_object($value) && $value instanceof self) {
            $this->lot[0] = $value[0];
            $this->lot[1] = $value[1];
            $this->lot[2] = $value[2];
        } else if (is_string($value)) {
            // Must start with `<` and end with `>`
            if ('<' === substr($value = trim($value), 0, 1) && '>' === substr($value, -1)) {
                // Must be an element
                if (1 === count($apart = $this->apart($value, $deep))) {
                    $this->lot = reset($apart);
                } else if (defined('TEST') && TEST) {
                    throw new LengthException($value);
                }
            } else if (defined('TEST') && TEST) {
                throw new ParseError($value);
            }
        }
    }

    public function __serialize(): array {
        $lot = parent::__serialize();
        unset($lot['c'], $lot['void']);
        return $lot;
    }

    public function __toString(): string {
        $deep = $this->deep;
        $lot = $this->lot;
        $strict = $this->strict;
        if (!isset($lot[0]) || false === $lot[0]) {
            $value = "";
            if ($deep && is_array($lot[1])) {
                foreach ($lot[1] as $v) {
                    $value .= is_string($v) ? $v : new static($v, $deep, $strict);
                }
                return $value;
            }
            return is_array($lot[1]) || is_object($lot[1]) ? json_encode($lot[1]) : s($lot[1]);
        }
        $value = '<' . $lot[0];
        if (!empty($lot[2])) {
            ksort($lot[2]);
            foreach ($lot[2] as $k => $v) {
                if (false === $v || null === $v) {
                    continue;
                }
                if (true === $v) {
                    $value .= ' ' . $k . ($strict ? '="' . $k . '"' : "");
                    continue;
                }
                $value .= ' ' . $k . '="' . htmlspecialchars(is_array($v) || is_object($v) ? json_encode($v) : s($v), ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) . '"';
            }
        }
        if (false === $lot[1]) {
            return $value . ($strict ? '/' : "") . '>';
        }
        $value .= '>';
        if ($deep && is_array($lot[1])) {
            foreach ($lot[1] as $v) {
                if (is_array($v) || is_object($v)) {
                    $value .= new static($v, $deep, $strict);
                    continue;
                }
                $value .= $v;
            }
        } else {
            $value .= s($lot[1]);
        }
        return $value . '</' . $lot[0] . '>';
    }

    public function count(): int {
        return 1; // Single node is always `1`
    }

    public function jsonSerialize() {
        $lot = $this->__serialize()['lot'] ?? [];
        if (isset($lot[2])) {
            $lot[2] = (object) $lot[2];
        }
        return $lot;
    }

    public function offsetExists($key): bool {
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

    public function offsetSet($key, $value): void {
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

    public function offsetUnset($key): void {
        if (is_numeric($key)) {
            $this->lot[$key] = 2 === $key ? [] : null;
        } else {
            unset($this->lot[2][$key]);
        }
    }

}