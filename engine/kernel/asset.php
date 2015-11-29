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

class Asset extends Base {

    protected static $assets = array();
    protected static $assets_x = array();

    // Get full version of private asset path
    public static function path($path, $fallback = false) {
        // External URL, nothing to check!
        if(strpos($path, '://') !== false) {
            return $path;
        }
        $path = File::path($path);
        // Full path, be quick!
        if(strpos($path, ROOT) === 0) {
            return File::exist($path, $fallback);
        }
        if($_path = File::exist(SHIELD . DS . Config::get('shield') . DS . ltrim($path, DS))) {
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
            return strpos($url, '://') !== false ? Filter::colon('asset:url', $url . ($config->resource_versioning && strpos($url, $config->url) === 0 && file_exists($path) ? '?' . sprintf(ASSET_VERSION_FORMAT, filemtime($path)) : ""), $source) : false;
        }
        return $path && file_exists($path) ? Filter::colon('asset:url', $url . ($config->resource_versioning ? '?' . sprintf(ASSET_VERSION_FORMAT, filemtime($path)) : ""), $source) : false;
    }

    // Return the HTML stylesheet of asset
    public static function stylesheet($path, $addon = "", $merge = false) {
        $path = (array) $path;
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
        $path = (array) $path;
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
        $path = (array) $path;
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
    public static function merge($path, $name = null, $addon = "", $call = null) {
        $the_path = ASSET . DS . File::path($name);
        $the_log = LOG . DS . 'asset.' . str_replace(array(ASSET . DS, DS), array("", '__'), $the_path) . '.log';
        $is_valid = true;
        if( ! file_exists($the_log)) {
            $is_valid = false;
        } else {
            $the_time = explode("\n", file_get_contents($the_log));
            if(count($the_time) !== count($path)) {
                $is_valid = false;
            } else {
                foreach($the_time as $i => $unix) {
                    $p = self::path($path[$i]);
                    if( ! file_exists($p) || (int) filemtime($p) !== (int) $unix) {
                        $is_valid = false;
                        break;
                    }
                }
            }
        }
        $unix = "";
        $content = "";
        $e = File::E($name);
        if( ! file_exists($the_path) || ! $is_valid) {
            if(Text::check($e)->in(array('gif', 'jpeg', 'jpg', 'png'))) {
                foreach($path as &$p) {
                    $p = Filter::colon('asset:source', $p);
                    if( ! self::ignored($p) && $p = Filter::colon('asset:path', self::path($p, false))) {
                        $unix .= filemtime($p) . "\n";
                    }
                }
                File::write(trim($unix))->saveTo($the_log);
                Image::take($path)->merge()->saveTo($the_path);
            } else {
                foreach($path as $p) {
                    $p = Filter::colon('asset:source', $p);
                    if( ! self::ignored($p) && $p = Filter::colon('asset:path', self::path($p, false))) {
                        $unix .= filemtime($p) . "\n";
                        $c = Filter::apply('asset:input', file_get_contents($p) . "\n", $p);
                        if(strpos(File::B($p), '.min.') === false) {
                            if(strpos(File::B($the_path), '.min.css') !== false) {
                                $c = Converter::detractShell($c);
                            } else if(strpos(File::B($the_path), '.min.js') !== false) {
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
                File::write(trim($unix))->saveTo($the_log);
                File::write(trim($content))->saveTo($the_path);
            }
        }
        if(is_null($call)) {
            $call = Mecha::alter($e, array(
                'css' => 'stylesheet',
                'js' => 'javascript',
                'gif' => 'image',
                'jpeg' => 'image',
                'jpg' => 'image',
                'png' => 'image'
            ));
        }
        return call_user_func('self::' . $call, $the_path, $addon);
    }

    // Check for loaded asset(s)
    public static function loaded($path = null) {
        if(is_null($path)) return self::$assets;
        return isset(self::$assets[$path]) ? $path : false;
    }

    // Do not let the `Asset` loads these file(s) ...
    public static function ignore($path) {
        if(is_array($path)) {
            foreach($path as $p) {
                self::$assets_x[$p] = 1;
            }
        } else {
            self::$assets_x[$path] = 1;
        }
    }

    // Check for ignored asset(s)
    public static function ignored($path = null) {
        if(is_null($path)) return self::$assets_x;
        return isset(self::$assets_x[$path]) ? $path : false;
    }

}