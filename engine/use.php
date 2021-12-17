<?php !session_id() && session_start();

if (defined('TEST')) {
    if (!is_dir($folder = __DIR__ . D . 'log')) {
        mkdir($folder, 0775, true);
    }
    ini_set('error_log', $folder . D . 'error');
    if (false === TEST) {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        ini_set('max_execution_time', 300); // 5 minute(s)
    } else {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', true);
        ini_set('display_startup_errors', true);
        ini_set('html_errors', 1);
    }
}

function abort(string $alert, $exit = true) {
    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace = explode("\n", n(ob_get_clean()), 2);
    array_shift($trace);
    $trace = trim(strtr(implode("\n", $trace), [PATH => '.']), "\n");
    echo '<details style="background:#f00;color:#fff;font:normal normal 100%/1.5 sans-serif;margin:0;padding:0;selection:none;"><summary style="cursor:pointer;display:block;margin:0;padding:.5em 1rem;">' . $alert . '</summary><pre style="background:#000;font:normal normal 100%/1.25 monospace;margin:0;overflow:auto;padding:0;white-space:pre;"><code style="display:block;font:inherit;margin:0;padding:.5em 1rem;">' . $trace . '</code></pre></details>';
    $exit && exit;
}

function any(iterable $value, $fn = null) {
    if (!is_callable($fn) && null !== $fn) {
        $fn = static function($v) use($fn) {
            return $v === $fn;
        };
    }
    foreach ($value as $k => $v) {
        if (call_user_func($fn, $v, $k)) {
            return true;
        }
    }
    return false;
}

// Convert class name to file name
function c2f(string $value = null, $accent = false) {
    return implode(D, map(preg_split('/[\\\\\/]/', $value), static function($v) use($accent) {
        return ltrim(strtr(h($v, '-', $accent, '_'), [
            '__-' => '.',
            '__' => '.'
        ]), '-');
    }));
}

function check(string $token, $id = 0) {
    $prev = $_SESSION['token'][$id] ?? [0, ""];
    return $prev[1] && $token && $prev[1] === $token ? $token : false;
}

function content(string $path) {
    return is_file($path) && is_readable($path) ? file_get_contents($path) : null;
}

function concat(array $value, ...$lot) {
    // `concat([…], […], […], false)`
    if (count($lot) > 1 && false === end($lot)) {
        array_pop($lot);
        return array_merge($value, ...$lot);
    }
    // `concat([…], […], […])`
    return array_merge_recursive($value, ...$lot);
}

function cookie(...$lot) {
    if (0 === count($lot)) {
        $cookie = [];
        foreach ($_COOKIE as $k => $v) {
            if (0 === strpos($k, '*')) {
                $cookie[$k] = json_decode(base64_decode($v));
            } else {
                $cookie[$k] = $v;
            }
        }
        return $cookie;
    }
    $key = array_shift($lot);
    if (1 === count($lot)) {
        if (isset($_COOKIE[$k = '*' . crc32($key)])) {
            return json_decode(base64_decode($_COOKIE[$k]), true);
        }
        return $_COOKIE[$key] ?? null;
    }
    $value = array_shift($lot);
    $expires = array_shift($lot) ?? '1 day';
    if (!is_array($expires)) {
        $expires = ['expires' => $expires];
    }
    // Use indexed parameter style to support PHP < 7.3
    $state = array_values(array_replace([
        'expires' => '1 day',
        'path' => '/',
        'domain' => "",
        'secure' => false,
        'httponly' => false,
        'samesite' => 'None'
    ], $expires));
    if (is_string($state[0])) {
        $state[0] = (int) (strtotime($state[0], $time = time()) - $time);
    }
    $state[0] += time();
    // <https://stackoverflow.com/a/1969339/1163000>
    setcookie('*' . crc32($key), base64_encode(json_encode($value)), ...$state);
}

function delete(string $path, $purge = true) {
    if (is_file($path)) {
        // Success?
        $out = unlink($path) ? path($path) : null;
        // Remove parent folder if empty
        if ($purge) {
            $folder = dirname($path);
            while (0 === q(g($folder))) {
                if (PATH === stream_resolve_include_path($folder)) {
                    break; // Stop once we are in the root!
                }
                if (!rmdir($folder)) {
                    break; // Error?
                }
                $folder = dirname($folder);
            }
        }
        return $out;
    }
    $out = [];
    $it = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach (new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST) as $k) {
        $v = path($k->getPathname());
        if ($k->isDir()) {
            $out[$v] = rmdir($v) ? 0 : null;
        } else {
            $out[$v] = unlink($v) ? 1 : null;
        }
    }
    $out[path($path)] = rmdir($path) ? 0 : null;
    return $out;
}

// Remove empty array, empty string and `null` value from array
function drop(iterable $value, callable $fn = null) {
    $n = null === $fn; // Use default filter?
    foreach ($value as $k => $v) {
        if (is_array($v) && !empty($v)) {
            if ($v = drop($v, $fn)) {
                $value[$k] = $v;
            } else {
                unset($value[$k]); // Drop!
            }
        } else if ($n) {
            if ("" === $v || null === $v || [] === $v) {
                unset($value[$k]); // Drop!
            }
        } else {
            if (call_user_func($fn, $v, $k)) {
                unset($value[$k]); // Drop!
            }
        }
    }
    return [] !== $value ? $value : null;
}

function eq($a, $b) {
    return $b === q($a);
}

function exist($path, $type = null) {
    if (is_array($path)) {
        foreach ($path as $v) {
            if ($v && $v = stream_resolve_include_path($v)) {
                if (0 === $type) {
                    return is_dir($v) ? $v : false;
                }
                if (1 === $type) {
                    return is_file($v) ? $v : false;
                }
                return $v;
            }
        }
        return false;
    }
    $path = stream_resolve_include_path($path);
    if (0 === $type) {
        return is_dir($path) ? $path : false;
    }
    if (1 === $type) {
        return is_file($path) ? $path : false;
    }
    return $path;
}

function extend(array $value, ...$lot) {
    // `extend([…], […], […], false)`
    if (count($lot) > 1 && false === end($lot)) {
        array_pop($lot);
        return array_replace($value, ...$lot);
    }
    // `extend([…], […], […])`
    return array_replace_recursive($value, ...$lot);
}

// Convert file name to class name
function f2c(string $value = null, $accent = false) {
    return implode("\\", map(preg_split('/[\\\\\/]/', $value), static function($v) use($accent) {
        return p(strtr($v, ['.' => '__']), $accent, '_');
    }));
}

// Convert file name to property name
function f2p(string $value = null, $accent = false) {
    return implode("\\", map(preg_split('/[\\\\\/]/', $value), static function($v) use($accent) {
        return c(strtr($v, ['.' => '__']), $accent, '_');
    }));
}

