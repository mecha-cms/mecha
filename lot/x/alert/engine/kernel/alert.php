<?php

final class Alert extends Genome implements \Countable, \IteratorAggregate, \JsonSerializable {

    public static $alert = null;

    private static function i(array $lot, string $kin) {
        $out = [];
        foreach ($lot as $v) {
            $out[] = [
                0 => 'alert',
                1 => i(...((array) $v)),
                2 => ['type' => $kin]
            ];
        }
        return $out;
    }

    public function __toString() {
        if ($alert = self::get()) {
            $out = "";
            foreach ($alert as $v) {
                $out .= new SGML($v);
            }
            return $out;
        }
        return "";
    }

    public function count(): int {
        return count((array) self::get(null, false));
    }

    public function getIterator(): \Traversable {
        return new \ArrayIterator(self::get(null, false) ?? []);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return self::get(null, false);
    }

    public static function __callStatic(string $kin, array $lot = []) {
        if (parent::_($kin)) {
            return parent::__callStatic($kin, $lot);
        }
        array_unshift($lot, $kin);
        return self::set(...$lot);
    }

    public static function get($kin = null, $clear = true) {
        if (!isset(self::$alert)) {
            self::$alert = $_SESSION['alert'] ?? [];
        }
        if (is_array($kin)) {
            $out = [];
            foreach ($kin as $v) {
                if (isset(self::$alert[$v])) {
                    $out = array_merge($out, self::i(self::$alert[$v], $v));
                    if ($clear) {
                        unset($_SESSION['alert'][$v]);
                    }
                }
            }
            return $out;
        }
        if (isset($kin) && isset(self::$alert[$kin])) {
            $out = self::i(self::$alert[$kin], $kin);
            if ($clear) {
                unset($_SESSION['alert'][$kin]);
            }
            return $out;
        }
        if (isset(self::$alert)) {
            $out = [];
            foreach ((array) self::$alert as $k => $v) {
                $out = array_merge($out, self::i($v, $k));
                if ($clear) {
                    unset($_SESSION['alert'][$k]);
                }
            }
            return $out;
        }
        return null;
    }

    public static function set(...$lot) {
        $v = array_shift($lot);
        self::$alert[$v][] = $_SESSION['alert'][$v][] = $lot;
    }

    public static function let($kin = null) {
        if (is_array($kin)) {
            foreach ($kin as $v) {
                unset(self::$alert[$v], $_SESSION['alert'][$v]);
            }
        } else if (isset($kin)) {
            unset(self::$alert[$kin], $_SESSION['alert'][$kin]);
        } else {
            self::$alert = null;
            unset($_SESSION['alert']);
        }
    }

}