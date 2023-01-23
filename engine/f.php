<?php

// This feature is available since PHP 8.1
if (!function_exists('array_is_list')) {
    function array_is_list(array $value) {
        if ([] === $value) {
            return true;
        }
        $key = -1;
        foreach ($value as $k => $v) {
            if ($k !== ++$key) {
                return false;
            }
        }
        return true;
    }
}

function abort(string $alert, $exit = true) {
    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace = explode("\n", n(ob_get_clean()), 2);
    array_shift($trace);
    $trace = trim(strtr(implode("\n", $trace), [PATH => '.']), "\n");
    echo '<details' . ($exit ? ' open' : "") . ' style="background:#ff8;border:2px solid #000;color:#000;font:normal normal 100%/1.5 sans-serif;margin:0;padding:0;selection:none;text-shadow:none;"><summary style="cursor:pointer;display:block;margin:0;padding:.5em .75rem;text-shadow:none;user-select:none;">' . $alert . '</summary><pre style="background:#ffa;border:1px solid #000;border-width:1px 0 0;font:normal normal 100%/1.25 monospace;margin:0;overflow:auto;padding:0;text-shadow:none;white-space:pre;"><code style="color:inherit;display:block;font:inherit;margin:0;padding:.5em .75rem;text-shadow:none;">' . $trace . '</code></pre></details>';
    $exit && exit;
}

