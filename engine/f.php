<?php

namespace _ {
    // Check for valid JSON string format
    function json($x, $r = false) {
        if (!\is_string($x) || "" === ($x = \trim($x))) {
            return false;
        }
        return (
            // Maybe an empty string, array or object
            '""' === $x ||
            '[]' === $x ||
            '{}' === $x ||
            // Maybe an encoded JSON string
            '"' === $x[0] && '"' === \substr($x, -1) ||
            // Maybe a numeric array
            '[' === $x[0] && ']' === \substr($x, -1) ||
            // Maybe an associative array
            '{' === $x[0] && '}' === \substr($x, -1)
        ) && (null !== ($x = \json_decode($x))) ? ($r ? $x : true) : false;
    }
}

namespace {
    // Check if array contains …
    function any(iterable $a, $fn = null) {
        if (!\is_callable($fn) && null !== $fn) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        foreach ($a as $k => $v) {
            if (\call_user_func($fn, $v, $k)) {
                return true;
            }
        }
        return false;
    }
    // Convert class name to file name
    function c2f(string $s, string $h = '-', string $n = '.') {
        return \ltrim(\str_replace(['\\', $n . $h, '_' . $h], [$n, $n, '_'], h($s, $h, false, '_\\\\')), $h);
    }
    // Get file content
    function content(string $f) {
        return \is_file($f) ? \file_get_contents($f) : null;
    }
    // Merge array value(s)
    function concat(array $a, ...$b) {
        // `concat([…], […], […], false)`
        if (\count($b) > 1 && false === \end($b)) {
            \array_pop($b);
            return \array_merge($a, ...$b);
        }
        // `concat([…], […], […])`
        return \array_merge_recursive($a, ...$b);
    }
    // A equal to B
    function eq($a, $b) {
        return $b === q($a);
    }
    // Check if file/folder does exist
    function exist($f) {
        if (\is_array($f)) {
            foreach ($f as $v) {
                if ($v && $v = \stream_resolve_include_path($v)) {
                    return $v;
                }
            }
            return false;
        }
        return \stream_resolve_include_path($f);
    }
    // Extend array value(s)
    function extend(array $a, ...$b) {
        // `extend([…], […], […], false)`
        if (\count($b) > 1 && false === \end($b)) {
            \array_pop($b);
            return \array_replace($a, ...$b);
        }
        // `extend([…], […], […])`
        return \array_replace_recursive($a, ...$b);
    }
    // Convert file name to class name
    function f2c(string $s, string $h = '-', string $n = '.') {
        return \strtr(p(\strtr($s, [
            $n => $n . $h,
            '_' => '_' . $h
        ]), false, $n . '_'), [$n => "\\"]);
    }
    // Convert file name to class property name
    function f2p(string $s, string $h = '-', string $n = '.') {
        if (0 === \strpos($s, $n)) {
            $s = '__' . \substr($s, 1);
        }
        return \strtr(c(\strtr($s, [
            $n => $h . $n,
            '_' => $h . '_'
        ]), false, $n . '_'), [$n => "\\"]);
    }
    // Fetch remote URL
    function fetch(string $url, $lot = null, $type = 'GET') {
        $headers = ['x-requested-with' => 'x-requested-with: CURL'];
        $chops = \explode('?', $url, 2);
        $type = \strtoupper($type);
        // `fetch('/', ['X-Foo' => 'Bar'])`
        if (\is_array($lot)) {
            foreach ($lot as $k => $v) {
                $headers[$k] = $k . ': ' . $v;
            }
        } else if (\is_string($lot)) {
            $headers['user-agent'] = 'user-agent: ' . $lot;
        }
        if (!isset($headers['user-agent'])) {
            // <https://tools.ietf.org/html/rfc7231#section-5.5.3>
            $port = (int) $_SERVER['SERVER_PORT'];
            $v = 'Mecha/' . \VERSION . ' (+http' . (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === $port ? 's' : "") . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "") . ')';
            $headers['user-agent'] = 'user-agent: ' . $v;
        }
        $target = 'GET' === $type ? $url : $chops[0];
        if (\extension_loaded('curl')) {
            $curl = \curl_init($target);
            \curl_setopt_array($curl, [
                \CURLOPT_FAILONERROR => true,
                \CURLOPT_FOLLOWLOCATION => true,
                \CURLOPT_CUSTOMREQUEST => $type,
                \CURLOPT_HTTPHEADER => \array_values($headers),
                \CURLOPT_MAXREDIRS => 2,
                \CURLOPT_RETURNTRANSFER => true,
                \CURLOPT_SSL_VERIFYPEER => false,
                \CURLOPT_TIMEOUT => 15
            ]);
            if ('POST' === $type) {
                \curl_setopt($curl, \CURLOPT_POSTFIELDS, $chops[1] ?? "");
            }
            $out = \curl_exec($curl);
            if (\defined("\\DEBUG") && 'CURL' === \DEBUG && false === $out) {
                throw new \UnexpectedValueException(\curl_error($curl));
            }
            \curl_close($curl);
        } else {
            $context = ['http' => ['method' => $type]];
            if ('POST' === $type) {
                $headers['content-type'] = 'content-type: application/x-www-form-urlencoded';
                $context['http']['content'] = $chops[1] ?? "";
            }
            $context['http']['header'] = \implode("\r\n", \array_values($headers));
            $out = \file_get_contents($target, false, \stream_context_create($context));
        }
        return false !== $out ? $out : null;
    }
    // Return the first element found in array that passed the function test
    function find(iterable $a, callable $fn) {
        foreach ($a as $k => $v) {
            if (\call_user_func($fn, $v, $k)) {
                return $v;
            }
        }
        return null;
    }
    // Call function with parameter(s) and optional scope
    function fire(callable $fn, array $a = [], $that = null, string $scope = null) {
        $fn = $fn instanceof \Closure ? $fn : \Closure::fromCallable($fn);
        // `fire($fn, [], Foo::class)`
        if (\is_string($that)) {
            $scope = $that;
            $that = null;
        }
        return \call_user_func($fn->bindTo($that, $scope ?? 'static'), ...$a);
    }
    // A greater than or equal to B
    function ge($a, $b) {
        return q($a) >= $b;
    }
    // Get array value recursively
    function get(array $a, string $k, string $s = '.') {
        $kk = \explode($s, \strtr($k, ["\\" . $s => \P]));
        foreach ($kk as $v) {
            $v = \strtr($v, [\P => $s]);
            if (!\is_array($a) || !\array_key_exists($v, $a)) {
                return null;
            }
            $a =& $a[$v];
        }
        return $a;
    }
    // A greater than B
    function gt($a, $b) {
        return q($a) > $b;
    }
    // Check if an element exists in array
    function has(array $a, string $s = "", string $x = \P) {
        return false !== \strpos($x . \implode($x, $a) . $x, $x . $s . $x);
    }
    // Filter out element(s) that pass the function test
    function is(iterable $a, $fn = null) {
        if (!\is_callable($fn) && null !== $fn) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        return $fn ? \array_filter($a, $fn, \ARRAY_FILTER_USE_BOTH) : \array_filter($a);
    }
    // A less than or equal to B
    function le($a, $b) {
        return q($a) <= $b;
    }
    // Remove array value
    function let(array &$a, string $k, string $s = '.') {
        $kk = \explode($s, \strtr($k, ["\\" . $s => \P]));
        while (\count($kk) > 1) {
            $k = \strtr(\array_shift($kk), [\P => $s]);
            if (\is_array($a) && \array_key_exists($k, $a)) {
                $a =& $a[$k];
            }
        }
        if (\is_array($a) && \array_key_exists($v = \array_shift($kk), $a)) {
            unset($a[$v]);
        }
        return $a;
    }
    // A less than B
    function lt($a, $b) {
        return q($a) < $b;
    }
    // Manipulate array value(s)
    function map(iterable $a, callable $fn) {
        $out = [];
        foreach ($a as $k => $v) {
            $out[$k] = \call_user_func($fn, $v, $k);
        }
        return $out;
    }
    // A not equal to B
    function ne($a, $b) {
        return q($a) !== $b;
    }
    // Filter out element(s) that does not pass the function test
    function not(iterable $a, $fn = null) {
        if (!\is_callable($fn) && null !== $fn) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        return \array_filter($a, function($v, $k) use($fn) {
            return !\call_user_func($fn, $v, $k);
        }, \ARRAY_FILTER_USE_BOTH);
    }
    // Generate new array contains value from the key
    function pluck(iterable $a, string $k, $alt = null) {
        return \array_filter(\array_map(function($v) use($alt, $k) {
            return $v[$k] ?? $alt;
        }, $a));
    }
    // Convert class property name to file name
    function p2f(string $s, string $h = '-', string $n = '.') {
        if (0 === \strpos($s, '__')) {
            $s = $n . \substr($s, 2);
        }
        return \strtr(h($s, $h, false, $n . "\\\\_"), ["\\" => $n]);
    }
    // Send HTML email
    function send($from, $to, string $title, string $content, array $lot = []) {
        // This function was intended to be used as a quick way to send HTML email
        // There are no such email validation proccess here
        // We assume that you have set the correct email address(es)
        if (\is_array($to)) {
            // ['foo@bar' => 'Foo Bar', 'baz@qux' => 'Baz Qux']
            if (\array_keys($to) !== \range(0, \count($to) - 1)) {
                $s = "";
                foreach ($to as $k => $v) {
                    $s .= ', ' . $v . ' <' . $k . '>';
                }
                $to = \substr($s, 2);
            // ['foo@bar', 'baz@qux']
            } else {
                $to = \implode(', ', $to);
            }
        }
        $lot = \array_filter(\array_replace([
            'content-type' => 'text/html; charset=ISO-8859-1',
            'from' => $from,
            'mime-version' => '1.0',
            'reply-to' => $to,
            'return-path' => $from,
            'x-mailer' => 'PHP/' . \PHP_VERSION
        ], $lot));
        foreach ($lot as $k => &$v) {
            $v = $k . ': ' . $v;
        }
        // Line(s) shouldn’t be larger than 70 character(s)
        $content = \wordwrap($content, 70, "\r\n");
        return \mail($to, $title, $content, \implode("\r\n", $lot));
    }
    // Set array value
    function set(array &$a, string $k, $v = null, string $s = '.') {
        $kk = \explode($s, \strtr($k, ["\\" . $s => \P]));
        while (\count($kk) > 1) {
            $k = \strtr(\array_shift($kk), [\P => $s]);
            if (!\array_key_exists($k, $a)) {
                $a[$k] = [];
            }
            $a =& $a[$k];
        }
        $a[\strtr(\array_shift($kk), [\P => $s])] = $v;
        return $a;
    }
    // Shake array
    function shake(array $a, $preserve_key = true) {
        if (\is_callable($preserve_key)) {
            // `$preserve_key` as `$fn`
            $a = \call_user_func($preserve_key, $a);
        } else {
            // <http://php.net/manual/en/function.shuffle.php#94697>
            if ($preserve_key) {
                $k = \array_keys($a);
                $v = [];
                \shuffle($k);
                foreach ($k as $kk) {
                    $v[$kk] = $a[$kk];
                }
                $a = $v;
                unset($k, $v);
            } else {
                \shuffle($a);
            }
        }
        return $a;
    }
    function step(string $a, string $s = '.', int $dir = 1) {
        $a = \strtr($a, ["\\" . $s => \P]);
        if (false !== \strpos($a, $s)) {
            $a = \explode($s, \trim($a, $s));
            $v = -1 === $dir ? \array_pop($a) : \array_shift($a);
            $k = \strtr(\implode($s, $a), [\P => $s]);
            $c = [$k => \strtr($v, [\P => $s])];
            if (-1 === $dir) {
                while ($b = \array_pop($a)) {
                    $v = \strtr($b . $s . $v, [\P => $s]);
                    $k = \strtr(\implode($s, $a), [\P => $s]);
                    $c += [$k => $v];
                }
            } else {
                while ($b = \array_shift($a)) {
                    $v .= \strtr($s . $b, [\P => $s]);
                    $k = \strtr(\implode($s, $a), [\P => $s]);
                    $c = [$k => $v] + $c;
                }
            }
            return $c;
        }
        return (array) $a;
    }
    // Get file content line by line
    function stream(string $f, int $c = 1024) {
        if (\is_file($f) && $h = \fopen($f, 'r')) {
            $c += 1;
            while (false !== ($v = \fgets($h, $c))) {
                yield \strtr($v, ["\r\n" => "\n", "\r" => "\n"]);
            }
            \fclose($h);
        }
        yield from [];
    }
    // Dump PHP code
    function test(...$a) {
        echo '<p style="border:2px solid #000;border-bottom-width:1px;">';
        foreach ($a as $b) {
            $s = \var_export($b, true);
            $s = \str_replace(["\n", "\r"], "", \highlight_string("<?php\n\n" . $s . "\n\n?>", true));
            $s = \str_replace('<code>', '<code style="display:block;word-wrap:break-word;white-space:pre-wrap;background:#fff;color:#000;border:0;border-bottom:1px solid #000;padding:.5em;border-radius:0;box-shadow:none;text-shadow:none;">', $s);
            echo $s;
        }
        echo '</p>';
    }
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
// j: Compare array(s) and return all item(s) that exists just once in any
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

