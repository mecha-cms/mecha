<?php

final class Path extends Genome {

    public static function B(string $path, int $step = 1, string $join = DS) {
        if (DS === $join || '/' === $join) {
            if (1 === $step) {
                return "" !== $path ? basename($path) : null;
            }
        }
        $path = str_replace([DS, '/'], $join, $path);
        $path = rtrim(implode($join, array_slice(explode($join, $path), $step * -1)), $join);
        return "" !== $path ? $path : null;
    }

    public static function D(string $path, int $step = 1, string $join = DS) {
        if (DS === $join || '/' === $join) {
            $dir = rtrim(dirname($path, $step), $join);
            return '.' !== $dir ? $dir : null;
        }
        $path = str_replace([DS, '/'], $join, $path);
        $a = explode($join, $path);
        $path = rtrim(implode($join, array_slice($a, 0, count($a) - $step)), $join);
        return "" !== $path ? $path : null;
    }

    public static function F(string $path, string $join = DS) {
        $f = pathinfo($path, PATHINFO_DIRNAME);
        $n = pathinfo($path, PATHINFO_FILENAME);
        if ("" === $n) {
            $n = pathinfo($path, PATHINFO_BASENAME);
        }
        return str_replace([DS, '/'], $join, '.' === $f ? $n : $f . DS . $n);
    }

    public static function N(string $path, $x = false) {
        return (string) pathinfo($path, $x ? PATHINFO_BASENAME : PATHINFO_FILENAME);
    }

    public static function R(string $path, string $root = ROOT, string $join = DS) {
        $root = str_replace([DS, '/'], $join, $root);
        return str_replace([DS, '/', $root . $join, $root], [$join, $join, "", ""], $path);
    }

    public static function X(string $path) {
        if (false === strpos($path, '.')) {
            return null;
        }
        $x = pathinfo($path, PATHINFO_EXTENSION);
        return $x ? strtolower($x) : null;
    }

}