function fetch(string $url, $lot = null, $type = 'GET') {
    $headers = ['x-requested-with' => 'x-requested-with: CURL'];
    $chops = explode('?', $url, 2);
    $type = strtoupper($type);
    // `fetch('/', ['x-foo' => 'bar'])`
    if (is_array($lot)) {
        foreach ($lot as $k => $v) {
            $headers[$k] = $k . ': ' . $v;
        }
    } else if (is_string($lot)) {
        $headers['user-agent'] = 'user-agent: ' . $lot;
    }
    if (!isset($headers['user-agent'])) {
        // <https://tools.ietf.org/html/rfc7231#section-5.5.3>
        $port = (int) $_SERVER['SERVER_PORT'];
        $v = 'Mecha/' . VERSION . ' (+http' . (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === $port ? 's' : "") . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "") . ')';
        $headers['user-agent'] = 'user-agent: ' . $v;
    }
    $target = 'GET' === $type ? $url : $chops[0];
    if (extension_loaded('curl')) {
        $curl = curl_init($target);
        curl_setopt_array($curl, [
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_HTTPHEADER => array_values($headers),
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15
        ]);
        if ('POST' === $type) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $chops[1] ?? "");
        }
        $out = curl_exec($curl);
        if (defined('TEST') && 'curl' === TEST && false === $out) {
            throw new UnexpectedValueException(curl_error($curl));
        }
        curl_close($curl);
    } else {
        $context = ['http' => ['method' => $type]];
        if ('POST' === $type) {
            $headers['content-type'] = 'content-type: application/x-www-form-urlencoded';
            $context['http']['content'] = $chops[1] ?? "";
        }
        $context['http']['header'] = implode("\r\n", array_values($headers));
        $out = file_get_contents($target, false, stream_context_create($context));
    }
    return false !== $out ? $out : null;
}

function find(iterable $value, callable $fn) {
    foreach ($value as $k => $v) {
        if (call_user_func($fn, $v, $k)) {
            return $v;
        }
    }
    return null;
}

function fire(callable $fn, array $lot = [], $that = null, string $scope = null) {
    $fn = $fn instanceof Closure ? $fn : Closure::fromCallable($fn);
    // `fire($fn, [], Foo::class)`
    if (is_string($that)) {
        $scope = $that;
        $that = null;
    }
    return call_user_func($fn->bindTo($that, $scope ?? 'static'), ...$lot);
}

function ge($a, $b) {
    return q($a) >= $b;
}

function get(array $value, string $key, string $join = '.') {
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    foreach ($keys as $k) {
        $k = strtr($k, [P => $join]);
        if (!is_array($value) || !array_key_exists($k, $value)) {
            return null;
        }
        $value =& $value[$k];
    }
    return $value;
}

function gt($a, $b) {
    return q($a) > $b;
}

function has(iterable $value, $has = "", string $x = P) {
    if (!is_string($has)) {
        foreach ($value as $v) {
            if ($has === $v) {
                return true;
            }
        }
        return false;
    }
    return false !== strpos($x . implode($x, $value) . $x, $x . $has . $x);
}

function is(iterable $value, $fn = null) {
    if (!is_callable($fn) && null !== $fn) {
        $fn = static function($v) use($fn) {
            return $v === $fn;
        };
    }
    return $fn ? array_filter($value, $fn, ARRAY_FILTER_USE_BOTH) : array_filter($value);
}

function le($a, $b) {
    return q($a) <= $b;
}

function let(array &$value, string $key, string $join = '.') {
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    while (count($keys) > 1) {
        $k = strtr(array_shift($keys), [P => $join]);
        if (is_array($value) && array_key_exists($k, $value)) {
            $value =& $value[$k];
        }
    }
    if (is_array($value) && array_key_exists($k = array_shift($keys), $value)) {
        unset($value[$k]);
    }
    return $value;
}

function lt($a, $b) {
    return q($a) < $b;
}

function map(iterable $value, callable $fn) {
    $out = [];
    foreach ($value as $k => $v) {
        $out[$k] = call_user_func($fn, $v, $k);
    }
    return $out;
}

function move(string $path, string $to, string $as = null) {
    $out = [];
    if (!is_dir($path) && !is_file($path)) {
        return [false, null];
    }
    if (is_file($out[0] = path($path))) {
        if (is_file($file = $to . DS . ($as ?? basename($path)))) {
            // Return `false` if file exist
            $out[1] = false;
        } else {
            if (!is_dir($folder = dirname($file))) {
                mkdir($folder, 0775, true);
            }
            // Return `$file` on success, `null` on error
            $out[1] = rename($path, $file) ? path($file) : null;
        }
        return $out;
    }
    // TODO: Move folder with its contents
}

function ne($a, $b) {
    return q($a) !== $b;
}

function not(iterable $value, $fn = null) {
    if (!is_callable($fn) && null !== $fn) {
        $fn = static function($v) use($fn) {
            return $v === $fn;
        };
    }
    return array_filter($value, static function($v, $k) use($fn) {
        return !call_user_func($fn, $v, $k);
    }, ARRAY_FILTER_USE_BOTH);
}

// Convert property name to file name
function p2f(string $value = null, $accent = false) {
    return implode(D, map(preg_split('/[\\\\\/]/', $value), static function($v) use($accent) {
        return strtr(h($v, '-', $accent, '_'), ['__' => '.']);
    }));
}

function path(string $value = null) {
    return stream_resolve_include_path($value) ?: null;
}

// Generate new array contains value from the key
function pluck(iterable $values, string $key, $value = null) {
    $out = [];
    foreach ($values as $v) {
        $out[] = $v[$key] ?? $value;
    }
    return $out;
}

function save(string $path, $value = "", $seal = null) {
    if (is_dir($path)) {
        // Error
        return null;
    }
    if (is_file($path)) {
        // Skip
        return false;
    }
    if (!is_dir($folder = dirname($path))) {
        mkdir($folder, 0775, true);
    }
    if (file_put_contents($path, $value)) {
        $seal && seal($path, $seal);
        // Success
        return path($path);
    }
    // Error
    return null;
}

function seal(string $path, $seal = null) {
    if (!is_dir($path) && !is_file($path)) {
        // Skip
        return false;
    }
    $seal = is_string($seal) ? octdec($seal) : ($seal ?? 0600);
    // Success?
    return chmod($path, $seal) ? $seal : null;
}

