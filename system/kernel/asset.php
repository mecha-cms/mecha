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
    public static $ignored = array();

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

    // Return the HTML StyleSheet of asset
    public static function stylesheet($path, $addon = "", $merge = false) {
        if($merge) {
            self::$loaded[$merge] = 1;
            return self::merge($path, $merge, $addon, 'stylesheet');
        }
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                if(self::url($path[$i]) !== false) {
                    self::$loaded[$path[$i]] = 1;
                    $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:stylesheet', str_repeat(TAB, 2) . '<link href="' . self::url($path[$i]) . '" rel="stylesheet"' . (is_array($addon) ? $addon[$i] : $addon) . ES . NL, $path[$i]) : "";
                }
            }
            return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
        }
        if(self::url($path) === false) {
            return "";
        }
        self::$loaded[$path] = 1;
        return ! self::ignored($path) ? Filter::apply('asset:stylesheet', O_BEGIN . str_repeat(TAB, 2) . '<link href="' . self::url($path) . '" rel="stylesheet"' . $addon . ES . O_END, $path) : "";
    }

    // Return the HTML JavaScript of asset
    public static function javascript($path, $addon = "", $merge = false) {
        if($merge) {
            self::$loaded[$merge] = 1;
            return self::merge($path, $merge, $addon, 'javascript');
        }
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                if(self::url($path[$i]) !== false) {
                    self::$loaded[$path[$i]] = 1;
                    $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:javascript', str_repeat(TAB, 2) . '<script src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . '></script>' . NL, $path[$i]) : "";
                }
            }
            return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
        }
        if(self::url($path) === false) {
            return "";
        }
        self::$loaded[$path] = 1;
        return ! self::ignored($path) ? Filter::apply('asset:javascript', O_BEGIN . str_repeat(TAB, 2) . '<script src="' . self::url($path) . '"' . $addon . '></script>' . O_END, $path) : "";
    }

    // DEPRECATED. Please use `Asset::javascript()`
    public static function script($path, $addon = "", $merge = false) {
        return self::javascript($path, $addon, $merge);
    }

    // Return the HTML image of asset
    public static function image($path, $addon = "", $merge = false) {
        if($merge) {
            self::$loaded[$merge] = 1;
            return self::merge($path, $merge, $addon, 'image');
        }
        if(is_array($path)) {
            $html = "";
            for($i = 0, $count = count($path); $i < $count; ++$i) {
                if(self::url($path[$i]) !== false) {
                    self::$loaded[$path[$i]] = 1;
                    $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:image', '<img src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . ES . NL, $path[$i]) : "";
                }
            }
            return O_BEGIN . rtrim($html, NL) . O_END;
        }
        if(self::url($path) === false) {
            return "";
        }
        self::$loaded[$path] = 1;
        return ! self::ignored($path) ? Filter::apply('asset:image', O_BEGIN . '<img src="' . self::url($path) . '"' . $addon . ES . O_END, $path) : "";
    }

    // Merge multiple asset files into a single file
    public static function merge($files = array(), $name = null, $addon = "", $call = null) {
        if( ! is_array($files)) {
            $files = array($files);
        }
        $the_file = ASSET . DS . str_replace(array('\\', '/'), DS, $name);
        $the_log = SYSTEM . DS . 'log' . DS . 'asset.' . str_replace(array(ASSET . DS, DS), array("", '__'), $the_file) . '.log';
        $is_valid = true;
        if(file_exists($the_log)) {
            $the_file_time = explode("\n", file_get_contents($the_log));
            foreach($the_file_time as $i => $time) {
                if(file_exists(self::pathTrace($files[$i])) && ((int) filemtime(self::pathTrace($files[$i])) !== (int) $time)) {
                    $is_valid = false;
                    break;
                }
            }
        } else {
            $is_valid = false;
        }
        $merged_time = "";
        $merged_content = "";
        $e = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if( ! file_exists($the_file) || ! $is_valid) {
            if($e == 'gif' || $e == 'jpg' || $e == 'jpeg' || $e == 'png') {
                foreach($files as $file) {
                    $path = self::pathTrace($file);
                    if(file_exists($path)) {
                        $merged_time .=  filemtime($path) . "\n";
                    }
                }
                File::write(trim($merged_time))->saveTo($the_log);
                Image::take($files)->merge()->saveTo($the_file);
            } else {
                foreach($files as $file) {
                    $path = self::pathTrace($file);
                    if(file_exists($path)) {
                        $merged_time .= filemtime($path) . "\n";
                        $c = file_get_contents($path);
                        if(strpos(basename($path), '.min.') === false) {
                            if(strpos(basename($the_file), '.min.css') !== false) {
                                $merged_content .= Converter::detractShell($c) . "\n";
                            } elseif(strpos(basename($the_file), '.min.js') !== false) {
                                $merged_content .= Converter::detractSword($c) . "\n";
                            } else {
                                $merged_content .= $c . "\n\n";
                            }
                        } else {
                            $merged_content .= $c . "\n\n";
                        }
                    }
                }
                File::write(trim($merged_time))->saveTo($the_log);
                File::write(trim($merged_content))->saveTo($the_file);
            }
        }
        if(is_null($call)) {
            if($e == 'css') {
                return self::stylesheet($the_file, $addon);
            } elseif($e == 'js') {
                return self::javascript($the_file, $addon);
            } elseif($e == 'gif' || $e == 'jpg' || $e == 'jpeg' || $e == 'png') {
                return self::image($the_file, $addon);
            } else {
                return "";
            }
        } else {
            return call_user_func_array('self::' . $call, array($the_file, $addon));
        }
    }

    // Check for loaded asset(s)
    public static function loaded($path = null) {
        if(is_null($path)) return self::$loaded;
        return isset(self::$loaded[$path]);
    }

    // Do not let the `Asset` loads these files ...
    public static function ignore($path = null) {
        if(is_array($path)) {
            foreach($path as $p) {
                self::$ignored[$p] = 1;
            }
        } else {
            self::$ignored[$path] = 1;
        }
    }

    // Check for ignored asset(s)
    public static function ignored($path = null) {
        if(is_null($path)) return self::$ignored;
        return isset(self::$ignored[$path]);
    }

}