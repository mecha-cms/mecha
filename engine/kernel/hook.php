<?php

class Hook extends Genome {

    protected static $lot;
    protected static $current;

    public static function fire($id, array $lot = [], $that = null, string $scope = null) {
        $c = static::class;
        if (is_array($id)) {
            foreach ($id as $v) {
                if (null !== ($r = self::fire($v, $lot, $that, $scope))) {
                    $lot[0] = $r;
                }
            }
        } else {
            self::$current[$c] = $id;
            if (!isset(self::$lot[1][$c][$id])) {
                self::$lot[1][$c][$id] = [];
                return $lot[0] ?? null;
            }
            $any = (new Anemon(self::$lot[1][$c][$id]))->sort([1, 'stack']);
            foreach ($any as $v) {
                if (null !== ($r = fire($v['fn'], $lot, $that, $scope))) {
                    $lot[0] = $r;
                }
            }
        }
        return $lot[0] ?? null;
    }

    public static function get(string $id = null) {
        $c = static::class;
        if (isset($id)) {
            return self::$lot[1][$c][$id] ?? null;
        }
        return self::$lot[1][$c] ?? [];
    }

    public static function is(string $id = null) {
        $c = static::class;
        return isset($id) ? self::$current[$c] === $id : self::$current[$c];
    }

    public static function let($id = null, $name = null) {
        $c = static::class;
        if (is_array($id)) {
            foreach ($id as $v) {
                self::let($v, $name);
            }
        } else if (isset($id)) {
            if (isset(self::$lot[1][$c][$id])) {
                if (isset($name)) {
                    self::$lot[0][$c][$id] = [];
                    foreach (self::$lot[1][$c][$id] as $k => $v) {
                        if ($v['fn'] === $name) {
                            if (is_string($name)) {
                                self::$lot[0][$c][$id][$name] = $v;
                            }
                            unset(self::$lot[1][$c][$id][$k]);
                        }
                    }
                } else {
                    self::$lot[0][$c][$id] = 1;
                    unset(self::$lot[1][$c][$id]);
                }
            } else {
                if (isset($name)) {
                    if (is_string($name)) {
                        self::$lot[0][$c][$id][$name] = 1;
                    }
                } else {
                    self::$lot[0][$c][$id] = 1;
                }
            }
        } else {
            self::$lot[1][$c] = [];
        }
    }

    public static function set($id = null, callable $fn, float $stack = 10) {
        $c = static::class;
        if (is_array($id)) {
            foreach ($id as $v) {
                self::set((string) $v, $fn, $stack);
            }
        } else {
            if (is_string($fn) && !empty(self::$lot[0][$c][$id][$fn])) {
                // Skip!
            } else {
                if (!isset(self::$lot[1][$c][$id])) {
                    self::$lot[1][$c][$id] = [];
                }
                self::$lot[1][$c][$id][] = [
                    '0' => null,
                    '1' => "",
                    '2' => [],
                    'fn' => $fn,
                    'stack' => (float) $stack
                ];
            }
        }
    }

}
