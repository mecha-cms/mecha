<?php

// <https://wiki.php.net/rfc/phase_out_serializable>
if (!interface_exists('Serializable')) {
    // PHP >= 10.0
    interface Serializable {
        public function serialize(): ?string;
        public function unserialize(string $data): void;
    }
}

// <https://wiki.php.net/rfc/stringable>
if (!interface_exists('Stringable')) {
    // PHP < 8.0
    interface Stringable {
        public function __toString(): string;
    }
}

// <https://wiki.php.net/rfc/is_list>
if (!function_exists('array_is_list')) {
    // PHP < 8.1
    function array_is_list(array $array): bool {
        if (!$array) {
            return true;
        }
        $key = -1;
        foreach ($array as $k => $v) {
            if ($k !== ++$key) {
                return false;
            }
        }
        return true;
    }
}

// <https://wiki.php.net/rfc/json_validate>
if (!function_exists('json_validate')) {
    // PHP < 8.3
    function json_validate(string $json): bool {
        $json = trim($json);
        if (
            'false' === $json ||
            // `null` is a valid JSON <https://www.rfc-editor.org/rfc/rfc8259#section-3>
            'null' === $json ||
            'true' === $json ||
            '""' === $json ||
            '[]' === $json ||
            '{}' === $json ||
            is_numeric($json)
        ) {
            return true;
        }
        return (
            // Maybe string
            '"' === $json[0] && '"' === substr($json, -1) ||
            // Maybe array
            '[' === $json[0] && ']' === substr($json, -1) ||
            // Maybe object
            '{' === $json[0] && '}' === substr($json, -1)
        ) && null !== json_decode($json);
    }
}

// To be used by `g()`
class RecursiveIteratorIteratorG extends RecursiveIteratorIterator implements Countable {
    public $c = 0;
    public $count = [];
    public $i = 0;
    public $list = false;
    public function count(): int {
        if (-1 !== ($v = $this->count[$c = $this->c] ?? -1)) {
            return $v;
        }
        // if (null !== ($v = content($f = LOT . D . 'cache' . D . 'g' . D . md5($c) . '.count'))) {
        //     return (int) ($v ?? 0);
        // }
        $this->rewind();
        $this->count[$c] = $v = iterator_count($this);
        $this->rewind();
        // if (!headers_sent()) {
        //     save($f, (string) $v, 0600);
        // }
        return $v;
    }
    #[ReturnTypeWillChange]
    public function current() {
        $v = parent::current();
        return $this->list ? $v : (is_dir($v) ? 0 : 1);
    }
    #[ReturnTypeWillChange]
    public function key() {
        return $this->list ? $this->i : parent::key();
    }
    public function next(): void {
        ++$this->i;
        parent::next();
    }
    public function rewind(): void {
        $this->i = 0;
        parent::rewind();
    }
}

function all(iterable $value, $valid = null, $that = null, $scope = 'static') {
    if (!$value || 0 === q($value)) {
        return true;
    }
    if (!is_callable($valid) && null !== $valid) {
        $valid = function ($v) use ($valid) {
            return $v === $valid;
        };
    }
    $valid = cue($valid, $that, $scope);
    foreach ($value as $k => $v) {
        if (!call_user_func($valid, $v, $k)) {
            return false;
        }
    }
    return true;
}

function any(iterable $value, $valid = null, $that = null, $scope = 'static') {
    if (!$value || 0 === q($value)) {
        return false;
    }
    if (!is_callable($valid) && null !== $valid) {
        $valid = function ($v) use ($valid) {
            return $v === $valid;
        };
    }
    $valid = cue($valid, $that, $scope);
    foreach ($value as $k => $v) {
        if (call_user_func($valid, $v, $k)) {
            return true;
        }
    }
    return false;
}