// Send email as HTML
function send($from, $to, string $title, string $content, array $lot = []) {
    // This function was intended to be used as a quick way to send HTML email
    // There is no such email validation proccess here
    // We assume that you have set the correct email address(es)
    if (is_array($to)) {
        // ['foo@bar' => 'Foo Bar', 'baz@qux' => 'Baz Qux']
        if (array_keys($to) !== range(0, count($to) - 1)) {
            $s = "";
            foreach ($to as $k => $v) {
                $s .= ', ' . $v . ' <' . $k . '>';
            }
            $to = substr($s, 2);
        // ['foo@bar', 'baz@qux']
        } else {
            $to = implode(', ', $to);
        }
    }
    $lot = array_filter(array_replace([
        'content-type' => 'text/html; charset=ISO-8859-1',
        'from' => $from,
        'mime-version' => '1.0',
        'reply-to' => $to,
        'return-path' => $from,
        'x-mailer' => 'PHP/' . PHP_VERSION
    ], $lot));
    foreach ($lot as $k => &$v) {
        $v = $k . ': ' . $v;
    }
    // Line(s) shouldn’t be larger than 70 character(s)
    $content = wordwrap($content, 70, "\r\n");
    return mail($to, $title, $content, implode("\r\n", $lot));
}

function set(array &$out, string $key, $value = null, string $join = '.') {
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    while (count($keys) > 1) {
        $k = strtr(array_shift($keys), [P => $join]);
        if (!array_key_exists($k, $out)) {
            $out[$k] = [];
        }
        $out =& $out[$k];
    }
    $out[strtr(array_shift($keys), [P => $join])] = $value;
    return $out;
}

function shake(array $value, $preserve_key = true) {
    if (is_callable($preserve_key)) {
        // `$preserve_key` as `$fn`
        $value = call_user_func($preserve_key, $value);
    } else {
        // <http://php.net/manual/en/function.shuffle.php#94697>
        if ($preserve_key) {
            $keys = array_keys($value);
            $values = [];
            shuffle($keys);
            foreach ($keys as $key) {
                $values[$key] = $value[$key];
            }
            $value = $values;
            unset($keys, $values);
        } else {
            shuffle($value);
        }
    }
    return $value;
}

// Tidy file size
function size(float $size, string $unit = null, int $round = 2) {
    $i = log($size, 1024);
    $x = ['B', 'KB', 'MB', 'GB', 'TB'];
    $u = $unit ? array_search($unit, $x) : ($size > 0 ? floor($i) : 0);
    $out = round($size / pow(1024, $u), $round);
    return $out < 0 ? null : trim($out . ' ' . $x[$u]);
}

function status(...$lot) {
    if (0 === count($lot)) {
        $out = [http_response_code(), [], []];
        if (function_exists('apache_request_headers')) {
            $out[1] = e(array_change_key_case((array) apache_request_headers(), CASE_LOWER));
        }
        foreach ($_SERVER as $k => $v) {
            if (0 === strpos($k, 'HTTP_')) {
                $out[1][strtolower(strtr(substr($k, 5), '_', '-'))] = e($v);
            }
        }
        if (function_exists('apache_response_headers')) {
            $out[2] = e(array_change_key_case((array) apache_response_headers(), CASE_LOWER));
        }
        foreach (headers_list() as $v) {
            $v = explode(':', $v, 2);
            if (isset($v[1])) {
                $out[2][strtolower($v[0])] = e(trim($v[1]));
            }
        }
        return $out;
    }
    $code = array_shift($lot);
    $values = (array) array_shift($lot);
    // `status(['content-type' => 'text/plain'])`
    if (is_array($code)) {
        $values = $code;
        $code = null;
    }
    // `status(200, ['content-type' => 'text/plain'])`
    if (is_int($code)) {
        http_response_code($code);
    }
    foreach ($values as $k => $v) {
        if (false === $v || null === $v) {
            header_remove($k);
            continue;
        }
        header($k . ': ' . s($v), true);
    }
}

// Break dot-notation sequence into step(s)
function step(string $value, string $join = '.', int $direction = 1) {
    $value = strtr($value, ["\\" . $join => P]);
    if (false !== strpos($value, $join)) {
        $values = explode($join, $value);
        $v = -1 === $direction ? array_pop($values) : array_shift($values);
        $k = strtr(implode($join, $values), [P => $join]);
        $c = [$k => strtr($v, [P => $join])];
        if (-1 === $direction) {
            while (null !== ($value = array_pop($values))) {
                $v = strtr($value . $join . $v, [P => $join]);
                $k = strtr(implode($join, $values), [P => $join]);
                $c += [$k => $v];
            }
        } else {
            while (null !== ($value = array_shift($values))) {
                $v .= strtr($join . $value, [P => $join]);
                $k = strtr(implode($join, $values), [P => $join]);
                $c = [$k => $v] + $c;
            }
        }
        return $c;
    }
    return (array) $value;
}

function store(string $path, array $blob, string $as = null) {
    if (!empty($blob['error'])) {
        // Error
        return $blob['error'];
    }
    if (is_file($file = $path . D . ($as ?? $blob['name']))) {
        // Skip
        return false;
    }
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
    // Success?
    return move_uploaded_file($blob['tmp_name'], $file) ? path($file) : null;
}

// Get file content line by line
function stream(string $path, int $max = 1024) {
    if (is_file($path) && $h = fopen($path, 'r')) {
        $max += 1;
        while (false !== ($v = fgets($h, $max))) {
            yield strtr($v, [
                "\r\n" => "\n",
                "\r" => "\n"
            ]);
        }
        fclose($h);
    }
    yield from [];
}

// Dump PHP code
function test(...$lot) {
    echo '<p style="border:2px solid #000;border-bottom-width:1px;">';
    foreach ($lot as $v) {
        $v = var_export($v, true);
        $v = strtr(highlight_string("<?php\n\n" . $v . "\n\n?>", true), [
            "\n" => "",
            "\r" => ""
        ]);
        $v = strtr($v, [
            '<code>' => '<code style="display:block;word-wrap:break-word;white-space:pre-wrap;background:#fff;color:#000;border:0;border-bottom:1px solid #000;padding:.5em;border-radius:0;box-shadow:none;text-shadow:none;">'
        ]);
        echo $v;
    }
    echo '</p>';
}

function token($id = 0, $for = '1 minute') {
    $prev = $_SESSION['token'][$id] ?? [0, ""];
    if ($prev[0] > time()) {
        return $prev[1];
    }
    $t = is_string($for) ? strtotime($for) : time() + $for;
    $_SESSION['token'][$id] = $v = [$t, sha1(uniqid(mt_rand(), true) . $id)];
    return $v[1];
}

function type(string $type = null) {
    if (!isset($type)) {
        $type = status()[1]['content-type'] ?? null;
        if (is_string($type)) {
            return explode(';', $type, 2)[0];
        }
        return null;
    }
    status(['content-type' => $type]);
}

