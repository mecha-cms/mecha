<?php namespace x\link;

function content($content) {
    if (!$content || false === \strpos($content, '<')) {
        return $content;
    }
    extract($GLOBALS, \EXTR_SKIP);
    $alter = $state->x->link->alter ?? [];
    if (!empty($alter)) {
        foreach ($alter as $k => $v) {
            if (false === \strpos($content, '</' . $k . '>') && false === \strpos($content, '<' . $k . ' ')) {
                continue;
            }
            $content = \preg_replace_callback('/<' . \x($k) . '(\s[^>]*?)>/', function($m) use($k, $v) {
                if (false === \strpos($m[1], '=')) {
                    return $m[0];
                }
                $that = new \HTML($m[0]);
                foreach ($v as $kk => $vv) {
                    if (!isset($that[$kk])) {
                        continue;
                    }
                    $that[$kk] = \Hook::fire('link', [$that[$kk], $kk, $k], $that);
                }
                return (string) $that;
            }, $content);
        }
    }
    return $content;
}

function link($value, $key, $name = null) {
    if (!$name || !\is_string($value)) {
        return $value;
    }
    extract($GLOBALS, \EXTR_SKIP);
    $v = $state->x->link->alter->{$name}->{$key} ?? 0;
    if (\is_callable($v)) {
        $value = \fire($v, [$value, $key, $name], $this);
    } else if ($v) {
        $value = \URL::long($value, false);
    }
    return $value;
}

\Hook::set('content', __NAMESPACE__ . "\\content", 0);
\Hook::set('link', __NAMESPACE__ . "\\link", 2);
