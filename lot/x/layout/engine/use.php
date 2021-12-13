<?php

namespace {
    foreach ([
        'camel' => "\\c",
        'description' => function(string $value = null, $max = 200) {
            // Make sure to add space at the end of the block tag(s) that will be removed
            // To make `<p>asdf.</p><p>asdf</p>` becomes `asdf. asdf` and not `asdf.asdf`
            $r = 'address|article|blockquote|details|div|d[dt]|figure|(?:fig)?caption|footer|h(?:[1-6]|eader|r)|li|main|nav|p(?:re)?|section|summary|t[dh]';
            $value = \preg_replace(['/\s+/', '/\s*(<\/(?:' . $r . ')>)\s*/i'], [' ', '$1 '], $value);
            $value = \strip_tags($value, '<a><abbr><b><br><cite><code><del><dfn><em><i><ins><kbd><mark><q><small><span><strong><sub><sup><time><u><var>');
            if (\is_int($max)) {
                $max = [$max, '&#x2026;'];
            }
            $utf8 = \extension_loaded('mbstring');
            // <https://stackoverflow.com/a/1193598/1163000>
            if (false !== \strpos($value, '<') || false !== \strpos($value, '&')) {
                $out = "";
                $done = $i = 0;
                $tags = [];
                while ($done < $max[0] && \preg_match('/<(?:\/[a-z\d:.-]+|[a-z\d:.-]+(?:\s[^>]*)?)>|&(?:[a-z\d]+|#\d+|#x[a-f\d]+);|[\x80-\xFF][\x80-\xBF]*/i', $value, $m, \PREG_OFFSET_CAPTURE, $i)) {
                    $tag = $m[0][0];
                    $pos = $m[0][1];
                    $str = \substr($value, $i, $pos - $i);
                    if ($done + \strlen($str) > $max[0]) {
                        $out .= \substr($str, 0, $max[0] - $done);
                        $done = $max[0];
                        break;
                    }
                    $out .= $str;
                    $done += \strlen($str);
                    if ($done >= $max[0]) {
                        break;
                    }
                    if ('&' === $tag[0] || \ord($tag) >= 0x80) {
                        $out .= $tag;
                        ++$done;
                    } else {
                        // `tag`
                        $n = \trim(\strtok($m[0][0], "\n\r\t "), '<>/');
                        // `</tag>`
                        if ('/' === $tag[1]) {
                            $open = \array_pop($tags);
                            \assert($open === $n); // Check that tag(s) are properly nested!
                            $out .= $tag;
                        // `<tag/>`
                        } else if ('/>' === \substr($tag, -2) || \preg_match('/^<(?:area|base|br|col|command|embed|hr|img|input|link|meta|param|source)(?=[\s>])/i', $tag)) {
                            $out .= $tag;
                        // `<tag>`
                        } else {
                            $out .= $tag;
                            $tags[] = $n;
                        }
                    }
                    // Continue after the tag…
                    $i = $pos + \strlen($tag);
                }
                // Print rest of the text…
                if ($done < $max[0] && $i < \strlen($value)) {
                    $out .= \substr($value, $i, $max[0] - $done);
                }
                // Close any open tag(s)…
                while ($close = \array_pop($tags)) {
                    $out .= '</' . $close . '>';
                }
                $out = \trim(\preg_replace('/\s*<br(\s[^>]*)?>\s*/', ' ', $out));
                $value = \trim(\strip_tags($value));
                $count = $utf8 ? \mb_strlen($value) : \strlen($value);
                $out = \trim($out) . ($count > $max[0] ? $max[1] : "");
                return "" !== $out ? $out : null;
            }
            $out = $utf8 ? \mb_substr($value, 0, $max[0]) : \substr($value, 0, $max[0]);
            $count = $utf8 ? \mb_strlen($value) : \strlen($value);
            $out = \trim($out) . ($count > $max[0] ? $max[1] : "");
            return "" !== $out ? $out : null;
        },
        'kebab' => function(string $value = null, string $join = '-', $accent = true) {
            return \trim(\h($value, $join, $accent), $join);
        },
        'lower' => "\\l",
        'pascal' => "\\p",
        'sentence' => function(string $value = null, string $tail = '.') {
            $value = \trim($value);
            if (\extension_loaded('mbstring')) {
                return \mb_strtoupper(\mb_substr($value, 0, 1)) . \mb_strtolower(\mb_substr($value, 1)) . $tail;
            }
            return \ucfirst(\strtolower($value)) . $tail;
        },
        'snake' => function(string $value = null, $a = true) {
            return \trim(\h($value, '_', $a), '_');
        },
        'text' => "\\w",
        'title' => function(string $value = null) {
            $value = \w($value);
            $out = \extension_loaded('mbstring') ? \mb_convert_case($value, \MB_CASE_TITLE) : \ucwords($value);
            // Convert to abbreviation if all case(s) are in upper
            $out = \u($out) === $out ? \strtr($out, [' ' => ""]) : $out;
            return "" !== $out ? $out : null;
        },
        'upper' => "\\u"
    ] as $k => $v) {
        \To::_($k, $v);
    }
    $GLOBALS['date'] = $GLOBALS['time'] = new \Time($_SERVER['REQUEST_TIME'] ?? \time());
    \class_alias('Time', 'Date');
    \Time::_('en', '%A, %B %d, %Y');
    // Alias for `State`
    \class_alias('State', 'Site');
    // Alias for `$state`
    $GLOBALS['site'] = $site = $state;
    // Default title for the layout
    $GLOBALS['t'] = $t = new \Anemone([$state->title], ' &#x00B7; ');
    // Merge layout state to the global state
    if (\is_file($file = \LOT . \D . 'layout' . \D . 'state.php')) {
        \State::set(require $file);
    }
}