function zone(string $zone = null) {
    if (!isset($zone)) {
        return date_default_timezone_get();
    }
    return date_default_timezone_set($zone);
}


// a: Convert object to array
// b: Keep value between `a` and `b`
// c: Convert text to camel case
// d: Declare class(es) with callback
// e: Evaluate string to their appropriate data type
// f: Filter/sanitize string
// g: Advance PHP `glob` function that returns generator
// h: Convert text to snake case with `-` (hyphen) as the default separator
// i: Internationalization
// j: Compare array(s) and return all item(s) that exist just once in any
// k: Search file in a folder by query
// l: Convert text to lower case
// m: Normalize range margin
// n: Normalize white-space in string
// o: Convert array to object
// p: Convert text to pascal case
// q: Quantity (length of a string, number, array and object)
// r: Replace string
// s: Convert data type to their string format
// t: Trim string from prefix and suffix once
// u: Convert text to upper case
// v: Un-escape
// w: Convert any data to plain word(s)
// x: Escape
// y: Convert iterator to plain array
// z: Export array/object into a compact PHP file

function a($value, $safe = true) {
    if (is_object($value)) {
        if ($safe) {
            return ($v = get_class($value)) && 'stdClass' !== $v ? $value : (array) $value;
        } else {
            $value = (array) $value;
        }
        foreach ($value as &$v) {
            $v = a($v, $safe);
        }
        unset($v);
    }
    return $value;
}

function b($value, array $range = [0]) {
    if (isset($range[0]) && $value < $range[0]) {
        return $range[0];
    }
    if (isset($range[1]) && $value > $range[1]) {
        return $range[1];
    }
    return $value;
}

function c(string $value = null, $accent = false, string $preserve = "") {
    $preserve = x($preserve);
    return strtr(preg_replace_callback('/([ ' . $preserve . '])([\p{L}\p{N}' . $preserve . '])/u', static function($m) {
        return $m[1] . u($m[2]);
    }, f($value, $accent, $preserve)), [' ' => ""]);
}

function d(string $folder, callable $fn = null) {
    spl_autoload_register(static function($object) use($folder, $fn) {
        $name = c2f($object);
        if (is_file($file = $folder . D . $name . '.php')) {
            extract($GLOBALS, EXTR_SKIP);
            require $file;
            if (is_callable($fn)) {
                call_user_func($fn, $object, $name);
            }
        }
    });
}

function e($value, array $lot = []) {
    if (is_string($value)) {
        if ("" === $value) {
            return $value;
        }
        if (is_numeric($value)) {
            return false !== strpos($value, '.') ? (float) $value : (int) $value;
        }
        if (array_key_exists($value, $lot = array_replace([
            'FALSE' => false,
            'NULL' => null,
            'TRUE' => true,
            'false' => false,
            'null' => null,
            'true' => true
        ], $lot))) {
            return $lot[$value];
        }
        return $value;
    }
    if (is_array($value)) {
        foreach ($value as &$v) {
            $v = e($v, $lot);
        }
        unset($v);
    }
    return $value;
}

