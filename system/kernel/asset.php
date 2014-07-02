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

    private static function pathTrace($path) {
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
        if(strpos(self::pathTrace($path), ROOT) === false) {
            return Filter::apply('asset:url', $path, $path);
        }
        return Filter::apply('asset:url', str_replace(array(ROOT, '\\'), array($config->url, '/'), self::pathTrace($path)) . ($config->resource_versioning ? '?v=' . filemtime(self::pathTrace($path)) : ""), $path);
    }

    // Return the HTML JavaScript of asset
    public static function javascript($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= Filter::apply('asset:javascript', '<script src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . '></script>', $path[$i]);
            }
            return $html;
        }
        return Filter::apply('asset:javascript', '<script src="' . self::url($path) . '"' . $addon . '></script>', $path);
    }

    // DEPRECATED !!! Please use `Asset::javascript()`
    public static function script($path, $addon = "") {
        return self::javascript($path, $addon);
    }

    // Return the HTML stylesheet of asset
    public static function stylesheet($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= Filter::apply('asset:css', '<link href="' . self::url($path[$i]) . '" rel="stylesheet"' . (is_array($addon) ? $addon[$i] : $addon) . ES, $path[$i]);
            }
            return $html;
        }
        return Filter::apply('asset:css', '<link href="' . self::url($path) . '" rel="stylesheet"' . $addon . ES, $path);
    }

    // Return the HTML image of asset
    public static function image($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= Filter::apply('asset:image', '<img src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . ES, $path[$i]);
            }
            return $html;
        }
        return Filter::apply('asset:image', '<img src="' . self::url($path) . '"' . $addon . ES, $path);
    }

}