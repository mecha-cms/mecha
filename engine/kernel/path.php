<?php

class Path extends Genome {

    public static function B($path, $step = 1, $s = null) {
        if (isset($s)) {
            $path = str_replace($s, DS, $path);
        } else {
            if ($step === 1) {
                return basename($path);
            }
            $s = DS;
        }
        $p = explode(DS, $path);
        return implode($s, array_slice($p, $step * -1));
    }

    public static function D($path, $step = 1, $s = null) {
        if ($t = isset($s)) {
            $path = str_replace([DS, $s], [X, DS], $path);
        } else if ($step === 1) {
            return dirname($path) === '.' ? "" : dirname($path);
        }
        while ($step > 0) {
            $path = dirname($path);
            --$step;
        }
        if ($t) {
            $path = str_replace([DS, X], [$s, DS], $path);
        }
        return $path === '.' ? "" : $path;
    }

    public static function N($path, $x = false) {
        return pathinfo($path, $x ? PATHINFO_BASENAME : PATHINFO_FILENAME);
    }

    public static function X($path, $fail = false) {
        if (strpos($path, '.') === false) return $fail;
        $x = pathinfo($path, PATHINFO_EXTENSION);
        return $x ? strtolower($x) : $fail;
    }

    public static function F($path, $root = null, $x = DS) {
        $f = pathinfo($path, PATHINFO_DIRNAME);
        $n = pathinfo($path, PATHINFO_FILENAME);
        if (isset($root)) {
            $f = str_replace(['/', $root . $x, $root], [$x, "", ""], $f);
        }
        $s = ($f === '.' ? "" : $f) . $x . $n;
        if (isset($root)) {
            $s = trim($s, $x);
        } else {
            $s = rtrim($s, $x);
        }
        return $s;
    }

}