$GLOBALS['F'] = [
    '¹' => '1',
    '²' => '2',
    '³' => '3',
    '°' => '0',
    'æ' => 'ae',
    'ǽ' => 'ae',
    'À' => 'A',
    'Á' => 'A',
    'Â' => 'A',
    'Ã' => 'A',
    'Å' => 'A',
    'Ǻ' => 'A',
    'Ă' => 'A',
    'Ǎ' => 'A',
    'Æ' => 'AE',
    'Ǽ' => 'AE',
    'à' => 'a',
    'á' => 'a',
    'â' => 'a',
    'ã' => 'a',
    'å' => 'a',
    'ǻ' => 'a',
    'ă' => 'a',
    'ǎ' => 'a',
    'ª' => 'a',
    '@' => 'at',
    'Ĉ' => 'C',
    'Ċ' => 'C',
    'ĉ' => 'c',
    'ċ' => 'c',
    '©' => 'c',
    'Ð' => 'Dj',
    'Đ' => 'D',
    'ð' => 'dj',
    'đ' => 'd',
    'È' => 'E',
    'É' => 'E',
    'Ê' => 'E',
    'Ë' => 'E',
    'Ĕ' => 'E',
    'Ė' => 'E',
    'è' => 'e',
    'é' => 'e',
    'ê' => 'e',
    'ë' => 'e',
    'ĕ' => 'e',
    'ė' => 'e',
    'ƒ' => 'f',
    'Ĝ' => 'G',
    'Ġ' => 'G',
    'ĝ' => 'g',
    'ġ' => 'g',
    'Ĥ' => 'H',
    'Ħ' => 'H',
    'ĥ' => 'h',
    'ħ' => 'h',
    'Ì' => 'I',
    'Í' => 'I',
    'Î' => 'I',
    'Ï' => 'I',
    'Ĩ' => 'I',
    'Ĭ' => 'I',
    'Ǐ' => 'I',
    'Į' => 'I',
    'Ĳ' => 'IJ',
    'ì' => 'i',
    'í' => 'i',
    'î' => 'i',
    'ï' => 'i',
    'ĩ' => 'i',
    'ĭ' => 'i',
    'ǐ' => 'i',
    'į' => 'i',
    'ĳ' => 'ij',
    'Ĵ' => 'J',
    'ĵ' => 'j',
    'Ĺ' => 'L',
    'Ľ' => 'L',
    'Ŀ' => 'L',
    'ĺ' => 'l',
    'ľ' => 'l',
    'ŀ' => 'l',
    'Ñ' => 'N',
    'ñ' => 'n',
    'ŉ' => 'n',
    'Ò' => 'O',
    'Ô' => 'O',
    'Õ' => 'O',
    'Ō' => 'O',
    'Ŏ' => 'O',
    'Ǒ' => 'O',
    'Ő' => 'O',
    'Ơ' => 'O',
    'Ø' => 'O',
    'Ǿ' => 'O',
    'Œ' => 'OE',
    'ò' => 'o',
    'ô' => 'o',
    'õ' => 'o',
    'ō' => 'o',
    'ŏ' => 'o',
    'ǒ' => 'o',
    'ő' => 'o',
    'ơ' => 'o',
    'ø' => 'o',
    'ǿ' => 'o',
    'º' => 'o',
    'œ' => 'oe',
    'Ŕ' => 'R',
    'Ŗ' => 'R',
    'ŕ' => 'r',
    'ŗ' => 'r',
    'Ŝ' => 'S',
    'Ș' => 'S',
    'ŝ' => 's',
    'ș' => 's',
    'ſ' => 's',
    'Ţ' => 'T',
    'Ț' => 'T',
    'Ŧ' => 'T',
    'Þ' => 'TH',
    'ţ' => 't',
    'ț' => 't',
    'ŧ' => 't',
    'þ' => 'th',
    'Ù' => 'U',
    'Ú' => 'U',
    'Û' => 'U',
    'Ũ' => 'U',
    'Ŭ' => 'U',
    'Ű' => 'U',
    'Ų' => 'U',
    'Ư' => 'U',
    'Ǔ' => 'U',
    'Ǖ' => 'U',
    'Ǘ' => 'U',
    'Ǚ' => 'U',
    'Ǜ' => 'U',
    'ù' => 'u',
    'ú' => 'u',
    'û' => 'u',
    'ũ' => 'u',
    'ŭ' => 'u',
    'ű' => 'u',
    'ų' => 'u',
    'ư' => 'u',
    'ǔ' => 'u',
    'ǖ' => 'u',
    'ǘ' => 'u',
    'ǚ' => 'u',
    'ǜ' => 'u',
    'Ŵ' => 'W',
    'ŵ' => 'w',
    'Ý' => 'Y',
    'Ÿ' => 'Y',
    'Ŷ' => 'Y',
    'ý' => 'y',
    'ÿ' => 'y',
    'ŷ' => 'y',
    'Ъ' => "",
    'Ь' => "",
    'А' => 'A',
    'Б' => 'B',
    'Ц' => 'C',
    'Ч' => 'Ch',
    'Д' => 'D',
    'Е' => 'E',
    'Ё' => 'E',
    'Э' => 'E',
    'Ф' => 'F',
    'Г' => 'G',
    'Х' => 'H',
    'И' => 'I',
    'Й' => 'J',
    'Я' => 'Ja',
    'Ю' => 'Ju',
    'К' => 'K',
    'Л' => 'L',
    'М' => 'M',
    'Н' => 'N',
    'О' => 'O',
    'П' => 'P',
    'Р' => 'R',
    'С' => 'S',
    'Ш' => 'Sh',
    'Щ' => 'Shch',
    'Т' => 'T',
    'У' => 'U',
    'В' => 'V',
    'Ы' => 'Y',
    'З' => 'Z',
    'Ж' => 'Zh',
    'ъ' => "",
    'ь' => "",
    'а' => 'a',
    'б' => 'b',
    'ц' => 'c',
    'ч' => 'ch',
    'д' => 'd',
    'е' => 'e',
    'ё' => 'e',
    'э' => 'e',
    'ф' => 'f',
    'г' => 'g',
    'х' => 'h',
    'и' => 'i',
    'й' => 'j',
    'я' => 'ja',
    'ю' => 'ju',
    'к' => 'k',
    'л' => 'l',
    'м' => 'm',
    'н' => 'n',
    'о' => 'o',
    'п' => 'p',
    'р' => 'r',
    'с' => 's',
    'ш' => 'sh',
    'щ' => 'shch',
    'т' => 't',
    'у' => 'u',
    'в' => 'v',
    'ы' => 'y',
    'з' => 'z',
    'ж' => 'zh',
    'Ä' => 'AE',
    'Ö' => 'OE',
    'Ü' => 'UE',
    'ß' => 'ss',
    'ä' => 'ae',
    'ö' => 'oe',
    'ü' => 'ue',
    'Ç' => 'C',
    'Ğ' => 'G',
    'İ' => 'I',
    'Ş' => 'S',
    'ç' => 'c',
    'ğ' => 'g',
    'ı' => 'i',
    'ş' => 's',
    'Ā' => 'A',
    'Ē' => 'E',
    'Ģ' => 'G',
    'Ī' => 'I',
    'Ķ' => 'K',
    'Ļ' => 'L',
    'Ņ' => 'N',
    'Ū' => 'U',
    'ā' => 'a',
    'ē' => 'e',
    'ģ' => 'g',
    'ī' => 'i',
    'ķ' => 'k',
    'ļ' => 'l',
    'ņ' => 'n',
    'ū' => 'u',
    'Ґ' => 'G',
    'І' => 'I',
    'Ї' => 'Ji',
    'Є' => 'Ye',
    'ґ' => 'g',
    'і' => 'i',
    'ї' => 'ji',
    'є' => 'ye',
    'Č' => 'C',
    'Ď' => 'D',
    'Ě' => 'E',
    'Ň' => 'N',
    'Ř' => 'R',
    'Š' => 'S',
    'Ť' => 'T',
    'Ů' => 'U',
    'Ž' => 'Z',
    'č' => 'c',
    'ď' => 'd',
    'ě' => 'e',
    'ň' => 'n',
    'ř' => 'r',
    'š' => 's',
    'ť' => 't',
    'ů' => 'u',
    'ž' => 'z',
    'Ą' => 'A',
    'Ć' => 'C',
    'Ę' => 'E',
    'Ł' => 'L',
    'Ń' => 'N',
    'Ó' => 'O',
    'Ś' => 'S',
    'Ź' => 'Z',
    'Ż' => 'Z',
    'ą' => 'a',
    'ć' => 'c',
    'ę' => 'e',
    'ł' => 'l',
    'ń' => 'n',
    'ó' => 'o',
    'ś' => 's',
    'ź' => 'z',
    'ż' => 'z',
    'Α' => 'A',
    'Β' => 'B',
    'Γ' => 'G',
    'Δ' => 'D',
    'Ε' => 'E',
    'Ζ' => 'Z',
    'Η' => 'E',
    'Θ' => 'Th',
    'Ι' => 'I',
    'Κ' => 'K',
    'Λ' => 'L',
    'Μ' => 'M',
    'Ν' => 'N',
    'Ξ' => 'X',
    'Ο' => 'O',
    'Π' => 'P',
    'Ρ' => 'R',
    'Σ' => 'S',
    'Τ' => 'T',
    'Υ' => 'Y',
    'Φ' => 'Ph',
    'Χ' => 'Ch',
    'Ψ' => 'Ps',
    'Ω' => 'O',
    'Ϊ' => 'I',
    'Ϋ' => 'Y',
    'ά' => 'a',
    'έ' => 'e',
    'ή' => 'e',
    'ί' => 'i',
    'ΰ' => 'Y',
    'α' => 'a',
    'β' => 'b',
    'γ' => 'g',
    'δ' => 'd',
    'ε' => 'e',
    'ζ' => 'z',
    'η' => 'e',
    'θ' => 'th',
    'ι' => 'i',
    'κ' => 'k',
    'λ' => 'l',
    'μ' => 'm',
    'ν' => 'n',
    'ξ' => 'x',
    'ο' => 'o',
    'π' => 'p',
    'ρ' => 'r',
    'ς' => 's',
    'σ' => 's',
    'τ' => 't',
    'υ' => 'y',
    'φ' => 'ph',
    'χ' => 'ch',
    'ψ' => 'ps',
    'ω' => 'o',
    'ϊ' => 'i',
    'ϋ' => 'y',
    'ό' => 'o',
    'ύ' => 'y',
    'ώ' => 'o',
    'ϐ' => 'b',
    'ϑ' => 'th',
    'ϒ' => 'Y',
    'أ' => 'a',
    'ب' => 'b',
    'ت' => 't',
    'ث' => 'th',
    'ج' => 'g',
    'ح' => 'h',
    'خ' => 'kh',
    'د' => 'd',
    'ذ' => 'th',
    'ر' => 'r',
    'ز' => 'z',
    'س' => 's',
    'ش' => 'sh',
    'ص' => 's',
    'ض' => 'd',
    'ط' => 't',
    'ظ' => 'th',
    'ع' => 'aa',
    'غ' => 'gh',
    'ف' => 'f',
    'ق' => 'k',
    'ك' => 'k',
    'ل' => 'l',
    'م' => 'm',
    'ن' => 'n',
    'ه' => 'h',
    'و' => 'o',
    'ي' => 'y',
    'ạ' => 'a',
    'ả' => 'a',
    'ầ' => 'a',
    'ấ' => 'a',
    'ậ' => 'a',
    'ẩ' => 'a',
    'ẫ' => 'a',
    'ằ' => 'a',
    'ắ' => 'a',
    'ặ' => 'a',
    'ẳ' => 'a',
    'ẵ' => 'a',
    'ẹ' => 'e',
    'ẻ' => 'e',
    'ẽ' => 'e',
    'ề' => 'e',
    'ế' => 'e',
    'ệ' => 'e',
    'ể' => 'e',
    'ễ' => 'e',
    'ị' => 'i',
    'ỉ' => 'i',
    'ọ' => 'o',
    'ỏ' => 'o',
    'ồ' => 'o',
    'ố' => 'o',
    'ộ' => 'o',
    'ổ' => 'o',
    'ỗ' => 'o',
    'ờ' => 'o',
    'ớ' => 'o',
    'ợ' => 'o',
    'ở' => 'o',
    'ỡ' => 'o',
    'ụ' => 'u',
    'ủ' => 'u',
    'ừ' => 'u',
    'ứ' => 'u',
    'ự' => 'u',
    'ử' => 'u',
    'ữ' => 'u',
    'ỳ' => 'y',
    'ỵ' => 'y',
    'ỷ' => 'y',
    'ỹ' => 'y',
    'Ạ' => 'A',
    'Ả' => 'A',
    'Ầ' => 'A',
    'Ấ' => 'A',
    'Ậ' => 'A',
    'Ẩ' => 'A',
    'Ẫ' => 'A',
    'Ằ' => 'A',
    'Ắ' => 'A',
    'Ặ' => 'A',
    'Ẳ' => 'A',
    'Ẵ' => 'A',
    'Ẹ' => 'E',
    'Ẻ' => 'E',
    'Ẽ' => 'E',
    'Ề' => 'E',
    'Ế' => 'E',
    'Ệ' => 'E',
    'Ể' => 'E',
    'Ễ' => 'E',
    'Ị' => 'I',
    'Ỉ' => 'I',
    'Ọ' => 'O',
    'Ỏ' => 'O',
    'Ồ' => 'O',
    'Ố' => 'O',
    'Ộ' => 'O',
    'Ổ' => 'O',
    'Ỗ' => 'O',
    'Ờ' => 'O',
    'Ớ' => 'O',
    'Ợ' => 'O',
    'Ở' => 'O',
    'Ỡ' => 'O',
    'Ụ' => 'U',
    'Ủ' => 'U',
    'Ừ' => 'U',
    'Ứ' => 'U',
    'Ự' => 'U',
    'Ử' => 'U',
    'Ữ' => 'U',
    'Ỳ' => 'Y',
    'Ỵ' => 'Y',
    'Ỷ' => 'Y',
    'Ỹ' => 'Y'
];

