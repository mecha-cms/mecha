<?php

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
final class IteratorG implements Countable, Iterator {
    private $i = 0;
    private $it;
    private $lot = [];
    private $valid = true;
    public function __construct(Iterator $it) {
        $this->it = $it;
    }
    public function count(): int {
        if ($this->valid) {
            foreach ($this as $v) {}
            $this->i = 0;
        }
        return count($this->lot);
    }
    public function current(): mixed {
        return $this->valid() ? $this->lot[$this->i][0] : null;
    }
    public function key(): mixed {
        return $this->valid() ? $this->lot[$this->i][1] : null;
    }
    public function next(): void {
        ++$this->i;
    }
    public function rewind(): void {
        $this->i = 0;
    }
    public function valid(): bool {
        if (isset($this->lot[$this->i])) {
            return true;
        }
        if (!$this->valid) {
            return false;
        }
        $it = $this->it;
        if (!$it->valid()) {
            $this->valid = false;
            return false;
        }
        $this->lot[] = [$it->current(), $it->key()];
        $it->next();
        return true;
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
    $r = [];
    $s = "";
    if ("" === $value) {
        return $r;
    }
    $c1 = " \n\r\t";
    $c2 = '0123456789';
    $c3 = 'abcdef';
    $c4 = 'ABCDEF';
    $c5 = $c3 . 'ghijklmnopqrstuvwxyz';
    $c6 = $c4 . 'GHIJKLMNOPQRSTUVWXYZ';
    $c7 = $c1 . '>';
    $i = 0;
    $max = strlen($value);
    $raw = $raw ? array_flip($raw) : [];
    $void = $void ? array_flip($void) : [];
    while ($i < $max) {
        $c = $value[$i];
        $next = $i + 1 < $max ? $value[$i + 1] : "\0";
        // <https://www.w3.org/TR/xml#sec-references>
        if ('&' === $c && false === strpos($c1, $next)) {
            $n = $i + 1;
            if ($n < $max && '#' === $value[$n]) {
                ++$n;
                if ($n < $max && ('X' === $value[$n] || 'x' === $value[$n])) {
                    $m = strspn($value, $c2 . $c3 . $c4, $n += 1);
                } else {
                    $m = strspn($value, $c2, $n);
                }
                if ($m && $n + $m < $max && ';' === $value[$n + $m]) {
                    "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                    $r[] = [substr($value, $i, $n + $m - $i + 1), -1];
                    $i = $n + $m + 1;
                    continue;
                }
            } else {
                if ($m = strspn($value, $c5 . $c6, $n)) {
                    $n += $m;
                    $n += strspn($value, $c2 . $c5 . $c6, $n);
                    if ($n < $max && ';' === $value[$n]) {
                        "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                        $r[] = [substr($value, $i, $n - $i + 1), -1];
                        $i = $n + 1;
                        continue;
                    }
                }
            }
        }
        if ('<' === $c && false === strpos($c7, $next)) {
            if ('!' === $next) {
                // <https://www.w3.org/TR/xml#sec-comments>
                if (0 === substr_compare($value, '<!--', $i, 4)) {
                    if (false !== ($z = strpos($value, '-->', $i + 4))) {
                        "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                        $r[] = [substr($value, $i, $z - $i + 3), 1];
                        $i = $z + 3;
                        continue;
                    }
                }
                // <https://www.w3.org/TR/xml#sec-cdata-sect>
                if (0 === substr_compare($value, '<![CDATA[', $i, 9)) {
                    if (false !== ($n = strpos($value, ']]>', $i + 9))) {
                        "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                        $r[] = [substr($value, $i, $n - $i + 3), 1];
                        $i = $n + 3;
                        continue;
                    }
                }
                // <https://www.w3.org/TR/xml#sec-entexpand>
                // <https://www.w3.org/TR/xml#vc-roottype>
                $n = $i + 2;
                if ($n >= $max || false !== strpos($c7, $value[$n])) {
                    $s .= '<';
                    ++$i;
                    continue;
                }
                $deep = $q = 0;
                $n = $i + 2;
                while ($n < $max) {
                    $v = $value[$n];
                    if ($q) {
                        $q === $v && ($q = 0);
                    } else {
                        if ("'" === $v || '"' === $v) {
                            $q = $v;
                        } else if ('[' === $v) {
                            ++$deep;
                        } else if (']' === $v && $deep > 0) {
                            --$deep;
                        } else if ('>' === $v && 0 === $deep) {
                            break;
                        }
                    }
                    ++$n;
                }
                if ($n < $max) {
                    "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                    $r[] = [substr($value, $i, $n - $i + 1), 1];
                    $i = $n + 1;
                    continue;
                }
            }
            // <https://www.w3.org/TR/xml#sec-pi>
            if ('?' === $next) {
                $n = $i + 2;
                $q = 0;
                while ($n < $max) {
                    $v = $value[$n];
                    if ($q) {
                        $q === $v && ($q = 0);
                    } else {
                        if ("'" === $v || '"' === $v) {
                            $q = $v;
                        } else if ('?' === $v && $n + 1 < $max && '>' === $value[$n + 1]) {
                            break;
                        }
                    }
                    ++$n;
                }
                if ($n < $max) {
                    "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                    $r[] = [substr($value, $i, $n - $i + 2), 1];
                    $i = $n + 2;
                    continue;
                }
            }
            // <https://www.w3.org/TR/xml#sec-starttags>
            $n = $i + 1;
            $q = 0;
            while ($n < $max) {
                $v = $value[$n];
                if ($q) {
                    $q === $v && ($q = 0);
                } else {
                    if ("'" === $v || '"' === $v) {
                        $q = $v;
                    } else if ('>' === $v) {
                        break;
                    }
                }
                ++$n;
            }
            $start = $n - $i + 1;
            if ($n < $max) {
                $z = $i + 1;
                $z += strspn($value, $c1, $z, $n - $z);
                $m = strlen($name = substr($value, $z, min(strcspn($value, $c1 . '/>', $z), $n - $z)));
                if (isset($raw[$name])) {
                    $n += 1;
                    while (1) {
                        // Raw HTML is left open for some reason until the end of the stream
                        if ("" !== $name && false === ($z = strpos($value, '</' . $name, $n))) {
                            "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                            $r[] = [substr($value, $i), 1, $start, null];
                            return $r;
                        }
                        $y = $z + 2 + $m;
                        $y += strspn($value, $c1, $y);
                        if ($y < $max && '>' === $value[$y]) {
                            "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                            $r[] = [substr($value, $i, $y - $i + 1), 1, $start, -($y - $z + 1)];
                            $i = $y + 1;
                            continue 2;
                        }
                        $n = max($y, $z + 1);
                    }
                }
                "" !== $s && ($r[] = [$s, 0]) && ($s = "");
                $x = $n - 1;
                while ($x > $i && false !== strpos($c1, $value[$x])) --$x;
                // <https://www.w3.org/TR/xml#d0e2480>
                $v = [substr($value, $i, $start), 2, $start];
                if (isset($void[$name])) {
                    $v[1] = 1;
                    $v[3] = false;
                } else if ($x > $i && '/' === $value[$x]) {
                    $v[1] = 1;
                    $v[3] = true;
                }
                $r[] = $v;
                $i = $n + 1;
                continue;
            }
        }
        $s .= $c;
        ++$i;
    }
    "" !== $s && ($r[] = [$s, 0]) && ($s = "");
    return $r;
}

// Convert class name to file name
function c2f(?string $value, $accent = false) {
    $value = implode(D, map(explode(P, strtr($value ?? "", "\\/", P . P)), static function ($v) use ($accent) {
        return ltrim(strtr(h($v, '-', $accent, '_') ?? "", [
            '_-' => '_',
            '__-' => '.',
            '__' => '.'
        ]), '-');
    }));
    return "" !== $value ? $value : null;
}

function check(string $token, $key = 0) {
    $past = $_SESSION['token'][$key ?? bin2hex(random_bytes(8))] ?? [0, ""];
    return $past[1] && $token && $past[1] === $token ? $token : false;
}

function choke(int $for = 1, $key = 0) {
    $current = $_SERVER['REQUEST_TIME'];
    $key = md5((string) ($key ?? bin2hex(random_bytes(8))));
    if (!is_file($file = ENGINE . D . 'log' . D . 'choke' . D . $key)) {
        if (!is_dir($folder = dirname($file))) {
            mkdir($folder, 0775, true);
        }
        touch($file, $current);
        return $for;
    }
    $past = filemtime($file);
    if ($for > $current - $past) {
        return $for - ($current - $past);
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

function content(string $path, ?string $value = null, $seal = null) {
    if (null !== $value) {
        if (is_dir($path) || (is_file($path) && !is_writable($path))) {
            return false;
        }
        if (!is_dir($folder = dirname($path))) {
            mkdir($folder, 0775, true);
        }
        if (is_int(file_put_contents($path, $value))) {
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
            if (0 === strpos($k, '+')) {
                $cookie[$k] = json_decode(base64_decode($v), true);
            } else {
                $cookie[$k] = $v;
            }
        }
        return $cookie;
    }
    $key = array_shift($lot);
    if (0 === count($lot)) {
        if (isset($_COOKIE[$k = '+' . substr(md5($key), 0, 16)])) {
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
    setcookie('+' . substr(md5($key), 0, 16), base64_encode(json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)), $with);
}

function delete(string $path, $purge = true) {
    if (is_dir($path)) {
        $it = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $r = [];
        foreach (new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST) as $k) {
            $v = path($k->getPathname());
            if ($k->isDir()) {
                $r[$v] = rmdir($v) ? 0 : null;
            } else {
                $r[$v] = unlink($v) ? 1 : null;
            }
        }
        $r[path($path)] = rmdir($path) ? 0 : null;
        return $r;
    }
    if (is_file($path)) {
        // Success?
        $r = unlink($path) ? path($path) : null;
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
        return $r;
    }
    return null;
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
    $test = match ($type) {
        0 => 'is_dir',
        1 => 'is_file',
        default => null
    };
    foreach ((array) $path as $v) {
        if (!$v) {
            continue;
        }
        if ($r = stream_resolve_include_path($v)) {
            if ($test && !$test($r)) {
                continue;
            }
            return $r;
        }
        if (strcspn($v, '*?[]{}') !== strlen($v)) {
            if (!($r = glob($v, GLOB_BRACE | GLOB_NOSORT)) || !($r = reset($r))) {
                continue;
            }
            if ($test && !$test($r)) {
                continue;
            }
            return $r;
        }
    }
    return false;
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
    $value = implode("\\", map(explode(P, strtr($value ?? "", "\\/", P . P)), static function ($v) use ($accent) {
        return p(strtr($v, ['.' => '__']), $accent, '_');
    }));
    return "" !== $value ? $value : null;
}

// Convert file name to property name
function f2p(?string $value, $accent = false) {
    $value = implode("\\", map(explode(P, strtr($value ?? "", "\\/", P . P)), static function ($v) use ($accent) {
        return c(strtr($v, ['.' => '__']), $accent, '_');
    }));
    return "" !== $value ? $value : null;
}

function fetch(string $from, $lot = null, $data = "") {
    $h = [];
    if (is_array($lot)) {
        foreach ($lot as $k => $v) {
            if (false === $v || null === $v) {
                continue;
            }
            $k = strtolower($k);
            if (is_array($v)) {
                foreach ($v as $kk => $vv) {
                    $h[$k . P . $kk] = $k . ': ' . s($vv);
                }
                continue;
            }
            $h[$k] = $k . ': ' . s($v);
        }
    } else if (is_string($lot)) {
        $h[$k = 'user-agent'] = $k . ': ' . $lot;
    }
    if (empty($h[$k = 'user-agent'])) {
        // <https://www.rfc-editor.org/rfc/rfc7231#section-5.5.3>
        $port = (int) ($_SERVER['SERVER_PORT'] ?? 0);
        $s = !empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === $port ? 's' : "";
        $h[$k] = $k . ': Mecha/' . VERSION . ' (+http' . $s . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "") . ')';
    }
    // `fetch('POST http://127.0.0.1/asdf')`
    if (strpos($from = trim($from), '/') > ($n = strcspn($from, " \n\r\t"))) {
        $link = trim(substr($from, $n + 1));
        $type = strtoupper(substr($from, 0, $n));
    // `fetch('http://127.0.0.1/asdf')`
    } else {
        $link = $from;
        $type = 'GET';
    }
    if (extension_loaded('curl')) {
        $c = curl_init($link);
        if ('HEAD' === $type) {
            curl_setopt($c, CURLOPT_HEADER, true);
            curl_setopt($c, CURLOPT_NOBODY, true);
        } else if (false !== strpos(',PATCH,POST,PUT,', ',' . $type . ',')) {
            $data_with_blob = "";
            $has_blob = false;
            if (is_array($data)) {
                $task = function ($data, $prefix = "") use (&$has_blob, &$task) {
                    $r = [];
                    foreach ($data as $k => $v) {
                        $kk = $prefix ? $prefix . '[' . $k . ']' : $k;
                        if (is_array($v)) {
                            $r += $task($v, $kk);
                            continue;
                        }
                        if (is_string($v) && is_file($v = 0 === strpos($v, ".\\") ? PATH . strtr(substr($v, 1), "\\", D) : $v)) {
                            $has_blob = true;
                            $r[$kk] = new CURLFile($v, mime_content_type($v) ?: 'application/octet-stream', basename($v));
                            continue;
                        }
                        $r[$kk] = $v;
                    }
                    return $r;
                };
                $data_with_blob = $task($data);
            }
            if ('application/json' === ($h['content-type'] ?? 0)) {
                $data = json_encode($data);
            } else if (!$has_blob && is_array($data)) {
                $data = http_build_query($data);
            }
            curl_setopt($c, CURLOPT_POSTFIELDS, $has_blob ? $data_with_blob : $data);
        }
        $h[$k = 'x-requested-with'] = $h[$k] ?? ($k . ': PHP cURL');
        curl_setopt_array($c, [
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => array_values($h),
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15
        ]);
        if (false !== ($r = curl_exec($c))) {
            if ('HEAD' === $type) {
                $h = explode("\n", n(trim($r)));
                $r = array_shift($h) . "\n";
                foreach ($h as $v) {
                    $r .= strtolower(strstr($v, ':', true)) . strstr($v, ':') . "\n";
                }
                $r = substr($r, 0, -1);
            }
        }
        if (defined('TEST') && 'curl' === TEST && false === $r) {
            throw new UnexpectedValueException(curl_error($c));
        }
        curl_close($c);
    } else {
        if ('HEAD' === $type) {
            $h = get_headers($link, true, stream_context_set_default(['http' => ['method' => $type]]));
            $h = array_change_key_case((array) $h, CASE_LOWER);
            // If `$link` is redirected and the new target is also redirected, we got the `Location` data as array.
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
            $r = [];
            if (isset($h[0]) && isset($h[1])) {
                foreach ($h as $k => $v) {
                    $k = is_int($k) ? $k : strtolower($k);
                    if (is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            $r[$kk][] = $k . ': ' . $vv;
                        }
                        continue;
                    }
                    $r[0][] = (is_int($k) ? "\n" : $k . ': ') . $v;
                }
                foreach ($r as &$v) {
                    $v = implode("\n", $v);
                }
                unset($v);
            } else {
                foreach ($h as $k => $v) {
                    $r[] = (is_int($k) ? "\n" : strtolower($k) . ': ') . (is_array($v) ? end($v) : $v);
                }
            }
            $r = trim(implode("\n", $r));
        } else {
            $h[$k = 'x-requested-with'] = $h[$k] ?? ($k . ': PHP');
            $x = [];
            if (false !== strpos(',PATCH,POST,PUT,', ',' . $type . ',')) {
                if ('application/json' === ($h['content-type'] ?? 0)) {
                    $data = json_encode($data);
                } else if (is_array($data)) {
                    $data = http_build_query($data);
                }
                $h[$k = 'content-type'] = $h[$k] ?? ($k . ': application/x-www-form-urlencoded');
                $x['http']['content'] = $data;
            }
            $x['http']['header'] = implode("\r\n", array_values($h));
            $x['http']['ignore_errors'] = true;
            $x['http']['method'] = $type;
            $x['ssl']['verify_peer'] = false;
            $x['ssl']['verify_peer_name'] = false;
            $r = file_get_contents($link, false, stream_context_create($x));
        }
    }
    return false !== $r ? $r : null;
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

function get(array|ArrayAccess $from, string $key, string $join = '.') {
    if (!$from || 0 === q($from)) {
        return null;
    }
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    foreach ($keys as $k) {
        $k = strtr($k, [P => $join]);
        if ($from instanceof ArrayAccess) {
            if (!$from->offsetExists($k)) {
                return null;
            }
            $from = $from->offsetGet($k);
            continue;
        }
        if (is_array($from)) {
            if (!array_key_exists($k, $from)) {
                return null;
            }
            $from = $from[$k];
            continue;
        }
        return null;
    }
    return $from;
}

function gt($a, $b) {
    return q($a) > $b;
}

function has(array|ArrayAccess $from, string $key, string $join = '.') {
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    foreach ($keys as $k) {
        $k = strtr($k, [P => $join]);
        if ($from instanceof ArrayAccess) {
            if (!$from->offsetExists($k)) {
                return false;
            }
            $from = $from->offsetGet($k);
            continue;
        }
        if (is_array($from)) {
            if (!array_key_exists($k, $from)) {
                return false;
            }
            $from = $from[$k];
            continue;
        }
        return false;
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

function let(array|ArrayAccess &$from, string $key, string $join = '.') {
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    $k = strtr(array_pop($keys), [P => $join]);
    foreach ($keys as $k) {
        $k = strtr($k, [P => $join]);
        if ($from instanceof ArrayAccess) {
            if (!$from->offsetExists($k)) {
                return false;
            }
            $from = $from->offsetGet($k);
            continue;
        }
        if (is_array($from)) {
            if (!array_key_exists($k, $from)) {
                return false;
            }
            $from =& $from[$k];
            continue;
        }
        return false;
    }
    if ($from instanceof ArrayAccess) {
        if (!$from->offsetExists($k)) {
            return false;
        }
        $from->offsetUnset($k);
        return true;
    }
    if (is_array($from)) {
        if (!array_key_exists($k, $from)) {
            return false;
        }
        unset($from[$k]);
        return true;
    }
    return false;
}

function &lot(...$lot) {
    static $test;
    $test ??= function (string $name) {
        if ("" === $name) {
            return false;
        }
        // <https://www.php.net/manual/en/language.variables.php>
        static $a, $b;
        if (!isset($a)) {
            $a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_' . implode("", range("\x80", "\xff"));
            $b = $a . '0123456789';
        }
        if (1 !== strspn($name[0], $a)) {
            return false;
        }
        return strlen($name) - 1 === strspn($name, $b, 1);
    };
    if (0 === ($max = count($lot)) || 1 === $max && is_array($lot[0])) {
        $r = [];
        foreach (array_replace($GLOBALS, $lot[0] ?? []) as $k => $v) {
            if ($test($k)) {
                $r[$k] = $v;
            }
        }
        return $r;
    }
    if (1 === $max && $test($lot[0])) {
        if (!array_key_exists($lot[0], $GLOBALS)) {
            $GLOBALS[$lot[0]] = null;
        }
        return $GLOBALS[$lot[0]];
    }
    $r =& $lot[1];
    if ($test($lot[0])) {
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
    $r = [];
    if (!is_dir($path) && !is_file($path)) {
        return [null, null];
    }
    $path = path($path);
    // Move a file to a folder
    if (is_file($r[0] = $path)) {
        if (is_file($file = $to . D . ($as ?? basename($path)))) {
            // Return `false` if file exists
            $r[1] = false;
        } else {
            if (!is_dir($folder = dirname($file))) {
                mkdir($folder, 0775, true);
            }
            // Return `$file` on success, `null` on error
            $r[1] = rename($path, $file) ? path($file) : null;
        }
        return $r;
    }
    // Move a folder with its contents to a folder
    $r = [$path, null];
    if (!is_dir($to)) {
        return $r;
    }
    $r[1] = [];
    if (!is_dir($to .= D . ($as ?? basename($path)))) {
        mkdir($to, 0775, true);
    }
    if ($path === ($to = path($to))) {
        return $r; // Nothing to move
    }
    foreach (g($path, 1, true) as $k => $v) {
        $file = $to . D . substr($k, strlen($path) + 1);
        if (!is_dir($folder = dirname($file))) {
            $r[1][$folder] = mkdir($folder, 0775, true) ? 0 : null;
        }
        $r[1][$file] = rename($k, $file) ? 1 : null;
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
    return $r;
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
    $value = implode(D, map(explode(P, strtr($value ?? "", "\\/", P . P)), static function ($v) use ($accent) {
        return strtr(h($v, '-', $accent, '_') ?? "", ['__' => '.']);
    }));
    return "" !== $value ? $value : null;
}

function pair(string $value) {
    $i = strspn($value, $c = " \n\r\t");
    $max = strlen($value);
    $r = [];
    if ($i >= $max) {
        return $r;
    }
    while ($i < $max) {
        if (0 === ($n = strcspn($value, $c . '=', $i))) {
            break;
        }
        $k = substr($value, $i, $n);
        $i += $n;
        $i += strspn($value, $c, $i);
        if ($i >= $max || '=' !== $value[$i]) {
            $r[$k] = true;
            continue;
        }
        ++$i;
        if ($i >= $max || false !== strpos($c, $value[$i])) {
            $r[$k] = "";
            $i += strspn($value, $c, $i);
            continue;
        }
        if ($i < $max) {
            $q = $value[$i];
            if ("'" === $q || '"' === $q) {
                ++$i;
                // Attribute value is left open for some reason until the end of the stream
                if (false === ($n = strpos($value, $q, $i))) {
                    $r[$k] = substr($value, $i);
                    return $r;
                }
                $r[$k] = substr($value, $i, $n - $i);
                $i = $n + 1;
                $i += strspn($value, $c, $i);
                continue;
            }
        }
        if ($n = strcspn($value, $c, $i)) {
            $r[$k] = substr($value, $i, $n);
            $i += $n;
        } else {
            $r[$k] = "";
        }
        $i += strspn($value, $c, $i);
    }
    return $r;
}

function path(?string $value) {
    if (0 === strpos($value = strtr($value ?? "", '/', D), D) && 0 !== strpos($value, PATH . D)) {
        $value = PATH . $value;
    }
    return stream_resolve_include_path(rtrim($value, D)) ?: null;
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

function queue(iterable $value, callable $at, $sort = -1, $keys = false, $that = null) {
    $count = $i = 0;
    $it = new class extends SplPriorityQueue {
        public $sort = -1;
        public function compare($a, $b): int {
            return -1 === ($d = $this->sort) || SORT_DESC === $d ? $a <=> $b : $b <=> $a;
        }
    };
    $it->setExtractFlags(SplPriorityQueue::EXTR_DATA);
    $it->sort = $sort;
    $max = PHP_INT_MAX;
    foreach ($value as $k => $v) {
        $it->insert($v = [$v, $keys ? $k : null], [fire($at, $v, $that) ?? PHP_INT_MIN, --$max]);
        ++$count;
    }
    $r = $keys ? new ArrayIterator : new SplFixedArray($count);
    while (!$it->isEmpty()) {
        $v = $it->extract();
        $r[$v[1] ?? $i++] = $v[0];
    }
    return $r;
}

function save(string $path, string $value, $seal = null) {
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

function set(array|ArrayAccess &$to, string $key, $value = null, string $join = '.') {
    $keys = explode($join, strtr($key, ["\\" . $join => P]));
    if (is_array($to)) {
        $key = array_pop($keys);
        $r =& $to;
        foreach ($keys as $k) {
            $k = strtr($k, [P => $join]);
            // In the case that the container is not valid, treat it as an empty array
            if (!array_key_exists($k, $r) || !is_array($r[$k])) {
                $r[$k] = [];
            }
            $r =& $r[$k];
        }
        return ($r[strtr($key, [P => $join])] = $value);
    }
    $r = $to;
    $stack = [];
    foreach ($keys as $k) {
        $k = strtr($k, [P => $join]);
        if ($r instanceof ArrayAccess) {
            $stack[] = [$r, $k];
            if (!$r->offsetExists($k)) {
                $r[$k] = [];
            }
            $r = $r[$k];
            continue;
        }
        if (is_array($r)) {
            $stack[] = [$r, $k];
            if (!array_key_exists($k, $r)) {
                $r[$k] = [];
            }
            $r = $r[$k];
            continue;
        }
        if (!$stack) {
            $r = [];
            continue;
        }
        // In the case that the container is not valid, treat it as an empty array
        [$v, $k] = array_pop($stack);
        if ($v instanceof ArrayAccess) {
            $v->offsetSet($k, $r = []);
        } else {
            $v[$k] = $r = [];
        }
        $stack[] = [$v, $k];
    }
    $r = $value;
    for ($i = count($stack) - 1; $i >= 0; --$i) {
        [$v, $k] = $stack[$i];
        if ($v instanceof ArrayAccess) {
            $v->offsetSet($k, $r);
        } else {
            $v[$k] = $r;
        }
        $r = $v;
    }
    return $value;
}

function shake(iterable $value, $keys = false, $that = null, $scope = 'static') {
    if (is_callable($keys)) {
        // `$keys` as `$task`
        return fire($keys, [$value], $that, $scope);
    }
    if (!is_array($value)) {
        return queue($value, function () {
            return random_int(0, PHP_INT_MAX);
        }, -1, $keys);
    }
    // <https://www.php.net/manual/en/function.shuffle.php#94697>
    if ($keys) {
        $keys = array_keys($value);
        $values = [];
        shuffle($keys);
        foreach ($keys as $key) {
            $values[$key] = $value[$key];
        }
        return $values;
    }
    shuffle($value);
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
    $v = round($size / pow($base, $u), $fix);
    return $v < 0 ? null : trim($v . ' ' . ($x[$u] ?? ""));
}

function status(...$lot) {
    if (0 === count($lot)) {
        $r = [http_response_code(), [], []];
        foreach ($_SERVER as $k => $v) {
            if (0 === strpos($k, 'HTTP_')) {
                $r[1][strtolower(strtr(substr($k, 5), '_', '-'))] = e($v);
            }
        }
        if (function_exists('apache_request_headers')) {
            $r[1] = e(array_change_key_case((array) apache_request_headers(), CASE_LOWER));
        }
        if (function_exists('apache_response_headers')) {
            $r[2] = e(array_change_key_case((array) apache_response_headers(), CASE_LOWER));
        }
        foreach (headers_list() as $v) {
            $v = explode(':', $v, 2);
            if (isset($v[1])) {
                $vv = e(trim($v[1]));
                if (isset($r[2][$k = strtolower($v[0])]) && $vv !== $r[2][$k]) {
                    $r[2][$k] = (array) $r[2][$k];
                    $r[2][$k][] = $vv;
                    continue;
                }
                $r[2][$k] = $vv;
            }
        }
        return $r;
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

function token($key = 0, $for = '+1 minute') {
    $past = $_SESSION['token'][$key] ?? [0, ""];
    if ($past[0] > time()) {
        return $past[1];
    }
    $t = is_string($for) ? strtotime($for) : time() + $for;
    $_SESSION['token'][$key] = $v = [$t, bin2hex(random_bytes(16) . $key)];
    return $v[1];
}

function type(?string $type = null) {
    if (!isset($type)) {
        $t = status();
        $type = $t[1]['content-type'] ?? $t[2]['content-type'] ?? null;
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
    if (!is_dir($folder)) {
        return new IteratorG(new EmptyIterator);
    }
    $it = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME);
    $it = new RecursiveIteratorIterator($it, 0 === $x || null === $x ? RecursiveIteratorIterator::CHILD_FIRST : RecursiveIteratorIterator::LEAVES_ONLY);
    $it->setMaxDepth(true === $deep ? -1 : (int) $deep);
    return new IteratorG((function ($it, $keys, $x) {
        $i = 0;
        foreach ($it as $path) {
            $f = !($d = is_dir($path));
            if (0 === $x && !$d) {
                continue;
            }
            if (1 === $x && !$f) {
                continue;
            }
            if (is_string($x)) {
                if ($d) {
                    continue;
                }
                if (false === strpos(',' . strtolower($x) . ',', ',' . strtolower(pathinfo($path, PATHINFO_EXTENSION)) . ',')) {
                    continue;
                }
            }
            yield ($keys ? $path : $i) => ($keys ? ($d ? 0 : 1) : $path);
            ++$i;
        }
    })($it, $keys, $x));
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
        if ($value !== $raw) { // This prevents recursive function call(s)
            $value = i($value, [], $or);
        }
    } catch (Throwable $e) {}
    return "" !== $value ? $value : null;
}

function j(array $a, array $b) {
    $r = [];
    foreach ($a as $k => $v) {
        if (is_array($v)) {
            if (!array_key_exists($k, $b) || !is_array($b[$k])) {
                $r[$k] = $v;
            } else {
                if ($vv = j($v, $b[$k])) {
                    $r[$k] = $vv;
                }
            }
        } else if (!array_key_exists($k, $b) || $v !== $b[$k]) {
            $r[$k] = $v;
        }
    }
    return $r;
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
    if (is_float($value) || is_int($value)) {
        return $value;
    }
    if (is_object($value)) {
        if ($value instanceof EmptyIterator) {
            return 0;
        }
        if ($value instanceof Traversable) {
            if ($r = method_exists($value, 'rewind')) {
                $value->rewind();
            }
            $v = iterator_count($value);
            $r && $value->rewind();
            return $v;
        }
        if ($value instanceof stdClass) {
            return count(get_object_vars($value));
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
    // Remove `-`, `.`, `_`, and `~` prefix/suffix from the file name then replace `-` with a space
    $value = trim(rawurldecode(preg_replace('/[-]+/', ' ', trim($value, '-._~'))));
    return "" !== $value ? $value : null;
}

function x(?string $value, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
    return "" !== ($value = addcslashes($value ?? "", $c . $d)) ? $value : null;
}

function y(iterable $value, $deep = 0) {
    if ($value instanceof Traversable) {
        $value = iterator_to_array($value, true);
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
                    if (T_NAME_FULLY_QUALIFIED === $v[0]) {
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
        $r = [];
        if (array_is_list($value)) {
            foreach ($value as $k => $v) {
                $r[] = z($v, $short);
            }
        } else {
            foreach ($value as $k => $v) {
                $r[] = z($k, $short) . '=>' . z($v, $short);
            }
        }
        return ($short ? '[' : 'array(') . implode(',', $r) . ($short ? ']' : ')');
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