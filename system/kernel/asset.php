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

    // Return the HTML JavaScript of asset
    public static function javascript($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= '<script src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . '></script>';
            }
            return Filter::apply('asset:javascript', $html);
        }
        return Filter::apply('asset:javascript', '<script src="' . self::url($path) . '"' . $addon . '></script>');
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
                $html .= '<link href="' . self::url($path[$i]) . '" rel="stylesheet"' . (is_array($addon) ? $addon[$i] : $addon) . EE_SUFFIX;
            }
            return Filter::apply('asset:css', $html);
        }
        return Filter::apply('asset:css', '<link href="' . self::url($path) . '" rel="stylesheet"' . $addon . EE_SUFFIX);
    }

    // Return HTML image of asset
    public static function image($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                $html .= '<img src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . EE_SUFFIX;
            }
            return Filter::apply('asset:image', $html);
        }
        return Filter::apply('asset:image', '<img src="' . self::url($path) . '"' . $addon . EE_SUFFIX);
    }

}