namespace x {
    function content($content) {
        $root = 'html';
        if (false !== \strpos($content, '<' . $root . ' ')) {
            return \preg_replace_callback('/<' . \x($root) . '(?:\s[^>]*)?>/', static function($m) {
                if (
                    false !== \strpos($m[0], ' class="') ||
                    false !== \strpos($m[0], ' class ') ||
                    ' class>' === \substr($m[0], -7)
                ) {
                    $r = new \HTML($m[0]);
                    $c = true === $r['class'] ? [] : \preg_split('/\s+/', $r['class'] ?? "");
                    $c = \array_unique(\array_merge($c, \array_keys(\array_filter((array) \State::get('[layout]', true)))));
                    \sort($c);
                    $r['class'] = \trim(\implode(' ', $c));
                    return $r;
                }
                return $m[0];
            }, $content);
        }
        return $content;
    }
    function get() {
        $folder = \LOT . \D . 'layout';
        $key = \strtr(\State::get('language') ?? "", '-', '_');
        // Fix for missing language key → default to `en`
        if (!\Time::_($key)) {
            \Time::_($key, \Time::_('en'));
        }
        // Run layout task if any
        if (\is_file($task = $folder . \D . 'task.php')) {
            (static function($f) {
                extract($GLOBALS, \EXTR_SKIP);
                require $f;
            })($task);
        }
        // Load user function(s) from the `.\lot\layout` folder if any
        if (\is_file($index = $folder . \D . 'index.php')) {
            (static function($f) {
                extract($GLOBALS, \EXTR_SKIP);
                require $f;
            })($index);
        }
        // Detect relative asset path to the `.\lot\layout\asset` folder
        if (null !== \State::get('x.asset') && $assets = \Asset::get()) {
            foreach ($assets as $k => $v) {
                foreach ($v as $kk => $vv) {
                    // Full path, no change!
                    if (
                        0 === \strpos($kk, \PATH) ||
                        0 === \strpos($kk, '//') ||
                        false !== \strpos($kk, '://')
                    ) {
                        continue;
                    }
                    if ($path = \Asset::path($folder . \D . 'asset' . \D . $kk)) {
                        \Asset::let($kk);
                        \Asset::set($path, $vv['stack'], $vv[2]);
                    }
                }
            }
        }
        // \Hook::fire('layout', [$path, $lot, $exit], $GLOBALS['state'], \Layout::class);
    }
    function layout(string $path, array $lot = [], $exit = true) {
        if (!\is_file(\LOT . \D . 'layout' . \D . 'index.php')) {
            // Missing `.\lot\layout\index.php` file :(
            return null;
        }
        if (null !== ($content = \Layout::get($path, $lot))) {
            $content = \Hook::fire('content', [$content]);
            \ob_start();
            \ob_start("\\ob_gzhandler");
            echo $content; // The response body
            \ob_end_flush();
            // <https://www.php.net/manual/en/function.ob-get-length.php#59294>
            \header('content-length: ' . \ob_get_length());
            echo \ob_get_clean();
            if ($exit) {
                \Hook::fire('let');
                exit;
            }
        } else if (\defined("\\TEST") && \TEST) {
            \abort('Layout <code>' . $path . '</code> does not exist.', $exit);
        }
    }
    \Hook::set('content', __NAMESPACE__ . "\\content", 20);
    \Hook::set('get', __NAMESPACE__ . "\\get", 0);
    \Hook::set('layout', __NAMESPACE__ . "\\layout", 10);
}

namespace x\layout {
    function are() {
        foreach ((array) \State::get('are', true) as $k => $v) {
            \State::set('[layout].are:' . $k, $v);
        }
    }
    function can() {
        foreach ((array) \State::get('can', true) as $k => $v) {
            \State::set('[layout].can:' . $k, $v);
        }
    }
    function has() {
        foreach ((array) \State::get('has', true) as $k => $v) {
            \State::set('[layout].has:' . $k, $v);
        }
    }
    function is() {
        foreach ((array) \State::get('is', true) as $k => $v) {
            \State::set('[layout].is:' . $k, $v);
        }
        if ($x = \State::get('is.error')) {
            \State::set('[layout].error:' . $x, true);
        }
    }
    function not() {
        foreach ((array) \State::get('not', true) as $k => $v) {
            \State::set('[layout].not:' . $k, $v);
        }
    }
    \Hook::set('content', __NAMESPACE__ . "\\are", 0);
    \Hook::set('content', __NAMESPACE__ . "\\can", 0);
    \Hook::set('content', __NAMESPACE__ . "\\has", 0);
    \Hook::set('content', __NAMESPACE__ . "\\is", 0);
    \Hook::set('content', __NAMESPACE__ . "\\not", 0);
}