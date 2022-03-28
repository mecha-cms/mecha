<?php

foreach ([
    '.css' => function($value, $key) {
        $link = $value['link'] ?? "";
        $path = $value['path'] ?? "";
        $x = false !== strpos($link, '://') || 0 === strpos($link, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $link . '?v=' . (is_file($path) ? filemtime($path) : 0) : $link;
        extract($value[2], EXTR_SKIP);
        if (isset($href) && is_callable($href)) {
            $value[2]['href'] = fire($href, [$u, $value, $key], null, Asset::class);
        } else if (empty($href)) {
            $value[2]['href'] = $u;
        }
        $value[0] = 'link';
        $value[1] = false;
        $value[2]['rel'] = 'stylesheet';
        unset($value['link'], $value['path'], $value['stack']);
        return new HTML($value);
    },
    '.js' => function($value, $key) {
        $link = $value['link'] ?? "";
        $path = $value['path'] ?? "";
        $x = false !== strpos($link, '://') || 0 === strpos($link, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $link . '?v=' . (is_file($path) ? filemtime($path) : 0) : $link;
        extract($value[2], EXTR_SKIP);
        if (isset($src) && is_callable($src)) {
            $value[2]['src'] = fire($src, [$u, $value, $key], null, Asset::class);
        } else if (empty($src)) {
            $value[2]['src'] = $u;
        }
        $value[0] = 'script';
        unset($value['link'], $value['path'], $value['stack']);
        return new HTML($value);
    }
] as $k => $v) {
    Asset::_($k, $v);
}

foreach (['script', 'style', 'template'] as $v) {
    Asset::_($v, function(string $content, float $stack = 10, array $lot = []) use($v) {
        $id = !empty($lot['id']) ? $lot['id'] : $v . ':' . sprintf('%u', crc32($content));
        if (!isset(static::$lot[0][$v][$id])) {
            static::$lot[1][$v][$id] = [
                '0' => $v,
                '1' => n(trim($content)),
                '2' => $lot,
                'link' => null,
                'path' => null,
                'stack' => (float) $stack
            ];
        }
    });
}

function asset(...$lot) {
    return count($lot) < 2 ? Asset::get(...$lot) : Asset::set(...$lot);
}