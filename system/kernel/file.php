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

    private static $config = array(
        'size_max' => 2097152, // Maximum allowed file size
        'extension_allow' => array( // List of allowed file extensions
            'css', 'draft', 'hold', 'html', 'js', 'md', 'txt',
            'bmp', 'cur', 'gif', 'ico', 'jpg', 'jpeg', 'png',
            'eot', 'ttf', 'woff',
            'gz', 'rar', 'tar', 'zip', 'zipx'
        ),
        'extension_image' => array(
            'gif', 'ico', 'jpg', 'jpeg', 'png'
        )
    );

    private function __construct() {}
    private function __clone() {}

    // Check if file already exist
    public static function exist($path, $fallback = false) {
        $path = str_replace(array('\\', '/'), DS, $path);
        return file_exists($path) ? $path : $fallback;
    }

    // Open a file
    public static function open($path) {
        self::$cache = "";
        self::$opened = null;
        $path = str_replace(array('\\', '/'), DS, $path);
        if(self::exist($path)) {
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

    // Show the opened file to screen
    public static function read() {
        $file = pathinfo(self::$opened);
        if( ! isset($file['extension'])) {
            $file['extension'] = "";
        }
        if(in_array(strtolower($file['extension']), self::$config['extension_image'])) {
            return '<img alt="' . basename(self::$opened) . '" src="' . str_replace(array(ROOT, '\\'), array(Config::get('url'), '/'), self::$opened) . '"' . ES;
        }
        return file_get_contents(self::$opened);
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
    public static function unserialize() {
        return unserialize(file_get_contents(self::$opened));
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
                    $dest = rtrim($dest, '\\/') . DS . basename(self::$opened);
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
        if( ! in_array(strtolower($info['extension']), self::$config['extension_allow'])) {
            return Notify::error(Config::speak('notify_error_file_extension', array($info['extension'])));
        }
        // Too large
        if($file['size'] > self::$config['size_max']) {
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
            Notify::success(vsprintf($custom_success_message, array($file['name'], $file['type'], $file['size'], $link)));
        } else {
            Notify::success(implode('<br>', $html), "");
        }
    }

    // Get file size then convert it to ...
    public static function size($file, $unit = 'Bytes') {
        $fs = is_numeric($file) ? $file : filesize($file);
        switch(strtolower($unit)) {
            case 'bytes': $size = $fs; break; // Bytes
            case 'kb': $size = $fs * .0009765625; break; // Bytes to KB
            case 'mb': $size = ($fs * .0009765625) * .0009765625; break; // Bytes to MB
            case 'gb': $size = (($fs * .0009765625) * .0009765625) * .0009765625; break; // Bytes to GB
        }
        return $size < 0 ? Config::speak('unknown') : trim(round($size, 2) . ' ' . $unit);
    }

    // Configure ...
    public static function configure($key, $value = "") {
        if(is_array($key)) {
            self::$config = array_merge(self::$config, $key);
        } else {
            self::$config[$key] = $value;
        }
        return new static;
    }

}