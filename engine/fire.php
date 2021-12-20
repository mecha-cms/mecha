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
d(__DIR__ . D . 'kernel', static function($object, $n) {
    if (is_file($f = __DIR__ . D . 'plug' . D . $n . '.php')) {
        extract($GLOBALS, EXTR_SKIP);
        require $f;
    }
});

// Set default state(s)…
$state = is_file($f = __DIR__ . D . '..' . D . 'state.php') ? require $f : [];
$GLOBALS['state'] = $state = new State($state);

// Set default response status and header(s)
status(403, ['x-powered-by' => 'Mecha/' . VERSION]);

// Set default response type
type('text/' . (error_get_last() ? 'plain' : 'html'));

// Set default time zone and locale
zone($state->zone);

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
$path = strtr($path, ['../' => ""]);

// If server root is `.\srv\http` and you have this application installed in `.\srv\http\test`
// then the the sub-folder path of this application will be `test`
$sub = trim(strtr(PATH . D, [rtrim(strtr($_SERVER['DOCUMENT_ROOT'] . D, '/', D), D) . D => ""]), D);

// Remove sub-folder from path
$path = "" !== $sub ? substr($path, strlen($sub) + 1) : $path;

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
        // All application page status is initially forbidden. If there are route hook available, we assume that we have a page but is not found.
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
    $d = dirname($v) . D . 'engine';
    d($d . D . 'kernel', static function($object, $n) use($d) {
        if (is_file($f = $d . D . 'plug' . D . $n . '.php')) {
            extract($GLOBALS, EXTR_SKIP);
            require $f;
        }
    });
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

unset($any, $d, $f, $folder, $hash, $host, $k, $n, $path, $port, $protocol, $query, $scheme, $sub, $uses, $v);

header_register_callback(static function() {
    Hook::fire('set');
});

register_shutdown_function(static function() {
    if (error_get_last()) {
        return;
    }
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