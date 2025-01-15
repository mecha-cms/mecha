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
        error_reporting(E_ALL);
        ini_set('display_errors', true);
        ini_set('display_startup_errors', true);
        ini_set('html_errors', 1);
    }
}

lot('F', [
    '°' => '0',
    '¹' => '1',
    '²' => '2',
    '³' => '3',
    'À' => 'A',
    'Á' => 'A',
    'Â' => 'A',
    'Ã' => 'A',
    'Ä' => 'AE',
    'Å' => 'A',
    'Æ' => 'AE',
    'Ç' => 'C',
    'È' => 'E',
    'É' => 'E',
    'Ê' => 'E',
    'Ë' => 'E',
    'Ì' => 'I',
    'Í' => 'I',
    'Î' => 'I',
    'Ï' => 'I',
    'Ð' => 'Dj',
    'Ñ' => 'N',
    'Ò' => 'O',
    'Ó' => 'O',
    'Ô' => 'O',
    'Õ' => 'O',
    'Ö' => 'OE',
    'Ø' => 'O',
    'Ù' => 'U',
    'Ú' => 'U',
    'Û' => 'U',
    'Ü' => 'UE',
    'Ý' => 'Y',
    'Þ' => 'TH',
    'ß' => 'ss',
    'à' => 'a',
    'á' => 'a',
    'â' => 'a',
    'ã' => 'a',
    'ä' => 'ae',
    'å' => 'a',
    'æ' => 'ae',
    'ç' => 'c',
    'è' => 'e',
    'é' => 'e',
    'ê' => 'e',
    'ë' => 'e',
    'ì' => 'i',
    'í' => 'i',
    'î' => 'i',
    'ï' => 'i',
    'ð' => 'dj',
    'ñ' => 'n',
    'ò' => 'o',
    'ó' => 'o',
    'ô' => 'o',
    'õ' => 'o',
    'ö' => 'oe',
    'ø' => 'o',
    'ù' => 'u',
    'ú' => 'u',
    'û' => 'u',
    'ü' => 'ue',
    'ý' => 'y',
    'þ' => 'th',
    'ÿ' => 'y',
    '‘' => "'",
    '’' => "'",
    '“' => '"',
    '”' => '"'
]);

// Normalize `$_GET`, `$_POST`, `$_REQUEST` value(s)
$any = [&$_GET, &$_POST, &$_REQUEST];
array_walk_recursive($any, static function (&$v) {
    // Trim white-space and normalize line-break
    $v = trim(strtr($v, ["\r\n" => "\n", "\r" => "\n"]));
    // Replace all empty value with `null` and evaluate other(s)
    $v = "" === $v ? null : e($v);
});

// Normalize `$_FILES` property to `$_POST`
if ('POST' === $_SERVER['REQUEST_METHOD']) {
    // <https://stackoverflow.com/a/30342756/1163000>
    $tidy = static function (array $in) use (&$tidy) {
        $alter = [
            'error' => 'status',
            'tmp_name' => 'path'
        ];
        $out = [];
        if (!is_array(reset($in))) {
            if (isset($in[$k = 'full_path'])) {
                $v = strtr(dirname($in[$k]), ['/' => D]);
                $out['folder'] = "" !== $v && '.' !== $v ? $v : null;
                unset($in[$k]);
            }
            foreach ($in as $k => $v) {
                $out[$alter[$k] ?? $k] = $v;
            }
            return $out;
        }
        if (isset($in[$k = 'full_path'])) {
            foreach ($in[$k] as $kk => $vv) {
                $vv = strtr(dirname($vv), ['/' => D]);
                $out[$kk]['folder'] = "" !== $vv && '.' !== $vv ? $vv : null;
            }
            unset($in[$k]);
        }
        foreach ($in as $k => $v) {
            foreach ($v as $kk => $vv) {
                $out[$kk][$alter[$k] ?? $k] = $vv;
            }
        }
        foreach ($out as &$v) {
            ksort($v);
            $v = $tidy($v);
        }
        unset($v);
        return $out;
    };
    foreach ($_FILES as $k => $v) {
        $_POST[$k] = array_replace_recursive($_POST[$k] ?? [], $tidy($v));
    }
}

