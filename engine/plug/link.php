<?php

function long(string $value) {
    $base = ($link = lot('link'))->base;
    // `long("")`
    if ("" === $value) {
        return $link->current;
    }
    // `long('/')`
    if ('/' === $value) {
        return $base;
    }
    // `long('//127.0.0.1')`
    if (0 === strpos($value, '//')) {
        return $link->scheme . $value;
    }
    if (
        0 === strpos($value, 'blob:') ||
        0 === strpos($value, 'data:') ||
        0 === strpos($value, 'javascript:') ||
        0 === strpos($value, 'mailto:') ||
        false !== strpos($value, '://')
    ) {
        return $value;
    }
    // `long('./foo/bar')`
    if ($value === '.' || 0 === strpos($value, './')) {
        $value = substr($value, 1);
    }
    // `long('/foo/bar')`
    if (0 === strpos($value, '/')) {
        return $base . (strspn($value = substr($value, 1), '?&#') ? "" : '/') . $value;
    }
    $path = substr($v = $link->current, 0, strcspn($v, '?&#'));
    // `long('foo/bar')`
    if ($value && strcspn($value, '.?&#') && $path !== $base) {
        $path = dirname($path);
    }
    // `long('../foo/bar')`
    while (0 === strpos($value, '../') && $path !== $base) {
        $path = dirname($path);
        $value = substr($value, 3);
    }
    return strtr(rtrim($path . '/' . ltrim($value, '/'), '/'), [
        '/#' => '#',
        '/&' => '?',
        '/?' => '?'
    ]);
}

function short(string $value) {
    $base = ($link = lot('link'))->base;
    if (0 === strpos($value, '//')) {
        $value = $link->scheme . substr($value, 2);
    }
    // Ignore external link(s)
    if (0 !== strpos($value . '/', $base . '/')) {
        return $value;
    }
    return "" === ($value = substr($value, strlen($base))) ? '/' : (0 === strpos($value, '&') ? '?' . substr($value, 1) : $value);
}