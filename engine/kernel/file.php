<?php

class File extends Genome {

    protected static $path = "";
    protected static $content = "";

    public static $config = [
        'sizes' => [0, 2097152], // Range of allowed file size(s)
        'extensions' => [] // List of allowed file extension(s)
    ];

    // Inspect file path
    public static function inspect($input, $key = null, $fail = false) {
        $path = To::path($input);
        $n = Path::N($path);
        $x = Path::X($path);
        $update = self::T($path);
        $update_date = isset($update) ? date(DATE_WISE, $update) : null;
        $output = [
            'path' => $path,
            'name' => $n,
            'url' => To::url($path),
            'extension' => is_file($path) ? $x : null,
            'update' => $update_date,
            'size' => file_exists($path) ? self::size($path) : null,
            'is' => [
                // Hidden file/folder only
                'hidden' => strpos($n, '.') === 0 || strpos($n, '_') === 0,
                'file' => is_file($path),
                'folder' => is_dir($path)
            ],
            '_update' => $update,
            '_size' => file_exists($path) ? filesize($path) : null
        ];
        return isset($key) ? Anemon::get($output, $key, $fail) : $output;
    }

    // List all file(s) from a folder
    public static function explore($folder = ROOT, $deep = false, $flat = false, $fail = false) {
        $folder = To::path($folder);
        $files = array_merge(
            glob($folder . DS . '*', GLOB_NOSORT),
            glob($folder . DS . '.*', GLOB_NOSORT)
        );
        $output = [];
        foreach ($files as $file) {
            $b = Path::B($file);
            if ($b && $b !== '.' && $b !== '..') {
                if (is_dir($file)) {
                    if (!$flat) {
                        $output[$file] = $deep ? self::explore($file, true, false, []) : 0;
                    } else {
                        $output[$file] = 0;
                        $output = $deep ? array_merge($output, self::explore($file, true, true, [])) : $output;
                    }
                } else {
                    $output[$file] = 1;
                }
            }
        }
        return !empty($output) ? $output : $fail;
    }

    // Check if file/folder does exist
    public static function exist($input, $fail = false) {
        if (is_array($input)) {
            foreach ($input as $v) {
                $v = To::path($v);
                if (file_exists($v)) {
                    return $v;
                }
            }
            return $fail;
        }
        $input = To::path($input);
        return file_exists($input) ? $input : $fail;
    }

    // Open a file
    public static function open($input) {
        self::$path = self::exist($input);
        self::$content = "";
        return new static;
    }

    // Append `$data` to the file content
    public static function append($data) {
        if (is_array(self::$content)) {
            self::$content = array_merge(self::$content, $data);
            return new static;
        }
        self::$content = file_get_contents(self::$path) . $data;
        return new static;
    }

    // Prepend `$data` to the file content
    public static function prepend($data) {
        if (is_array(self::$content)) {
            self::$content = array_merge($data, self::$content);
            return new static;
        }
        self::$content = $data . file_get_contents(self::$path);
        return new static;
    }

    // Print the file content
    public static function read($fail = null) {
        return self::$path !== false ? file_get_contents(self::$path) : $fail;
    }

    // Print the file content line by line
    public static function get($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if (self::$path !== false && ($hand = fopen(self::$path, 'r'))) {
            while (($chunk = fgets($hand, $ch)) !== false) {
                $output .= $chunk;
                if (
                    is_int($stop) && $stop === $i ||
                    is_string($stop) && strpos($chunk, $stop) !== false ||
                    is_array($stop) && strpos($chunk, $stop[0]) === $stop[1] ||
                    is_callable($stop) && call_user_func($stop, [$chunk, $i], $output)
                ) break;
                ++$i;
            }
            fclose($hand);
            return rtrim($output);
        }
        return $fail;
    }

    // Write `$data` before save
    public static function write($data) {
        self::$content = $data;
        return new static;
    }

    // Import the exported PHP file
    public static function import($fail = []) {
        if (self::$path === false) return $fail;
        return include self::$path;
    }

    // Export value to a PHP file
    public static function export($data, $format = '<?php return %{0}%;') {
        self::$content = __replace__($format, z($data));
        return new static;
    }

    // Serialize `$data` before save
    public static function serialize($data) {
        self::$content = serialize($data);
        return new static;
    }

    // Unserialize the serialized `$data` to output
    public static function unserialize($fail = []) {
        if (self::$path !== false) {
            $data = file_get_contents(self::$path);
            return __is_serialize__($data) ? unserialize($data) : $fail;
        }
        return $fail;
    }

    // Save the `$data`
    public static function save($consent = null) {
        self::saveTo(self::$path, $consent);
        return new static;
    }

