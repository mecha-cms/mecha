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
 * -- RESULT: ----------------------------------------------------------------------
 *
 *    http://example.org/foo.css
 *
 *    <link href="http://example.org/foo.css" rel="stylesheet">
 *    <link href="http://example.org/foo.css" rel="stylesheet" id="main">
 *
 * ---------------------------------------------------------------------------------
 *
 */

class Asset {

    private static $root = "";

    private function __construct() {}

    private static function checkPath($path) {
        $path = ltrim($path, '\\/');
        $config = Config::get();
        if(File::exist(SHIELD . DS . $config->shield . DS . $path)) {
            return SHIELD . DS . $config->shield . DS . $path;
        } else {
            if(File::exist(ROOT . DS . $path)) {
                return ROOT . DS . $path;
            } else {
                return '## FILE NOT FOUND: ' . $path . ' ##';
            }
        }
    }

    // Get public asset URL
    public static function url($path) {
        $config = Config::get();
        return str_replace(array(ROOT, '\\'), array($config->url, '/'), self::checkPath($path)) . ($config->resource_versioning ? '?v=' . filemtime(self::checkPath($path)) : "");
    }

    // Get asset content
    public static function read($path) {
        return File::exist(self::checkPath($path)) ? File::open(self::checkPath($path))->read() : "";
    }

    // Create a new asset file
    public static function create($content, $path) {
        File::write($content)->saveTo(self::checkPath($path));
    }

    // Update content of an asset file
    public static function update($content, $path) {
        File::open(self::checkPath($path))->write($content)->save();
    }

    // Delete an asset file
    public static function delete($path) {
        File::open(self::checkPath($path))->delete();
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