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
    '©' => 'c',
    'ª' => 'a',
    '°' => '0',
    '²' => '2',
    '³' => '3',
    '¹' => '1',
    'º' => 'o',
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
    'Ā' => 'A',
    'ā' => 'a',
    'Ă' => 'A',
    'ă' => 'a',
    'Ą' => 'A',
    'ą' => 'a',
    'Ć' => 'C',
    'ć' => 'c',
    'Ĉ' => 'C',
    'ĉ' => 'c',
    'Ċ' => 'C',
    'ċ' => 'c',
    'Č' => 'C',
    'č' => 'c',
    'Ď' => 'D',
    'ď' => 'd',
    'Đ' => 'D',
    'đ' => 'd',
    'Ē' => 'E',
    'ē' => 'e',
    'Ĕ' => 'E',
    'ĕ' => 'e',
    'Ė' => 'E',
    'ė' => 'e',
    'Ę' => 'E',
    'ę' => 'e',
    'Ě' => 'E',
    'ě' => 'e',
    'Ĝ' => 'G',
    'ĝ' => 'g',
    'Ğ' => 'G',
    'ğ' => 'g',
    'Ġ' => 'G',
    'ġ' => 'g',
    'Ģ' => 'G',
    'ģ' => 'g',
    'Ĥ' => 'H',
    'ĥ' => 'h',
    'Ħ' => 'H',
    'ħ' => 'h',
    'Ĩ' => 'I',
    'ĩ' => 'i',
    'Ī' => 'I',
    'ī' => 'i',
    'Ĭ' => 'I',
    'ĭ' => 'i',
    'Į' => 'I',
    'į' => 'i',
    'İ' => 'I',
    'ı' => 'i',
    'Ĳ' => 'IJ',
    'ĳ' => 'ij',
    'Ĵ' => 'J',
    'ĵ' => 'j',
    'Ķ' => 'K',
    'ķ' => 'k',
    'Ĺ' => 'L',
    'ĺ' => 'l',
    'Ļ' => 'L',
    'ļ' => 'l',
    'Ľ' => 'L',
    'ľ' => 'l',
    'Ŀ' => 'L',
    'ŀ' => 'l',
    'Ł' => 'L',
    'ł' => 'l',
    'Ń' => 'N',
    'ń' => 'n',
    'Ņ' => 'N',
    'ņ' => 'n',
    'Ň' => 'N',
    'ň' => 'n',
    'ŉ' => 'n',
    'Ō' => 'O',
    'ō' => 'o',
    'Ŏ' => 'O',
    'ŏ' => 'o',
    'Ő' => 'O',
    'ő' => 'o',
    'Œ' => 'OE',
    'œ' => 'oe',
    'Ŕ' => 'R',
    'ŕ' => 'r',
    'Ŗ' => 'R',
    'ŗ' => 'r',
    'Ř' => 'R',
    'ř' => 'r',
    'Ś' => 'S',
    'ś' => 's',
    'Ŝ' => 'S',
    'ŝ' => 's',
    'Ş' => 'S',
    'ş' => 's',
    'Š' => 'S',
    'š' => 's',
    'Ţ' => 'T',
    'ţ' => 't',
    'Ť' => 'T',
    'ť' => 't',
    'Ŧ' => 'T',
    'ŧ' => 't',
    'Ũ' => 'U',
    'ũ' => 'u',
    'Ū' => 'U',
    'ū' => 'u',
    'Ŭ' => 'U',
    'ŭ' => 'u',
    'Ů' => 'U',
    'ů' => 'u',
    'Ű' => 'U',
    'ű' => 'u',
    'Ų' => 'U',
    'ų' => 'u',
    'Ŵ' => 'W',
    'ŵ' => 'w',
    'Ŷ' => 'Y',
    'ŷ' => 'y',
    'Ÿ' => 'Y',
    'Ź' => 'Z',
    'ź' => 'z',
    'Ż' => 'Z',
    'ż' => 'z',
    'Ž' => 'Z',
    'ž' => 'z',
    'ſ' => 's',
    'ƒ' => 'f',
    'Ơ' => 'O',
    'ơ' => 'o',
    'Ư' => 'U',
    'ư' => 'u',
    'Ǎ' => 'A',
    'ǎ' => 'a',
    'Ǐ' => 'I',
    'ǐ' => 'i',
    'Ǒ' => 'O',
    'ǒ' => 'o',
    'Ǔ' => 'U',
    'ǔ' => 'u',
    'Ǖ' => 'U',
    'ǖ' => 'u',
    'Ǘ' => 'U',
    'ǘ' => 'u',
    'Ǚ' => 'U',
    'ǚ' => 'u',
    'Ǜ' => 'U',
    'ǜ' => 'u',
    'Ǻ' => 'A',
    'ǻ' => 'a',
    'Ǽ' => 'AE',
    'ǽ' => 'ae',
    'Ǿ' => 'O',
    'ǿ' => 'o',
    'Ș' => 'S',
    'ș' => 's',
    'Ț' => 'T',
    'ț' => 't',
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
    'Ё' => 'E',
    'Є' => 'Ye',
    'І' => 'I',
    'Ї' => 'Ji',
    'А' => 'A',
    'Б' => 'B',
    'В' => 'V',
    'Г' => 'G',
    'Д' => 'D',
    'Е' => 'E',
    'Ж' => 'Zh',
    'З' => 'Z',
    'И' => 'I',
    'Й' => 'J',
    'К' => 'K',
    'Л' => 'L',
    'М' => 'M',
    'Н' => 'N',
    'О' => 'O',
    'П' => 'P',
    'Р' => 'R',
    'С' => 'S',
    'Т' => 'T',
    'У' => 'U',
    'Ф' => 'F',
    'Х' => 'H',
    'Ц' => 'C',
    'Ч' => 'Ch',
    'Ш' => 'Sh',
    'Щ' => 'Shch',
    'Ъ' => "",
    'Ы' => 'Y',
    'Ь' => "",
    'Э' => 'E',
    'Ю' => 'Ju',
    'Я' => 'Ja',
    'а' => 'a',
    'б' => 'b',
    'в' => 'v',
    'г' => 'g',
    'д' => 'd',
    'е' => 'e',
    'ж' => 'zh',
    'з' => 'z',
    'и' => 'i',
    'й' => 'j',
    'к' => 'k',
    'л' => 'l',
    'м' => 'm',
    'н' => 'n',
    'о' => 'o',
    'п' => 'p',
    'р' => 'r',
    'с' => 's',
    'т' => 't',
    'у' => 'u',
    'ф' => 'f',
    'х' => 'h',
    'ц' => 'c',
    'ч' => 'ch',
    'ш' => 'sh',
    'щ' => 'shch',
    'ъ' => "",
    'ы' => 'y',
    'ь' => "",
    'э' => 'e',
    'ю' => 'ju',
    'я' => 'ja',
    'ё' => 'e',
    'є' => 'ye',
    'і' => 'i',
    'ї' => 'ji',
    'Ґ' => 'G',
    'ґ' => 'g',
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
    'Ạ' => 'A',
    'ạ' => 'a',
    'Ả' => 'A',
    'ả' => 'a',
    'Ấ' => 'A',
    'ấ' => 'a',
    'Ầ' => 'A',
    'ầ' => 'a',
    'Ẩ' => 'A',
    'ẩ' => 'a',
    'Ẫ' => 'A',
    'ẫ' => 'a',
    'Ậ' => 'A',
    'ậ' => 'a',
    'Ắ' => 'A',
    'ắ' => 'a',
    'Ằ' => 'A',
    'ằ' => 'a',
    'Ẳ' => 'A',
    'ẳ' => 'a',
    'Ẵ' => 'A',
    'ẵ' => 'a',
    'Ặ' => 'A',
    'ặ' => 'a',
    'Ẹ' => 'E',
    'ẹ' => 'e',
    'Ẻ' => 'E',
    'ẻ' => 'e',
    'Ẽ' => 'E',
    'ẽ' => 'e',
    'Ế' => 'E',
    'ế' => 'e',
    'Ề' => 'E',
    'ề' => 'e',
    'Ể' => 'E',
    'ể' => 'e',
    'Ễ' => 'E',
    'ễ' => 'e',
    'Ệ' => 'E',
    'ệ' => 'e',
    'Ỉ' => 'I',
    'ỉ' => 'i',
    'Ị' => 'I',
    'ị' => 'i',
    'Ọ' => 'O',
    'ọ' => 'o',
    'Ỏ' => 'O',
    'ỏ' => 'o',
    'Ố' => 'O',
    'ố' => 'o',
    'Ồ' => 'O',
    'ồ' => 'o',
    'Ổ' => 'O',
    'ổ' => 'o',
    'Ỗ' => 'O',
    'ỗ' => 'o',
    'Ộ' => 'O',
    'ộ' => 'o',
    'Ớ' => 'O',
    'ớ' => 'o',
    'Ờ' => 'O',
    'ờ' => 'o',
    'Ở' => 'O',
    'ở' => 'o',
    'Ỡ' => 'O',
    'ỡ' => 'o',
    'Ợ' => 'O',
    'ợ' => 'o',
    'Ụ' => 'U',
    'ụ' => 'u',
    'Ủ' => 'U',
    'ủ' => 'u',
    'Ứ' => 'U',
    'ứ' => 'u',
    'Ừ' => 'U',
    'ừ' => 'u',
    'Ử' => 'U',
    'ử' => 'u',
    'Ữ' => 'U',
    'ữ' => 'u',
    'Ự' => 'U',
    'ự' => 'u',
    'Ỳ' => 'Y',
    '«' => "",
    '»' => "",
    'ỳ' => 'y',
    'Ỵ' => 'Y',
    'ỵ' => 'y',
    'Ỷ' => 'Y',
    'ỷ' => 'y',
    'Ỹ' => 'Y',
    'ỹ' => 'y',
    '‘' => "'",
    '’' => "'",
    '“' => '"',
    '”' => '"'
];

