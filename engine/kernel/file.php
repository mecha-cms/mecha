<?php

/**
 * ======================================================================
 *  FILE OPERATION
 * ======================================================================
 *
 * -- CODE: -------------------------------------------------------------
 *
 *    // Create a file
 *    File::write('some text')->saveTo('path/to/file.txt');
 *
 * ----------------------------------------------------------------------
 *
 *    // Show file content to page
 *    echo File::open('path/to/file.txt')->read();
 *
 * ----------------------------------------------------------------------
 *
 *    // Append text to file
 *    File::open('path/to/file.txt')->append('test append.')->save();
 *
 * ----------------------------------------------------------------------
 *
 *    // Prepend text to file
 *    File::open('path/to/file.txt')->prepend('test prepend.')->save();
 *
 * ----------------------------------------------------------------------
 *
 *    // Update a file
 *    File::open('path/to/file.txt')->write('some text')->save();
 *
 * ----------------------------------------------------------------------
 *
 *    // Rename a file
 *    File::open('path/to/file.txt')->renameTo('file-1.txt');
 *
 * ----------------------------------------------------------------------
 *
 *    // Delete a file
 *    File::open('path/to/file.txt')->delete();
 *
 * ----------------------------------------------------------------------
 *
 *    // Upload a file
 *    File::upload($_FILES['file'], 'path/to/folder');
 *
 * ----------------------------------------------------------------------
 *
 */

class File extends __ {

    protected static $cache = "";
    protected static $open = null;
    protected static $i = 0;

    public static $config = array(
        'file_size_min_allow' => 0, // Minimum allowed file size
        'file_size_max_allow' => 2097152, // Maximum allowed file size
        'file_extension_allow' => array() // List of allowed file extension(s)
    );

    // Inspect file path
    public static function inspect($path, $output = null, $fallback = false) {
        $path = self::path($path);
        $n = self::N($path);
        $e = self::E($path);
        $update = self::T($path);
        $update_date = ! is_null($update) ? date('Y-m-d H:i:s', $update) : null;
        $results = array(
            'path' => $path,
            'name' => $n,
            'url' => self::url($path),
            'extension' => is_file($path) ? $e : null,
            'update_raw' => $update,
            'update' => $update_date,
            'size_raw' => file_exists($path) ? filesize($path) : null,
            'size' => file_exists($path) ? self::size($path) : null,
            'is' => array(
                // hidden file/folder only
                'hidden' => strpos($n, '__') === 0 || strpos($n, '.') === 0,
                'file' => is_file($path),
                'folder' => is_dir($path)
            )
        );
        return ! is_null($output) ? Mecha::GVR($results, $output, $fallback) : $results;
    }

    // Check if file/folder is hidden
    public static function hidden($path, $recursive = true) {
        if( ! $recursive) {
            $path = File::B($path);
            // hidden file/folder only
            return strpos($path, '.') === 0 || strpos($path, '__') === 0;
        }
        $path = DS . self::path($path);
        // hidden file/folder or visible file/folder in hidden folder(s)
        return strpos($path, DS . '.') !== false || strpos($path, DS . '__') !== false;
    }

    // List all file(s) from a folder
    public static function explore($folder = ROOT, $recursive = false, $flat = false, $fallback = false) {
        $results = array();
        $folder = rtrim(self::path($folder), DS);
        $files = array_merge(
            glob($folder . DS . '*', GLOB_NOSORT),
            glob($folder . DS . '.*', GLOB_NOSORT)
        );
        foreach($files as $file) {
            if(self::B($file) !== '.' && self::B($file) !== '..') {
                if(is_dir($file)) {
                    if( ! $flat) {
                        $results[$file] = $recursive ? self::explore($file, true, false, array()) : 0;
                    } else {
                        $results[$file] = 0;
                        $results = $recursive ? array_merge($results, self::explore($file, true, true, array())) : $results;
                    }
                } else {
                    $results[$file] = 1;
                }
            }
        }
        return ! empty($results) ? $results : $fallback;
    }

    // Check if file already exist
    public static function exist($path, $fallback = false) {
        $path = self::path($path);
        return file_exists($path) ? $path : $fallback;
    }

    // Open a file
    public static function open($path) {
        $path = self::path($path);
        self::$cache = "";
        self::$open = $path;
        self::$i = 0;
        return new static;
    }

    // Append some data to the opened file
    public static function append($data) {
        self::$cache = file_get_contents(self::$open) . $data;
        return new static;
    }

