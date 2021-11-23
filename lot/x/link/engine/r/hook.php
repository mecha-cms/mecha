<?php namespace x\link;

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
            $content = \preg_replace_callback('/<' . \x($k) . '(\s[^>]*?)>/', function($m) use($k, $v) {
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
                        $vvv = \URL::long($vvv, false);
                    }
                    $that[$kk] = $vvv;
                }
                return (string) $that;
            }, $content);
        }
    }
    return $content;
}

\Hook::set('content', __NAMESPACE__ . "\\content", 0);