<?php

class Hook extends Genome {

    private function __construct() {}

    protected static $current;
    protected static $lot;

    public static function fire($name, array $lot = [], $that = null, $scope = null) {
        $c = static::class;
        if (is_array($name)) {
            foreach ($name as $v) {
                if (null !== ($r = self::fire($v, $lot, $that, $scope))) {
                    $lot[0] = $r;
                }
            }
        } else {
            self::$current[$c] = $name;
            if (!isset(self::$lot[1][$c][$name])) {
                self::$lot[1][$c][$name] = [];
                return $lot[0] ?? null;
            }
            if (null !== $that) {
                foreach (self::$lot[1][$c][$name] as $v) {
                    if (null !== ($r = fire($v['task'], $lot, $that, $scope))) {
                        $lot[0] = $r;
                    }
                }
            } else {
                foreach (self::$lot[1][$c][$name] as $v) {
                    if (null !== ($r = fire($v['task'], $lot))) {
                        $lot[0] = $r;
                    }
                }
            }
        }
        return $lot[0] ?? null;
    }

    public static function get(?string $name = null) {
        $c = static::class;
        if (isset($name)) {
            return self::$lot[1][$c][$name] ?? null;
        }
        return self::$lot[1][$c] ?? [];
    }

    public static function is(?string $name = null) {
        $c = static::class;
        return isset($name) ? self::$current[$c] === $name : self::$current[$c];
    }

    public static function let($name = null, ?callable $task = null) {
        $c = static::class;
        if (is_string($task)) {
            $task = trim($task, "\\");
        }
        if (is_array($name)) {
            foreach ($name as $v) {
                self::let($v, $name);
            }
        } else if (isset($name)) {
            if (isset(self::$lot[1][$c][$name])) {
                if (isset($task)) {
                    self::$lot[0][$c][$name] = [];
                    foreach (self::$lot[1][$c][$name] as $k => $v) {
                        if ($v['task'] === $task) {
                            self::$lot[0][$c][$name][is_object($task) ? spl_object_hash($task) : $task] = $v;
                            unset(self::$lot[1][$c][$name][$k]);
                        }
                    }
                } else {
                    self::$lot[0][$c][$name] = 1;
                    unset(self::$lot[1][$c][$name]);
                }
            } else {
                if (isset($task)) {
                    self::$lot[0][$c][$name][is_object($task) ? spl_object_hash($task) : $task] = 1;
                } else {
                    self::$lot[0][$c][$name] = 1;
                }
            }
        } else {
            self::$lot[1][$c] = [];
        }
    }

    public static function set($name, callable $task, float $stack = 10) {
        $c = static::class;
        if (is_string($task)) {
            $task = trim($task, "\\");
        }
        if (is_array($name)) {
            foreach ($name as $v) {
                self::set((string) $v, $task, $stack);
            }
        } else {
            if (!empty(self::$lot[0][$c][$name][is_object($task) ? spl_object_hash($task) : $task])) {
                // Skip!
            } else {
                if (!isset(self::$lot[1][$c][$name])) {
                    self::$lot[1][$c][$name] = [];
                }
                self::$lot[1][$c][$name][] = [
                    '0' => c2f($c),
                    '1' => "",
                    '2' => [],
                    'stack' => (float) $stack,
                    'task' => $task
                ];
                if (count(self::$lot[1][$c][$name]) > 1) {
                    self::$lot[1][$c][$name] = (new Anemone(self::$lot[1][$c][$name]))->sort([1, 'stack'])->lot;
                }
            }
        }
    }

}