<?php

class Page extends Genome {

    public static $page = [];

    protected $lot = [];
    protected $lot_alt = [];

    private $pref = "";

    public function __construct($input = null, $lot = [], $NS = 'page') {
        $this->pref = $NS . '.';
        $path = is_array($input) ? (isset($input['path']) ? $input['path'] : null) : $input;
        $id = md5($this->pref . $path);
        if (isset(self::$page[$id])) {
            $this->lot = self::$page[$id][1];
            $this->lot_alt = self::$page[$id][0];
        } else {
            $t = File::T($path, time());
            $date = date(DATE_WISE, $t);
            $n = $path ? Path::N($path) : null;
            $x = Path::X($path);
            $this->lot_alt = array_replace([
                'time' => $date,
                'update' => $date,
                'slug' => $n,
                'title' => To::title($n), // fake `title` data from the page’s file name
                'type' => u($x), // fake `type` data from the page’s file extension
                'state' => $x,
                'id' => (string) $t,
                'url' => To::url($path)
            ], a(Config::get('page', [])), $lot);
            $this->lot = is_array($input) ? $input : (isset($input) ? ['path' => $input] : []);
            // Set `time` value from the page’s file name
            if (
                $n &&
                is_numeric($n[0]) &&
                (
                    // `2017-04-21.page`
                    substr_count($n, '-') === 2 ||
                    // `2017-04-21-14-25-00.page`
                    substr_count($n, '-') === 5
                ) &&
                is_numeric(str_replace('-', "", $n)) &&
                preg_match('#^\d{4,}-\d{2}-\d{2}(?:-\d{2}-\d{2}-\d{2})?$#', $n)
            ) {
                $t = new Date($n);
                $this->lot['time'] = $t->format();
                $this->lot['title'] = $t->F2;
                $this->lot['id'] = $t->format('U');
            // else, set `time` value by page’s file modification time
            } else {
                $t = new Date(File::open(Path::F($path) . DS . 'time.data')->read($date));
                $this->lot['time'] = $t->format();
                $this->lot['id'] = $t->format('U');
            }
            if (!array_key_exists('date', $this->lot)) {
                $this->lot['date'] = new Date($this->lot['time']);
            }
            self::$page[$id] = [$this->lot_alt, $this->lot];
        }
        parent::__construct();
    }

    public function __call($key, $lot) {
        $fail = array_shift($lot);
        $fail_alt = array_shift($lot);
        $x = $this->__get($key);
        if (is_string($fail) && strpos($fail, '~') === 0) {
            return call_user_func(substr($fail, 1), $x !== null ? $x : $fail_alt);
        } else if ($fail instanceof \Closure) {
            return call_user_func($fail, $x !== null ? $x : $fail_alt);
        }
        return $x !== null ? $x : $fail;
    }

    public function __set($key, $value = null) {
        $id = md5($this->pref . (isset($this->lot['path']) ? $this->lot['path'] : null));
        $this->lot[$key] = self::$page[$id][1][$key] = $value;
    }

    public function __get($key) {
        $lot = $this->lot;
        $lot_alt = $this->lot_alt;
        if (!array_key_exists($key, $lot)) {
            if (isset($lot['path'])) {
                // Prioritize data from a file…
                if ($data = File::open(Path::F($lot['path']) . DS . $key . '.data')->get()) {
                    $lot[$key] = e($data);
                } else if ($page = File::open($lot['path'])->read()) {
                    $lot = array_replace($lot_alt, $lot, e(self::apart($page)));
                }
            }
            if (!array_key_exists($key, $lot)) {
                $lot[$key] = array_key_exists($key, $lot_alt) ? e($lot_alt[$key]) : null;
            }
        }
        // Prioritize data from a file…
        if (isset($lot['path']) && $data = File::open(Path::F($lot['path']) . DS . $key . '.data')->get()) {
            $lot[$key] = e($data);
        }
        $this->lot = $lot;
        self::$page[md5($this->pref . (isset($lot['path']) ? $lot['path'] : null))][1] = $lot;
        // $this->lot_alt = $lot_alt;
        return Hook::NS($this->pref . $key, [$lot[$key], $lot, $key, $this]);
    }

    public function __unset($key) {
        $id = md5($this->pref . (isset($this->lot['path']) ? $this->lot['path'] : null));
        unset($this->lot[$key], self::$page[$id][1][$key]);
    }

    public function __toString() {
        $path = isset($this->lot['path']) ? $this->lot['path'] : null;
        return $path && file_exists($path) ? file_get_contents($path) : "";
    }

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    public static function v($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    public static function x($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    public static function apart($input) {
        $input = n($input);
        $data = [];
        if (strpos($input, self::$v[0]) !== 0) {
            $data['content'] = self::v($input);
        } else {
            $input = str_replace([X . self::$v[0], X], "", X . $input . N . N);
            $input = explode(self::$v[1] . N . N, $input, 2);
            // Do data…
            foreach (explode(self::$v[4], $input[0]) as $v) {
                $v = explode(self::$v[2], $v, 2);
                $data[self::v($v[0])] = e(self::v(isset($v[1]) ? $v[1] : false));
            }
            // Do content…
            $data['content'] = trim(isset($input[1]) ? $input[1] : "", "\n");
        }
        return $data;
    }

    public static function unite($input) {
        $data = [];
        $content = "";
        if (isset($input['content'])) {
            $content = $input['content'];
            unset($input['content']);
        }
        foreach ($input as $k => $v) {
            $v = is_array($v) ? json_encode($v) : self::x(s($v));
            if ($v && strpos($v, "\n") !== false) {
                $v = json_encode($v); // contains line–break
            }
            $data[] = self::x($k) . self::$v[2] . $v;
        }
        return ($data ? self::$v[0] . implode(N, $data) . self::$v[1] : "") . ($content ? N . N . $content : "");
    }

    protected static $data = [];

    public static function open($path, $lot = [], $NS = 'page') {
        self::$data = ['path' => $path];
        return new static($path, $lot, $NS);
    }

    public static function data($input, $fn = null, $NS = 'page') {
        if (!is_array($input)) {
            if (is_callable($fn)) {
                self::$data[$input] = call_user_func($fn, self::$data);
                $input = [];
            } else {
                $input = ['content' => $input];
            }
        }
        self::$data = $data = array_replace(self::$data, $input);
        foreach ($data as $k => $v) {
            if ($v === false) unset(self::$data[$k], $data[$k]);
        }
        unset($data['path']);
        return new static(null, $data, $NS);
    }

    public function get($key, $fail = null, $NS = 'page') {
        if (is_array($key)) {
            $output = [];
            foreach ($key as $k => $v) {
                $output[$k] = $this->{$k}($v);
            }
            return $output;
        }
        return $this->{$key}($fail);
    }

    public static function saveTo($path, $consent = 0600) {
        unset(self::$data['path']);
        File::write(self::unite(self::$data))->saveTo($path, $consent);
    }

    public static function save($consent = 0600) {
        return self::saveTo(self::$data['path'], $consent);
    }

}