    // Save the `$data` to …
    public static function saveTo($input, $consent = null) {
        $input = To::path($input);
        if (!file_exists(Path::D($input))) {
            mkdir(Path::D($input), 0777, true);
        }
        $r = fopen($input, 'w');
        if (file_exists($input) && $r !== false) {
            fwrite($r, self::$content);
            fclose($r);
            if (isset($consent)) {
                chmod($input, $consent);
            }
            self::$path = $input;
        }
        return new static;
    }

    // Rename the file/folder
    public static function renameTo($name) {
        if (self::$path !== false) {
            $a = Path::B(self::$path);
            $b = Path::D(self::$path) . DS;
            if ($name !== $a) {
                rename($b . $a, $b . $name);
            }
            self::$path = $b . $name;
        }
        return new static;
    }

    // Move the file/folder to …
    public static function moveTo($target = ROOT) {
        if (self::$path !== false) {
            $target = To::path($target);
            if (is_dir($target) && is_file(self::$path)) {
                $target .= DS . Path::B(self::$path);
            }
            if (!file_exists(Path::D($target))) {
                mkdir(Path::D($target), 0777, true);
            }
            rename(self::$path, $target);
            self::$path = $target;
        }
        return new static;
    }

    // Copy the file/folder to …
    public static function copyTo($target = ROOT, $s = '.%{0}%') {
        $i = 1;
        if (self::$path !== false) {
            foreach ((array) $target as $v) {
                $v = To::path($v);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= DS . Path::B(self::$path);
                } else {
                    if (!file_exists(Path::D($v))) {
                        mkdir(Path::D($v), 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy(self::$path, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.[a-z\d]+$#', __replace__($s, $i) . '.$1', $v);
                    copy(self::$path, $v);
                    ++$i;
                }
                self::$path = $v;
            }
        }
        return new static;
    }

    // Delete the file
    public static function delete() {
        if (self::$path !== false) {
            if (is_dir(self::$path)) {
                $a = new \RecursiveDirectoryIterator(self::$path, \FilesystemIterator::SKIP_DOTS);
                $b = new \RecursiveIteratorIterator($a, \RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if ($o->isFile()) {
                        unlink($o->getPathname());
                    } else {
                        rmdir($o->getPathname());
                    }
                }
                rmdir(self::$path);
            } else {
                unlink(self::$path);
            }
        }
    }

    // Get file modification time
    public static function T($input, $fail = null) {
        return file_exists($input) ? filemtime($input) : $fail;
    }

    // Set file permission
    public static function consent($consent) {
        if (self::$path !== false) {
            chmod(self::$path, $consent);
        }
        return new static;
    }

    // Upload the file
    public static function upload($file, $target = ROOT, $fn = null, $fail = false) {
        $target = To::path($target);
        // Create a safe file name
        $file['name'] = To::file($file['name']);
        $x = Path::X($file['name']);
        $e = Language::message_info_file_upload();
        // Something goes wrong
        if ($file['error'] > 0 && isset($e[$file['error']])) {
            Message::error($e[$file['error']]);
        } else {
            // Unknown file type
            if (empty($file['type'])) {
                Message::error('file_type');
            }
            // Bad file extension
            $x_ok = X . implode(X, self::$config['extensions']) . X;
            if (strpos($x_ok, X . $x . X) === false) {
                Message::error('file_x', '<em>' . $x . '</em>');
            }
            // Too small
            if ($file['size'] < self::$config['sizes'][0]) {
                Message::error('file_size.0', self::size($file['size']));
            }
            // Too large
            if ($file['size'] > self::$config['sizes'][1]) {
                Message::error('file_size.1', self::size($file['size']));
            }
        }
        if (!Message::$x) {
            // Destination not found
            if (!file_exists($target)) Folder::set($target);
            // Move the uploaded file to the destination folder
            if (!file_exists($target . DS . $file['name'])) {
                // Create private asset URL to be hooked on file uploaded
                $file['path'] = $target . DS . $file['name'];
                // Move…
                move_uploaded_file($file['tmp_name'], $file['path']);
                // Create public asset URL to be hooked on file uploaded
                $file['url'] = To::url($target) . '/' . $file['name'];
                Message::success('file_upload', '<code>' . $file['name'] . '</code>');
                if (is_callable($fn)) {
                    return call_user_func($fn, $file);
                }
                return $target . DS . $file['name'];
            }
            Message::error('file_exist', '<code>' . $file['name'] . '</code>');
            return $fail;
        }
        return $fail;
    }

    // Convert file size to …
    public static function size($file, $unit = null, $prec = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $size_base = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        if (!$u = array_search((string) $unit, $x)) {
            $u = $size > 0 ? floor($size_base) : 0;
        }
        $output = round($size / pow(1024, $u), $prec);
        return $output < 0 ? Language::unknown() : trim($output . ' ' . $x[$u]);
    }

}