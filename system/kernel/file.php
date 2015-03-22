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
 *    // Show file content to a web page
 *    echo File::open('path/to/file.txt')->read();
 *
 *    // Append some text to file
 *    File::open('path/to/file.txt')->append('test append.')->save();
 *
 *    // Prepend some text to file
 *    File::open('path/to/file.txt')->prepend('test prepend.')->save();
 *
 *    // Update a file
 *    File::open('path/to/file.txt')->write('some text')->save();
 *
 *    // Rename a file
 *    File::open('path/to/file.txt')->renameTo('file-1.txt');
 *
 *    // Delete a file
 *    File::open('path/to/file.txt')->delete();
 *
 *    // Upload a file
 *    File::upload($_FILES['file'], 'path/to/folder');
 *
 *    // etc.
 *
 * ----------------------------------------------------------------------
 *
 */

class File {

    private static $cache = "";
    private static $opened = null;
    private static $increment = 0;

    public static $config = array(
        'file_size_min_allow' => 0, // Minimum allowed file size
        'file_size_max_allow' => 2097152, // Maximum allowed file size
        'file_extension_allow' => array( // List of allowed file extensions

            ## Script
            'cache',
            'css',
            'draft',
            // 'htaccess',
            'hold',
            'htm',
            'html',
            'js',
            'json',
            'jsonp',
            'less',
            'md',
            'markdown',
            // 'php',
            'scss',
            'txt',
            'xml',

            ## Image
            'bmp',
            'cur',
            'gif',
            'ico',
            'jpg',
            'jpeg',
            'png',
            'svg',

            ## Font
            'eot',
            'otf',
            'svg',
            'ttf',
            'woff',
            'woff2',

            ## Media
            'avi',
            'flv',
            'mkv',
            'mov',
            'mp3',
            'mp4',
            'm4a',
            'm4v',
            'swf',
            'wav',
            'wma',

            ## Package
            'gz',
            'iso',
            'rar',
            'tar',
            'zip',
            'zipx'

        )
    );

    // Check if file already exist
    public static function exist($path, $fallback = false) {
        $path = self::path($path);
        return file_exists($path) ? $path : $fallback;
    }

    // Open a file
    public static function open($path) {
        self::$cache = "";
        self::$opened = null;
        $path = self::path($path);
        if(file_exists($path)) {
            self::$opened = $path;
        }
        return new static;
    }

    // Append some data to the opened file
    public static function append($data) {
        self::$cache = file_get_contents(self::$opened) . $data;
        return new static;
    }

    // Prepend some data to the opened file
    public static function prepend($data) {
        self::$cache = $data . file_get_contents(self::$opened);
        return new static;
    }

    // Show the opened file to the screen
    public static function read($fallback = "") {
        return file_exists(self::$opened) ? file_get_contents(self::$opened) : $fallback;
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
        if(file_exists(self::$opened)) {
            $data = file_get_contents(self::$opened);
            return preg_match('#^([adObis]:|N;)#', $data) ? unserialize($data) : $fallback;
        }
        return $fallback;
    }

