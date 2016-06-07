<?php

/**
 * ================================================================================
 *  ASSET STUFF
 * ================================================================================
 *
 * -- CODE: -----------------------------------------------------------------------
 *
 *    echo Asset:stylesheet('foo.css');
 *
 * --------------------------------------------------------------------------------
 *
 *    echo Asset:stylesheet('foo.css', ' type="text/css"');
 *
 * --------------------------------------------------------------------------------
 *
 *    echo Asset::stylesheet(array(
 *        'foo.css', 'bar.css', 'baz.css', 'qux.css'
 *    ));
 *
 * --------------------------------------------------------------------------------
 *
 */

class Asset extends __ {

    public static $assets = array();
    public static $assets_x = array();

    // Get full version of private asset path
    public static function path($path, $fallback = false) {
        $config = Config::get();
        // External URL, nothing to check!
        if(strpos($path, '://') !== false || strpos($path, '//') === 0 || strpos($path, ':') !== false) {
            // Fix broken external URL `http://://example.com`
            $path = str_replace('://://', '://', $path);
            // Fix broken external URL `http:////example.com`
            $path = str_replace(':////', '://', $path);
            // Fix broken external URL `http:example.com`
            if(strpos($path, $config->scheme . ':') === 0 && strpos($path, $config->protocol) !== 0) {
                $path = str_replace(X . $config->scheme . ':', $config->protocol, X . $path);
            }
            // Check if URL very external ...
            if(strpos($path, $config->url) !== 0) return $path;
        }
        // ... else, try parse it into private asset path
        $path = File::path($path);
        // Full path, be quick!
        if(strpos($path, ROOT) === 0) {
            return File::exist($path, $fallback);
        }
        if($_path = File::exist(SHIELD . DS . $config->shield . DS . ltrim($path, DS))) {
            return $_path;
        } else if($_path = File::exist(ASSET . DS . ltrim($path, DS))) {
            return $_path;
        } else if($_path = File::exist(ROOT . DS . ltrim($path, DS))) {
            return $_path;
        }
        return $fallback;
    }

    // Get public asset URL
    public static function url($source) {
        $config = Config::get();
        $source = Filter::colon('asset:source', $source);
        $path = Filter::colon('asset:path', self::path($source, false));
        $url = File::url($path);
        if($path && strpos($path, ROOT) === false) {
            return strpos($url, '://') !== false || strpos($url, '//') === 0 ? Filter::colon('asset:url', $url, $source) : false;
        }
        return $path && file_exists($path) ? Filter::colon('asset:url', $url, $source) : false;
    }

    // Return the HTML stylesheet of asset
    public static function stylesheet($path, $addon = "", $merge = false) {
        $path = is_string($path) ? explode(' ', $path) : (array) $path;
        if($merge !== false) {
            return self::merge($path, $merge, $addon, __FUNCTION__);
        }
        $html = "";
        for($i = 0, $count = count($path); $i < $count; ++$i) {
            $url = self::url($path[$i]);
            if($url !== false) {
                self::$assets[$path[$i]] = 1;
                if(is_array($addon)) {
                    $attr = isset($addon[$i]) ? $addon[$i] : end($addon);
                } else {
                    $attr = $addon;
                }
                $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:stylesheet', str_repeat(TAB, 2) . '<link href="' . $url . '" rel="stylesheet"' . $attr . ES . NL, $path[$i], $url) : "";
            } else {
                // File does not exist
                $html .= str_repeat(TAB, 2) . '<!-- ' . $path[$i] . ' -->' . NL;
            }
        }
        return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
    }

    // Return the HTML javascript of asset
    public static function javascript($path, $addon = "", $merge = false) {
        $path = is_string($path) ? explode(' ', $path) : (array) $path;
        if($merge !== false) {
            return self::merge($path, $merge, $addon, __FUNCTION__);
        }
        $html = "";
        for($i = 0, $count = count($path); $i < $count; ++$i) {
            $url = self::url($path[$i]);
            if($url !== false) {
                self::$assets[$path[$i]] = 1;
                if(is_array($addon)) {
                    $attr = isset($addon[$i]) ? $addon[$i] : end($addon);
                } else {
                    $attr = $addon;
                }
                $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:javascript', str_repeat(TAB, 2) . '<script src="' . $url . '"' . $attr . '></script>' . NL, $path[$i], $url) : "";
            } else {
                // File does not exist
                $html .= str_repeat(TAB, 2) . '<!-- ' . $path[$i] . ' -->' . NL;
            }
        }
        return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
    }

