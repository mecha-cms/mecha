<?php

/**
 * ================================================================================
 *  ASSETS STUFF
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

    protected static $loaded = array();
    protected static $ignored = array();

    // Get full version of private asset path
    public static function path($path) {
        $path = File::path($path);
        if($_path = File::exist(SHIELD . DS . Config::get('shield') . DS . ltrim($path, DS))) {
            return $_path;
        } else if($_path = File::exist(ROOT . DS . ltrim($path, DS))) {
            return $_path;
        }
        return $path;
    }

    // Get public asset URL
    public static function url($path_origin) {
        $config = Config::get();
        $path = self::path($path_origin);
        $url = File::url($path);
        if(strpos($path, ROOT) === false) {
            if(strpos($url, '://') === false) return false;
            return Filter::apply('asset:url', $url . ($config->resource_versioning && strpos($url, $config->url) === 0 ? '?' . sprintf(ASSET_VERSION_FORMAT, filemtime($path)) : ""), $path_origin);
        }
        if( ! file_exists($path)) return false;
        return Filter::apply('asset:url', $url . ($config->resource_versioning ? '?' . sprintf(ASSET_VERSION_FORMAT, filemtime($path)) : ""), $path_origin);
    }

    // Return the HTML stylesheet of asset
    public static function stylesheet($path, $addon = "", $merge = false) {
        if($merge) {
            return self::merge($path, $merge, $addon, 'stylesheet');
        }
        if( ! is_array($path)) {
            $path = strpos($path, '.css;') !== false ? explode(';', $path) : array($path);
        }
        $html = "";
        for($i = 0, $count = count($path); $i < $count; ++$i) {
            if(self::url($path[$i]) !== false) {
                self::$loaded[$path[$i]] = 1;
                $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:stylesheet', str_repeat(TAB, 2) . '<link href="' . self::url($path[$i]) . '" rel="stylesheet"' . (is_array($addon) ? $addon[$i] : $addon) . ES . NL, $path[$i]) : "";
            } else {
                // File does not exist
                $html .= str_repeat(TAB, 2) . '<!-- ' . $path[$i] . ' -->' . NL;
            }
        }
        return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
    }

    // Return the HTML javascript of asset
    public static function javascript($path, $addon = "", $merge = false) {
        if($merge) {
            return self::merge($path, $merge, $addon, 'javascript');
        }
        if( ! is_array($path)) {
            $path = strpos($path, '.js;') !== false ? explode(';', $path) : array($path);
        }
        $html = "";
        for($i = 0, $count = count($path); $i < $count; ++$i) {
            if(self::url($path[$i]) !== false) {
                self::$loaded[$path[$i]] = 1;
                $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:javascript', str_repeat(TAB, 2) . '<script src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . '></script>' . NL, $path[$i]) : "";
            } else {
                // File does not exist
                $html .= str_repeat(TAB, 2) . '<!-- ' . $path[$i] . ' -->' . NL;
            }
        }
        return O_BEGIN . rtrim(substr($html, strlen(TAB . TAB)), NL) . O_END;
    }

    // Return the HTML image of asset
    public static function image($path, $addon = "", $merge = false) {
        if($merge) {
            return self::merge($path, $merge, $addon, 'image');
        }
        if( ! is_array($path)) {
            $path = strpos($path, ';') !== false ? explode(';', $path) : array($path);
        }
        $html = "";
        for($i = 0, $count = count($path); $i < $count; ++$i) {
            if(self::url($path[$i]) !== false) {
                self::$loaded[$path[$i]] = 1;
                $html .= ! self::ignored($path[$i]) ? Filter::apply('asset:image', '<img src="' . self::url($path[$i]) . '"' . (is_array($addon) ? $addon[$i] : $addon) . ES . NL, $path[$i]) : "";
            } else {
                // File does not exist
                $html .= '<!-- ' . $path[$i] . ' -->' . NL;
            }
        }
        return O_BEGIN . rtrim($html, NL) . O_END;
    }

    // Merge multiple asset files into a single file
    public static function merge($files, $name = null, $addon = "", $call = null) {
        if( ! is_array($files)) {
            $files = strpos($files, ';') !== false ? explode(';', $files) : array($files);
        }
        $the_file = ASSET . DS . File::path($name);
        $the_file_log = SYSTEM . DS . 'log' . DS . 'asset.' . str_replace(array(ASSET . DS, DS), array("", '__'), $the_file) . '.log';
        $is_valid = true;
        if(file_exists($the_file_log)) {
            $the_file_time = explode("\n", file_get_contents($the_file_log));
            foreach($the_file_time as $i => $time) {
                $path = self::path($files[$i]);
                if( ! file_exists($path) || (int) filemtime($path) !== (int) $time) {
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
            if($e == 'gif' || $e == 'jpeg' || $e == 'jpg' || $e == 'png') {
                foreach($files as $file) {
                    if( ! self::ignored($file)) {
                        $path = self::path($file);
                        if(file_exists($path)) {
                            $merged_time .=  filemtime($path) . "\n";
                        }
                    }
                }
                File::write(trim($merged_time))->saveTo($the_file_log);
                Image::take($files)->merge()->saveTo($the_file);
            } else {
                foreach($files as $file) {
                    if( ! self::ignored($file)) {
                        $path = self::path($file);
                        if(file_exists($path)) {
                            $merged_time .= filemtime($path) . "\n";
                            $c = file_get_contents($path);
                            if(strpos(basename($path), '.min.') === false) {
                                if(strpos(basename($the_file), '.min.css') !== false) {
                                    $merged_content .= Converter::detractShell($c) . "\n";
                                } else if(strpos(basename($the_file), '.min.js') !== false) {
                                    $merged_content .= Converter::detractSword($c) . "\n";
                                } else {
                                    $merged_content .= $c . "\n\n";
                                }
                            } else {
                                $merged_content .= $c . "\n\n";
                            }
                        }
                    }
                }
                File::write(trim($merged_time))->saveTo($the_file_log);
                File::write(trim($merged_content))->saveTo($the_file);
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
        return call_user_func_array('self::' . $call, array($the_file, $addon));
    }

    // Check for loaded asset(s)
    public static function loaded($path = null) {
        if(is_null($path)) return self::$loaded;
        return isset(self::$loaded[$path]);
    }

    // Do not let the `Asset` loads these files ...
    public static function ignore($path) {
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