// Normalize `$_GET`, `$_POST`, `$_REQUEST` value(s)
$any = [&$_GET, &$_POST, &$_REQUEST];
array_walk_recursive($any, static function (&$v) {
    // Trim white-space and normalize line-break
    $v = trim(strtr($v, ["\r\n" => "\n", "\r" => "\n"]));
    // Replace all empty value with `null` and evaluate other(s)
    $v = "" === $v ? null : e($v);
});

// Normalize `$_FILES` value(s) to `$_POST`
if ('POST' === $_SERVER['REQUEST_METHOD']) {
    // <https://stackoverflow.com/a/30342756/1163000>
    $tidy = static function (array $in) use (&$tidy) {
        // The normalized value(s) do not follow the default value(s) given by `$_FILES`. Instead, it
        // uses its own value(s) with slightly different specification, to make it easier for user(s)
        // who are already familiar with the property of the page file.
        $alter = [
            'error' => 'status',
            'full_path' => 'from',
            'tmp_name' => 'path'
        ];
        $out = [];
        if (!is_array(reset($in))) {
            foreach ($in as $k => $v) {
                $out[$alter[$k] ?? $k] = $v;
            }
            return $out;
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
d(__DIR__ . D . 'kernel', static function ($object, $n) {
    if (is_file($f = __DIR__ . D . 'plug' . D . $n . '.php')) {
        extract($GLOBALS, EXTR_SKIP);
        require $f;
    }
    // Load plug(s) of extension(s) and layout(s)…
    foreach (glob(__DIR__ . D . '..' . D . 'lot' . D . '{x,y}' . D . '*' . D . 'engine' . D . 'plug' . D . $n . '.php', GLOB_BRACE | GLOB_NOSORT) as $v) {
        if (!is_file(dirname($v = stream_resolve_include_path($v), 3) . D . 'index.php')) {
            continue; // Skip in-active extension(s) and layout(s)
        }
        require $v;
    }
});

// Set default state(s)…
$state = is_file($f = __DIR__ . D . '..' . D . 'state.php') ? require $f : [];
$GLOBALS['state'] = $state = new State($state);

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

// If server root is `.\srv\http` and you have this system installed in `.\srv\http\a\b\c`
// then the sub-folder path of this system will be `a\b\c`
$sub = trim(strtr(PATH . D, [rtrim(strtr($_SERVER['DOCUMENT_ROOT'] . D, '/', D), D) . D => ""]), D);

// Remove sub-folder from path
$path = "" !== $sub ? substr($path, strlen($sub) + 1) : $path;

$path = "" !== $path ? '/' . $path : null;
$query = "" !== $query ? '?' . $query : null;
$hash = !empty($_COOKIE['hash']) ? '#' . $_COOKIE['hash'] : null;

$GLOBALS['url'] = $url = new URL($protocol . $host . $path . $query . $hash);

function anemone(...$lot) {
    return new Anemone(...$lot);
}

function from(...$lot) {
    return From::_(...$lot);
}

function hook(...$lot) {
    return count($lot) < 2 ? Hook::get(...$lot) : Hook::set(...$lot);
}

function kick(string $path = null, int $status = null) {
    $path = Hook::fire('kick', [$path, $status]);
    header('location: ' . $path, true, $status ?? 301);
    exit;
}

function long(string $value) {
    $url = $GLOBALS['url'];
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

function state(...$lot) {
    if (count($lot) < 2) {
        $lot[] = true; // Force to array
        return State::get(...$lot);
    }
    return State::set(...$lot);
}

function to(...$lot) {
    return To::_(...$lot);
}

try {
    $uses = [];
    foreach (glob(__DIR__ . D . '..' . D . 'lot' . D . '{x,y}' . D . '*' . D . 'index.php', GLOB_BRACE | GLOB_NOSORT) as $v) {
        $n = basename($d = dirname($v = stream_resolve_include_path($v)));
        if (empty($GLOBALS[strtoupper($r = basename(dirname($d)))][0][$v])) {
            $uses[$v] = content($d . D . $n) ?? $r . '.' . $n;
            // Load state(s)…
            State::set($r . '.' . ($k = strtr($n, ['.' => "\\."])), []);
            if (is_file($v = $d . D . 'state.php')) {
                (static function ($k, $v, $a) {
                    extract($GLOBALS, EXTR_SKIP);
                    State::set($r . '.' . $k, array_replace_recursive((array) require $v, $a));
                })($k, $v, $state[$r][$n] ?? []);
            }
        }
    }
    natsort($uses);
    foreach ($uses = array_keys($uses) as $use) {
        $GLOBALS[strtoupper(basename(dirname($use, 2)))][1][] = $use;
    }
    // Load class(es)…
    foreach ($uses as $v) {
        $d = dirname($v) . D . 'engine';
        d($d . D . 'kernel', static function ($object, $n) use ($d) {
            if (is_file($f = $d . D . 'plug' . D . $n . '.php')) {
                extract($GLOBALS, EXTR_SKIP);
                require $f;
            }
            // Load plug(s) of other extension(s) and layout(s)…
            foreach (glob(__DIR__ . D . '..' . D . 'lot' . D . '{x,y}' . D . '*' . D . 'engine' . D . 'plug' . D . $n . '.php', GLOB_BRACE | GLOB_NOSORT) as $v) {
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
                    extract($GLOBALS, EXTR_SKIP);
                    require $k;
                })($k);
            }
            extract($GLOBALS, EXTR_SKIP);
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

Hook::fire('set');

// Run task(s) if any…
if (is_file($task = PATH . D . 'task.php')) {
    (static function ($f) {
        extract($GLOBALS, EXTR_SKIP);
        require $f;
    })($task);
}

// Reset all possible global variable(s) to keep the presence of user-defined variable(s) clean. We don’t use
// special feature to define variable in the response so clearing user data on global scope becomes necessary.
unset($any, $d, $e, $f, $folder, $hash, $host, $k, $n, $path, $port, $protocol, $query, $r, $scheme, $sub, $task, $uses, $v, $x);

Hook::fire('get');

Hook::fire('let');