<?php

namespace _\lot\x\layout {
    function alert($content) {
        if (false !== \strpos($content, '</alert>')) {
            return \preg_replace_callback('#(?:\s*<alert(?:\s[^>]+)?>[\s\S]*?<\/alert>\s*)+#', function($m) {
                return '<div class="alert p">' . \str_replace([
                    '<alert type="',
                    '</alert>'
                ], [
                    '<p class="',
                    '</p>'
                ], $m[0]) . '</div>';
            }, $content);
        }
        return $content;
    }
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
    function get() {
        $folder = \Layout::$state['path'] . \DS;
        // Run layout task if any
        if (\is_file($task = $folder . 'task.php')) {
            (function($task) {
                extract($GLOBALS, \EXTR_SKIP);
                require $task;
            })($task);
        }
        // Load user function(s) from the `.\lot\layout` folder if any
        if (\is_file($fn = $folder . 'index.php')) {
            (function($fn) {
                extract($GLOBALS, \EXTR_SKIP);
                require $fn;
            })($fn);
        }
        // Detect relative asset path to the `.\lot\layout\asset` folder
        if (null !== \State::get('x.asset') && $assets = \Asset::get()) {
            foreach ($assets as $k => $v) {
                foreach ($v as $kk => $vv) {
                    // Full path, no change!
                    if (
                        0 === strpos($kk, \ROOT) ||
                        0 === strpos($kk, '//') ||
                        false !== strpos($kk, '://')
                    ) {
                        continue;
                    }
                    if ($path = \Asset::path($folder . 'asset' . \DS . $kk)) {
                        \Asset::let($kk);
                        \Asset::set($path, $vv['stack'], $vv[2]);
                    }
                }
            }
        }
    }
    \Hook::set('content', __NAMESPACE__, 20);
    \Hook::set('content', __NAMESPACE__ . "\\alert", 0);
    \Hook::set('content', __NAMESPACE__ . "\\are", 0);
    \Hook::set('content', __NAMESPACE__ . "\\can", 0);
    \Hook::set('content', __NAMESPACE__ . "\\has", 0);
    \Hook::set('content', __NAMESPACE__ . "\\is", 0);
    \Hook::set('content', __NAMESPACE__ . "\\not", 0);
    \Hook::set('get', __NAMESPACE__ . "\\get", 0);
}

namespace _\lot\x {
    // Generate HTML class(es)
    function layout($content) {
        $root = 'html';
        if (false !== \strpos($content, '<' . $root . ' ')) {
            return \preg_replace_callback('#<' . \x($root) . '(?:\s[^>]*)?>#', function($m) {
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
}
