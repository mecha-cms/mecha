<?php

namespace _ {
    // Check for valid JSON string format
    function json($value, $r = false) {
        if (!\is_string($value) || "" === ($value = \trim($value))) {
            return false;
        }
        return (
            // Maybe boolean
            'false' === $value ||
            'null' === $value ||
            'true' === $value ||
            // Maybe empty string, array or object
            '""' === $value ||
            '[]' === $value ||
            '{}' === $value ||
            // Maybe number
            \is_numeric($value) ||
            // Maybe encoded JSON string
            '"' === $value[0] && '"' === \substr($value, -1) ||
            // Maybe numeric array
            '[' === $value[0] && ']' === \substr($value, -1) ||
            // Maybe associative array
            '{' === $value[0] && '}' === \substr($value, -1)
        ) && (null !== ($value = \json_decode($value))) ? ($r ? $value : true) : false;
    }
}

namespace {
    // Check if array contains …
    function any(iterable $value, $fn = null) {
        if (!\is_callable($fn) && null !== $fn) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        foreach ($value as $k => $v) {
            if (\call_user_func($fn, $v, $k)) {
                return true;
            }
        }
        return false;
    }
    // Convert class name to file name
    function c2f(string $value = null, $accent = false) {
        return \implode(\DS, map(\preg_split('/[\\\\\/]/', $value), function($v) use($accent) {
            return \ltrim(\strtr(h($v, '-', $accent, '_'), [
                '__-' => '.',
                '__' => '.'
            ]), '-');
        }));
    }
    // Get or set file content
    function content(string $path, $value = null) {
        if (null !== $value) {
            if (\is_dir($path) || (\is_file($path) && !\is_writable($path))) {
                return false;
            }
            if (!\is_dir($parent = \dirname($path))) {
                \mkdir($parent, 0775, true);
            }
            return \is_int(\file_put_contents($path, (string) $value));
        }
        return \is_file($path) && \is_readable($path) ? \file_get_contents($path) : null;
    }
    // Merge array value(s)
    function concat(array $value, ...$lot) {
        // `concat([…], […], […], false)`
        if (\count($lot) > 1 && false === \end($lot)) {
            \array_pop($lot);
            return \array_merge($value, ...$lot);
        }
        // `concat([…], […], […])`
        return \array_merge_recursive($value, ...$lot);
    }
    // Remove empty array, empty string and `null` value from array
    function drop(iterable $value, callable $fn = null) {
        $n = null === $fn; // Use default filter?
        foreach ($value as $k => $v) {
            if (\is_array($v) && !empty($v)) {
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
                if (\call_user_func($fn, $v, $k)) {
                    unset($value[$k]); // Drop!
                }
            }
        }
        return [] !== $value ? $value : null;
    }
    // A equal to B
    function eq($a, $b) {
        return $b === q($a);
    }
    // Check if file/folder does exist
    function exist($path) {
        if (\is_array($path)) {
            foreach ($path as $v) {
                if ($v && $v = \stream_resolve_include_path($v)) {
                    return $v;
                }
            }
            return false;
        }
        return \stream_resolve_include_path($path);
    }
    // Extend array value(s)
    function extend(array $value, ...$lot) {
        // `extend([…], […], […], false)`
        if (\count($lot) > 1 && false === \end($lot)) {
            \array_pop($lot);
            return \array_replace($value, ...$lot);
        }
        // `extend([…], […], […])`
        return \array_replace_recursive($value, ...$lot);
    }
    // Convert file name to class name
    function f2c(string $value = null, $accent = false) {
        return \implode("\\", map(\preg_split('/[\\\\\/]/', $value), function($v) use($accent) {
            return p(\strtr($v, [
                '.' => '__'
            ]), $accent, '_');
        }));
    }
    // Convert file name to property name
    function f2p(string $value = null, $accent = false) {
        return \implode("\\", map(\preg_split('/[\\\\\/]/', $value), function($v) use($accent) {
            return c(\strtr($v, [
                '.' => '__'
            ]), $accent, '_');
        }));
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
    function find(iterable $value, callable $fn) {
        foreach ($value as $k => $v) {
            if (\call_user_func($fn, $v, $k)) {
                return $v;
            }
        }
        return null;
    }
    // Call function with parameter(s) and optional scope
    function fire(callable $fn, array $lot = [], $that = null, string $scope = null) {
        $fn = $fn instanceof \Closure ? $fn : \Closure::fromCallable($fn);
        // `fire($fn, [], Foo::class)`
        if (\is_string($that)) {
            $scope = $that;
            $that = null;
        }
        return \call_user_func($fn->bindTo($that, $scope ?? 'static'), ...$lot);
    }
    // A greater than or equal to B
    function ge($a, $b) {
        return q($a) >= $b;
    }
    // Get array value recursively
    function get(array $value, string $key, string $join = '.') {
        $keys = \explode($join, \strtr($key, [
            "\\" . $join => \P
        ]));
        foreach ($keys as $k) {
            $k = \strtr($k, [
                \P => $join
            ]);
            if (!\is_array($value) || !\array_key_exists($k, $value)) {
                return null;
            }
            $value =& $value[$k];
        }
        return $value;
    }
    // A greater than B
    function gt($a, $b) {
        return q($a) > $b;
    }
    // Check if an element exists in array
    function has(iterable $value, $has = "", string $x = \P) {
        if (!\is_string($has)) {
            foreach ($value as $v) {
                if ($has === $v) {
                    return true;
                }
            }
            return false;
        }
        return false !== \strpos($x . \implode($x, $value) . $x, $x . $has . $x);
    }
    // Filter out element(s) that pass the function test
    function is(iterable $value, $fn = null) {
        if (!\is_callable($fn) && null !== $fn) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        return $fn ? \array_filter($value, $fn, \ARRAY_FILTER_USE_BOTH) : \array_filter($value);
    }
    // A less than or equal to B
    function le($a, $b) {
        return q($a) <= $b;
    }
    // Remove array value
    function let(array &$value, string $key, string $join = '.') {
        $keys = \explode($join, \strtr($key, [
            "\\" . $join => \P
        ]));
        while (\count($keys) > 1) {
            $k = \strtr(\array_shift($keys), [
                \P => $join
            ]);
            if (\is_array($value) && \array_key_exists($k, $value)) {
                $value =& $value[$k];
            }
        }
        if (\is_array($value) && \array_key_exists($k = \array_shift($keys), $value)) {
            unset($value[$k]);
        }
        return $value;
    }
    // A less than B
    function lt($a, $b) {
        return q($a) < $b;
    }
    // Manipulate array value(s)
    function map(iterable $value, callable $fn) {
        $out = [];
        foreach ($value as $k => $v) {
            $out[$k] = \call_user_func($fn, $v, $k);
        }
        return $out;
    }
    // A not equal to B
    function ne($a, $b) {
        return q($a) !== $b;
    }
    // Filter out element(s) that does not pass the function test
    function not(iterable $value, $fn = null) {
        if (!\is_callable($fn) && null !== $fn) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        return \array_filter($value, function($v, $k) use($fn) {
            return !\call_user_func($fn, $v, $k);
        }, \ARRAY_FILTER_USE_BOTH);
    }
    // Resolve relative file/folder path
    function path(string $value = null) {
        return \stream_resolve_include_path($value) ?: null;
    }
    // Generate new array contains value from the key
    function pluck(iterable $values, string $key, $value = null) {
        $out = [];
        foreach ($values as $v) {
            $out[] = $v[$key] ?? $value;
        }
        return $out;
    }
    // Convert property name to file name
    function p2f(string $value = null, $accent = false) {
        return \implode(\DS, map(\preg_split('/[\\\\\/]/', $value), function($v) use($accent) {
            return \strtr(h($v, '-', $accent, '_'), [
                '__' => '.'
            ]);
        }));
    }
    // Send email as HTML
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
    function set(array &$out, string $key, $value = null, string $join = '.') {
        $keys = \explode($join, \strtr($key, [
            "\\" . $join => \P
        ]));
        while (\count($keys) > 1) {
            $k = \strtr(\array_shift($keys), [
                \P => $join
            ]);
            if (!\array_key_exists($k, $out)) {
                $out[$k] = [];
            }
            $out =& $out[$k];
        }
        $out[\strtr(\array_shift($keys), [
            \P => $join
        ])] = $value;
        return $out;
    }
    // Shake array value
    function shake(array $value, $preserve_key = true) {
        if (\is_callable($preserve_key)) {
            // `$preserve_key` as `$fn`
            $value = \call_user_func($preserve_key, $value);
        } else {
            // <http://php.net/manual/en/function.shuffle.php#94697>
            if ($preserve_key) {
                $keys = \array_keys($value);
                $values = [];
                \shuffle($keys);
                foreach ($keys as $key) {
                    $values[$key] = $value[$key];
                }
                $value = $values;
                unset($keys, $values);
            } else {
                \shuffle($value);
            }
        }
        return $value;
    }
    // Break dot-notation sequence into step(s)
    function step(string $value, string $join = '.', int $direction = 1) {
        $value = \strtr($value, [
            "\\" . $join => \P
        ]);
        if (false !== \strpos($value, $join)) {
            $values = \explode($join, $value);
            $v = -1 === $direction ? \array_pop($values) : \array_shift($values);
            $k = \strtr(\implode($join, $values), [
                \P => $join
            ]);
            $c = [$k => \strtr($v, [
                \P => $join
            ])];
            if (-1 === $direction) {
                while (null !== ($value = \array_pop($values))) {
                    $v = \strtr($value . $join . $v, [
                        \P => $join
                    ]);
                    $k = \strtr(\implode($join, $values), [
                        \P => $join
                    ]);
                    $c += [$k => $v];
                }
            } else {
                while (null !== ($value = \array_shift($values))) {
                    $v .= \strtr($join . $value, [
                        \P => $join
                    ]);
                    $k = \strtr(\implode($join, $values), [
                        \P => $join
                    ]);
                    $c = [$k => $v] + $c;
                }
            }
            return $c;
        }
        return (array) $value;
    }
    // Get file content line by line
    function stream(string $path, int $max = 1024) {
        if (\is_file($path) && $h = \fopen($path, 'r')) {
            $max += 1;
            while (false !== ($v = \fgets($h, $max))) {
                yield \strtr($v, [
                    "\r\n" => "\n",
                    "\r" => "\n"
                ]);
            }
            \fclose($h);
        }
        yield from [];
    }
    // Dump PHP code
    function test(...$lot) {
        echo '<p style="border:2px solid #000;border-bottom-width:1px;">';
        foreach ($lot as $v) {
            $v = \var_export($v, true);
            $v = \strtr(\highlight_string("<?php\n\n" . $v . "\n\n?>", true), [
                "\n" => "",
                "\r" => ""
            ]);
            $v = \strtr($v, [
                '<code>' => '<code style="display:block;word-wrap:break-word;white-space:pre-wrap;background:#fff;color:#000;border:0;border-bottom:1px solid #000;padding:.5em;border-radius:0;box-shadow:none;text-shadow:none;">'
            ]);
            echo $v;
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
    function a($value, $safe = true) {
        if (\is_object($value)) {
            if ($safe) {
                return ($v = \get_class($value)) && 'stdClass' !== $v ? $value : (array) $value;
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
        return \strtr(\preg_replace_callback('/([ ' . $preserve . '])([\p{L}\p{N}' . $preserve . '])/u', function($m) {
            return $m[1] . u($m[2]);
        }, f($value, $accent, $preserve)), [
            ' ' => ""
        ]);
    }
    function d(string $folder, $fn = null) {
        \spl_autoload_register(function($name) use($folder, $fn) {
            $n = c2f($name);
            $file = $folder . \DS . $n . '.php';
            if (\is_file($file)) {
                extract($GLOBALS, \EXTR_SKIP);
                require $file;
                if (\is_callable($fn)) {
                    \call_user_func($fn, $name, $n);
                }
            }
        });
    }
    function e($value, array $lot = []) {
        if (\is_string($value)) {
            if ("" === $value) {
                return $value;
            }
            if (\array_key_exists($value, $lot = \array_replace([
                'TRUE' => true,
                'FALSE' => false,
                'NULL' => null,
                'true' => true,
                'false' => false,
                'null' => null
            ], $lot))) {
                return $lot[$value];
            }
            if (\is_numeric($value)) {
                return false !== \strpos($value, '.') ? (float) $value : (int) $value;
            }
            if (false !== ($v = \_\json($value, true))) {
                return $v;
            }
            // `"abcdef"` or `'abcdef'`
            if ('"' === $value[0] && '"' === \substr($value, -1) || "'" === $value[0] && "'" === \substr($value, -1)) {
                $v = \substr(\substr($value, 1), 0, -1);
                $a = \strpos($v, $value[0]);
                $b = \strpos($v, "\\");
                // `'ab\'cd\'ef'`
                if (
                    false !== $a &&
                    $b + 1 === $a &&
                    \preg_match('/^' . $value[0] . '(?:[^' . $value[0] . '\\\]|\\\.)*' . $value[0] . '$/', $value)
                ) {
                    return \strtr($v, [
                        "\\" . $value[0] => $value[0]
                    ]);
                }
                return $v;
            }
            return $value;
        }
        if (\is_array($value)) {
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
        $value = \preg_replace([
            // Remove HTML tag(s) and character(s) reference
            '/<[^>]+?>|&(?:[a-z\d]+|#\d+|#x[a-f\d]+);/i',
            // Remove anything except character(s) white-list
            '/[^\p{L}\p{N}\s' . $preserve . ']/u',
            // Convert multiple white-space to single space
            '/\s+/'
        ], ' ', $value);
        return $accent && !empty($GLOBALS['F']) ? \strtr($value, $GLOBALS['F']) : $value;
    }
    // Advance glob function
    function g(string $folder, $x = null, $deep = 0) {
        if (\is_dir($folder)) {
            $it = new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS);
            $it = new \RecursiveCallbackFilterIterator($it, function($v, $k, $a) use($deep, $x) {
                if ($deep > 0 && $a->hasChildren()) {
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
            $it = new \RecursiveIteratorIterator($it, null === $x || 0 === $x ? \RecursiveIteratorIterator::CHILD_FIRST : \RecursiveIteratorIterator::LEAVES_ONLY);
            $it->setMaxDepth(true === $deep ? -1 : (\is_int($deep) ? $deep : 0));
            foreach ($it as $k => $v) {
                yield $k => $v->isDir() ? 0 : 1;
            }
        }
        return yield from [];
    }
    function h(string $value = null, string $join = '-', $accent = false, string $preserve = "") {
        return \strtr(\preg_replace_callback('/\p{Lu}/', function($m) use($join) {
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
        return \is_string($value) && $lot ? \vsprintf($value, $lot) : $value;
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
    function k(string $folder, $x = null, $deep = 0, $query = [], $content = false) {
        foreach (g($folder, $x, $deep) as $k => $v) {
            foreach ((array) $query as $q) {
                if ("" === $q) {
                    continue;
                }
                // Find by query in file name…
                if (false !== \stripos($k, $q)) {
                    yield $k => $v;
                // Find by query in file content…
                } else if ($content && 1 === $v) {
                    foreach (stream($k) as $vv) {
                        if (false !== \stripos($vv, $q)) {
                            yield $k => 1;
                        }
                    }
                }
            }
        }
    }
    function l(string $value = null) {
        return \extension_loaded('mbstring') ? \mb_strtolower($value) : \strtolower($value);
    }
    function m($value, array $a, array $b) {
        // <https://stackoverflow.com/a/14224813/1163000>
        return ($value - $a[0]) * ($b[1] - $b[0]) / ($a[1] - $a[0]) + $b[0];
    }
    function n(string $value = null, string $tab = '    ') {
        // <https://stackoverflow.com/a/18870840/1163000>
        $value = \strtr($value, [
            "\xEF\xBB\xBF" => ""
        ]);
        // Tab to 4 space(s), line-break to `\n`
        return \strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n",
            "\t" => $tab
        ]);
    }
    function o($value, $safe = true) {
        if (\is_array($value)) {
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
        if (\is_int($value) || \is_float($value)) {
            return $value;
        }
        if (\is_string($value)) {
            return \extension_loaded('mbstring') ? \mb_strlen($value) : \strlen($value);
        }
        if ($value instanceof \Traversable) {
            return \iterator_count($value);
        }
        if ($value instanceof \stdClass) {
            return \count((array) $value);
        }
        return \count($value);
    }
    function r($from, $to, string $value = null) {
        if (\is_string($from) && \is_string($to)) {
            return 1 === \strlen($from) && 1 === \strlen($to) ? \strtr($value, $from, $to) : \strtr($value, [
                $from => $to
            ]);
        }
        return \strtr($value, \array_combine($from, $to));
    }
    function s($value, array $lot = []) {
        if (\is_array($value)) {
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
        if (\is_object($value)) {
            return \json_encode($value);
        }
        if (\is_string($value)) {
            return $lot[$value = (string) $value] ?? $value;
        }
        return (string) $value;
    }
    function t(string $value = null, string $open = '"', string $close = null) {
        if ($value) {
            if ("" !== $open && 0 === \strpos($value, $open)) {
                $value = \substr($value, \strlen($open));
            }
            $close = $close ?? $open;
            if ("" !== $close && $close === \substr($value, $end = -\strlen($close))) {
                $value = \substr($value, 0, $end);
            }
        }
        return $value;
    }
    function u(string $value = null) {
        return \extension_loaded('mbstring') ? \mb_strtoupper($value) : \strtoupper($value);
    }
    function v(string $value = null, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
        $lot = [];
        foreach (\str_split($c . $d, 1) as $v) {
            $lot["\\" . $v] = $v;
        }
        return \strtr($value, $lot);
    }
    function w(string $value = null, $preserve_tags = [], $preserve_break = false) {
        // Should be a HTML input
        if (false !== \strpos($value, '<') || false !== \strpos($value, ' ') || false !== \strpos($value, "\n")) {
            $preserve_tags = '<' . \implode('><', \is_string($preserve_tags) ? \explode(',', $preserve_tags) : (array) $preserve_tags) . '>';
            return \preg_replace($preserve_break ? '/ +/' : '/\s+/', ' ', \trim(\strip_tags($value, $preserve_tags)));
        }
        // [1]. Replace `+` with ` `
        // [2]. Replace `-` with ` `
        // [3]. Replace `---` with ` - `
        // [4]. Replace `--` with `-`
        return \preg_replace([
            '/^[._]+|[._]+$/', // remove `.` and `__` prefix/suffix in file name
            '/---/',
            '/--/',
            '/-/',
            '/\s+/',
            '/[' . \P . ']/'
        ], [
            "",
            ' ' . \P . ' ',
            \P,
            ' ',
            ' ',
            '-'
        ], \urldecode($value));
    }
    function x(string $value = null, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
        return \addcslashes($value, $c . $d);
    }
    function y(iterable $value) {
        if ($value instanceof \Traversable) {
            return \iterator_to_array($value);
        }
        return (array) $value;
    }
    function z($value, $short = true) {
        if (\is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[] = \var_export($k, true) . '=>' . z($v, $short);
            }
            return ($short ? '[' : 'array(') . \implode(',', $out) . ($short ? ']' : ')');
        }
        return \var_export($a, true);
    }
}
