<?php namespace x\link;

function content($content) {
    if (!$content || false === \strpos($content, '<')) {
        return $content;
    }
    extract($GLOBALS, \EXTR_SKIP);
    if (!empty($state->x->link->alter)) {
        foreach ($state->x->link->alter as $k => $v) {
            if (false === \strpos($content, '</' . $k . '>') && false === \strpos($content, '<' . $k . ' ')) {
                continue;
            }
            $content = \preg_replace_callback('/<' . $k . '(\s[^>]*?)>/', function($m) use($v) {
                if (false === \strpos($m[1], '=')) {
                    return $m[0];
                }
                $n = new \HTML($m[0]);
                foreach ($v as $kk => $vv) {
                    if (\is_string($n[$kk])) {
                        $vvv = $n[$kk];
                        if (\is_callable($vv)) {
                            $n[$kk] = \call_user_func($vv, $vvv, $n);
                        } else if (\is_string($vvv)) {
                            $n[$kk] = \URL::long($vvv, false);
                        }
                    }
                }
                return (string) $n;
            }, $content);
        }
    }
    return $content;
}

\Hook::set('content', __NAMESPACE__ . "\\content", 0);
