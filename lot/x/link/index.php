<?php

namespace x\link {
    // Get folder path relative to the server’s root
    $folder = \trim(\strtr(\PATH . \D, [\rtrim(\strtr($_SERVER['DOCUMENT_ROOT'], '/', \D), \D) . \D => ""]), \D);
    // Set correct base URL
    \define(__NAMESPACE__ . "\\host", $_SERVER['HTTP_HOST']);
    \define(__NAMESPACE__ . "\\route", \x\link\host . ($folder ? '/' . $folder : ""));
    function content($content) {
        if (!$content || false === \strpos($content, '<')) {
            return $content;
        }
        extract($GLOBALS, \EXTR_SKIP);
        $alter = $state->x->link->alter ?? [];
        if (!empty($alter)) {
            foreach ($alter as $k => $v) {
                if (
                    false === \strpos($content, '</' . $k . '>') &&
                    false === \strpos($content, '<' . $k . ' ') &&
                    false === \strpos($content, '<' . $k . "\n") &&
                    false === \strpos($content, '<' . $k . "\t")
                ) {
                    continue;
                }
                $content = \preg_replace_callback('/<' . \x($k) . '(\s[^>]*?)>/', static function($m) use($k, $v) {
                    if (false === \strpos($m[1], '=')) {
                        return $m[0];
                    }
                    $that = new \HTML($m[0]);
                    foreach ($v as $kk => $vv) {
                        if (!$vv || !isset($that[$kk])) {
                            continue;
                        }
                        $vvv = $that[$kk];
                        if (\is_callable($vv)) {
                            $vvv = \fire($vv, [$vvv, $kk, $k], $that);
                        } else {
                            $vvv = \Hook::fire('link', [$vvv]);
                        }
                        $that[$kk] = $vvv;
                    }
                    return (string) $that;
                }, $content);
            }
        }
        return $content;
    }
    function kick($path) {
        return \x\link\link($path ?? $GLOBALS['url']->current());
    }
    function link($path) {
        return \strtr(\long($path), ['://' . \x\link\host => '://' . \x\link\route]);
    }
    \Hook::set('content', __NAMESPACE__ . "\\content", 0);
    \Hook::set('kick', __NAMESPACE__ . "\\kick", 0);
    \Hook::set('link', __NAMESPACE__ . "\\link", 0);
}

namespace x\link\f {
    function image_source_set($value, $key, $name) {
        return \fire("\\x\\link\\f\\source_set", [$value, $key, $name], $this);
    }
    function source_set($value, $key, $name) {
        if (!$value) {
            return $value;
        }
        $out = "";
        foreach (\preg_split('/(\s*,\s*)(?!,)/', $value, null, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if (',' === \trim($v)) {
                $out .= $v;
                continue;
            }
            $out .= \Hook::fire('link', [\rtrim($v, ',')]);
        }
        return $out;
    }
}