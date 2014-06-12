<?php

/**
 * ================================================================================
 *  ASSETS STUFF
 * ================================================================================
 *
 * -- CODE: -----------------------------------------------------------------------
 *
 *    echo Asset:url('foo.css');
 *
 *    echo Asset:stylesheet('foo.css');
 *    echo Asset:stylesheet('foo.css', ' id="main"');
 *
 * -- RESULT: ---------------------------------------------------------------------
 *
 *    http://example.org/foo.css
 *
 *    <link href="http://example.org/foo.css" rel="stylesheet">
 *    <link href="http://example.org/foo.css" rel="stylesheet" id="main">
 *
 * --------------------------------------------------------------------------------
 *
 */

class Asset {

    private static $root = "";

    private static function tracePath($path) {
        $config = Config::get();
        if($_path = File::exist(SHIELD . DS . $config->shield . DS . ltrim($path, '\\/'))) {
            return $_path;
        } else {
            if($_path = File::exist(ROOT . DS . ltrim($path, '\\/'))) {
                return $_path;
            }
        }
        return $path;
    }

    // Get public asset URL
    public static function url($path) {
        $config = Config::get();
        if(strpos(self::tracePath($path), ROOT) === false) {
            return Filter::apply('asset:url', self::tracePath($path));
        }
        return Filter::apply('asset:url', str_replace(array(ROOT, '\\'), array($config->url, '/'), self::tracePath($path)) . ($config->resource_versioning ? '?v=' . filemtime(self::tracePath($path)) : ""));
    }

    // Return HTML script of asset
    public static function script($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= '<script src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . '></script>';
            }
            return $html;
        }
        return '<script src="' . self::url($path) . '"' . $addon . '></script>';
    }

    // Return HTML stylesheet of asset
    public static function stylesheet($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= '<link href="' . self::url($path[$i]) . '" rel="stylesheet"' . (is_array($addon) ? $addon[$i] : $addon) . '>';
            }
            return $html;
        }
        return '<link href="' . self::url($path) . '" rel="stylesheet"' . $addon . '>';
    }

    // Return HTML image of asset
    public static function image($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= '<img src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . '>';
            }
            return $html;
        }
        return '<img src="' . self::url($path) . '"' . $addon . '>';
    }

}