    // Return the HTML image of asset
    public static function image($path, $addon = "", $merge = false) {
        $path = is_string($path) ? explode(' ', $path) : (array) $path;
        if($merge !== false) {
            return self::merge($path, $merge, $addon, __FUNCTION__);
        }
        $html = "";
        for($i = 0, $count = count($path); $i < $count; ++$i) {
            $url = self::url($path[$i]);
            if($url !== false) {
                self::$assets[$path[$i]] = 1;
                if(is_array($addon)) {
                    $attr = isset($addon[$i]) ? $addon[$i] : end($addon);
                } else {
                    $attr = $addon;
                }
                $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:image', '<img src="' . $url . '"' . $attr . ES . NL, $path[$i], $url) : "";
            } else {
                // File does not exist
                $html .= '<!-- ' . $path[$i] . ' -->' . NL;
            }
        }
        return O_BEGIN . rtrim($html, NL) . O_END;
    }

    // Merge multiple asset file(s) into a single file
    public static function merge($path, $name = null, $addon = "", $fn = null) {
        $cache = strpos($name, ROOT) === 0 ? File::path($name) : ASSET . DS . File::path($name);
        $log = CACHE . DS . 'asset.' . md5($cache) . '.log';
        $is_valid = true;
        if( ! file_exists($log)) {
            $is_valid = false;
        } else {
            $time = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if(count($time) !== count($path)) {
                $is_valid = false;
            } else {
                foreach($time as $i => $u) {
                    if($p = Filter::colon('asset:path', self::path($path[$i], false))) {
                        if( ! file_exists($p) || (int) filemtime($p) !== (int) $u) {
                            $is_valid = false;
                            break;
                        }
                    }
                }
            }
        }
        $unix = "";
        $content = "";
        $e = File::E($name);
        if( ! $is_valid || ! file_exists($cache)) {
            File::open($cache)->delete(); // delete cache ...
            if(Mecha::walk(array('gif', 'jpeg', 'jpg', 'png'))->has($e)) {
                $images = array();
                foreach($path as $p) {
                    $p = Filter::colon('asset:source', $p);
                    if( ! self::ignored($p) && $p = Filter::colon('asset:path', self::path($p, false))) {
                        if( ! file_exists($p)) continue;
                        $unix .= filemtime($p) . "\n";
                        $images[] = $p;
                    }
                }
                if( ! empty($images)) {
                    File::write(substr($unix, 0, -1))->saveTo($log);
                    Image::take($images)->merge()->saveTo($cache);
                }
            } else {
                foreach($path as $p) {
                    $p = Filter::colon('asset:source', $p);
                    if( ! self::ignored($p) && $p = Filter::colon('asset:path', self::path($p, false))) {
                        if( ! file_exists($p)) continue;
                        $unix .= filemtime($p) . "\n";
                        $c = Filter::apply('asset:input', file_get_contents($p) . "\n", $p);
                        if(strpos(File::B($p), '.min.') === false) {
                            if(substr($cache, -8) === '.min.css') {
                                $c = Converter::detractShell($c);
                            } else if(substr($cache, -7) === '.min.js') {
                                $c = Converter::detractSword($c);
                            } else {
                                $c = $c . "\n";
                            }
                            $content .= Filter::apply('asset:output', $c, $p);
                        } else {
                            $content .= Filter::apply('asset:output', $c . "\n", $p);
                        }
                    }
                }
                if($content = trim($content)) {
                    File::write(substr($unix, 0, -1))->saveTo($log);
                    File::write($content)->saveTo($cache);
                }
            }
        }
        if(is_null($fn)) {
            $fn = Mecha::alter($e, array(
                'css' => 'stylesheet',
                'js' => 'javascript',
                'gif' => 'image',
                'jpeg' => 'image',
                'jpg' => 'image',
                'png' => 'image'
            ));
        }
        return call_user_func('self::' . $fn, $cache, $addon);
    }

    // Check for loaded asset(s)
    public static function loaded($path = null, $fallback = false) {
        if( ! is_null($path)) {
            return isset(self::$assets[$path]) ? $path : $fallback;
        }
        return ! empty(self::$assets) ? self::$assets : $fallback;
    }

    // alias for `Asset::loaded()`
    public static function exist($path = null, $fallback = false) {
        return self::loaded($path, $fallback);
    }

    // Do not let the `Asset` loads these file(s) ...
    public static function ignore($path) {
        if(is_array($path)) {
            foreach($path as $p) {
                self::$assets_x[$p] = isset(self::$assets[$p]) ? self::$assets[$p] : 1;
            }
        } else {
            self::$assets_x[$path] = isset(self::$assets[$path]) ? self::$assets[$path] : 1;
        }
    }

    // Check for ignored asset(s)
    public static function ignored($path = null, $fallback = false) {
        if( ! is_null($path)) {
            return isset(self::$assets_x[$path]) ? self::$assets_x[$path] : $fallback;
        }
        return ! empty(self::$assets_x) ? self::$assets_x : $fallback;
    }

}