// Load class(es)…
d($folder = __DIR__ . D . 'kernel', static function ($object, $n) use ($folder) {
    if (is_file($f = dirname($folder) . D . 'plug' . D . $n . '.php')) {
        extract(lot(), EXTR_SKIP);
        require $f;
    }
    // Load plug(s) of extension(s) and layout(s)…
    foreach (glob(dirname($folder, 2) . D . 'lot' . D . '{x,y}' . D . '*' . D . 'engine' . D . 'plug' . D . $n . '.php', GLOB_BRACE | GLOB_NOSORT) as $v) {
        if (!is_file(dirname($v = stream_resolve_include_path($v), 3) . D . 'index.php')) {
            continue; // Skip in-active extension(s) and layout(s)
        }
        require $v;
    }
});

// Add a hook that will execute just before the response body is sent
header_register_callback(static function () {
    Hook::fire('enter');
});

// Add a hook that will execute just after the response body is sent
register_shutdown_function(static function () {
    // This hook will also execute when the application is forced to stop by using the `exit` or `die` command
    Hook::fire('exit');
});

// Set default state(s)…
$state = is_file($f = __DIR__ . D . '..' . D . 'state.php') ? require $f : [];

lot('state', $state = new State($state));

$port = (int) $_SERVER['SERVER_PORT'];
$scheme = 'http' . (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === $port ? 's' : "");
$protocol = $scheme . '://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";

[$path, $query] = array_replace(["", ""], explode('?', $_SERVER['REQUEST_URI'], 2));

// Prevent cross-site script attack
$path = strtr(trim($path, '/'), [
    '<' => '%3C',
    '>' => '%3E',
    '&' => '%26',
    '"' => '%22'
]);

// Prevent directory traversal attack
while (false !== strpos($path, '../')) {
    $path = strtr($path, ['../' => ""]);
}

// If server root is `.\srv\http` and you have this system installed in `.\srv\http\a\b\c`
// then the sub-folder path of this system will be `a\b\c`
$sub = trim(strtr(PATH . D, [rtrim(strtr($_SERVER['DOCUMENT_ROOT'] . D, '/', D), D) . D => ""]), D);

// Remove sub-folder from path
$path = "" !== $sub ? substr($path, strlen($sub) + 1) : $path;

$path = "" !== $path ? '/' . $path : null;
$query = "" !== $query ? '?' . $query : null;
$hash = !empty($_COOKIE['hash']) ? '#' . $_COOKIE['hash'] : null;

lot('url', $url = new URL($protocol . $host . $path . $query . $hash));

function anemone(...$lot) {
    return Anemone::from(...$lot);
}

function hook(...$lot) {
    return count($lot) < 2 ? Hook::get(...$lot) : Hook::set(...$lot);
}

function kick(?string $path = null, ?int $status = null) {
    $path = Hook::fire('kick', [$path, $status]);
    header('location: ' . $path, true, $status ?? 301);
    exit;
}

