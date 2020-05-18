<?php

foreach ([
    '.css' => function($value, $key) {
        $path = $value['path'];
        $url = $value['url'];
        $x = false !== strpos($url, '://') || 0 === strpos($url, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $url . '?v=' . (is_file($path) ? filemtime($path) : 0) : $url;
        extract($value[2], EXTR_SKIP);
        if (isset($href) && is_callable($href)) {
            $value[2]['href'] = fire($href, [$u, $value, $key], null, Asset::class);
        } else if (empty($href)) {
            $value[2]['href'] = $u;
        }
        $value[0] = 'link';
        $value[1] = false;
        $value[2]['rel'] = 'stylesheet';
        unset($value['path'], $value['stack'], $value['url']);
        return new HTML($value);
    },
    '.js' => function($value, $key) {
        $path = $value['path'];
        $url = $value['url'];
        $x = false !== strpos($url, '://') || 0 === strpos($url, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $url . '?v=' . (is_file($path) ? filemtime($path) : 0) : $url;
        extract($value[2], EXTR_SKIP);
        if (isset($src) && is_callable($src)) {
            $value[2]['src'] = fire($src, [$u, $value, $key], null, Asset::class);
        } else if (empty($src)) {
            $value[2]['src'] = $u;
        }
        $value[0] = 'script';
        unset($value['path'], $value['stack'], $value['url']);
        return new HTML($value);
    }
] as $k => $v) {
    Asset::_($k, $v);
}

foreach (['.gif', '.jpg', '.jpeg', '.png'] as $v) {
    Asset::_($v, function($value, $key) {
        $path = $value['path'];
        $url = $value['url'];
        $x = false !== strpos($url, '://') || 0 === strpos($url, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $url . '?v=' . (is_file($path) ? filemtime($path) : 0) : $url;
        extract($value[2], EXTR_SKIP);
        if (isset($src) && is_callable($src)) {
            $value[2]['src'] = fire($src, [$u, $value, $key], null, Asset::class);
        } else if (empty($src)) {
            $value[2]['src'] = $u;
        }
        $value[0] = 'img';
        $value[1] = false;
        unset($value['path'], $value['stack'], $value['url']);
        return new HTML($value);
    });
}

foreach (['script', 'style', 'template'] as $v) {
    Asset::_($v, function(string $content, float $stack = 10, array $data = []) use($v) {
        $id = $data['id'] ?? $v . ':' . sprintf('%u', crc32($content));
        if (!isset(static::$lot[0][$v][$id])) {
            static::$lot[1][$v][$id] = [
                '0' => $v,
                '1' => n(trim($content)),
                '2' => $data,
                'path' => null,
                'url' => null,
                'stack' => (float) $stack
            ];
        }
    });
}
