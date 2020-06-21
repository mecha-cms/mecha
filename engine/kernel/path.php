<?php

final class Path extends Genome {

    public static function B(string $path = null, int $step = 1, string $join = DS) {
        if (DS === $join || '/' === $join) {
            if (1 === $step) {
                return null !== $path && "" !== $path ? basename($path) : null;
            }
        }
        $path = strtr($path, [DS => $join, '/' => $join]);
        $path = rtrim(implode($join, array_slice(explode($join, $path), $step * -1)), $join);
        return "" !== $path ? $path : null;
    }

    public static function D(string $path = null, int $step = 1, string $join = DS) {
        if (DS === $join || '/' === $join) {
            $dir = rtrim(dirname($path, $step), $join);
            return '.' !== $dir ? $dir : null;
        }
        $path = strtr($path, [DS => $join, '/' => $join]);
        $a = explode($join, $path);
        $path = rtrim(implode($join, array_slice($a, 0, count($a) - $step)), $join);
        return "" !== $path ? $path : null;
    }

    public static function F(string $path = null, string $join = DS) {
        $f = pathinfo($path, PATHINFO_DIRNAME);
        $n = pathinfo($path, PATHINFO_FILENAME);
        if ("" === $n) {
            $n = pathinfo($path, PATHINFO_BASENAME);
        }
        return strtr('.' !== $f ? $f . DS . $n : $n, [DS => $join, '/' => $join]);
    }

    public static function N(string $path = null, $x = false) {
        $n = pathinfo($path, $x ? PATHINFO_BASENAME : PATHINFO_FILENAME);
        return "" !== $n ? $n : null;
    }

    public static function R(string $path = null, string $root = ROOT, string $join = DS) {
        $root = strtr($root, [DS => $join, '/' => $join]);
        return strtr(strtr($path, [DS => $join, '/' => $join]), [
            $root . $join => "",
            $root => ""
        ]);
    }

    public static function X(string $path = null) {
        if (false === strpos($path, '.')) {
            return null;
        }
        $x = pathinfo($path, PATHINFO_EXTENSION);
        return $x ? strtolower($x) : null;
    }

}
