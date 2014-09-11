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

    public static $loaded = array();

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
        $url = self::pathTrace($path);
        if(strpos($url, '://') === false && ! File::exist($url)) {
            return false;
        }
        if(strpos($url, ROOT) === false) {
            return Filter::apply('asset:url', $path . ($config->resource_versioning && strpos($url, $config->url) === 0 ? '?v=' . filemtime(str_replace(array($config->url, '\\', '/'), array(ROOT, DS, DS), $url)) : ""), $path);
        }
        return Filter::apply('asset:url', str_replace(array(ROOT, '\\'), array($config->url, '/'), $url) . ($config->resource_versioning ? '?v=' . filemtime($url) : ""), $path);
    }

    // Return the HTML JavaScript of asset
    public static function javascript($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                if(self::url($path[$i]) !== false) {
                    self::$loaded[$path[$i]] = 1;
                    $html .= Filter::apply('asset:javascript', str_repeat(TAB, 2) . '<script src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . '></script>' . NL, $path[$i]);
                }
            }
            return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
        }
        if(self::url($path) === false) {
            return "";
        }
        self::$loaded[$path] = 1;
        return Filter::apply('asset:javascript', O_BEGIN . str_repeat(TAB, 2) . '<script src="' . self::url($path) . '"' . $addon . '></script>' . O_END, $path);
    }

    // DEPRECATED. Please use `Asset::javascript()`
    public static function script($path, $addon = "") {
        return self::javascript($path, $addon);
    }

    // Return the HTML StyleSheet of asset
    public static function stylesheet($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                if(self::url($path[$i]) !== false) {
                    self::$loaded[$path[$i]] = 1;
                    $html .= Filter::apply('asset:stylesheet', str_repeat(TAB, 2) . '<link href="' . self::url($path[$i]) . '" rel="stylesheet"' . (is_array($addon) ? $addon[$i] : $addon) . ES . NL, $path[$i]);
                }
            }
            return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
        }
        if(self::url($path) === false) {
            return "";
        }
        self::$loaded[$path] = 1;
        return Filter::apply('asset:stylesheet', O_BEGIN . str_repeat(TAB, 2) . '<link href="' . self::url($path) . '" rel="stylesheet"' . $addon . ES . O_END, $path);
    }

    // Return the HTML image of asset
    public static function image($path, $addon = "") {
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                if(self::url($path[$i]) !== false) {
                    self::$loaded[$path[$i]] = 1;
                    $html .= Filter::apply('asset:image', '<img src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . ES . NL, $path[$i]);
                }
            }
            return O_BEGIN . rtrim($html, NL) . O_END;
        }
        if(self::url($path) === false) {
            return "";
        }
        self::$loaded[$path] = 1;
        return Filter::apply('asset:image', O_BEGIN . '<img src="' . self::url($path) . '"' . $addon . ES . O_END, $path);
    }

    // Checks for loaded asset(s)
    public static function loaded($path = null) {
        if(is_null($path)) return self::$loaded;
        return isset(self::$loaded[str_replace(DS, '/', $path)]);
    }

}