function f(string $value = null, $accent = true, string $preserve = "") {
    // This function does not trim white-space at the start and end of the string
    $preserve = x($preserve);
    $value = preg_replace([
        // Remove HTML tag(s) and character(s) reference
        '/<[^>]+?>|&(?:[a-z\d]+|#\d+|#x[a-f\d]+);/i',
        // Remove anything except character(s) white-list
        '/[^\p{L}\p{N}\s' . $preserve . ']/u',
        // Convert multiple white-space to single space
        '/\s+/'
    ], ' ', $value);
    return $accent && !empty($GLOBALS['F']) ? strtr($value, $GLOBALS['F']) : $value;
}

function g(string $folder, $x = null, $deep = 0) {
    if (is_dir($folder)) {
        $it = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveCallbackFilterIterator($it, static function($v, $k, $a) use($deep, $x) {
            if ($deep > 0 && $a->hasChildren()) {
                return true;
            }
            // Filter by type (`0` for folder and `1` for file)
            if (0 === $x || 1 === $x) {
                return $v->{'is' . (0 === $x ? 'Dir' : 'File')}();
            }
            // Filter file(s) by extension
            if (is_string($x)) {
                $x = ',' . $x . ',';
                return $v->isFile() && false !== strpos($x, ',' . $v->getExtension() . ',');
            }
            // Filter by function
            if (is_callable($x)) {
                return fire($x, [$k, $v->isDir() ? 0 : 1], $v);
            }
            // No filter
            return true;
        });
        $it = new RecursiveIteratorIterator($it, null === $x || 0 === $x ? RecursiveIteratorIterator::CHILD_FIRST : RecursiveIteratorIterator::LEAVES_ONLY);
        $it->setMaxDepth(true === $deep ? -1 : (is_int($deep) ? $deep : 0));
        foreach ($it as $k => $v) {
            yield $k => $v->isDir() ? 0 : 1;
        }
    }
    return yield from [];
}

function h(string $value = null, string $join = '-', $accent = false, string $preserve = "") {
    return strtr(preg_replace_callback('/\p{Lu}/', static function($m) use($join) {
        return $join . l($m[0]);
    }, f($value, $accent, $join . $preserve)), [
        ' ' => $join,
        ' ' . $join => $join,
        $join . $join => $join
    ]);
}

function i($value = null, $lot = [], $or = null) {
    if (null === $value) {
        return;
    }
    $lot = (array) $lot;
    if ($lot) {
        // Also translate the argument(s)
        foreach ($lot as &$v) {
            $v = i($v);
        }
    }
    $value = $GLOBALS['I'][$value] ?? $or ?? $value;
    return is_string($value) && $lot ? vsprintf($value, $lot) : $value;
}

