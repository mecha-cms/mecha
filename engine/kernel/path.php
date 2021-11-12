<?php

final class Path extends Genome {

    public static function B(string $value = null, int $step = 1, string $join = DS) {
        if (DS === $join || '/' === $join) {
            if (1 === $step) {
                return null !== $value && "" !== $value ? basename($value) : null;
            }
        }
        $value = strtr($value, [
            DS => $join,
            '/' => $join
        ]);
        $value = rtrim(implode($join, array_slice(explode($join, $value), $step * -1)), $join);
        return "" !== $value ? $value : null;
    }

    public static function D(string $value = null, int $step = 1, string $join = DS) {
        if (DS === $join || '/' === $join) {
            $dir = rtrim(dirname($value, $step), $join);
            return '.' !== $dir ? $dir : null;
        }
        $value = strtr($value, [
            DS => $join,
            '/' => $join
        ]);
        $a = explode($join, $value);
        $value = rtrim(implode($join, array_slice($a, 0, count($a) - $step)), $join);
        return "" !== $value ? $value : null;
    }

    public static function F(string $value = null, string $join = DS) {
        $f = pathinfo($value, PATHINFO_DIRNAME);
        $n = pathinfo($value, PATHINFO_FILENAME);
        if ("" === $n) {
            $n = pathinfo($value, PATHINFO_BASENAME);
        }
        return strtr('.' !== $f ? $f . DS . $n : $n, [
            DS => $join,
            '/' => $join
        ]);
    }

    public static function N(string $value = null, $x = false) {
        $n = pathinfo($value, $x ? PATHINFO_BASENAME : PATHINFO_FILENAME);
        return "" !== $n ? $n : null;
    }

    public static function R(string $value = null, string $folder = ROOT, string $join = DS) {
        $folder = strtr($folder, [
            DS => $join,
            '/' => $join
        ]);
        return strtr(strtr($value, [
            DS => $join,
            '/' => $join
        ]), [
            $folder . $join => "",
            $folder => ""
        ]);
    }

    public static function X(string $value = null) {
        if (false === strpos($value, '.')) {
            return null;
        }
        $x = pathinfo($value, PATHINFO_EXTENSION);
        return $x ? strtolower($x) : null;
    }

}