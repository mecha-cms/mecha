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
 *    File::upload($_FILES['file'], 'path/to');
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

    private function __construct() {}
    private function __clone() {}

    // Check if file already exist
    public static function exist($path) {
        $path = str_replace(array('\\', '/'), DS, $path);
        return file_exists($path) ? $path : false;
    }

    // Open a file, then cache its contents to `$cache`
    public static function open($path) {
        self::$cache = "";
        self::$opened = null;
        $path = str_replace(array('\\', '/'), DS, $path);
        if(self::exist($path)) {
            self::$opened = $path;
            if( ! is_dir($path) && $output = file_get_contents($path)) {
                self::$cache = $output;
            }
        }
        return new static;
    }

    // Append some data to the opened file
    public static function append($data) {
        self::$cache .= $data;
        return new static;
    }

    // Prepend some data to the opened file
    public static function prepend($data) {
        self::$cache = $data . self::$cache;
        return new static;
    }

    // Show the opened file to screen
    public static function read() {
        $cache = self::$cache;
        $opened = self::$opened;
        self::$cache = "";
        self::$opened = null;
        $image_extensions = array('gif', 'ico', 'jpg', 'jpeg', 'png');
        $file = pathinfo($opened);
        if( ! isset($file['extension'])) {
            $file['extension'] = "";
        }
        if(in_array(strtolower($file['extension']), $image_extensions)) {
            return '<img alt="' . basename($opened) . '" src="' . str_replace(array(ROOT, '\\'), array(Config::get('url'), '/'), $opened) . '">';
        }
        return $cache;
    }

    // Write something before saving
    public static function write($data) {
        self::$cache = $data;
        return new static;
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
        $path = str_replace(array('\\', '/'), DS, $path);
        if( ! self::exist(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        $handle = fopen($path, 'w') or die('Cannot open file: ' . $path);
        fwrite($handle, self::$cache);
        fclose($handle);
        if( ! is_null($permission)) {
            chmod($path, $permission);
        }
    }

    // Rename a file
    public static function renameTo($new_name) {
        $root = rtrim(dirname(self::$opened), '\\/') . DS; 
        $old_name = ltrim(basename(self::$opened), '\\/');
        if($new_name != $old_name) {
            rename($root . $old_name, $root . $new_name);
        }
    }

    // Move file or folder to somewhere
    public static function moveTo($destination = ROOT) {
        $destination = rtrim($destination, '\\/');
        if(self::exist(self::$opened)) {
            if(is_dir($destination)) {
                $destination .= DS . basename(self::$opened);
            }
            rename(self::$opened, $destination);
        }
    }

    // Copy a file
    public static function copyTo($destination = ROOT) {
        if(self::exist(self::$opened)) {
            if( ! is_array($destination)) {
                $destination = array($destination);
            }
            foreach($destination as $dest) {
                if(is_dir($dest)) {
                    $dest = rtrim($dest, '\\/') . DS . ltrim(basename(self::$opened), '\\/');
                }
                if( ! self::exist($dest) && ! self::exist(preg_replace('#\.(.*?)$#', '.' . self::$increment . '.$1', $dest))) {
                    self::$increment = 0;
                    copy(self::$opened, $dest);
                } else {
                    self::$increment++;
                    copy(self::$opened, preg_replace('#\.(.*?)$#', '.' . self::$increment . '.$1', $dest));
                }
            }
        }
    }

    // Set file permission
    public static function setPermission($permission) {
        chmod(self::$opened, $permission);
    }

    // Upload a file
    public static function upload($file, $destination = ROOT, $custom_success_message = "") {
        $config = Config::get();
        $speak = Config::speak();
        $settings = array(
            'max' => 2097152, // Maximum allowed file size
            'allow' => array( // List of allowed file extensions
                'css', 'html', 'js', 'md', 'txt',
                'bmp', 'cur', 'gif', 'ico', 'jpg', 'jpeg', 'png',
                'eot', 'ttf', 'woff',
                'gz', 'rar', 'tar', 'zip', 'zipx'
            )
        );
        // Create a safe file name
        $renamed = array();
        $parts = explode('.', $file['name']);
        foreach($parts as $part) {
            $renamed[] = Text::parse($part)->to_slug_moderate;
        }
        $info = pathinfo($file['name']);
        $file['name'] = implode('.', $renamed);
        // No file selected
        if(empty($file['name'])) {
            return Notify::error($speak->notify_error_no_file_selected);
        }
        // Bad file extension
        if( ! in_array(strtolower($info['extension']), $settings['allow'])) {
            return Notify::error(Config::speak('notify_error_file_extension', array($info['extension'])));
        }
        // Too large
        if($file['size'] > $settings['max']) {
            return Notify::error(Config::speak('notify_error_file_size', array(self::size($settings['max'], 'KB'))));
        }
        // Something goes wrong
        if($file['error'] > 0) {
            return Notify::error($speak->error . ': <code>' . $file['error'] . '</code>');
        }
        // Move the uploaded file to the destination folder
        if( ! self::exist($destination . DS . $file['name'])) {
            move_uploaded_file($file['tmp_name'], $destination . DS . $file['name']);
        } else {
            return Notify::error(Config::speak('notify_file_exist', array('<code>' . $file['name'] . '</code>')));
        }
        // Create public asset link to show on file uploaded
        $link = str_replace(array(ROOT, '\\'), array($config->url, '/'), $destination) . '/' . $file['name'];
        $uploaded = array(
            $speak->uploaded => $file['name'],
            $speak->type => $file['type'],
            $speak->size => ($file['size'] / 1024) . ' KB',
            $speak->link => '<a href="' . $link . '" target="_blank">' . $link . '</a>'
        );
        $html = array();
        foreach($uploaded as $key => $value) {
            $html[] = '<strong>' . $key . ':</strong> ' . $value;
        }
        if( ! empty($custom_success_message)) {
            Notify::success(vsprintf($custom_success_message, array($file['name'], $file['type'], ($file['size'] / 1024) . ' KB', $link)));
        } else {
            Notify::success(implode('<br>', $html), "");
        }
    }

    // Get file size then convert it to ...
    public static function size($file, $type = 'Bytes') {
        switch(strtolower($type)) {
            case 'bytes': $size = filesize($file); break; // bytes
            case 'kb': $size = filesize($file) * .0009765625; break; // bytes to KB
            case 'mb': $size = (filesize($file) * .0009765625) * .0009765625; break; // bytes to MB
            case 'gb': $size = ((filesize($file) * .0009765625) * .0009765625) * .0009765625; break; // bytes to GB
        }
        return $size < 0 ? Config::speak('unknown') : trim(round($size, 2) . ' ' . $type);
    }

}