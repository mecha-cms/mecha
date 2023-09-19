<?php

class XML extends Genome implements ArrayAccess, Countable, JsonSerializable {

    protected $c = [];
    protected $lot = [
        0 => null,
        1 => null,
        2 => []
    ];

    protected function deep($value) {
        if (is_object($value) && $value instanceof self) {
            return [[$value[0], $value[1], $value[2]]];
        }
        $out = [];
        if (is_array($value)) {
            foreach ($value as $v) {
                if (is_object($v) && $v instanceof self) {
                    $out[] = $v->__toString();
                    continue;
                }
                if (is_array($v)) {
                    $v = new static($v, $this->deep);
                    $out[] = $v->__toString();
                    continue;
                }
                $out[] = (string) $v;
            }
            return implode("", $out);
        }
        if (is_string($value)) {
            if (preg_match_all('/' . implode('|', [
                // Character data section
                '<\!\[CDATA\[[\s\S]*?\]\]>',
                // Comment
                '<\!--[\s\S]*?-->',
                // Document type
                '<\!(?>"[^"]*"|\'[^\']*\'|[^>])*>',
                // Processing instruction
                '<\?(?>"[^"]*"|\'[^\']*\'|[^>?])*\?>',
                // Element
                '<([^\s"\'\/<=>]+)(?>\s(?>"[^"]*"|\'[^\']*\'|[^\/>])*)?(?>>(?>(?R)|[\s\S])*?<\/\1>|\/' . ($this->strict ? "" : '?') . '>)',
                // Text
                '[^<>]+',
                // Remaining `<` and `>` character(s) to escape
                '[<>]'
            ]) . '/', $value, $m)) {
                foreach ($m[0] as $v) {
                    // Must starts with `<` and ends with `>`
                    if (0 === strpos($v, '<') && '>' === substr($v, -1)) {
                        // Maybe a document type or a character data section or a comment or a processing instruction
                        if (isset($v[1]) && false !== strpos('!?', $v[1])) {
                            $out[] = [null, $v, []];
                            continue;
                        }
                        // Must be an element
                        if (!empty($this->c[substr($v, strrpos($v, '/') + 1, -1)])) {
                            $v = new static($v, false);
                            $out[] = [$v[0], $v[1], $v[2]];
                            continue;
                        }
                        $v = new static($v, $this->deep);
                        $out[] = [$v[0], $v[1], $v[2]];
                        continue;
                    }
                    // Plain text
                    $out[] = [
                        '&' => '&amp;',
                        '<' => '&lt;',
                        '>' => '&gt;'
                    ][$v] ?? $v;
                }
            }
        }
        // Return plain text if it is the only item in array
        if (1 === count($out) && is_string($v = reset($out))) {
            return $v;
        }
        // Else, return the array
        return $out;
    }

    public $deep = false;
    public $strict = true;

    public function __construct($value = [], $deep = false) {
        $this->deep = $deep;
        if (is_array($value)) {
            $this->lot = array_replace_recursive($this->lot, $value);
        } else if (is_object($value) && $value instanceof self) {
            $this->lot[0] = $value[0];
            $this->lot[1] = $value[1];
            $this->lot[2] = $value[2];
        } else if (is_string($value)) {
            // Must starts with `<` and ends with `>`
            if (0 === strpos($value, '<') && '>' === substr($value, -1)) {
                if (preg_match('/^<([^\s"\'\/<=>]+)(\s(?>"[^"]*"|\'[^\']*\'|[^\/>])*)?(?:>([\s\S]*?)<\/(\1)>|\/' . ($this->strict ? "" : '?') . '>)$/', n($value), $m)) {
                    $this->lot = [
                        0 => $m[1],
                        1 => isset($m[4]) ? ($deep ? $this->deep($m[3]) : $m[3]) : false,
                        2 => []
                    ];
                    $this->strict = '/>' === substr($value, -2);
                    if (isset($m[2]) && preg_match_all('/\s+([^\s"\'\/<=>]+)(?>=("[^"]*"|\'[^\']*\'|[^\s\/>]*))?/', $m[2], $mm)) {
                        if (!empty($mm[1])) {
                            foreach ($mm[1] as $i => $k) {
                                $v = $mm[2][$i];
                                $v = htmlspecialchars_decode(0 === strpos($v, '"') && '"' === substr($v, -1) || 0 === strpos($v, "'") && "'" === substr($v, -1) ? substr($v, 1, -1) : $v, ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE);
                                $this->lot[2][$k] = $this->strict && $v === $k || isset($mm[0][$i]) && false === strpos($mm[0][$i], '=') ? true : $v;
                            }
                        }
                    }
                } else if (defined('TEST') && TEST) {
                    throw new ParseError(static::class . ': ' . $value);
                }
            } else if (defined('TEST') && TEST) {
                throw new ParseError(static::class . ': ' . $value);
            }
        }
    }

    public function __toString(): string {
        $lot = $this->lot;
        if (!isset($lot[0]) || false === $lot[0]) {
            return $this->deep && (is_array($lot[1]) || is_object($lot[1])) ? $this->deep($lot[1]) : s($lot[1]);
        }
        $out = '<' . $lot[0];
        if (!empty($lot[2])) {
            ksort($lot[2]);
            foreach ($lot[2] as $k => $v) {
                if (false === $v || null === $v) {
                    continue;
                }
                if (true === $v) {
                    $out .=  ' ' . $k . ($this->strict ? '="' . $k . '"' : "");
                    continue;
                }
                $out .= ' ' . $k . '="' . htmlspecialchars(is_array($v) || is_object($v) ? json_encode($v) : s($v), ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) . '"';
            }
        }
        return $out . (false === $lot[1] ? ($this->strict ? '/' : "") : '>' . ($this->deep && (is_array($lot[1]) || is_object($lot[1])) ? $this->deep($lot[1]) : s($lot[1])) . '</' . $lot[0]) . '>';
    }

    public function count(): int {
        return 1; // Single node is always `1`
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize() {
        $lot = $this->lot;
        $lot[2] = (object) ($lot[2] ?? []);
        return $lot;
    }

    public function offsetExists($key): bool {
        if (is_numeric($key)) {
            return isset($this->lot[$key]);
        }
        return isset($this->lot[2][$key]);
    }

    #[ReturnTypeWillChange]
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