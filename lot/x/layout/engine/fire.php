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
    // Merge layout state to the global state
    if (\is_file($file = \LOT . \D . 'layout' . \D . 'state.php')) {
        \State::set(require $file);
    }
}

namespace x\layout {
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
                \extract($GLOBALS, \EXTR_SKIP);
                require $f;
            })($task);
        }
        // Load user function(s) from the `.\lot\layout` folder if any
        if (\is_file($index = $folder . \D . 'index.php')) {
            (static function($f) {
                \extract($GLOBALS, \EXTR_SKIP);
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
    }
    function route($r) {
        if (!\is_file(\LOT . \D . 'layout' . \D . 'index.php')) {
            // Missing `.\lot\layout\index.php` file :(
            return null;
        }
        \extract($r);
        if (isset($kick)) {
            \kick($kick);
            exit;
        }
        \status((int) ($status ?? 404), (array) ($lot ?? []));
        \type($type ?? 'text/html');
        \ob_start();
        \ob_start("\\ob_gzhandler");
        if (isset($layout)) {
            if (null !== ($content = \Layout::get($layout))) {
                echo \Hook::fire('content', [$content]);
            } else if (\defined("\\TEST") && \TEST) {
                \status(403);
                \abort('Layout <code>' . $layout . '</code> does not exist.');
            }
        } else if (isset($path) && \is_file($path)) {
            echo \Hook::fire('content', [(static function($f) {
                \ob_start();
                \extract($GLOBALS, \EXTR_SKIP);
                require $f;
                return \ob_get_clean();
            })(\Hook::fire('path', [$path]))]);
        } else {
            echo \Hook::fire('content', [$content ?? null]);
        }
        \ob_end_flush();
        // <https://www.php.net/manual/en/function.ob-get-length.php#59294>
        \header('content-length: ' . \ob_get_length());
        echo \ob_get_clean();
    }
    \Hook::set('content', __NAMESPACE__ . "\\content", 20);
    \Hook::set('get', __NAMESPACE__ . "\\get", 0);
    \Hook::set('route', __NAMESPACE__ . "\\route", 1000);
}

namespace x\layout\state {
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