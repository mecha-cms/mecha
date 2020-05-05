<?php

final class Alert extends Genome implements \Countable, \IteratorAggregate, \JsonSerializable {

    private static function i(array $lot, string $kin) {
        $out = [];
        foreach ($lot as $v) {
            $out[] = ['alert', i(...$v), ['type' => $kin]];
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

    public function count() {
        return count((array) self::get(null, false));
    }

    public function getIterator() {
        return new \ArrayIterator(self::get(null, false) ?? []);
    }

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

    public static function get($kin = null, $x = true) {
        if (is_array($kin)) {
            $out = [];
            foreach ($kin as $v) {
                if (isset($_SESSION['alert'][$v])) {
                    $out = array_merge($out, self::i($_SESSION['alert'][$v], $v));
                    if ($x) {
                        unset($_SESSION['alert'][$v]);
                    }
                }
            }
            return $out;
        }
        if (isset($kin) && isset($_SESSION['alert'][$kin])) {
            $out = self::i($_SESSION['alert'][$kin], $kin);
            if ($x) {
                unset($_SESSION['alert'][$kin]);
            }
            return $out;
        }
        if (isset($_SESSION['alert'])) {
            $out = [];
            foreach ((array) $_SESSION['alert'] as $k => $v) {
                $out = array_merge($out, self::i($v, $k));
                if ($x) {
                    unset($_SESSION['alert'][$k]);
                }
            }
            return $out;
        }
        return null;
    }

    public static function set(...$lot) {
        $_SESSION['alert'][array_shift($lot)][] = $lot;
    }

    public static function let($kin = null) {
        if (is_array($kin)) {
            foreach ($kin as $v) {
                unset($_SESSION['alert'][$v]);
            }
        } else if (isset($kin)) {
            unset($_SESSION['alert'][$kin]);
        } else {
            unset($_SESSION['alert']);
        }
    }

}
