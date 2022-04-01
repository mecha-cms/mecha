<?php

class XML extends Genome implements \ArrayAccess, \Countable, \JsonSerializable {

    protected $lot = [
        0 => null,
        1 => null,
        2 => []
    ];

    public $strict = true;

    public function __construct($value = []) {
        if (is_array($value)) {
            $this->lot = array_replace_recursive($this->lot, $value);
        } else if (is_object($value) && $value instanceof self) {
            $this->lot[0] = $value[0];
            $this->lot[1] = $value[1];
            $this->lot[2] = $value[2];
        } else if (is_string($value)) {
            // Must starts with `<` and ends with `>`
            if (0 === strpos($value, '<') && '>' === substr($value, -1)) {
                if (preg_match('/<([^\s"\'\/<=>]+)(\s(?:"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'|[^\/>])*)?(?:>((?R)|[\s\S]*?)<\/(\1)>|\/' . ($this->strict ? "" : '?') . '>)/', n($value), $m)) {
                    $this->lot = [
                        0 => $m[1],
                        1 => isset($m[4]) ? $m[3] : false,
                        2 => []
                    ];
                    $this->strict = '/>' === substr($value, -2);
                    if (isset($m[2]) && preg_match_all('/\s+([^\s"\'=]+)(?:=("(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'|[^\s\/>]*))?/', $m[2], $mm)) {
                        if (!empty($mm[1])) {
                            foreach ($mm[1] as $k => $v) {
                                $vv = $mm[2][$k];
                                if (0 === strpos($vv, '"') && '"' === substr($vv, -1) || 0 === strpos($vv, "'") && "'" === substr($vv, -1)) {
                                    $this->lot[2][$v] = To::HTML(substr($vv, 1, -1));
                                } else {
                                    $this->lot[2][$v] = "" === $vv ? true : $vv;
                                }
                            }
                        }
                    }
                } else {
                    throw new \ParseError(static::class . ': ' . $value);
                }
            } else {
                throw new \ParseError(static::class . ': ' . $value);
            }
        }
    }

    public function __toString() {
        $lot = $this->lot;
        if (!isset($lot[0]) || false === $lot[0]) {
            return $lot[1] ?? null;
        }
        $out = '<' . $lot[0];
        if (!empty($lot[2])) {
            ksort($lot[2]);
            foreach ($lot[2] as $k => $v) {
                if (false === $v || null === $v) {
                    continue;
                }
                if (true === $v) {
                    $out .=  ' ' . $k;
                    continue;
                }
                $out .= ' ' . $k . '="' . From::HTML($v) . '"';
            }
        }
        return $out . (false === $lot[1] ? ($this->strict ? '/' : "") : '>' . $lot[1] . '</' . $lot[0]) . '>';
    }

    public function count(): int {
        return 1; // Single node is always `1`
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->lot;
    }

    public function offsetExists($key): bool {
        if (is_numeric($key)) {
            return isset($this->lot[$key]);
        }
        return isset($this->lot[2][$key]);
    }

    #[\ReturnTypeWillChange]
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
            unset($this->lot[$key]);
        } else {
            unset($this->lot[2][$key]);
        }
    }

}