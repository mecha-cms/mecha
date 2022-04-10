<?php

namespace {
    $GLOBALS['date'] = $GLOBALS['time'] = new \Time($_SERVER['REQUEST_TIME'] ?? \time());
    \class_alias('Time', 'Date');
    \Time::_('en', '%A, %B %d, %Y');
    // Alias for `State`
    \class_alias('State', 'Site');
    // Alias for `$state`
    $GLOBALS['site'] = $site = $state;
    // Default title for the layout
    $GLOBALS['t'] = $t = new \Anemone([$state->title], ' &#x00B7; ');
    // Merge layout state(s) to the global state
    foreach (\g(\LOT . \D . 'y', 0) as $k => $v) {
        if (!\is_file($k . \D . 'index.php')) {
            continue;
        }
        if (\is_file($v = $k . \D . 'state.php')) {
            \State::set(require $v);
        }
    }
}

namespace x\layout {
    function content($content) {
        if (false !== \strpos($content, '</html>')) {
            return \preg_replace_callback('/<html(?:\s[^>]*)?>/', static function($m) {
                if (
                    false !== \strpos($m[0], ' class="') ||
                    false !== \strpos($m[0], ' class ') ||
                    ' class>' === \substr($m[0], -7)
                ) {
                    $r = new \HTML($m[0]);
                    $c = true === $r['class'] ? [] : \preg_split('/\s+/', $r['class'] ?? "");
                    $c = \array_unique(\array_merge($c, \array_keys(\array_filter((array) \State::get('[y]', true)))));
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
        $key = \strtr(\State::get('language') ?? "", '-', '_');
        // Fix for missing language key → default to `en`
        if (!\Time::_($key)) {
            \Time::_($key, \Time::_('en'));
        }
        foreach (\g(\LOT . \D . 'y', 0) as $k => $v) {
            // Load user function(s) from the `.\lot\y\*` folder if any
            if (\is_file($index = ($folder = $k) . \D . 'index.php')) {
                // Run layout task if any
                if (\is_file($task = $folder . \D . 'task.php')) {
                    (static function($f) {
                        \extract($GLOBALS, \EXTR_SKIP);
                        require $f;
                    })($task);
                }
                (static function($f) {
                    \extract($GLOBALS, \EXTR_SKIP);
                    require $f;
                })($index);
            }
            // Detect relative asset path to the `.\lot\y\*` folder
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
                        if ($path = \Asset::path($folder . \D . $kk)) {
                            \Asset::let($kk);
                            \Asset::set($path, $vv['stack'], $vv[2]);
                        }
                    }
                }
            }
        }
    }
    function route($content, $path) {
        \ob_start();
        \ob_start("\\ob_gzhandler");
        // `$content = ['page', [], 200];`
        if (\is_array($content) && isset($content[0]) && \is_string($content[0])) {
            $content = \Layout::get(...$content);
        }
        echo \Hook::fire('content', [$content]);
        \ob_end_flush();
        // <https://www.php.net/manual/en/function.ob-get-length.php#59294>
        \header('content-length: ' . \ob_get_length());
        return \ob_get_clean();
    }
    \Hook::set('content', __NAMESPACE__ . "\\content", 20);
    \Hook::set('get', __NAMESPACE__ . "\\get", 0);
    \Hook::set('route', __NAMESPACE__ . "\\route", 1000);
}

namespace x\layout\state {
    function are() {
        foreach ((array) \State::get('are', true) as $k => $v) {
            \State::set('[y].are:' . $k, $v);
        }
    }
    function can() {
        foreach ((array) \State::get('can', true) as $k => $v) {
            \State::set('[y].can:' . $k, $v);
        }
    }
    function has() {
        foreach ((array) \State::get('has', true) as $k => $v) {
            \State::set('[y].has:' . $k, $v);
        }
    }
    function is() {
        foreach ((array) \State::get('is', true) as $k => $v) {
            \State::set('[y].is:' . $k, $v);
        }
        if ($x = \State::get('is.error')) {
            \State::set('[y].error:' . $x, true);
        }
    }
    function not() {
        foreach ((array) \State::get('not', true) as $k => $v) {
            \State::set('[y].not:' . $k, $v);
        }
    }
    \Hook::set('content', __NAMESPACE__ . "\\are", 0);
    \Hook::set('content', __NAMESPACE__ . "\\can", 0);
    \Hook::set('content', __NAMESPACE__ . "\\has", 0);
    \Hook::set('content', __NAMESPACE__ . "\\is", 0);
    \Hook::set('content', __NAMESPACE__ . "\\not", 0);
}