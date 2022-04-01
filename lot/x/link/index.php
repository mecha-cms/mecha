<?php

namespace x\link {
    // Get sub-folder path relative to the server’s root
    $sub = \trim(\strtr(\PATH . \D, [\rtrim(\strtr($_SERVER['DOCUMENT_ROOT'], '/', \D), \D) . \D => ""]), \D);
    // Set correct base URL
    \define(__NAMESPACE__ . "\\host", $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "");
    \define(__NAMESPACE__ . "\\index", \x\link\host . ($sub ? '/' . $sub : ""));
    function content($content) {
        if (!$content || false === \strpos($content, '<')) {
            return $content;
        }
        \extract($GLOBALS, \EXTR_SKIP);
        $alter = $state->x->link ?? [];
        $alter_content = (array) ($alter->content ?? []);
        $alter_data = (array) ($alter->data ?? []);
        $z = '(?:\s(?:[a-z\d:-]+=(?:"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\')|[^\/>])*?)';
        if ($alter_content) {
            foreach ($alter_content as $k => $v) {
                if (!$v || false === \strpos($content, '</' . $k . '>')) {
                    continue;
                }
                $content = \preg_replace_callback('/(<' . \x($k) . $z . '?>)([\s\S]*?)(<\/' . \x($k) . '>)/i', static function($m) use($v) {
                    $m[2] = \is_callable($v) ? \fire($v, [$m[2], (new \HTML($m[1]))[2] ?? []]) : \x\link\link($m[2]);
                    return $m[1] . $m[2] . $m[3];
                }, $content);
            }
        }
        if ($alter_data) {
            $keep = (static function($tags) use($z) {
                $out = [];
                foreach ($tags as $tag) {
                    $out[] = '<' . \x($tag) . $z . '?>[\s\S]*?<\/' . \x($tag) . '>';
                }
                return \implode('|', $out);
            })(\array_keys($alter_content));
            $out = "";
            foreach (\preg_split('/(' . $keep . ')/i', $content, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $part) {
                $n = \strtok(\substr($part, 1, -1), " \n\r\t>");
                if ($part && '<' === $part[0] && '>' === \substr($part, -1) && '</' . $n . '>' === \substr($part, -(\strlen($n) + 3))) {
                    $out .= !empty($alter_data[$n]) ? \preg_replace_callback('/^<[a-z\d:-]+' . $z . '?>/', static function($m) use($alter_data) {
                        return \x\link\data($m[0], $alter_data);
                    }, $part) : $part;
                } else {
                    $out .= \x\link\data($part, $alter_data);
                }
            }
            $content = $out;
        }
        return $content;
    }
    function data($content, $data) {
        if (!$content || false === \strpos($content, '<')) {
            return $content;
        }
        $z = '(?:\s(?:[a-z\d:-]+=(?:"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\')|[^\/>])*?)';
        foreach ($data as $k => $v) {
            if (!$v || (
                false === \strpos($content, '</' . $k . '>') &&
                false === \strpos($content, '<' . $k . ' ') &&
                false === \strpos($content, '<' . $k . "\n") &&
                false === \strpos($content, '<' . $k . "\r") &&
                false === \strpos($content, '<' . $k . "\t")
            )) {
                continue;
            }
            $content = \preg_replace_callback('/<' . \x($k) . '(' . $z . ')>/i', static function($m) use($k, $v) {
                if (false === \strpos($m[1], '=')) {
                    return $m[0];
                }
                $that = new \HTML($m[0]);
                // Need to do the hard way for the `on*` and `style` attribute(s)
                foreach ($that[2] as $kk => $vv) {
                    if (0 !== \strpos($kk, 'on') && 'style' !== $kk) {
                        continue;
                    }
                    $vvv = $that[$kk];
                    if (\is_callable($vv = $v->{$kk} ?? \P)) {
                        $vvv = \fire($vv, [$vvv, $kk, $k], $that);
                    } else {
                        $vvv = \call_user_func(__NAMESPACE__ . "\\content\\" . ('style' !== $kk ? 'script' : $kk), $vvv, $that[2]);
                    }
                    $that[$kk] = $vvv;
                }
                foreach ($v as $kk => $vv) {
                    if (!$vv || !isset($that[$kk])) {
                        continue;
                    }
                    $vvv = $that[$kk];
                    if (\is_callable($vv)) {
                        $vvv = \fire($vv, [$vvv, $kk, $k], $that);
                    } else {
                        $vvv = \x\link\link($vvv);
                    }
                    $that[$kk] = $vvv;
                }
                return (string) $that;
            }, $content);
        }
        return $content;
    }
    function kick($path) {
        return \x\link\link($path ?? $GLOBALS['url']->current);
    }
    function link($path) {
        return null !== $path ? \strtr(\long($path), ['://' . \x\link\host => '://' . \x\link\index]) : null;
    }
    \Hook::set('content', __NAMESPACE__ . "\\content", 0);
    \Hook::set('kick', __NAMESPACE__ . "\\kick", 0);
    \Hook::set('link', __NAMESPACE__ . "\\link", 0);
}

namespace x\link\content {
    function script($content) {
        if (false === \strpos($content, '://')) {
            return $content;
        }
        $content = \preg_replace_callback('/\bhttps?:\/\/[^\s"<]+\b\/?/i', static function($m) {
            return \x\link\link($m[0]);
        }, $content);
        return $content;
    }
    function style($content) {
        if (false !== \strpos($content, 'url(')) {
            $content = \preg_replace_callback('/\burl\(([^()]+)\)/', static function($m) {
                if ('"' === $m[1][0] && '"' === \substr($m[1], -1)) {
                    return 'url("' . \long(\substr($m[1], 1, -1)) . '")';
                }
                if ("'" === $m[1][0] && "'" === \substr($m[1], -1)) {
                    return "url('" . \long(\substr($m[1], 1, -1)) . "')";
                }
                return 'url(' . \long($m[1]) . ')';
            }, $content);
        }
        if (false === \strpos($content, '://')) {
            return $content;
        }
        $content = \preg_replace_callback('/\bhttps?:\/\/[^\s"<]+\b\/?/i', static function($m) {
            return \x\link\link($m[0]);
        }, $content);
        return $content;
    }
}

namespace x\link\data\img {
    function srcset($value) {
        if (!$value) {
            return $value;
        }
        $out = "";
        foreach (\preg_split('/(\s*,\s*)(?!,)/', $value, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if (',' === \trim($v)) {
                $out .= $v;
                continue;
            }
            $out .= \x\link\link(\rtrim($v, ','));
        }
        return $out;
    }
}

namespace x\link\data\svg {
    function href($value) {
        if ($value && \is_string($value) && '#' === $value[0]) {
            // Do not resolve hash-only value!
            return $value;
        }
        return \x\link\link($value);
    }
}