    // Delete the opened file
    public static function delete() {
        if( ! is_null(self::$opened)) {
            if(is_dir(self::$opened)) {
               foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$opened, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                    if($file->isFile()) {
                        unlink($file->getPathname());
                    } else {
                        rmdir($file->getPathname());
                    }
                }
                rmdir(self::$opened);
            } else {
                unlink(self::$opened);
            }
        }
    }

    // Save the written data
    public static function save($permission = null) {
        self::saveTo(self::$opened, $permission);
        return new static;
    }

    // Save the written data to somewhere
    public static function saveTo($path, $permission = null) {
        $path = self::path($path);
        if( ! file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        $handle = fopen($path, 'w') or die('Cannot open file: ' . $path);
        fwrite($handle, self::$cache);
        fclose($handle);
        if( ! is_null($permission)) {
            chmod($path, $permission);
        }
        self::$opened = $path;
        return new static;
    }

    // Rename a file
    public static function renameTo($new_name) {
        $root = rtrim(dirname(self::$opened), '\\/') . DS; 
        $old_name = ltrim(basename(self::$opened), '\\/');
        if($new_name != $old_name) {
            rename($root . $old_name, $root . $new_name);
        }
        self::$opened = $root . $new_name;
        return new static;
    }

    // Move file or folder to somewhere
    public static function moveTo($destination = ROOT) {
        $destination = rtrim($destination, '\\/');
        if(file_exists(self::$opened)) {
            if(is_dir($destination)) {
                $destination .= DS . basename(self::$opened);
            }
            rename(self::$opened, $destination);
        }
        self::$opened = $destination;
        return new static;
    }

    // Copy a file
    public static function copyTo($destination = ROOT) {
        if(file_exists(self::$opened)) {
            if( ! is_array($destination)) {
                $destination = array($destination);
            }
            foreach($destination as $dest) {
                if(is_dir($dest)) {
                    $dest = rtrim($dest, '\\/') . DS . basename(self::$opened);
                }
                if( ! file_exists($dest) && ! file_exists(preg_replace('#\.(.*?)$#', '.' . self::$increment . '.$1', $dest))) {
                    self::$increment = 0;
                    copy(self::$opened, $dest);
                } else {
                    self::$increment++;
                    copy(self::$opened, preg_replace('#\.(.*?)$#', '.' . self::$increment . '.$1', $dest));
                }
            }
        }
    }

    // Create new directory
    public static function dir($paths, $permission = 0777) {
        if( ! is_array($paths)) {
            $paths = array($paths);
        }
        foreach($paths as $i => $path) {
            if( ! file_exists($path)) {
                mkdir(self::path($path), (is_array($permission) ? $permission[$i] : $permission), true);
            }
        }
    }

    // Set file permission
    public static function setPermission($permission) {
        chmod(self::$opened, $permission);
        return new static;
    }

    // Upload a file
    public static function upload($file, $destination = ROOT, $custom_success_message = "") {
        $config = Config::get();
        $speak = Config::speak();
        $destination = self::path($destination);
        $errors = Mecha::A($speak->notify_file);
        // Create a safe file name
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file['name'] = Text::parse($file['name'], '->safe_file_name');
        // Something goes wrong
        if($file['error'] > 0 && isset($errors[$file['error']])) {
            Notify::error($errors[$file['error']]);
        } else {
            // Unknown file type
            if( ! isset($file['type']) || empty($file['type'])) {
                Notify::error($speak->notify_error_file_type_unknown);
            }
            // Bad file extension
            $extension_allow = array_flip(self::$config['file_extension_allow']);
            if( ! isset($extension_allow[$extension])) {
                Notify::error(Config::speak('notify_error_file_extension', array($extension)));
            }
            // Too small
            if($file['size'] < self::$config['file_size_min_allow']) {
                Notify::error(Config::speak('notify_error_file_size_min', array(self::size(self::$config['file_size_min_allow'], 'KB'))));
            }
            // Too large
            if($file['size'] > self::$config['file_size_max_allow']) {
                Notify::error(Config::speak('notify_error_file_size_max', array(self::size(self::$config['file_size_max_allow'], 'KB'))));
            }
        }
        if( ! Notify::errors()) {
            // Move the uploaded file to the destination folder
            if( ! file_exists($destination . DS . $file['name'])) {
                move_uploaded_file($file['tmp_name'], $destination . DS . $file['name']);
            } else {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $file['name'] . '</code>')));
            }
            // Create public asset link to show on file uploaded
            $link = self::url($destination) . '/' . $file['name'];
            $html = array(
                '<strong>' . $speak->uploaded . ':</strong> ' . $file['name'],
                '<strong>' . $speak->type . ':</strong> ' . $file['type'],
                '<strong>' . $speak->size . ':</strong> ' . ($file['size'] / 1024) . ' KB',
                '<strong>' . $speak->link . ':</strong> <a href="' . $link . '" target="_blank">' . $link . '</a>'
            );
            if( ! empty($custom_success_message)) {
                Notify::success(vsprintf($custom_success_message, array($file['name'], $file['type'], $file['size'], $link)));
            } else {
                Notify::success(implode('<br>', $html), "");
            }
            self::$opened = $destination . DS . $file['name'];
            return new static;
        } else {
            return false;
        }
    }

    // Convert file size
    public static function size($file, $unit = null, $precision = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $base = log($size, 1024);
        $suffix = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        if ( ! $u = array_search((string) $unit, $suffix)) {
            $u = ($size > 0) ? floor($base) : 0;
        }
        $output = round($size / pow(1024, $u), $precision);
        return $output < 0 ? Config::speak('unknown') : trim($output . ' ' . $suffix[$u]);
    }

    // Convert URL to file path
    public static function path($url) {
        $base = Config::get('url');
        $proof = str_replace(array('\\', '/'), array(DS, DS), $base);
        return str_replace(array($base, '/', '\\', $proof), array(ROOT, DS, DS, ROOT), $url);
    }

    // Convert file path to URL
    public static function url($path) {
        $base = Config::get('url');
        $proof = str_replace(array('\\', '/', DS), array('/', '/', '/'), ROOT);
        return str_replace(array(ROOT, '\\', '/', DS, $proof), array($base, '/', '/', '/', $base), $path);
    }

    // Configure ...
    public static function configure($key, $value = null) {
        if(is_array($key)) {
            self::$config = array_replace_recursive(self::$config, $key);
        } else {
            if(is_array($value)) {
                foreach($value as $k => $v) {
                    self::$config[$key][$k] = $v;
                }
            } else {
                self::$config[$key] = $value;
            }
        }
        return new static;
    }

}