    // Prepend some data to the opened file
    public static function prepend($data) {
        self::$cache = $data . file_get_contents(self::$open);
        return new static;
    }

    // Show the opened file to the screen
    public static function read($fallback = "") {
        return file_exists(self::$open) ? file_get_contents(self::$open) : $fallback;
    }

    // Read the opened file line by line
    public static function get($stop_at = null, $fallback = false, $chars = 1024) {
        $i = 0;
        $results = "";
        if($handle = fopen(self::$open, 'r')) {
            while(($buffer = fgets($handle, $chars)) !== false) {
                if(is_int($stop_at) && $stop_at === $i) break;
                $results .= $buffer;
                $i++;
                if(is_string($stop_at) && strpos($buffer, $stop_at) !== false) break;
            }
            fclose($handle);
            return rtrim($results);
        }
        return $fallback;
    }

    // Write something before saving
    public static function write($data) {
        self::$cache = $data;
        return new static;
    }

    // Serialize the data before saving
    public static function serialize($data) {
        self::$cache = serialize($data);
        return new static;
    }

    // Unserialize the serialized data to output
    public static function unserialize($fallback = array()) {
        if(file_exists(self::$open)) {
            $data = file_get_contents(self::$open);
            return Guardian::check($data, '->serialize') ? unserialize($data) : $fallback;
        }
        return $fallback;
    }