function any(iterable $value, $fn = null) {
    if (!is_callable($fn) && null !== $fn) {
        $fn = static function ($v) use ($fn) {
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
function c2f(?string $value, $accent = false) {
    $value = implode(D, map(preg_split('/[\\\\\/]/', $value ?? ""), static function ($v) use ($accent) {
        return ltrim(strtr(h($v, '-', $accent, '_') ?? "", [
            '_-' => '_',
            '__-' => '.',
            '__' => '.'
        ]), '-');
    }));
    return "" !== $value ? $value : null;
}

function check(string $token, $id = 0) {
    $prev = $_SESSION['token'][$id ?? uniqid()] ?? [0, ""];
    return $prev[1] && $token && $prev[1] === $token ? $token : false;
}

function choke(int $for = 1, $id = 0) {
    $current = $_SERVER['REQUEST_TIME'];
    $id = $id ?? uniqid();
    if (!is_file($file = ENGINE . D . 'log' . D . $id)) {
        if (!is_dir($folder = dirname($file))) {
            mkdir($folder, 0775, true);
        }
        touch($file, 0);
        return false;
    }
    $prev = filemtime($file);
    if ($for > $current - $prev) {
        return $for - ($current - $prev);
    }
    touch($file, $current);
    return false;
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

function content(string $path, $value = null, $seal = null) {
    if (null !== $value) {
        if (is_dir($path) || (is_file($path) && !is_writable($path))) {
            return false;
        }
        if (!is_dir($folder = dirname($path))) {
            mkdir($folder, 0775, true);
        }
        if (is_int(file_put_contents($path, (string) $value))) {
            $seal && seal($path, $seal);
            return true;
        }
        return false;
    }
    return is_file($path) && is_readable($path) ? file_get_contents($path) : null;
}

function cookie(...$lot) {
    if (0 === count($lot)) {
        $cookie = [];
        foreach ($_COOKIE as $k => $v) {
            if (0 === strpos($k, '*')) {
                $cookie[$k] = json_decode(base64_decode($v), true);
            } else {
                $cookie[$k] = $v;
            }
        }
        return $cookie;
    }
    $key = array_shift($lot);
    if (0 === count($lot)) {
        if (isset($_COOKIE[$k = '*' . sprintf('%u', crc32($key))])) {
            return json_decode(base64_decode($_COOKIE[$k]), true);
        }
        return $_COOKIE[$key] ?? null;
    }
    $value = array_shift($lot);
    $expires = array_shift($lot) ?? '+1 day';
    if (!is_array($expires)) {
        $expires = ['expires' => $expires];
    }
    $state = array_replace([
        'domain' => "",
        'expires' => '+1 day',
        'httponly' => true, // Safe by default
        'path' => '/',
        'samesite' => 'Strict', // Safe by default
        'secure' => !empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === (int) $_SERVER['SERVER_PORT']
    ], $expires);
    if (is_string($state['expires'])) {
        $state['expires'] = strtotime($state['expires']);
    }
    // <https://stackoverflow.com/a/1969339/1163000>
    setcookie('*' . sprintf('%u', crc32($key)), base64_encode(json_encode($value)), $state);
}

function delete(string $path, $purge = true) {
    if (is_file($path)) {
        // Success?
        $out = unlink($path) ? path($path) : null;
        // Remove parent folder if empty
        if ($purge) {
            $folder = path(dirname($path));
            while (0 === q(g($folder))) {
                if (PATH === $folder) {
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
        if (0 === $type) {
            foreach ($path as $v) {
                if ($v && $v = stream_resolve_include_path($v)) {
                    if (!is_dir($v)) {
                        continue;
                    }
                    return $v;
                }
            }
            return false;
        }
        if (1 === $type) {
            foreach ($path as $v) {
                if ($v && $v = stream_resolve_include_path($v)) {
                    if (!is_file($v)) {
                        continue;
                    }
                    return $v;
                }
            }
            return false;
        }
        foreach ($path as $v) {
            if ($v && $v = stream_resolve_include_path($v)) {
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
function f2c(?string $value, $accent = false) {
    $value = implode("\\", map(preg_split('/[\\\\\/]/', $value ?? ""), static function ($v) use ($accent) {
        return p(strtr($v, ['.' => '__']), $accent, '_');
    }));
    return "" !== $value ? $value : null;
}

// Convert file name to property name
function f2p(?string $value, $accent = false) {
    $value = implode("\\", map(preg_split('/[\\\\\/]/', $value ?? ""), static function ($v) use ($accent) {
        return c(strtr($v, ['.' => '__']), $accent, '_');
    }));
    return "" !== $value ? $value : null;
}

function fetch(string $url, $lot = null, $type = 'GET') {
    $chops = explode('?', $url, 2);
    $headers = ['x-requested-with' => 'x-requested-with: cURL'];
    $target = 'GET' === ($type = strtoupper($type)) ? $url : $chops[0];
    // `fetch('/', ['x-foo' => 'bar'])`
    if (is_array($lot)) {
        foreach ($lot as $k => $v) {
            if (false === $v || null === $v) {
                unset($headers[$k]);
                continue;
            }
            if (is_array($v)) {
                foreach ($v as $vv) {
                    $headers[] = $k . ': ' . $vv;
                }
            } else {
                $headers[$k] = $k . ': ' . $v;
            }
        }
    } else if (is_string($lot)) {
        $headers['user-agent'] = 'user-agent: ' . $lot;
    }
    if (!isset($headers['user-agent'])) {
        // <https://www.rfc-editor.org/rfc/rfc7231#section-5.5.3>
        $port = (int) $_SERVER['SERVER_PORT'];
        $v = 'Mecha/' . VERSION . ' (+http' . (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === $port ? 's' : "") . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "") . ')';
        $headers['user-agent'] = 'user-agent: ' . $v;
    }
    if (extension_loaded('curl')) {
        $c = curl_init($target);
        curl_setopt_array($c, [
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => array_values($headers),
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15
        ]);
        if ('HEAD' === $type) {
            curl_setopt($c, CURLOPT_HEADER, true);
            curl_setopt($c, CURLOPT_NOBODY, true);
        } else if ('POST' === $type) {
            curl_setopt($c, CURLOPT_POSTFIELDS, $chops[1] ?? "");
        }
        if (false !== ($out = curl_exec($c))) {
            if ('HEAD' === $out) {
                $out = n(trim($out));
            }
        }
        if (defined('TEST') && 'curl' === TEST && false === $out) {
            throw new UnexpectedValueException(curl_error($c));
        }
        curl_close($c);
    } else {
        if ('HEAD' === $type) {
            $heads = get_headers($target, true, stream_context_set_default(['http' => ['method' => $type]]));
            $heads = array_change_key_case((array) $heads, CASE_LOWER);
            // If `$target` is redirected and the new target is also redirected, we got the `Location` data as array.
            // We also got the HTTP code header in a number indexed value.
            //
            // <https://www.php.net/manual/en/function.get-headers.php#120075>
            //
            // [
            //     0 => 'HTTP/1.1 302 Moved Temporarily',
            //     'Location' => [
            //         0 => '/test.php?id=2',
            //         1 => '/test.php?id=3',
            //         2 => '/test.php?id=4'
            //     ],
            //     1 => 'HTTP/1.1 302 Moved Temporarily',
            //     2 => 'HTTP/1.1 302 Moved Temporarily',
            //     3 => 'HTTP/1.1 200 OK'
            // ]
            $out = [];
            if (isset($heads[0]) && isset($heads[1])) {
                foreach ($heads as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            $out[$kk][] = strtolower($k) . ': ' . $vv;
                        }
                        continue;
                    }
                    $out[0][] = (is_int($k) ? "\n" : strtolower($k) . ': ') . $v;
                }
                foreach ($out as &$v) {
                    $v = implode("\n", $v);
                }
                unset($v);
            } else {
                unset($heads[0]); // Remove the `HTTP/1.1 200 OK` part
                foreach ($heads as $k => $v) {
                    $out[] = (is_int($k) ? "\n" : strtolower($k) . ': ') . (is_array($v) ? end($v) : $v);
                }
            }
            $out = trim(implode("\n", $out));
        } else {
            $context = [];
            $headers['x-requested-with'] = 'x-requested-with: PHP';
            if ('POST' === $type) {
                $context['http']['content'] = $chops[1] ?? "";
                $headers['content-type'] = 'content-type: application/x-www-form-urlencoded';
            }
            $context['http']['header'] = implode("\r\n", array_values($headers));
            $context['http']['ignore_errors'] = true;
            $context['http']['method'] = $type;
            $out = file_get_contents($target, false, stream_context_create($context));
        }
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

function fire(callable $fn, array $lot = [], $that = null, $scope = 'static') {
    $fn = $fn instanceof Closure ? $fn : Closure::fromCallable($fn);
    // `fire($fn, [], Foo::class)`
    if (is_string($that)) {
        $scope = $that;
        $that = null;
    }
    return call_user_func($fn->bindTo($that, $scope), ...$lot);
}

function ge($a, $b) {
    return q($a) >= $b;
}

function get(array $from, string $key, string $join = '.') {
    if (!$from) {
        return null;
    }
    if (false === strpos($key = strtr($key, ["\\" . $join => P]), $join)) {
        return $from[strtr($key, [P => $join])] ?? null;
    }
    $keys = explode($join, $key);
    foreach ($keys as $k) {
        $k = strtr($k, [P => $join]);
        if (!is_array($from) || !array_key_exists($k, $from)) {
            return null;
        }
        $from =& $from[$k];
    }
    return $from;
}

function gt($a, $b) {
    return q($a) > $b;
}

function has(array $from, string $key, string $join = '.') {
    if (!$from) {
        return false;
    }
    if (false === strpos($key = strtr($key, ["\\" . $join => P]), $join)) {
        return array_key_exists(strtr($key, [P => $join]), $from);
    }
    $keys = explode($join, $key);
    foreach ($keys as $k) {
        $k = strtr($k, [P => $join]);
        if (!is_array($from) || !array_key_exists($k, $from)) {
            return false;
        }
        $from =& $from[$k];
    }
    return true;
}

function ip() {
    if ($for = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? "") {
        $ip = strpos($for, ',') > 0 ? trim(strtok($ip[0], ',')) : $for;
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
}

function is(iterable $value, $fn = null, $keys = false) {
    if (!is_callable($fn) && null !== $fn) {
        $fn = static function ($v) use ($fn) {
            return $v === $fn;
        };
    }
    $value = $fn ? array_filter($value, $fn, ARRAY_FILTER_USE_BOTH) : array_filter($value);
    return $keys ? $value : array_values($value);
}

function le($a, $b) {
    return q($a) <= $b;
}

function let(array &$from, string $key, string $join = '.') {
    if (!$from) {
        return false;
    }
    if (false === strpos($key = strtr($key, ["\\" . $join => P]), $join)) {
        if (array_key_exists($key = strtr($key, [P => $join]), $from)) {
            unset($from[$key]);
            return true;
        }
        return false;
    }
    $keys = explode($join, $key);
    while (count($keys) > 1) {
        $k = strtr(array_shift($keys), [P => $join]);
        if (is_array($from) && array_key_exists($k, $from)) {
            $from =& $from[$k];
        }
    }
    if (is_array($from) && array_key_exists($k = array_shift($keys), $from)) {
        unset($from[$k]);
        return true;
    }
    return false;
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
        return [null, null];
    }
    $path = path($path);
    // Move a file to a folder
    if (is_file($out[0] = $path)) {
        if (is_file($file = $to . D . ($as ?? basename($path)))) {
            // Return `false` if file exists
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
    // Move a folder with its contents to a folder
    $out = [$path, null];
    if (!is_dir($to)) {
        return $out;
    }
    $out[1] = [];
    if (!is_dir($to .= D . ($as ?? basename($path)))) {
        mkdir($to, 0775, true);
    }
    if ($path === ($to = path($to))) {
        return $out; // Nothing to move
    }
    foreach (g($path, 1, true) as $k => $v) {
        $file = $to . D . substr($k, strlen($path) + 1);
        if (!is_dir($folder = dirname($file))) {
            $out[1][$folder] = mkdir($folder, 0775, true) ? 0 : null;
        }
        $out[1][$file] = rename($k, $file) ? 1 : null;
    }
    // Delete empty folder
    foreach (g($path, 0, true) as $k => $v) {
        if (0 === q(g($k))) {
            rmdir($k);
        }
    }
    // Delete folder
    if (0 === q(g($path))) {
        rmdir($path);
    }
    return $out;
}

function ne($a, $b) {
    return q($a) !== $b;
}

function not(iterable $value, $fn = null, $keys = false) {
    if (!is_callable($fn) && null !== $fn) {
        $fn = static function ($v) use ($fn) {
            return $v === $fn;
        };
    }
    $value = array_filter($value, static function ($v, $k) use ($fn) {
        return !call_user_func($fn, $v, $k);
    }, ARRAY_FILTER_USE_BOTH);
    return $keys ? $value : array_values($value);
}

// Convert property name to file name
function p2f(?string $value, $accent = false) {
    $value = implode(D, map(preg_split('/[\\\\\/]/', $value ?? ""), static function ($v) use ($accent) {
        return strtr(h($v, '-', $accent, '_') ?? "", ['__' => '.']);
    }));
    return "" !== $value ? $value : null;
}

function path(?string $value) {
    return stream_resolve_include_path($value ?? "") ?: null;
}

// Generate new array contains value from the key
function pluck(iterable $from, string $key, $value = null, $keys = false) {
    $out = [];
    foreach ($from as $k => $v) {
        $out[$k] = $v[$key] ?? $value;
    }
    return $keys ? $out : array_values($out);
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
    if (is_int(file_put_contents($path, $value))) {
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
function send(string $from, $to, string $title, string $content, array $lot = []) {
    // This function was intended to be used as a quick way to send HTML email. There is no such email validation
    // proccess here. We assume that you have set the correct email address(es)
    if (is_array($to)) {
        // ['foo@bar', 'baz@qux']
        if (array_is_list($to)) {
            $to = implode(', ', $to);
        // ['foo@bar' => 'Foo Bar', 'baz@qux' => 'Baz Qux']
        } else {
            $out = "";
            foreach ($to as $k => $v) {
                $out .= ', ' . $v . ' <' . $k . '>';
            }
            $to = substr($out, 2);
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

function set(array &$to, string $key, $value = null, string $join = '.') {
    if (false === strpos($key = strtr($key, ["\\" . $join => P]), $join)) {
        $to[strtr($key, [P => $join])] = $value;
        return $to;
    }
    $keys = explode($join, $key);
    while (count($keys) > 1) {
        $k = strtr(array_shift($keys), [P => $join]);
        if (!array_key_exists($k, $to)) {
            $to[$k] = [];
        }
        $to =& $to[$k];
    }
    $to[strtr(array_shift($keys), [P => $join])] = $value;
    return $to;
}

function shake(array $value, $keys = false) {
    if (is_callable($keys)) {
        // `$keys` as `$fn`
        $value = call_user_func($keys, $value);
    } else {
        // <http://php.net/manual/en/function.shuffle.php#94697>
        if ($keys) {
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

// Tidy file size <https://en.wikipedia.org/wiki/Byte#Multiple-byte_units>
function size(float $size, string $unit = null, int $fix = 2, int $base = 1000) {
    $bases = [
        1000 => ['B', 'KB', 'MB', 'GB', 'TB'],
        1024 => ['B', 'KiB', 'MiB', 'GiB', 'TiB']
    ];
    $x = $bases[$base] ?? [];
    $u = $unit ? array_search($unit, $x) : ($size > 0 ? floor(log($size, $base)) : 0);
    $out = round($size / pow($base, $u), $fix);
    return $out < 0 ? null : trim($out . ' ' . ($x[$u] ?? ""));
}

function status(...$lot) {
    if (0 === count($lot)) {
        $out = [http_response_code(), [], []];
        foreach ($_SERVER as $k => $v) {
            if (0 === strpos($k, 'HTTP_')) {
                $out[1][strtolower(strtr(substr($k, 5), '_', '-'))] = e($v);
            }
        }
        if (function_exists('apache_request_headers')) {
            $out[1] = e(array_change_key_case((array) apache_request_headers(), CASE_LOWER));
        }
        if (function_exists('apache_response_headers')) {
            $out[2] = e(array_change_key_case((array) apache_response_headers(), CASE_LOWER));
        }
        foreach (headers_list() as $v) {
            $v = explode(':', $v, 2);
            if (isset($v[1])) {
                $vv = e(trim($v[1]));
                if (isset($out[2][$k = strtolower($v[0])]) && $vv !== $out[2][$k]) {
                    $out[2][$k] = (array) $out[2][$k];
                    $out[2][$k][] = $vv;
                    continue;
                }
                $out[2][$k] = $vv;
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
        if (is_array($v)) {
            foreach ($v as $vv) {
                header($k . ': ' . s($vv), false);
            }
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
    if (!empty($blob['status'])) {
        // Error
        return $blob['status'];
    }
    if (is_file($file = $path . D . ($as ?? $blob['name']))) {
        // Skip
        return false;
    }
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
    // Success?
    return move_uploaded_file($blob['path'], $file) ? path($file) : null;
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

function token($id = 0, $for = '+1 minute') {
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

function ua() {
    return $_SERVER['HTTP_USER_AGENT'] ?? null;
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
    if ($safe && is_object($value) && 'stdClass' !== get_class($value)) {
        return $value;
    }
    if (is_object($value)) {
        $value = (array) $value;
        foreach ($value as &$v) {
            $v = a($v, $safe);
        }
        unset($v);
    }
    return $value;
}

function b($value, array $range = [0, null]) {
    if (isset($range[0]) && $value < $range[0]) {
        return $range[0];
    }
    if (isset($range[1]) && $value > $range[1]) {
        return $range[1];
    }
    return $value;
}

function c(?string $value, $accent = false, string $keep = "") {
    $value = strtr(preg_replace_callback('/([ ' . ($keep = x($keep)) . '])([\p{L}\p{N}' . $keep . '])/u', static function ($m) {
        return $m[1] . u($m[2]);
    }, f($value, $accent, $keep ?? "") ?? ""), [' ' => ""]);
    return "" !== $value ? $value : null;
}

function d(string $folder, callable $fn = null) {
    spl_autoload_register(static function ($object) use ($folder, $fn) {
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
            if (strlen($value) > 1 && '0' === $value[0] && false === strpos($value, '.')) {
                return $value; // Preserve as-is!
            }
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

function f(?string $value, $accent = true, string $keep = "") {
    // Remove HTML tag(s) and character(s) reference
    $value = preg_replace('/<[^>]+?>|&(?:[a-z\d]+|#\d+|#x[a-f\d]+);/i', ' ', $value ?? "");
    if (!$accent || is_array($accent)) {
        $value = !empty($GLOBALS['F']) ? strtr($value, array_replace($GLOBALS['F'], (array) $accent)) : $value;
    }
    $value = preg_replace([
        // Remove anything except character(s) white-list
        '/[^\p{L}\p{N}«»‘’“”\s' . ($keep = x($keep)) . ']/u',
        // Convert multiple white-space to single space
        '/\s+/'
    ], ' ', $value);
    // This function does not trim white-space at the start and end of the string
    return "" !== $value ? $value : null;
}

function g(string $folder, $x = null, $deep = 0) {
    if (is_dir($folder)) {
        $it = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveCallbackFilterIterator($it, static function ($v, $k, $a) use ($deep, $x) {
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

function h(?string $value, string $join = '-', $accent = false, string $keep = "") {
    $value = strtr(preg_replace_callback('/\p{Lu}/u', static function ($m) use ($join) {
        return $join . l($m[0]);
    }, f($value, $accent, $join . $keep) ?? ""), [
        ' ' => $join,
        ' ' . $join => $join,
        $join . $join => $join
    ]);
    return "" !== $value ? $value : null;
}

function i(?string $value, $lot = [], string $or = null) {
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
    $value = $lot ? vsprintf($value, $lot) : $value;
    return "" !== $value ? $value : null;
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

function l(?string $value) {
    $value = (string) $value;
    $value = extension_loaded('mbstring') ? mb_strtolower($value) : strtolower($value);
    return "" !== $value ? $value : null;
}

function m($value, array $a, array $b) {
    // <https://stackoverflow.com/a/14224813/1163000>
    return ($value - $a[0]) * ($b[1] - $b[0]) / ($a[1] - $a[0]) + $b[0];
}

function n(?string $value, string $tab = '    ') {
    $value = (string) $value;
    // <https://stackoverflow.com/a/18870840/1163000>
    $value = strtr($value, ["\xEF\xBB\xBF" => ""]);
    // Tab to 4 space(s), line-break to `\n`
    $value = strtr($value, [
        "\r\n" => "\n",
        "\r" => "\n",
        "\t" => $tab
    ]);
    return "" !== $value ? $value : null;
}

function o($value, $safe = true) {
    if (is_array($value)) {
        $value = $safe && array_is_list($value) ? $value : (object) $value;
        foreach ($value as &$v) {
            $v = o($v, $safe);
        }
        unset($v);
    }
    return $value;
}

function p(?string $value, $accent = false, string $keep = "") {
    return c(' ' . $value, $accent, $keep);
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
    if ($value instanceof Countable) {
        return count($value);
    }
    if ($value instanceof Traversable) {
        return iterator_count($value);
    }
    if ($value instanceof stdClass) {
        return count((array) $value);
    }
    return empty($value) ? 0 : 1;
}

function r(?string $value, $from, $to) {
    if (is_string($from) && is_string($to)) {
        return 1 === strlen($from) && 1 === strlen($to) ? strtr($value, $from, $to) : strtr($value, [
            $from => $to
        ]);
    }
    $value = strtr($value, array_combine($from, $to));
    return "" !== $value ? $value : null;
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

function t(?string $value, string $open = '"', string $close = null) {
    if ($value) {
        if ("" !== $open && 0 === strpos($value, $open)) {
            $value = substr($value, strlen($open));
        }
        $close = $close ?? $open;
        if ("" !== $close && $close === substr($value, $end = -strlen($close))) {
            $value = substr($value, 0, $end);
        }
    }
    return "" !== $value ? $value : null;
}

function u(?string $value) {
    $value = (string) $value;
    $value = extension_loaded('mbstring') ? mb_strtoupper($value) : strtoupper($value);
    return "" !== $value ? $value : null;
}

function v(?string $value, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
    $lot = [];
    foreach (str_split($c . $d, 1) as $v) {
        $lot["\\" . $v] = $v;
    }
    return "" !== ($value = strtr($value ?? "", $lot)) ? $value : null;
}

function w(?string $value, $keep = [], $break = false) {
    $value = (string) $value;
    // Should be a HTML input
    if (false !== strpos($value, '<') || false !== strpos($value, ' ') || false !== strpos($value, "\n")) {
        $keep = is_string($keep) ? explode(',', $keep) : (array) $keep;
        return preg_replace($break ? '/ +/' : '/\s+/', ' ', trim(strip_tags($value, $keep)));
    }
    // Replace `+` with ` `
    // Replace `-` with ` `
    // Replace `---` with ` - `
    // Replace `--` with `-`
    $value = preg_replace([
        '/^[._]+|[._]+$/', // remove `.` and `_` prefix/suffix in file name
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
    return "" !== $value ? $value : null;
}

function x(?string $value, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
    return "" !== ($value = addcslashes($value ?? "", $c . $d)) ? $value : null;
}

function y(iterable $value) {
    if ($value instanceof Traversable) {
        return iterator_to_array($value);
    }
    return (array) $value;
}

function z($value, $short = true) {
    if (is_object($value)) {
        if ($value instanceof stdClass) {
            return '(object)' . z((array) $value, $short);
        }
        if ($value instanceof Closure) {
            $content = "";
            $f = new ReflectionFunction($value);
            $lot = [];
            foreach ($f->getParameters() as $p) {
                $value = "";
                if ($type = $p->getType()) {
                    if (method_exists($type, 'getTypes')) {
                        foreach ($type->getTypes() as $v) {
                            $value .= $v->getName() . '|';
                        }
                        $value = substr($value, 0, -1) . ' ';
                    } else {
                        $value .= $type->getName() . ' ';
                    }
                }
                if ($p->isVariadic()) {
                    $value .= '...';
                }
                if ($p->isPassedByReference()) {
                    $value .= '&';
                }
                $value .= '$' . $p->getName();
                if ($p->isDefaultValueAvailable()) {
                    $value .= '=';
                    if ($p->isDefaultValueConstant()) {
                        $value .= $p->getDefaultValueConstantName();
                    } else {
                        $value .= z($p->getDefaultValue(), $short);
                    }
                }
                $lot[] = $value;
            }
            $body = implode("", array_slice(file($f->getFileName()), $start = $f->getStartLine() - 1, $f->getEndLine() - $start));
            $tokens = token_get_all('<?' . 'php ' . $body);
            foreach ($tokens as $k => $v) {
                // Peek previous token
                if (is_array($prev = $tokens[$k - 1] ?? "")) {
                    $prev = $prev[1];
                }
                // Peek next token
                if (is_array($next = $tokens[$k + 1] ?? "")) {
                    $next = $next[1];
                }
                if (is_array($v)) {
                    if (T_COMMENT === $v[0] || T_DOC_COMMENT === $v[0]) {
                        // Remove comment(s)
                        continue;
                    }
                    if (T_OPEN_TAG === $v[0]) {
                        // Remove the `<?php ` prefix
                        continue;
                    }
                    if (T_ECHO === $v[0] || T_PRINT === $v[0]) {
                        if ('<?php ' === substr($content, -6)) {
                            $content = substr($content, 0, -4) . '='; // Replace `<?php echo` with `<?=`
                            continue;
                        }
                        $content .= 'echo '; // Replace `print` with `echo`
                        continue;
                    }
                    if (T_CASE === $v[0] || T_RETURN === $v[0] || T_YIELD === $v[0]) {
                        $content .= $v[1] . ' ';
                        continue;
                    }
                    if (T_IF === $v[0]) {
                        if ('else ' === substr($content, -5)) {
                            $content = substr($content, 0, -1) . 'if'; // Replace `else if` with `elseif`
                            continue;
                        }
                    }
                    if (T_DNUMBER === $v[0]) {
                        if (0 === strpos($v[1], '0.')) {
                            $v[1] = substr($v[1], 1); // Replace `0.` prefix with `.` from float
                        }
                        $v[1] = rtrim(rtrim($v[1], '0'), '.'); // Remove trailing `.0` from float
                        $content .= $v[1];
                        continue;
                    }
                    if (T_START_HEREDOC === $v[0]) {
                        $content .= '<<<' . ("'" === $v[1][3] ? "'S'" : 'S') . "\n";
                        continue;
                    }
                    if (T_END_HEREDOC === $v[0]) {
                        $content .= 'S';
                        continue;
                    }
                    if (T_CONSTANT_ENCAPSED_STRING === $v[0] || T_ENCAPSED_AND_WHITESPACE === $v[0]) {
                        // Need to escape `{` and `}` in string so we can match function body properly later
                        $content .= strtr($v[1], [
                            '{' => P . '\\x7B' . P,
                            '}' => P . '\\x7D' . P
                        ]);
                        continue;
                    }
                    // Any type cast
                    if (0 === strpos($v[1], '(') && ')' === substr($v[1], -1) && '_CAST' === substr(token_name($v[0]), -5)) {
                        $content = rtrim($content) . '(' . trim(substr($v[1], 1, -1)) . ')'; // Remove white-space after `(` and before `)`
                        continue;
                    }
                    if (T_WHITESPACE === $v[0]) {
                        if ("" === $next || "" === $prev) {
                            continue;
                        }
                        if (' ' === substr($content, -1)) {
                            continue; // Has been followed by single space, skip!
                        }
                        // Check if previous or next token contains only punctuation mark(s). White-space around this
                        // token usually safe to be removed. They must be PHP operator(s) like `&&` and `||`. Of course,
                        // they can also be present in comment and string, but we already filtered them before.
                        if (
                            (function_exists('ctype_punct') && ctype_punct($next) || preg_match('/^\p{P}$/', $next)) ||
                            (function_exists('ctype_punct') && ctype_punct($prev) || preg_match('/^\p{P}$/', $prev))
                        ) {
                            if (function_exists('ctype_alnum') && ctype_alnum(strtr($prev, ['_' => ""])) || preg_match('/^\w+$/', $prev)) {
                                // `$_` variable is all punctuation but it needs to be preceded by a space to ensure
                                // that we don’t experience a result like `static$_=1` in the output.
                                if ('$' === $next[0]) {
                                    $content .= ' ';
                                    continue;
                                }
                                // `_` is a punctuation but it needs to be preceded by a space to ensure that we don’t
                                // experience a result like `function_(){}` or `const_=1` in the output.
                                if ('_' === $next[0]) {
                                    $content .= ' ';
                                    continue;
                                }
                            }
                            continue;
                        }
                        // Check if previous or next token is a comment, then remove white-space around it!
                        if (
                            0 === strpos($next, '#') ||
                            0 === strpos($prev, '#') ||
                            0 === strpos($next, '//') ||
                            0 === strpos($prev, '//') ||
                            '/*' === substr($next, 0, 2) && '*/' === substr($next, -2) ||
                            '/*' === substr($prev, 0, 2) && '*/' === substr($prev, -2)
                        ) {
                            continue;
                        }
                        // Remove white-space after type cast
                        if (0 === strpos($prev, '(') && ')' === substr($prev, -1) && preg_match('/^\(\s*[^()\s]+\s*\)$/', $prev)) {
                            continue;
                        }
                        // Convert multiple white-space to single space
                        $content .= ' ';
                    }
                    $content .= ("" === trim($v[1]) ? "" : $v[1]);
                    continue;
                }
                // Replace `-0` with `0`
                if ('-' === $v && '0' === $next) {
                    continue;
                }
                // Remove trailing `,`
                if (',' === substr($content, -1) && false !== strpos(')]}', $v)) {
                    $content = substr($content, 0, -1);
                }
                if (
                    'case ' === substr($content, -5) ||
                    'echo ' === substr($content, -5) ||
                    'return ' === substr($content, -7) ||
                    'yield ' === substr($content, -6)
                ) {
                    if ($v && false !== strpos('!([', $v[0])) {
                        $content = substr($content, 0, -1);
                    }
                }
                $content .= ("" === trim($v) ? "" : $v);
            }
            // Match function body
            if (false !== strpos($content, '}') && preg_match('/\{((?>[^{}]++|(?R))*)\}/', $content, $m)) {
                $content = strtr($m[1], [
                    P . "\\x7B" . P => '{',
                    P . "\\x7D" . P => '}'
                ]);
            } else {
                $content = "";
            }
            $value = 'function(' . implode(',', $lot) . ')';
            // Need to check if `ReflectionFunction::getClosureUsedVariables()` method is available as we want to
            // support PHP 7.3. Method `ReflectionFunction::getClosureUsedVariables()` is available since PHP 8.0.
            // <https://www.php.net/manual/en/reflectionfunctionabstract.getclosureusedvariables.php>
            if (method_exists($f, 'getClosureUsedVariables')) {
                if ($uses = $f->getClosureUsedVariables()) {
                    // TODO: Find a way to check if used variable is passed by reference
                    $value .= 'use($' . implode(',$', array_keys($uses)) . ')';
                }
            }
            if ($type = $f->getReturnType()) {
                $value .= ':' . $type;
            }
            return $value . '{' . $content . '}';
        }
        // TODO: Find a way to extract argument(s) from class instance
        return 'new ' . get_class($value);
    }
    if (is_array($value)) {
        $out = [];
        if (array_is_list($value)) {
            foreach ($value as $k => $v) {
                $out[] = z($v, $short);
            }
        } else {
            foreach ($value as $k => $v) {
                // It is currently not possible to extract function body without the `\n` character which is required
                // by `ReflectionFunction::{getEndLine,getStartLine}()` to be able to return the value correctly
                $n = is_object($v) && !($v instanceof stdClass) ? "\n" : "";
                $out[] = $n . var_export($k, true) . '=>' . z($v, $short);
            }
        }
        return ($short ? '[' : 'array(') . implode(',', $out) . ($short ? ']' : ')');
    }
    $value = var_export($value, true);
    return 'NULL' === $value ? 'null' : ("''" === $value ? '""' : $value);
}