namespace {
    function a($o, $safe = true) {
        if (\is_object($o)) {
            if ($safe) {
                return ($v = \get_class($o)) && 'stdClass' !== $v ? $o : (array) $o;
            } else {
                $o = (array) $o;
            }
            foreach ($o as &$oo) {
                $oo = a($oo, $safe);
            }
            unset($oo);
        }
        return $o;
    }
    function b($x, array $a = [0]) {
        if (isset($a[0]) && $x < $a[0]) {
            return $a[0];
        }
        if (isset($a[1]) && $x > $a[1]) {
            return $a[1];
        }
        return $x;
    }
    function c(string $x = null, $a = false, string $i = "") {
        return \str_replace(' ', "", \preg_replace_callback('#([ ' . $i . '])([\p{L}\p{N}' . $i . '])#u', function($m) {
            return $m[1] . u($m[2]);
        }, f($x, $a, $i)));
    }
    function d(string $f, $fn = null) {
        \spl_autoload_register(function($c) use($f, $fn) {
            $n = c2f($c);
            $f .= \DS . $n . '.php';
            if (\is_file($f)) {
                extract($GLOBALS, \EXTR_SKIP);
                require $f;
                if (\is_callable($fn)) {
                    \call_user_func($fn, $c, $n);
                }
            }
        });
    }
    function e($x, array $a = []) {
        if (\is_string($x)) {
            if ("" === $x) {
                return $x;
            }
            if (\array_key_exists($x, $a = \array_replace([
                'TRUE' => true,
                'FALSE' => false,
                'NULL' => null,
                'true' => true,
                'false' => false,
                'null' => null
            ], $a))) {
                return $a[$x];
            }
            if (\is_numeric($x)) {
                return false !== \strpos($x, '.') ? (float) $x : (int) $x;
            }
            if (false !== ($v = \_\json($x, true))) {
                return $v;
            }
            // `"abcdef"` or `'abcdef'`
            if ('"' === $x[0] && '"' === \substr($x, -1) || "'" === $x[0] && "'" === \substr($x, -1)) {
                $v = \substr(\substr($x, 1), 0, -1);
                $a = \strpos($v, $x[0]);
                $b = \strpos($v, "\\");
                // `'ab\'cd\'ef'`
                if (
                    false !== $a &&
                    $b + 1 === $a &&
                    \preg_match('/^' . $x[0] . '(?:[^' . $x[0] . '\\\]|\\\.)*' . $x[0] . '$/', $x)
                ) {
                    return \str_replace("\\" . $x[0], $x[0], $v);
                }
                return $v;
            }
            return $x;
        } else if (\is_array($x)) {
            foreach ($x as $k => &$v) {
                $v = e($v, $a);
            }
            unset($v);
        }
        return $x;
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
    // $x: the string input
    // $a: replace multi-byte string into their accent
    // $i: character(s) to keep
    function f(string $x = null, $a = true, string $i = "") {
        // this function does not trim white-space at the start and end of the string
        $x = \preg_replace([
            // remove HTML tag(s) and character(s) reference
            '#<[^>]+?>|&(?:[a-z\d]+|\#\d+|\#x[a-f\d]+);#i',
            // remove anything except character(s) white-list
            '#[^\p{L}\p{N}\s' . $i . ']#u',
            // convert multiple white-space to single space
            '#\s+#'
        ], ' ', $x);
        return $a && !empty($GLOBALS['F']) ? \strtr($x, $GLOBALS['F']) : $x;
    }
    // Advance glob function
    function g(string $f, $x = null, $r = 0) {
        if (\is_dir($f)) {
            $g = new \RecursiveDirectoryIterator($f, \FilesystemIterator::SKIP_DOTS);
            $g = new \RecursiveCallbackFilterIterator($g, function($v, $k, $a) use($r, $x) {
                if ($r > 0 && $a->hasChildren()) {
                    return true;
                }
                // Filter by type (`0` for folder and `1` for file)
                if (0 === $x || 1 === $x) {
                    return $v->{'is' . (0 === $x ? 'Dir' : 'File')}();
                }
                // Filter file(s) by extension
                if (\is_string($x)) {
                    $x = ',' . $x . ',';
                    return $v->isFile() && false !== strpos($x, ',' . $v->getExtension() . ',');
                }
                // Filter by function
                if (\is_callable($x)) {
                    return fire($x, [], $v);
                }
                // No filter
                return true;
            });
            $g = new \RecursiveIteratorIterator($g, !isset($x) || 0 === $x ? \RecursiveIteratorIterator::CHILD_FIRST : \RecursiveIteratorIterator::LEAVES_ONLY);
            $g->setMaxDepth(true === $r ? -1 : (\is_int($r) ? $r : 0));
            foreach ($g as $k => $v) {
                yield $k => $v->isDir() ? 0 : 1;
            }
        }
        return yield from [];
    }
    function h(string $x = null, string $h = '-', $a = false, $i = "") {
        return \str_replace([' ', $h . $h], $h, \preg_replace_callback('#\p{Lu}#', function($m) use($h) {
            return $h . l($m[0]);
        }, f($x, $a, x($h) . $i)));
    }
    function i($x = null, $a = [], $f = null) {
        if (null === $x) {
            return;
        }
        $a = (array) $a;
        if ($a) {
            // Also translate the argument(s)
            foreach ($a as &$v) {
                $v = i($v);
            }
        }
        $x = $GLOBALS['I'][$x] ?? $f ?? $x;
        return \is_string($x) && $a ? \vsprintf($x, $a) : $x;
    }
    function j(array $a, array $b) {
        $f = [];
        foreach ($a as $k => $v) {
            if (\is_array($v)) {
                if (!\array_key_exists($k, $b) || !\is_array($b[$k])) {
                    $f[$k] = $v;
                } else {
                    $ff = j($v, $b[$k]);
                    if (!empty($ff)) {
                        $f[$k] = $ff;
                    }
                }
            } else if (!\array_key_exists($k, $b) || $v !== $b[$k]) {
                $f[$k] = $v;
            }
        }
        return $f;
    }
    function k(string $f, $x = null, $r = 0, $q = [], $c = false) {
        foreach (g($f, $x, $r) as $k => $v) {
            foreach ($q as $n) {
                if ("" === $n) {
                    continue;
                }
                // Find by query in file name…
                if (false !== \stripos($k, $n)) {
                    yield $k => $v;
                // Find by query in file content…
                } else if ($c && 1 === $v) {
                    foreach (stream($k) as $vv) {
                        if (false !== \stripos($vv, $n)) {
                            yield $k => 1;
                        }
                    }
                }
            }
        }
    }
    function l(string $x = null) {
        return \extension_loaded('mbstring') ? \mb_strtolower($x) : \strtolower($x);
    }
    function m($x, array $a, array $b) {
        // <https://stackoverflow.com/a/14224813/1163000>
        return ($x - $a[0]) * ($b[1] - $b[0]) / ($a[1] - $a[0]) + $b[0];
    }
    // Reserved
    function mecha() {}
    function n(string $x = null, string $t = '    ') {
        // <https://stackoverflow.com/a/18870840/1163000>
        $x = \str_replace("\xEF\xBB\xBF", "", $x);
        // Tab to 4 space(s), line-break to `\n`
        return \str_replace(["\t", "\r\n", "\r"], [$t, "\n", "\n"], $x);
    }
    function o($a, $safe = true) {
        if (\is_array($a)) {
            if ($safe) {
                $a = array_keys($a) !== range(0, count($a) - 1) ? (object) $a : $a;
            } else {
                $a = (object) $a;
            }
            foreach ($a as &$aa) {
                $aa = o($aa, $safe);
            }
            unset($aa);
        }
        return $a;
    }
    function p(string $x = null, $a = false, $i = "") {
        return \ltrim(c(' ' . $x, $a, $i), ' ');
    }
    function q($x) {
        if (true === $x) {
            return 1;
        }
        if (false === $x || null === $x) {
            return 0;
        }
        if (\is_int($x) || \is_float($x)) {
            return $x;
        }
        if (\is_string($x)) {
            return \extension_loaded('mbstring') ? \mb_strlen($x) : \strlen($x);
        }
        if ($x instanceof \Traversable) {
            return \iterator_count($x);
        }
        if ($x instanceof \stdClass) {
            return \count((array) $x);
        }
        return \count($x);
    }
    function r($a, $b, string $c = null) {
        if (\is_string($a) && \is_string($b)) {
            return 1 === \strlen($a) && 1 === \strlen($b) ? \strtr($c, $a, $b) : \strtr($c, [$a => $b]);
        }
        return \strtr($c, \array_combine($a, $b));
    }
    function s($x, array $a = []) {
        if (\is_array($x)) {
            foreach ($x as &$v) {
                $v = s($v, $a);
            }
            unset($v);
            return $x;
        }
        if (true === $x) {
            return $a['true'] ?? 'true';
        }
        if (false === $x) {
            return $a['false'] ?? 'false';
        }
        if (null === $x) {
            return $a['null'] ?? 'null';
        }
        if (\is_object($x)) {
            return \json_encode($x);
        }
        if (\is_string($x)) {
            return $a[$x = (string) $x] ?? $x;
        }
        return (string) $x;
    }
    function t(string $x = null, string $o = '"', string $c = null) {
        if ($x) {
            if ("" !== $o && 0 === \strpos($x, $o)) {
                $x = \substr($x, \strlen($o));
            }
            $c = $c ?? $o;
            if ("" !== $c && $c === \substr($x, $e = -\strlen($c))) {
                $x = \substr($x, 0, $e);
            }
        }
        return $x;
    }
    function u(string $x = null) {
        return \extension_loaded('mbstring') ? \mb_strtoupper($x) : \strtoupper($x);
    }
    function v(string $x = null, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
        $r = [];
        foreach (\array_merge((array) $c, \str_split($d, 1)) as $v) {
            $r["\\" . $v] = $v;
        }
        return \strtr($x, $r);
    }
    // $c: list of HTML tag name(s) to be excluded from `strip_tags()`
    // $n: @keep line-break in the output or replace them with a space? (default is !@keep)
    function w(string $x = null, $c = [], $n = false) {
        // Should be a HTML input
        if (false !== \strpos($x, '<') || false !== \strpos($x, ' ') || false !== \strpos($x, "\n")) {
            $c = '<' . \implode('><', \is_string($c) ? \explode(',', $c) : (array) $c) . '>';
            return \preg_replace($n ? '# +#' : '#\s+#', ' ', \trim(\strip_tags($x, $c)));
        }
        // [1]. Replace `+` with ` `
        // [2]. Replace `-` with ` `
        // [3]. Replace `---` with ` - `
        // [4]. Replace `--` with `-`
        return \preg_replace([
            '#^[._]+|[._]+$#', // remove `.` and `__` prefix/suffix of file name
            '#---#',
            '#--#',
            '#-#',
            '#\s+#',
            '#' . \P . '#'
        ], [
            "",
            ' ' . \P . ' ',
            \P,
            ' ',
            ' ',
            '-'
        ], \urldecode($x));
    }
    function x(string $x = null, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
        return \addcslashes($x, $d . $c);
    }
    function y(iterable $a) {
        if (\is_object($a) && $a instanceof \Traversable) {
            return \iterator_to_array($a);
        }
        return (array) $a;
    }
    // $b: use `[]` or `array()` syntax?
    function z($a, $b = true) {
        if (\is_array($a)) {
            $o = [];
            foreach ($a as $k => $v) {
                $o[] = \var_export($k, true) . '=>' . z($v, $b);
            }
            return ($b ? '[' : 'array(') . \implode(',', $o) . ($b ? ']' : ')');
        }
        return \var_export($a, true);
    }
}