function long(string $value) {
    $url = lot('url');
    $r = (string) $url;
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
        return rtrim($r . $value, '/');
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
        if ($value && false === strpos('.?&#', $value[0]) && $parent !== $r) {
            $parent = dirname($parent);
        }
        if (0 !== ($count = substr_count($value . '/', '../'))) {
            while ($count && $parent !== $r) {
                $parent = dirname($parent);
                --$count;
            }
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
    $url = lot('url');
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
    return "" === $value ? '/' : (0 === strpos($value, '&') ? '?' . substr($value, 1) : $value);
}

function state(...$lot) {
    if (count($lot) < 2) {
        $lot[] = true; // Force to array
        return State::get(...$lot);
    }
    return State::set(...$lot);
}

try {
    $uses = [];
    foreach (glob(dirname(__DIR__) . D . 'lot' . D . '{x,y}' . D . '*' . D . 'index.php', GLOB_BRACE | GLOB_NOSORT) as $v) {
        $n = basename($folder = dirname($v = stream_resolve_include_path($v)));
        if (empty(lot(strtoupper($r = basename(dirname($folder))))[0][$v])) {
            $uses[$v] = content($folder . D . $n) ?? $r . '.' . $n;
            // Load state(s)…
            State::set($r . '.' . ($k = strtr($n, ['.' => "\\."])), []);
            if (is_file($v = $folder . D . 'state.php')) {
                (static function ($k, $v, $a) {
                    extract(lot(), EXTR_SKIP);
                    State::set($r . '.' . $k, array_replace_recursive((array) require $v, $a));
                })($k, $v, $state[$r][$n] ?? []);
            }
        }
    }
    natsort($uses);
    foreach ($uses = array_keys($uses) as $use) {
        lot(strtoupper(basename(dirname($use, 2))))[1][] = $use;
    }
    // Load class(es)…
    foreach ($uses as $v) {
        d($folder = dirname($v) . D . 'engine' . D . 'kernel', static function ($object, $n) use ($folder) {
            if (is_file($f = dirname($folder) . D . 'plug' . D . $n . '.php')) {
                extract(lot(), EXTR_SKIP);
                require $f;
            }
            // Load plug(s) of other extension(s) and layout(s)…
            foreach (glob(dirname($folder, 3) . D . '*' . D . 'engine' . D . 'plug' . D . $n . '.php', GLOB_BRACE | GLOB_NOSORT) as $v) {
                if ($f === ($v = stream_resolve_include_path($v))) {
                    continue; // Skip current plug
                }
                if (!is_file(dirname($v, 3) . D . 'index.php')) {
                    continue; // Skip in-active extension(s) and layout(s)
                }
                require $v;
            }
        });
    }
    // Load extension(s) and layout(s)…
    foreach ($uses as $v) {
        (static function ($v) {
            // Load task(s)…
            if (is_file($k = dirname($v) . D . 'task.php')) {
                (static function ($k) {
                    extract(lot(), EXTR_SKIP);
                    require $k;
                })($k);
            }
            extract(lot(), EXTR_SKIP);
            require $v;
        })($v);
    }
} catch (Throwable $e) {
    // Core error?
    if (0 === strpos($file = $e->getFile(), ENGINE . D)) {
        file_put_contents(ENGINE . D . 'log' . D . 'error', ((string) $e) . PHP_EOL, FILE_APPEND);
    // Catch error that occurs in the extension and layout file(s) then immediately disable!
    } else {
        [$k, $name] = explode(D, substr($file, strlen($folder = LOT . D)), 3);
        file_put_contents(ENGINE . D . 'log' . D . 'error-' . $k, ((string) $e) . PHP_EOL, FILE_APPEND);
        rename($folder . $k . D . $name . D . 'index.php', $folder . $k . D . $name . D . '.index.php');
    }
}

// Set default response status and header(s)
status(403, ['x-powered-by' => 'Mecha/' . VERSION]);

// Set default response type
type('text/' . (error_get_last() ? 'plain' : 'html'));

// Set default time zone and locale
zone($state->zone);

// Run task(s) if any…
if (is_file($task = PATH . D . 'task.php')) {
    (static function ($f) {
        extract(lot(), EXTR_SKIP);
        require $f;
    })($task);
}

// Reset all possible global variable(s) to keep the presence of user-defined variable(s) clean. We don’t use
// special feature to define variable in the response so clearing user data on global scope becomes necessary.
unset($any, $e, $f, $file, $folder, $hash, $host, $k, $n, $name, $path, $port, $protocol, $query, $r, $scheme, $sub, $task, $uses, $v, $x);

Hook::fire(['set', 'get', 'let']);