function apart(string $value, array $raw = [], array $void = []) {
    $from = $value;
    $i = -1;
    $s = " \n\r\t";
    $to = [];
    $raw = $raw ? P . implode(P, $raw) . P : P;
    $void = $void ? P . implode(P, $void) . P : P;
    while ("" !== $from) {
        if ($n = strcspn($from, '<&')) {
            if (0 === ($to[$i][1] ?? 1)) {
                $to[$i][0] .= substr($from, 0, $n);
                $from = substr($from, $n);
                continue;
            }
            $to[++$i] = [substr($from, 0, $n), 0];
            $from = substr($from, $n);
        }
        // <https://www.w3.org/TR/xml#sec-references>
        if ('&' === ($from[0] ?? 0)) {
            if ('#' === substr($from, 1, 1) && (
                ($n = strspn($from, '0123456789', $x = 2)) ||
                ($n = strspn($from, '0123456789ABCDEFabcdef', $x = 3)) && 'x' === substr($from, 2, 1)
            ) && ';' === substr($from, $n + $x, 1)) {
                $to[++$i] = [substr($from, 0, $n += ($x + 1)), -1];
                $from = substr($from, $n);
                continue;
            }
            if (($n = strspn($from, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', 1)) && ';' === substr($from, $n + 1, 1)) {
                $to[++$i] = [substr($from, 0, $n += 2), -1];
                $from = substr($from, $n);
                continue;
            }
            // Entity that does not end with a `;` is not valid
            if (0 === ($to[$i][1] ?? 1)) {
                $to[$i][0] .= substr($from, 0, 1);
            } else {
                $to[++$i] = [substr($from, 0, 1), 0];
            }
            $from = substr($from, 1);
            continue;
        }
        // Tag such as `<>` and `< >` is not valid because it does not have a name
        if (false !== strpos($s . '>', substr($from, 1, 1))) {
            if (0 === ($to[$i][1] ?? 1)) {
                $to[$i][0] .= substr($from, 0, 2);
            } else {
                $to[++$i] = [substr($from, 0, 2), 0];
            }
            $from = substr($from, 2);
            continue;
        }
        // <https://www.w3.org/TR/xml#sec-comments>
        if ('<!--' === substr($from, 0, 4)) {
            // Comment is left open for some reason until the end of the stream
            if (false === ($n = strpos($from, '-->'))) {
                $to[++$i] = [$from, 1];
                break;
            }
            $to[++$i] = [substr($from, 0, $n += 3), 1];
            $from = substr($from, $n);
            continue;
        }
        // <https://www.w3.org/TR/xml#sec-cdata-sect>
        if ('<![CDATA[' === substr($from, 0, 9)) {
            // Character data section is left open for some reason until the end of the stream
            if (false === ($n = strpos($from, ']]>'))) {
                $to[++$i] = [$from, 1];
                break;
            }
            $to[++$i] = [substr($from, 0, $n += 3), 1];
            $from = substr($from, $n);
            continue;
        }
        if ('<!' === substr($from, 0, 2)) {
            // DTD such as `<!>` and `<! >` is not valid because it does not have a name
            if (false !== strpos($s . '>', substr($from, 2, 1))) {
                if (0 === ($to[$i][1] ?? 1)) {
                    $to[$i][0] .= substr($from, 0, 3);
                } else {
                    $to[++$i] = [substr($from, 0, 3), 0];
                }
                $from = substr($from, 3);
                continue;
            }
            $to[++$i] = [substr($from, 0, 2), 1];
            $from = substr($from, 2);
            // <https://www.w3.org/TR/xml#sec-entexpand>
            // <https://www.w3.org/TR/xml#vc-roottype>
            while (($n = strcspn($from, '["' . "'")) < strpos($from, '>')) {
                if ($n > 0) {
                    $to[$i][0] .= substr($from, 0, $n);
                    $from = substr($from, $n);
                }
                // DTD is left open for some reason until the end of the stream
                if (false === ($n = strpos($from, '[' === $from[0] ? ']' : $from[0], 1))) {
                    $to[$i][0] .= $from;
                    $from = "";
                    break;
                }
                $to[$i][0] .= substr($from, 0, $n += 1);
                $from = substr($from, $n);
                continue;
            }
            // DTD is left open for some reason until the end of the stream
            if ("" === $from || false === ($n = strpos($from, '>'))) {
                $to[$i][0] .= $from;
                break;
            }
            $to[$i][0] .= substr($from, 0, $n += 1);
            $from = substr($from, $n);
            continue;
        }
        // <https://www.w3.org/TR/xml#sec-pi>
        if ('<?' === substr($from, 0, 2)) {
            /* PI such as `<?>` and `<? >` is not valid because it does not have a name */
            if (false !== strpos($s . '>', substr($from, 2, 1))) {
                if (0 === ($to[$i][1] ?? 1)) {
                    $to[$i][0] .= substr($from, 0, 3);
                } else {
                    $to[++$i] = [substr($from, 0, 3), 0];
                }
                $from = substr($from, 3);
                continue;
            }
            $to[++$i] = [substr($from, 0, 2), 1];
            $from = substr($from, 2);
            while (($n = strcspn($from, '"' . "'")) < strpos($from, '?>')) {
                if ($n > 0) {
                    $to[$i][0] .= substr($from, 0, $n);
                    $from = substr($from, $n);
                }
                // PI is left open for some reason until the end of the stream
                if (false === ($n = strpos($from, $from[0], 1))) {
                    $to[$i][0] .= $from;
                    $from = "";
                    break;
                }
                $to[$i][0] .= substr($from, 0, $n += 1);
                $from = substr($from, $n);
                continue;
            }
            // PI is left open for some reason until the end of the stream
            if ("" === $from || false === ($n = strpos($from, '?>'))) {
                $to[$i][0] .= $from;
                break;
            }
            $to[$i][0] .= substr($from, 0, $n += 2);
            $from = substr($from, $n);
            continue;
        }
        // <https://www.w3.org/TR/xml#sec-starttags>
        $k = rtrim(strtok(substr($from, 1), $s . '>'), '/');
        $to[++$i] = [substr($from, 0, $n = 1 + strlen($k)), false !== strpos($void, P . $k . P) ? 1 : 2];
        $from = substr($from, $n);
        // <https://www.w3.org/TR/xml#NT-AttValue>
        while (($n = strcspn($from, '"' . "'")) < strpos($from, '>')) {
            if ($n > 0) {
                $to[$i][0] .= substr($from, 0, $n);
                $from = substr($from, $n);
            }
            // Tag is left open for some reason until the end of the stream
            if (false === ($n = strpos($from, $from[0], 1))) {
                $to[$i][0] .= $from;
                $from = "";
                break;
            }
            $to[$i][0] .= substr($from, 0, $n += 1);
            $from = substr($from, $n);
            continue;
        }
        // Tag is left open for some reason until the end of the stream
        if ("" === $from || false === ($n = strpos($from, '>'))) {
            $to[$i][0] .= $from;
            $to[$i][1] = 0;
            break;
        }
        $to[$i][0] .= substr($from, 0, $n += 1);
        $to[$i][2] = strlen($to[$i][0]);
        // <https://www.w3.org/TR/xml#d0e2480>
        if ('/' === substr($from, $n - 2, 1)) {
            $to[$i][1] = 1;
            $to[$i][3] = true;
        }
        $from = substr($from, $n);
        if (false === strpos($raw, P . $k . P)) {
            continue;
        }
        $last = -1;
        $to[$i][1] = 1;
        while (false !== ($last = strpos($from, '</' . $k, $last + 1))) {
            if (false !== strpos($s . '>', substr($from, $last + strlen($k) + 2, 1))) {
                $n = $last + strlen($k) + 2;
                $to[$i][0] .= substr($from, 0, $n);
                $from = substr($from, $n);
                // Raw element is left open for some reason until the end of the stream
                if (false === ($n = strpos($from, '>'))) {
                    $to[$i][0] .= $from;
                    $to[$i][3] = null;
                    break;
                }
                $to[$i][0] .= substr($from, 0, $n += 1);
                $to[$i][3] = -(strlen($k) + 2 + $n);
                $from = substr($from, $n);
                // I still feel that this line is ugly. I’m sorry :(
                continue 2;
            }
        }
        $to[$i][0] .= $from;
        break;
    }
    return $to;
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
    $id = md5((string) ($id ?? uniqid()));
    if (!is_file($file = ENGINE . D . 'log' . D . 'choke' . D . $id)) {
        if (!is_dir($folder = dirname($file))) {
            mkdir($folder, 0775, true);
        }
        touch($file, $current);
        return $for;
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
        return null;
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
    $with = array_shift($lot) ?? '+1 day';
    if (!is_array($with)) {
        $with = ['expires' => $with];
    }
    $with = array_replace([
        'domain' => "",
        'expires' => '+1 day',
        'httponly' => true, // Safe by default
        'path' => '/',
        'samesite' => 'Strict', // Safe by default
        'secure' => !empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === (int) $_SERVER['SERVER_PORT']
    ], $with);
    if (is_string($with['expires'])) {
        $with['expires'] = strtotime($with['expires']);
    }
    // <https://stackoverflow.com/a/1969339/1163000>
    setcookie('*' . sprintf('%u', crc32($key)), base64_encode(json_encode($value)), $with);
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

// Remove empty array and array-like, empty string and `null` value from array and array-like
function drop(iterable $value, ?callable $valid = null, $that = null, $scope = 'static') {
    if (!$value || 0 === q($value)) {
        return null;
    }
    $valid = cue($valid ?? function ($v, $k) {
        return "" === $v || null === $v || [] === $v || is_countable($v) && 0 === count($v);
    }, $that, $scope);
    foreach ($value as $k => $v) {
        if (is_array($v) && !empty($v)) {
            if ($v = drop($v, $valid, $that, $scope)) {
                $value[$k] = $v;
            } else {
                unset($value[$k]); // Drop!
            }
        } else {
            if (call_user_func($valid, $v, $k)) {
                unset($value[$k]); // Drop!
            }
        }
    }
    return [] !== $value ? (is_countable($value) && 0 !== count($value) ? $value : null) : null;
}

// [E]scape HTML [at]tribute’s value
function eat(?string $value) {
    return "" !== ($value = htmlspecialchars($value ?? "", ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false)) ? $value : null;
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
            $context['ssl']['verify_peer'] = false;
            $context['ssl']['verify_peer_name'] = false;
            $out = file_get_contents($target, false, stream_context_create($context));
        }
    }
    return false !== $out ? $out : null;
}

function find(iterable $value, callable $valid, $that = null, $scope = 'static') {
    if (!$value || 0 === q($value)) {
        return null;
    }
    $valid = cue($valid, $that, $scope);
    foreach ($value as $k => $v) {
        if (call_user_func($valid, $v, $k)) {
            return $v;
        }
    }
    return null;
}

function fire(callable $task, array $lot = [], $that = null, $scope = 'static') {
    return call_user_func(cue($task, $that, $scope), ...$lot);
}

function ge($a, $b) {
    return q($a) >= $b;
}

function get(iterable $from, string $key, string $join = '.') {
    if (!$from || 0 === q($from)) {
        return null;
    }
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    foreach ($keys as $key) {
        $key = strtr($key, [P => $join]);
        if (is_object($from) && $from instanceof ArrayAccess && !$from->offsetExists($key)) {
            return null;
        }
        if (!is_array($from) || !array_key_exists($key, $from)) {
            return null;
        }
        $from =& $from[$key];
    }
    return $from;
}

function gt($a, $b) {
    return q($a) > $b;
}

function has(iterable $from, string $key, string $join = '.') {
    if (!$from || 0 === q($from)) {
        return false;
    }
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    foreach ($keys as $key) {
        $key = strtr($key, [P => $join]);
        if (is_object($from) && $from instanceof ArrayAccess && !$from->offsetExists($key)) {
            return false;
        }
        if (!is_array($from) || !array_key_exists($key, $from)) {
            return false;
        }
        $from =& $from[$key];
    }
    return true;
}

function ip() {
    if ($for = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? "") {
        $ip = trim(strstr($for, ',', true) ?: $for);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
}

function is(iterable $value, $valid = null, $keys = false, $that = null, $scope = 'static') {
    if (!$value || 0 === q($value)) {
        return $value;
    }
    if (is_callable($valid) && is_object($value) && $value instanceof Traversable) {
        return new CallbackFilterIterator($value instanceof IteratorAggregate ? $value->getIterator() : $value, cue($valid, $that, $scope));
    }
    $value = $valid ? array_filter($value, cue($valid, $that, $scope), ARRAY_FILTER_USE_BOTH) : array_filter($value);
    return $keys ? $value : array_values($value);
}

function le($a, $b) {
    return q($a) <= $b;
}

function let(iterable &$from, string $key, string $join = '.') {
    if (!$from || 0 === q($from)) {
        return false;
    }
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    $k = strtr(array_pop($keys), [P => $join]);
    while ($keys) {
        $key = strtr(array_shift($keys), [P => $join]);
        if (is_array($from) && array_key_exists($key, $from) || is_object($from) && $from instanceof ArrayAccess && $from->offsetExists($key)) {
            $from =& $from[$key];
        }
    }
    if (is_array($from) && array_key_exists($k, $from) || is_object($from) && $from instanceof ArrayAccess && $from->offsetExists($k)) {
        unset($from[$k]);
        return true;
    }
    return false;
}

function &lot(...$lot) {
    if (0 === count($lot)) {
        return $GLOBALS;
    }
    // <https://www.php.net/manual/en/language.variables.php>
    $pattern = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';
    if (1 === count($lot)) {
        if (is_string($lot[0])) {
            $r =& $GLOBALS[$lot[0]] ?? null;
            return $r;
        }
        $lot[0] = (array) ($lot[0] ?? []);
        foreach ($lot[0] as $k => $v) {
            if ("" === $k || is_int($k) || is_numeric($k[0]) || !preg_match($pattern, $k)) {
                unset($lot[0][$k]);
            }
        }
        $lot[0] = array_replace($GLOBALS, $lot[0]);
        $r =& $lot[0];
        return $r;
    }
    $r =& $lot[1] ?? null;
    if ("" !== $lot[0] && is_string($lot[0]) && !is_numeric($lot[0]) && preg_match($pattern, $lot[0])) {
        $GLOBALS[$lot[0]] = $r;
    }
    return $r;
}

function lt($a, $b) {
    return q($a) < $b;
}

function map(iterable $value, callable $at, $that = null, $scope = 'static') {
    if (!$value || 0 === q($value)) {
        return $value;
    }
    $at = cue($at, $that, $scope);
    foreach ($value as $k => $v) {
        $value[$k] = call_user_func($at, $v, $k);
    }
    return $value;
}

function move(string $path, string $to, ?string $as = null) {
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

function not(iterable $value, $valid = null, $keys = false, $that = null, $scope = 'static') {
    if (!$value || 0 === q($value)) {
        return $value;
    }
    if (!is_callable($valid) && null !== $valid) {
        $valid = function ($v) use ($valid) {
            return $v === $valid;
        };
    }
    if (is_callable($valid) && is_object($value) && $value instanceof Traversable) {
        $valid = cue($valid, $that, $scope);
        return new CallbackFilterIterator($value instanceof IteratorAggregate ? $value->getIterator() : $value, function ($v, $k) use ($valid) {
            return !call_user_func($valid, $v, $k);
        });
    }
    $valid = cue($valid, $that, $scope);
    $value = array_filter($value, static function ($v, $k) use ($valid) {
        return !call_user_func($valid, $v, $k);
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

function pair(string $value) {
    if ("" === ($from = trim($value))) {
        return [];
    }
    $s = " \n\r\t";
    $to = [];
    while ("" !== $from) {
        if ($n = strcspn($from, '"' . "'" . $s)) {
            $k = trim(substr($from, 0, $n));
            $from = substr($from, $n);
            if ('=' === substr($k, -1)) {
                $k = trim(substr($k, 0, -1));
                if ('"' === ($c = $from[0] ?? 0) || "'" === $c) {
                    // Attribute value is left open for some reason until the end of the stream
                    if (false === ($n = strpos($from, $c, 1))) {
                        $to[$k] = substr($from, 1);
                        break;
                    }
                    $to[$k] = substr($from, 1, $n - 1);
                    $from = trim(substr($from, $n + 1));
                    continue;
                }
                $to[$k] = "";
                $from = substr($from, 1);
                continue;
            }
            if (($n = strpos($k, '=')) > 0) {
                $to[substr($k, 0, $n)] = substr($k, $n + 1);
                $from = substr($from, 1);
                continue;
            }
            $to[$k] = true;
            $from = substr($from, 1);
            continue;
        }
        $to[trim($from)] = true;
        break;
    }
    return $to;
}

function path(?string $value) {
    return stream_resolve_include_path($value ?? "") ?: null;
}

function pluck(iterable $from, string $key, $value = null, $that = null, $scope = 'static') {
    if (!$from || 0 === q($from)) {
        return [];
    }
    $dot = false !== strpos(strtr($key, ["\\." => P]), '.');
    return map($from, function ($v, $k) use ($dot, $key, $value) {
        if ($dot && is_array($v)) {
            return get($v, $key) ?? $value;
        }
        return $v[$key] ?? $value;
    }, $that, $scope);
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

function set(iterable &$to, string $key, $value = null, string $join = '.') {
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    $k = strtr(array_pop($keys), [P => $join]);
    while ($keys) {
        $key = strtr(array_shift($keys), [P => $join]);
        if (is_array($to) && !array_key_exists($key, $to) || is_object($to) && $to instanceof ArrayAccess && !$to->offsetExists($key)) {
            $to[$key] = [];
        }
        $to =& $to[$key];
    }
    return ($to[$k] = $value);
}

function shake(array $value, $keys = false, $that = null, $scope = 'static') {
    if (is_callable($keys)) {
        // `$keys` as `$task`
        $value = fire($keys, [$value], $that, $scope);
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
function size(float $size, ?string $unit = null, int $fix = 2, int $base = 1000) {
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
    return ["" => $value];
}

function store(string $path, array $blob, ?string $as = null) {
    if (!empty($blob['status'])) {
        // Error
        return $blob['status'];
    }
    if ("" !== trim($route = $blob['route'] ?? "", '/')) {
        $path .= D . dirname($route);
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
function stream(string $path, ?int $max = 1024) {
    if (is_file($path) && $h = fopen($path, 'r')) {
        $max = is_int($max) ? $max + 1 : $max;
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

function cue(callable $task, $that = null, $scope = 'static') {
    $task = $task instanceof Closure ? $task : Closure::fromCallable($task);
    if (is_string($that)) {
        $scope = $that;
        $that = null;
    }
    return $that ? $task->bindTo($that, $scope) : $task;
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

function type(?string $type = null) {
    if (!isset($type)) {
        $type = status()[2]['content-type'] ?? null;
        if (is_string($type)) {
            return trim(strstr($type . ';', ';', true));
        }
        return null;
    }
    $type = trim(strstr($type . ';', ';', true));
    status(['content-type' => $type . '; charset=utf-8']);
}

function ua() {
    return $_SERVER['HTTP_USER_AGENT'] ?? null;
}

function zone(?string $zone = null) {
    if (!isset($zone)) {
        return 'UTC' !== ($zone = date_default_timezone_get()) ? $zone : null;
    }
    return date_default_timezone_set($zone);
}


// a: Convert object to array
// b: Keep value between `$a` and `$b`
// c: Convert text to camel case
// d: Declare class(es) with task
// e: Evaluate string to their data type
// f: Filter/sanitize string
// g: Advance PHP `glob()` function that returns iterator
// h: Convert text to snake case with `-` (hyphen) as the default separator
// i: Internationalization
// j: Compare array(s) and return all item(s) that exist just once in any
// k: Search file in a folder by query
// l: Convert text to lower case
// m: Normalize range margin
// n: Normalize white-space in string
// o: Convert array to object
// p: Convert text to pascal case
// q: Quantity (length of a string, number, array, and object)
// r: Replace string
// s: Convert data type to their string format
// t: Trim string from prefix and suffix once
// u: Convert text to upper case
// v: Un-escape
// w: Convert any data to plain word(s)
// x: Escape
// y: Convert iterator to plain array
// z: Export array/object into a compact PHP data string

function a($value, $safe = true) {
    if ($safe && is_object($value) && 'stdClass' !== get_class($value)) {
        return $value;
    }
    if (is_array($value) || is_object($value)) {
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
    $value = strtr(preg_replace_callback('/([ ' . ($keep = x($keep)) . ']+)([\p{L}\p{N}' . $keep . '])/u', static function ($m) {
        if (' ' !== substr($m[1], -1)) {
            return $m[1] . $m[2];
        }
        return $m[1] . u($m[2]);
    }, f($value, $accent, $keep ?? "") ?? ""), [' ' => ""]);
    return "" !== $value ? $value : null;
}

function d(string $folder, ?callable $task = null) {
    spl_autoload_register(static function ($object) use ($folder, $task) {
        $f = c2f($object);
        if (is_file($file = $folder . D . $f . '.php')) {
            extract(lot(), EXTR_SKIP);
            require $file;
            if (is_callable($task)) {
                call_user_func($task, $object, $f, $file);
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
    $value = preg_replace('/<("[^"]*"|\'[^\']*\'|[^>])+>|&(?>[a-z\d]+|#\d+|#x[a-f\d]+);/i', ' ', $value ?? "");
    if (!$accent || is_array($accent)) {
        // If this condition is not checked, the translation below will incorrectly translate all `'0'` to `false`
        // (which will be casted as an empty string), and so any string containing a `'0'` character will always drop
        // that character in the output :(
        if (false === $accent) {
            $accent = []; // Because `(array) false` will turn into `[0 => false]` which we don’t want
        }
        $value = ($any = (array) lot('F')) ? strtr($value, array_replace($any, (array) $accent)) : $value;
    }
    $value = preg_replace([
        // Remove anything except character(s) white-list
        '/[^\p{L}\p{N}«»‘’“”\s' . x($keep) . ']/u',
        // Convert multiple white-space to single space
        '/\s+/'
    ], ' ', $value);
    // This function does not trim white-space at the start and end of the string
    return "" !== $value ? $value : null;
}

function g(string $folder, $x = null, $deep = 0, $keys = true) {
    if (is_dir($folder) && (new FilesystemIterator($folder))->valid()) {
        $it = new RecursiveDirectoryIterator($folder, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveCallbackFilterIterator($it, static function ($v, $k, $it) use ($deep, $x) {
            if ($deep > 0 && $it->hasChildren()) {
                return true;
            }
            // Filter by type (`0` for folder and `1` for file)
            if (0 === $x) {
                return is_dir($v);
            }
            if (1 === $x) {
                return is_file($v);
            }
            // Filter by file extension
            if (is_string($x)) {
                $x = ',' . $x . ',';
                return is_file($v) && false !== strpos($x, ',' . pathinfo($v, PATHINFO_EXTENSION) . ',');
            }
            // No filter
            return true;
        });
        $it = new RecursiveIteratorIteratorG($it, null === $x || 0 === $x ? RecursiveIteratorIteratorG::CHILD_FIRST : RecursiveIteratorIteratorG::LEAVES_ONLY);
        $it->c = $folder . '?deep=' . s($deep) . '&x=' . s($x);
        $it->list = !$keys;
        $it->setMaxDepth(true === $deep ? -1 : (is_int($deep) ? $deep : 0));
        // To get the first element of a `RecursiveIteratorIterator` instance, a rewind is needed, somehow :(
        $it->rewind(); // This should execute the `RecursiveIteratorIterator::beginIteration()` method
        return $it;
    }
    return new EmptyIterator;
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

function i(?string $value, $lot = [], ?string $or = null) {
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
    $value = $raw = lot('I')[$value] ?? $or ?? $value;
    try {
        $value = $lot ? vsprintf($value, $lot) : $value;
        // Also translate the result after the argument(s) are applied
        if ($value !== $raw) { // This prevents recursive function calls
            $value = i($value, [], $or);
        }
    } catch (Throwable $e) {}
    return "" !== $value ? $value : null;
}

function j(array $a, array $b) {
    $out = [];
    foreach ($a as $k => $v) {
        if (is_array($v)) {
            if (!array_key_exists($k, $b) || !is_array($b[$k])) {
                $out[$k] = $v;
            } else {
                if ($vv = j($v, $b[$k])) {
                    $out[$k] = $vv;
                }
            }
        } else if (!array_key_exists($k, $b) || $v !== $b[$k]) {
            $out[$k] = $v;
        }
    }
    return $out;
}

function k(string $folder, $x = null, $deep = 0, $keys = true, $query = [], $content = false) {
    $query = (array) $query;
    return new CallbackFilterIterator(g($folder, $x, $deep, $keys), static function ($v, $k) use ($content, $keys, $query) {
        $test = $keys ? $k : $v;
        foreach ($query as $q) {
            if ("" === $q) {
                continue;
            }
            $strict = $q !== strtolower($q); // Case sensitive?
            // Find by query in file name…
            if (false !== ($strict ? strpos($test, $q) : stripos($test, $q))) {
                return true;
            }
            // Find by query in file content…
            if (!is_file($test)) {
                return false;
            }
            if ($content) {
                foreach (stream($test, strlen($q)) as $v) {
                    if (false !== ($strict ? strpos($v, $q) : stripos($v, $q))) {
                        return true;
                    }
                }
            }
        }
        return false;
    });
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

function n(?string $value, $tab = '    ', string $c = ' ') {
    $value = (string) $value;
    // <https://stackoverflow.com/a/18870840/1163000>
    $value = strtr($value, ["\xEF\xBB\xBF" => ""]);
    // Tab to 4 space(s), line-break to `\n`
    $value = strtr($value, [
        "\r\n" => "\n",
        "\r" => "\n"
    ]);
    // Tab(s) to pad(s)
    if (is_int($tab)) {
        $value = explode("\n", $value);
        foreach ($value as &$v) {
            while (false !== ($pad = strstr($v, "\t", true))) {
                $v = $pad . str_repeat($c, $tab - ($n = strlen($pad)) % $tab) . substr($v, $n + 1);
            }
        }
        unset($v);
        $value = implode("\n", $value);
    // Tab(s) to character(s)
    } else if (is_string($tab)) {
        $value = strtr($value, ["\t" => $tab]);
    }
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
    $value = strtr(preg_replace_callback('/([ ' . ($keep = x($keep)) . ']+)([\p{L}\p{N}' . $keep . '])/u', static function ($m) {
        return $m[1] . u($m[2]);
    }, f(' ' . $value, $accent, $keep ?? "") ?? ""), [' ' => ""]);
    return "" !== $value ? $value : null;
}

function q($value) {
    if (true === $value) {
        return 1;
    }
    if (false === $value || null === $value) {
        return 0;
    }
    if (is_countable($value)) {
        return count($value);
    }
    if (is_int($value) || is_float($value)) {
        return $value;
    }
    if (is_object($value)) {
        if ($value instanceof EmptyIterator) {
            return 0;
        }
        if ($value instanceof stdClass) {
            return count((array) $value);
        }
        if ($value instanceof Traversable) {
            if ($r = method_exists($value, 'rewind')) {
                $value->rewind();
            }
            $v = iterator_count($value);
            $r && $value->rewind();
            return $v;
        }
        return 1;
    }
    if (is_string($value)) {
        return extension_loaded('mbstring') ? mb_strlen($value) : strlen($value);
    }
    return empty($value) ? 0 : 1;
}

function r(?string $value, $from, $to = null) {
    if ("" === ($value ?? "")) {
        return null;
    }
    // `r('…', '…', '…')`
    if (is_string($from) && is_string($to)) {
        return "" !== ($value = strtr($value, [$from => $to])) ? $value : null;
    }
    // `r('…', ['…' => '…'])`
    if (null === $to) {
        return "" !== ($value = strtr($value, $from)) ? $value : null;
    }
    // `r('…', ['…', '…'], function ($c) { … })`
    if (is_callable($to)) {
        usort($from, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });
        $i = 0;
        $max = strlen($value);
        $r = "";
        while ($i < $max) {
            $done = false;
            foreach ($from as $v) {
                if (($n = strlen($v)) && $v === substr($value, $i, $n)) {
                    $done = true;
                    $i += $n;
                    $r .= call_user_func($to, $v);
                    break;
                }
            }
            if (!$done) {
                $r .= $value[$i++];
            }
        }
        return "" !== $r ? $r : null;
    }
    // `r('…', ['…', '…'], '…')`
    if (is_string($to)) {
        $to = array_fill(0, count($from), $to);
    }
    // `r('…', ['…', '…'], ['…', '…'])`
    return "" !== ($value = strtr($value, array_combine($from, array_replace($from, $to)))) ? $value : null;
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

function t(?string $value, string $open = '"', ?string $close = null) {
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
    // Should be a HTML input (if `$keep` is not empty, assume that input is a HTML anyway)
    if ($keep || false !== strpos($value, '<') || false !== strpos($value, ' ') || false !== strpos($value, "\n")) {
        $keep = is_string($keep) ? explode(',', $keep) : (array) $keep;
        $value = trim(preg_replace($break ? '/[ \t]+/' : '/\s+/', ' ', strip_tags($value, $keep)));
        return "" !== $value ? $value : null;
    }
    // Remove `-`, `.`, `_`, and `~` prefix/suffix from the file name then replace `-` with a space. If you want to keep
    // the `-`, be sure to write it as `%2D` in the file name. Then, it decodes the file name as if it were an URL.
    $value = trim(rawurldecode(preg_replace('/[-]+/', ' ', trim($value, '-._~'))));
    return "" !== $value ? $value : null;
}

function x(?string $value, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
    return "" !== ($value = addcslashes($value ?? "", $c . $d)) ? $value : null;
}

function y(iterable $value, $deep = 0) {
    if ($value instanceof Traversable) {
        $value = iterator_to_array($value);
        if (true === $deep) {
            foreach ($value as &$v) {
                $v = is_iterable($v) || $v instanceof stdClass ? y($v, $deep) : $v;
            }
            unset($v);
            return $value;
        }
        if ($deep > 0) {
            foreach ($value as &$v) {
                $v = is_iterable($v) || $v instanceof stdClass ? y($v, $deep - 1) : $v;
            }
            unset($v);
            return $value;
        }
        return $value;
    }
    return (array) $value;
}

function z($value, $short = true) {
    if (is_object($value)) {
        if ($value instanceof stdClass) {
            return '(object)' . z((array) $value, $short);
        }
        if (method_exists($value, '__set_state')) {
            $content = $stack = "";
            foreach (array_slice(token_get_all('<?php ' . var_export($value, true)), 1) as $v) {
                if (is_array($v)) {
                    if (T_CONSTANT_ENCAPSED_STRING === $v[0]) {
                        $content .= z(substr($v[1], 1, -1), $short);
                        continue;
                    }
                    // TODO: Prefer this one once we drop PHP 7.3 support!
                    // if (T_NAME_FULLY_QUALIFIED === $v[0]) {
                    if ('T_NAME_FULLY_QUALIFIED' === token_name($v[0])) {
                        $content .= trim($v[1], "\\");
                        continue;
                    }
                    if (T_WHITESPACE === $v[0]) {
                        continue;
                    }
                    $content .= "''" === ($v = $v[1]) ? '""' : ('NULL' === $v ? 'null' : $v);
                    continue;
                }
                if ('(' === $v) {
                    if ($short && 'array' === substr($content, -5)) {
                        $content = substr($content, 0, -5) . '[';
                        $stack .= '[';
                        continue;
                    }
                    $content .= $v;
                    $stack .= '(';
                    continue;
                }
                if (')' === $v) {
                    $content = trim($content, ',');
                    if ($short && '[' === substr($stack, -1)) {
                        $content .= ']';
                        $stack = substr($stack, 0, -1);
                        continue;
                    }
                    $content .= $v;
                    $stack = substr($stack, 0, -1);
                    continue;
                }
                $content .= $v;
            }
            return $content;
        }
        return 'new ' . get_class($value); // Broken :(
    }
    if (is_array($value)) {
        $out = [];
        if (array_is_list($value)) {
            foreach ($value as $k => $v) {
                $out[] = z($v, $short);
            }
        } else {
            foreach ($value as $k => $v) {
                $out[] = z($k, $short) . '=>' . z($v, $short);
            }
        }
        return ($short ? '[' : 'array(') . implode(',', $out) . ($short ? ']' : ')');
    }
    $value = var_export($value, true);
    if ("''" === $value) {
        return '""';
    }
    if ('NULL' === $value) {
        return 'null';
    }
    $test = substr($value, 1);
    if (0 === strpos($test, $v = ENGINE . D)) {
        $value = "ENGINE.D.'" . strtr(substr($test, strlen($v)), [D => "'.D.'"]);
    } else if (0 === strpos($test, $v = LOT . D)) {
        $value = "LOT.D.'" . strtr(substr($test, strlen($v)), [D => "'.D.'"]);
    } else if (0 === strpos($test, $v = PATH . D)) {
        $value = "PATH.D.'" . strtr(substr($test, strlen($v)), [D => "'.D.'"]);
    }
    return $value;
}