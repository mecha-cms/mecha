<?php

class File extends Genome {

    protected $path = "";
    protected $content = "";
    protected $c = [];

    public static $config = [
        'sizes' => [0, 2097152], // Range of allowed file size(s)
        'extensions' => [] // List of allowed file extension(s)
    ];

    public function __construct($path, $config = []) {
        $this->path = file_exists($path) ? $path : false;
        $this->content = "";
        $this->c = !empty($config) ? $config : self::$config;
        parent::__construct();
    }

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
                'hidden' => $n === "" || strpos($n, '.') === 0 || strpos($n, '_') === 0,
                'file' => is_file($path),
                'files' => is_dir($path),
                'folder' => is_dir($path) // alias for `is.files`
            ],
            '_update' => $update,
            '_size' => file_exists($path) ? filesize($path) : null
        ];
        return isset($key) ? Anemon::get($output, $key, $fail) : $output;
    }

    // List all file(s) from a folder
    public static function explore($folder = ROOT, $deep = false, $flat = false, $fail = []) {
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
    public static function open(...$lot) {
        return new self(...$lot);
    }

    // Append `$data` to the file content
    public function append($data) {
        if ($this->path === false) {
            return $this;
        }
        if (is_array($this->content)) {
            $data = (array) $data;
            $this->content = $this->content + $data;
            return $this;
        }
        $this->content = file_get_contents($this->path) . $data;
        return $this;
    }

    // Prepend `$data` to the file content
    public function prepend($data) {
        if ($this->path === false) {
            return $this;
        }
        if (is_array($this->content)) {
            $data = (array) $data;
            $this->content = $data + $this->content;
            return $this;
        }
        $this->content = $data . file_get_contents($this->path);
        return $this;
    }

    // Print the file content
    public function read($fail = null) {
        if ($this->path !== false) {
            $content = file_get_contents($this->path);
            return $content !== false ? $content : $fail;
        }
        return $fail;
    }

    // Print the file content line by line
    public function get($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if ($this->path !== false && ($hand = fopen($this->path, 'r'))) {
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
        $self = new self(__DIR__);
        $self->content = $data;
        return $self;
    }

    // Import the exported PHP file
    public function import($fail = []) {
        if ($this->path === false) {
            return $fail;
        }
        return include $this->path;
    }

    // Export value to a PHP file
    public static function export($data, $format = '<?php return %{0}%;') {
        $self = new self(__DIR__);
        $self->content = __replace__($format, z($data));
        return $self;
    }

    // Serialize `$data` before save
    public static function serialize($data) {
        $self = new self(__DIR__);
        $self->content = serialize($data);
        return $self;
    }

    // Unserialize the serialized `$data` to output
    public function unserialize($fail = []) {
        if ($this->path !== false) {
            $data = file_get_contents($this->path);
            return __is_serialize__($data) ? unserialize($data) : $fail;
        }
        return $fail;
    }

    // Save the `$data`
    public static function save($consent = null) {
        $this->saveTo($this->path, $consent);
        return $this;
    }

    // Save the `$data` to …
    public function saveTo($path, $consent = null) {
        $this->path = $path;
        $path = To::path($path);
        if (!file_exists($d = Path::D($path))) {
            mkdir($d, 0777, true);
        }
        file_put_contents($path, $this->content);
        if (isset($consent)) {
            chmod($path, $consent);
        }
        return $this;
    }

    // Rename the file/folder
    public function renameTo($name) {
        if ($this->path !== false) {
            $a = Path::B($this->path);
            $b = Path::D($this->path) . DS;
            if ($name !== $a && !file_exists($b . $name)) {
                rename($b . $a, $b . $name);
            }
            $this->path = $b . $name;
        }
        return $this;
    }

    // Move the file/folder to …
    public function moveTo($path = ROOT) {
        if ($this->path !== false) {
            $p = $this->path;
            $path = To::path($path);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            if (is_file($p)) {
                $path .= DS . Path::B($p);
            }
            if ($p !== $path) {
                self::open($path)->delete();
                rename($p, $path);
                $this->path = $path;
            }
        }
        return $this;
    }

    // Copy the file/folder to …
    public function copyTo($path = ROOT, $s = '.%{0}%') {
        // TODO: make it possible to copy folder with its content(s)
        $i = 1;
        if ($this->path !== false) {
            $b = DS . Path::B($this->path);
            $o = [];
            foreach ((array) $path as $v) {
                $v = To::path($v);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= $b;
                } else {
                    if (!file_exists($d = Path::D($v))) {
                        mkdir($d, 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy($this->path, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.([a-z\d]+)$#', __replace__($s, $i) . '.$1', $v);
                    copy($this->path, $v);
                    ++$i;
                }
                $o[] = $v;
            }
            // Return sring if singular and array if plural…
            $this->path = count($o) === 1 ? $o[0] : $o;
        }
        return $this;
    }

    // Delete the file
    public function delete() {
        $path = $this->path;
        if ($path !== false) {
            if (is_dir($path)) {
                $a = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
                $b = new \RecursiveIteratorIterator($a, \RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if ($o->isFile()) {
                        unlink($o->getPathname());
                    } else {
                        rmdir($o->getPathname());
                    }
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    // Get file modification time
    public static function T($input, $fail = null) {
        return file_exists($input) ? filemtime($input) : $fail;
    }

    // Set file permission
    public function consent($consent) {
        if ($this->path !== false) {
            chmod($this->path, $consent);
        }
        return $this;
    }

    // Upload the file
    public static function upload($f, $path = ROOT, $fn = null, $fail = false, $c = []) {
        $path = To::path($path);
        $config = !empty($c) ? $c : self::$config;
        // Sanitize file name
        $f['name'] = To::file($f['name']);
        $x = Path::X($f['name']);
        $e = Language::message_info_file_upload();
        // Something goes wrong
        if ($f['error'] > 0 && isset($e[$f['error']])) {
            Message::error($e[$f['error']]);
        } else {
            // Unknown file type
            if (empty($f['type'])) {
                Message::error('file_type');
            }
            // Bad file extension
            $xx = X . implode(X, $config['extensions']) . X;
            if (strpos($xx, X . $x . X) === false) {
                Message::error('file_x', '<em>' . $x . '</em>');
            }
            // Too small
            if ($f['size'] < $config['sizes'][0]) {
                Message::error('file_size.0', self::size($f['size']));
            }
            // Too large
            if ($f['size'] > $config['sizes'][1]) {
                Message::error('file_size.1', self::size($f['size']));
            }
        }
        if (!Message::$x) {
            // Destination not found
            if (!file_exists($path)) {
                Folder::set($path);
            }
            // Move the uploaded file to the destination folder
            if (!file_exists($path . DS . $f['name'])) {
                // Move…
                $path .= DS . $f['name'];
                move_uploaded_file($f['tmp_name'], $path);
                Message::success('file_upload', '<code>' . $f['name'] . '</code>');
                if (is_callable($fn)) {
                    return call_user_func($fn, self::inspect($path));
                }
                return $path;
            }
            Message::error('file_exist', '<code>' . $f['name'] . '</code>');
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