function j(array $a, array $b) {
    $f = [];
    foreach ($a as $k => $v) {
        if (is_array($v)) {
            if (!array_key_exists($k, $b) || !is_array($b[$k])) {
                $f[$k] = $v;
            } else {
                $ff = j($v, $b[$k]);
                if (!empty($ff)) {
                    $f[$k] = $ff;
                }
            }
        } else if (!array_key_exists($k, $b) || $v !== $b[$k]) {
            $f[$k] = $v;
        }
    }
    return $f;
}

function k(string $folder, $x = null, $deep = 0, $query = [], $content = false) {
    foreach (g($folder, $x, $deep) as $k => $v) {
        foreach ((array) $query as $q) {
            if ("" === $q) {
                continue;
            }
            // Find by query in file name…
            if (false !== stripos($k, $q)) {
                yield $k => $v;
            // Find by query in file content…
            } else if ($content && 1 === $v) {
                foreach (stream($k) as $vv) {
                    if (false !== stripos($vv, $q)) {
                        yield $k => 1;
                    }
                }
            }
        }
    }
}

function l(string $value = null) {
    return extension_loaded('mbstring') ? mb_strtolower($value) : strtolower($value);
}

function m($value, array $a, array $b) {
    // <https://stackoverflow.com/a/14224813/1163000>
    return ($value - $a[0]) * ($b[1] - $b[0]) / ($a[1] - $a[0]) + $b[0];
}

function n(string $value = null, string $tab = '    ') {
    // <https://stackoverflow.com/a/18870840/1163000>
    $value = strtr($value, ["\xEF\xBB\xBF" => ""]);
    // Tab to 4 space(s), line-break to `\n`
    return strtr($value, [
        "\r\n" => "\n",
        "\r" => "\n",
        "\t" => $tab
    ]);
}

function o($value, $safe = true) {
    if (is_array($value)) {
        if ($safe) {
            $value = array_keys($value) !== range(0, count($value) - 1) ? (object) $value : $value;
        } else {
            $value = (object) $value;
        }
        foreach ($value as &$v) {
            $v = o($v, $safe);
        }
        unset($v);
    }
    return $value;
}

function p(string $value = null, $accent = false, string $preserve = "") {
    return c(' ' . $value, $accent, $preserve);
}

function q($value) {
    if (true === $value) {
        return 1;
    }
    if (false === $value || null === $value) {
        return 0;
    }
    if (is_int($value) || is_float($value)) {
        return $value;
    }
    if (is_string($value)) {
        return extension_loaded('mbstring') ? mb_strlen($value) : strlen($value);
    }
    if ($value instanceof Traversable) {
        return iterator_count($value);
    }
    if ($value instanceof stdClass) {
        return count((array) $value);
    }
    return count($value);
}

function r($from, $to, string $value = null) {
    if (is_string($from) && is_string($to)) {
        return 1 === strlen($from) && 1 === strlen($to) ? strtr($value, $from, $to) : strtr($value, [
            $from => $to
        ]);
    }
    return strtr($value, array_combine($from, $to));
}

function s($value, array $lot = []) {
    if (is_array($value)) {
        foreach ($value as &$v) {
            $v = s($v, $lot);
        }
        unset($v);
        return $value;
    }
    if (true === $value) {
        return $lot['true'] ?? 'true';
    }
    if (false === $value) {
        return $lot['false'] ?? 'false';
    }
    if (null === $value) {
        return $lot['null'] ?? 'null';
    }
    if (is_string($value)) {
        return $lot[$value] ?? $value;
    }
    if (is_object($value)) {
        if (method_exists($value, '__toString')) {
            return $value->__toString();
        }
        return json_encode($value);
    }
    return (string) $value;
}

function t(string $value = null, string $open = '"', string $close = null) {
    if ($value) {
        if ("" !== $open && 0 === strpos($value, $open)) {
            $value = substr($value, strlen($open));
        }
        $close = $close ?? $open;
        if ("" !== $close && $close === substr($value, $end = -strlen($close))) {
            $value = substr($value, 0, $end);
        }
    }
    return $value;
}

function u(string $value = null) {
    return extension_loaded('mbstring') ? mb_strtoupper($value) : strtoupper($value);
}

function v(string $value = null, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
    $lot = [];
    foreach (str_split($c . $d, 1) as $v) {
        $lot["\\" . $v] = $v;
    }
    return strtr($value, $lot);
}

function w(string $value = null, $preserve_tags = [], $preserve_break = false) {
    // Should be a HTML input
    if (false !== strpos($value, '<') || false !== strpos($value, ' ') || false !== strpos($value, "\n")) {
        $preserve_tags = '<' . implode('><', is_string($preserve_tags) ? explode(',', $preserve_tags) : (array) $preserve_tags) . '>';
        return preg_replace($preserve_break ? '/ +/' : '/\s+/', ' ', trim(strip_tags($value, $preserve_tags)));
    }
    // [1]. Replace `+` with ` `
    // [2]. Replace `-` with ` `
    // [3]. Replace `---` with ` - `
    // [4]. Replace `--` with `-`
    return preg_replace([
        '/^[._]+|[._]+$/', // remove `.` and `__` prefix/suffix in file name
        '/---/',
        '/--/',
        '/-/',
        '/\s+/',
        '/[' . P . ']/'
    ], [
        "",
        ' ' . P . ' ',
        P,
        ' ',
        ' ',
        '-'
    ], urldecode($value));
}

function x(string $value = null, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
    return addcslashes($value, $c . $d);
}

function y(iterable $value) {
    if ($value instanceof Traversable) {
        return iterator_to_array($value);
    }
    return (array) $value;
}

function z($value, $short = true) {
    if (is_array($value)) {
        $out = [];
        foreach ($value as $k => $v) {
            $out[] = var_export($k, true) . '=>' . z($v, $short);
        }
        return ($short ? '[' : 'array(') . implode(',', $out) . ($short ? ']' : ')');
    }
    return var_export($value, true);
}

// Normalize `$_REQUEST` value
$any = [&$_GET, &$_POST, &$_REQUEST];
array_walk_recursive($any, static function(&$v) {
    // Trim white-space and normalize line-break
    $v = trim(strtr($v, ["\r\n" => "\n", "\r" => "\n"]));
    // Replace all empty value with `null` and evaluate other(s)
    $v = "" === $v ? null : e($v);
});

// Normalize `$_FILES` value to `$_POST`
if ('POST' === $_SERVER['REQUEST_METHOD']) {
    foreach ($_FILES as $k => $v) {
        if (isset($v['name']) && is_array($v['name'])) {
            $vv = [];
            foreach (['error', 'name', 'size', 'tmp_name', 'type'] as $kk) {
                array_walk_recursive($v[$kk], static function(&$v, $k, $kk) {
                    $v = [$kk => $v];
                }, $kk);
                $vv = array_replace_recursive($vv, $v[$kk]);
            }
            $_POST[$k] = $vv;
        } else {
            $_POST[$k] = $v;
        }
    }
}