    // Delete the opened file
    public static function delete() {
        if(file_exists(self::$open)) {
            if(is_dir(self::$open)) {
               foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$open, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                    if($file->isFile()) {
                        unlink($file->getPathname());
                    } else {
                        rmdir($file->getPathname());
                    }
                }
                rmdir(self::$open);
            } else {
                unlink(self::$open);
            }
        }
    }

    // Save the written data
    public static function save($permission = null) {
        self::saveTo(self::$open, $permission);
        return new static;
    }

    // Save the written data to ...
    public static function saveTo($path, $permission = null) {
        $path = self::path($path);
        if( ! file_exists(self::D($path))) {
            mkdir(self::D($path), 0777, true);
        }
        $handle = fopen($path, 'w') or die('Cannot open file: ' . $path);
        fwrite($handle, self::$cache);
        fclose($handle);
        if( ! is_null($permission)) {
            chmod($path, $permission);
        }
        self::$open = $path;
        return new static;
    }

    // Rename a file
    public static function renameTo($new_name) {
        if(file_exists(self::$open)) {
            $root = rtrim(self::D(self::$open), DS) . DS; 
            $old_name = ltrim(self::B(self::$open), DS);
            if($new_name !== $old_name) {
                rename($root . $old_name, $root . $new_name);
            }
            self::$open = $root . $new_name;
        }
        return new static;
    }

    // Move file or folder to ...
    public static function moveTo($destination = ROOT) {
        if(file_exists(self::$open)) {
            $destination = rtrim(self::path($destination), DS);
            if(is_dir($destination) && is_file(self::$open)) {
                $destination .= DS . self::B(self::$open);
            }
            if( ! file_exists(self::D($destination))) {
                mkdir(self::D($destination), 0777, true);
            }
            rename(self::$open, $destination);
            self::$open = $destination;
        }
        return new static;
    }

    // Copy a file
    public static function copyTo($destination = ROOT, $s = '.') {
        if(file_exists(self::$open)) {
            $destination = (array) $destination;
            foreach($destination as $dest) {
                $dest = self::path($dest);
                if(is_dir($dest)) {
                    if( ! file_exists($dest)) {
                        mkdir($dest, 0777, true);
                    }
                    $dest = rtrim(self::path($dest), DS) . DS . self::B(self::$open);
                } else {
                    if( ! file_exists(self::D($dest))) {
                        mkdir(self::D($dest), 0777, true);
                    }
                }
                if( ! file_exists($dest) && ! file_exists(preg_replace('#\.(.*?)$#', $s . self::$i . '.$1', $dest))) {
                    self::$i = 0;
                    copy(self::$open, $dest);
                } else {
                    self::$i++;
                    copy(self::$open, preg_replace('#\.(.*?)$#', $s . self::$i . '.$1', $dest));
                }
            }
            self::$i = 0;
        }
    }

    // Create new directory
    public static function pocket($paths, $permission = 0777) {
        $paths = (array) $paths;
        foreach($paths as $i => $path) {
            if( ! file_exists($path)) {
                mkdir(self::path($path), (is_array($permission) ? $permission[$i] : $permission), true);
            }
        }
    }

    // Get file base name
    public static function B($path, $step = 1, $s = DS) {
        if(($s !== DS && $s !== '/') || $step > 1) {
            $p = explode($s, $path);
            return implode($s, array_slice($p, $step * -1));
        }
        return basename($path);
    }

    // Get file directory name
    public static function D($path, $step = 1, $s = DS) {
        if(($s !== DS && $s !== '/') || $step > 1) {
            $p = explode($s, $path);
            for($i = 0; $i < $step; ++$i) {
                array_pop($p);
            }
            return implode($s, $p);
        }
        return dirname($path) === '.' ? "" : dirname($path);
    }

    // Get file name without extension
    public static function N($path, $extension = false) {
        return pathinfo($path, $extension ? PATHINFO_BASENAME : PATHINFO_FILENAME);
    }

    // Get file name extension
    public static function E($path, $fallback = "") {
        if(strpos($path, '.') === false) return $fallback;
        $e = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $e ? $e : $fallback;
    }

    // Get file modification time
    public static function T($path, $fallback = null) {
        return file_exists($path) ? filemtime($path) : $fallback;
    }

    // Set file permission
    public static function setPermission($permission) {
        chmod(self::$open, $permission);
        return new static;
    }

    // Upload a file
    public static function upload($file, $destination = ROOT, $callback = null) {
        $config = Config::get();
        $speak = Config::speak();
        $destination = rtrim(self::path($destination), DS);
        $errors = $speak->notify_file;
        // Create a safe file name
        $file['name'] = Text::parse($file['name'], '->safe_file_name');
        $e = self::E($file['name']);
        // Something goes wrong
        if($file['error'] > 0 && isset($errors[$file['error']])) {
            Notify::error($errors[$file['error']]);
        } else {
            // Unknown file type
            if( ! isset($file['type']) || empty($file['type'])) {
                Notify::error($speak->notify_error_file_type_unknown);
            }
            // Bad file extension
            $e_allow = ',' . implode(',', self::$config['file_extension_allow']) . ',';
            if(strpos($e_allow, ',' . $e . ',') === false) {
                Notify::error(Config::speak('notify_error_file_extension', $e));
            }
            // Too small
            if($file['size'] < self::$config['file_size_min_allow']) {
                Notify::error(Config::speak('notify_error_file_size_min', self::size(self::$config['file_size_min_allow'], 'KB')));
            }
            // Too large
            if($file['size'] > self::$config['file_size_max_allow']) {
                Notify::error(Config::speak('notify_error_file_size_max', self::size(self::$config['file_size_max_allow'], 'KB')));
            }
        }
        if( ! Notify::errors()) {
            // Destination not found
            if( ! file_exists($destination)) self::pocket($destination);
            // Move the uploaded file to the destination folder
            if( ! file_exists($destination . DS . $file['name'])) {
                move_uploaded_file($file['tmp_name'], $destination . DS . $file['name']);
                // Create public asset URL to show on file uploaded
                $url = self::url($destination) . '/' . $file['name'];
                Notify::success(Config::speak('notify_file_uploaded', '<code>' . $file['name'] . '</code>'));
                if(is_callable($callback)) {
                    return call_user_func($callback, $file['name'], $file['type'], $file['size'], $url);
                }
                return $destination . DS . $file['name'];
            }
            Notify::error(Config::speak('notify_file_exist', '<code>' . $file['name'] . '</code>'));
            return false;
        }
        return false;
    }

    // Convert file size to ...
    public static function size($file, $unit = null, $precision = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $base = log($size, 1024);
        $suffix = array('B', 'KB', 'MB', 'GB', 'TB');
        if ( ! $u = array_search((string) $unit, $suffix)) {
            $u = ($size > 0) ? floor($base) : 0;
        }
        $output = round($size / pow(1024, $u), $precision);
        return $output < 0 ? Config::speak('unknown') : trim($output . ' ' . $suffix[$u]);
    }

    // Convert URL to file path
    public static function path($url) {
        $base = Config::get('url');
        $p = str_replace(array('\\', '/'), DS, $base);
        return str_replace(array($base, '\\', '/', $p), array(ROOT, DS, DS, ROOT), $url);
    }

    // Convert file path to URL
    public static function url($path) {
        $base = Config::get('url');
        $p = str_replace(array('\\', DS), '/', ROOT);
        return str_replace(array(ROOT, '\\', DS, $p), array($base, '/', '/', $base), $path);
    }

}