<?php

URL::_('long', function(string $path, $root = true) {
    global $url;
    // `URL::long('//example.com')`
    if (0 === strpos($path, '//')) {
        return rtrim($url['protocol'] . ':' . $path, '/');
    // `URL::long('/foo/bar/baz/qux')`
    } else if (0 === strpos($path, '/')) {
        if (false !== strpos('?#', $path[1] ?? P)) {
            $path = substr($path, 1);
        } else if (1 === strpos($path, '&')) {
            $path = '?' . substr($path, 2);
        }
        return rtrim($url->ground . $path, '/');
    }
    // `URL::long('&foo=bar&baz=qux')`
    $a = explode('?', $path, 2);
    if (1 === count($a) && false !== strpos($a[0], '&')) {
        $a = explode('&', strtr($a[0], ['&amp;' => '&']), 2);
        $path = implode('?', $a);
    }
    if (
        false === strpos($path, '://') &&
        0 !== strpos($path, 'data:') &&
        0 !== strpos($path, 'javascript:') &&
        0 !== strpos($path, '?') &&
        0 !== strpos($path, '&') &&
        0 !== strpos($path, '#')
    ) {
        return rtrim($url->{$root ? 'ground' : 'root'} . '/' . ltrim($path, '/'), '/');
    }
    return $path;
});

URL::_('short', function(string $path, $root = true) {
    global $url;
    if (0 === strpos($path, '//') && 0 !== strpos($path, '//' . $url->host)) {
        return $path; // Ignore external URL
    }
    if ($root) {
        $out = str_replace([
            // `http://127.0.0.1`
            P . $url->ground,
            // `//127.0.0.1`
            P . '//' . $url->host,
            P
        ], "", P . $path);
        return "" === $out ? '/' : $out;
    }
    return ltrim(str_replace([
        // `http://127.0.0.1/foo`
        P . $url->root,
        // `//127.0.0.1/foo`
        P . '//' . $url->host . $url->d
    ], "", P . $path), '/');
});