// Load class(es)…
d(__DIR__ . D . 'use');

function anemone(...$lot) {
    return new Anemone(...$lot);
}

function hook(...$lot) {
    return count($lot) < 2 ? Hook::get(...$lot) : Hook::set(...$lot);
}

function state(...$lot) {
    if (count($lot) < 2) {
        $lot[] = true; // Force to array
        return State::get(...$lot);
    }
    return State::set(...$lot);
}

// Set default state(s)…
$state = is_file($f = __DIR__ . D . '..' . D . 'state.php') ? require $f : [];
$GLOBALS['state'] = $state = new State($state);

// Set default response status and header(s)
status(403, ['x-powered-by' => 'Mecha/' . VERSION]);

// Set default response type
type('text/html');

// Set default time zone and locale
zone($state->zone);

$port = (int) $_SERVER['SERVER_PORT'];
$scheme = 'http' . (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === $port ? 's' : "");
$protocol = $scheme . '://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
$path = ltrim($_GET['_'] ?? "", '/');
$query = explode('&', $_SERVER['QUERY_STRING'], 2)[1] ?? "";

unset($_GET['_']);

// Prevent cross-site script attack
$path = strtr(trim($path, '/'), [
    '<' => '%3C',
    '>' => '%3E',
    '&' => '%26',
    '"' => '%22'
]);

// Prevent directory traversal attack
$path = strtr($path, ['../' => ""]);

$path = "" !== $path ? '/' . $path : null;
$query = "" !== $query ? '?' . $query : null;
$hash = !empty($_COOKIE['hash']) ? '#' . $_COOKIE['hash'] : null;

$GLOBALS['url'] = $url = new URL($protocol . $host . $path . $query . $hash);

function kick(string $path = null, int $status = null) {
    $path = Hook::fire('kick', [$path, $status]);
    header('location: ' . $path, true, $status ?? 301);
    exit;
}

function long(string $value) {
    $url = $GLOBALS['url'];
    if ("" === $value) {
        return $url->current;
    }
    // `long('//example.com')`
    if (0 === strpos($value, '//')) {
        return rtrim(substr($url->protocol, 0, -2) . $value, '/');
    }
    // `long('./foo/bar/baz')`
    if ('.' === $value || 0 === strpos($value, './')) {
        $value = substr($value, 1);
    }
    // `long('/foo/bar/baz')`
    if (0 === strpos($value, '/')) {
        if (false !== strpos('?&#', $value[1] ?? P)) {
            $value = substr($value, 1);
        }
        if (0 === strpos($value, '&')) {
            $value = '?' . substr($value, 1);
        }
        return rtrim($url . $value, '/');
    }
    // `long('?foo=bar&baz=qux')`
    if (
        false === strpos($value, '://') &&
        0 !== strpos($value, 'blob:') &&
        0 !== strpos($value, 'data:') &&
        0 !== strpos($value, 'javascript:') &&
        0 !== strpos($value, 'mailto:')
    ) {
        $parent = strtok($url->current, '?&#');
        // `long('foo/bar/baz')`
        if ($value && false === strpos('.?&#', $value[0])) {
            $parent = dirname($parent);
        }
        if (0 !== ($count = substr_count($value . '/', '../'))) {
            $parent = dirname($parent, $count);
            $value = strtr($value . '/', ['../' => ""]);
        }
        return strtr(rtrim($parent . '/' . trim($value, '/'), '/'), [
            '/?' => '?',
            '/&' => '?',
            '/#' => '#'
        ]);
    }
    return $value;
}

function short(string $value) {
    $url = $GLOBALS['url'];
    $parent = $url . "";
    if (0 === strpos($value, '//')) {
        if (0 !== strpos($value, '//' . $url->host)) {
            return $value; // Ignore external URL
        }
        $value = $url->protocol . substr($value, 2);
    } else {
        if (0 !== strpos($value, $parent)) {
            return $value; // Ignore external URL
        }
    }
    $value = substr($value, strlen($parent));
    return "" === $value ? '/' : $value;
}

Hook::set('get', function() use($hash, $path, $query) {
    if (Hook::get('route')) {
        // All site page status is initially forbidden. If there are some route hook available, we will assume that we has a page but is not found.
        status(404);
    }
    Hook::fire('route', [$path, $query, $hash]);
}, 10);

$uses = [];
foreach (glob(__DIR__ . D . '..' . D . 'lot' . D . 'x' . D . '*' . D . 'index.php', GLOB_NOSORT) as $v) {
    if (empty($GLOBALS['X'][0][$v])) {
        $n = basename($d = dirname($v));
        $uses[path($v)] = content($d . D . $n) ?? $n;
        // Load state(s)…
        State::set('x.' . ($k = strtr($n, ['.' => "\\."])), []);
        if (is_file($v = $d . D . 'state.php')) {
            (static function($k, $v, $a) {
                extract($GLOBALS, EXTR_SKIP);
                State::set('x.' . $k, array_replace_recursive((array) require $v, $a));
            })($k, $v, $state['x'][$n] ?? []);
        }
    }
}

natsort($uses);

$GLOBALS['X'][1] = $uses = array_keys($uses);

// Load class(es)…
foreach ($uses as $v) {
    d(dirname($v) . D . 'engine' . D . 'use');
}

// Load extension(s)…
foreach ($uses as $v) {
    (static function($v) {
        // Load task(s)…
        if (is_file($k = dirname($v) . D . 'task.php')) {
            (static function($k) {
                extract($GLOBALS, EXTR_SKIP);
                require $k;
            })($k);
        }
        extract($GLOBALS, EXTR_SKIP);
        require $v;
    })($v);
}

unset($any, $d, $f, $folder, $hash, $host, $k, $n, $path, $port, $protocol, $query, $scheme, $uses, $v);

header_register_callback(static function() {
    Hook::fire('set');
});

register_shutdown_function(static function() {
    // Run task(s) if any…
    if (is_file($task = PATH . D . 'task.php')) {
        (static function($f) {
            extract($GLOBALS, EXTR_SKIP);
            require $f;
        })($task);
    }
    // Ideally, a response body should be stated in this hook.
    Hook::fire('get');
    // This hook is useful for running task(s) after the response ends. However, since response may issue an `exit`,
    // when the command is executed, it will make this hook fail to execute. As a workaround, you may need to execute
    // this hook before every `exit` command on every response body you make in the hook above.
    Hook::fire('let');
});