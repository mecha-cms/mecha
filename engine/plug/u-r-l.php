<?php

URL::_('long', function(string $path, $ground = true) {
    extract($GLOBALS, EXTR_SKIP);
    $d = $url->{$ground ? 'ground' : 'root'};
    // `URL::long('//example.com')`
    if (0 === strpos($path, '//')) {
        return rtrim($url['protocol'] . ':' . $path, '/');
    }
    // `URL::long('/foo/bar/baz/qux')`
    if (0 === strpos($path, '/')) {
        if (false !== strpos('?#', $path[1] ?? P)) {
            $path = substr($path, 1);
        } else if (1 === strpos($path, '&')) {
            $path = '?' . substr($path, 2);
        }
        return rtrim($d . $path, '/');
    }
    // `URL::long('?foo=bar&baz=qux')`
    if (
        false === strpos($path, '://') &&
        0 !== strpos($path, 'data:') &&
        0 !== strpos($path, 'javascript:')
    ) {
        return strtr(rtrim($d . '/' . trim($path, '/'), '/'), [
            '/?' => '?',
            '/&' => '?',
            '/#' => '#'
        ]);
    }
    return $path;
});

URL::_('short', function(string $path, $ground = true) {
    extract($GLOBALS, EXTR_SKIP);
    $d = $url->{$ground ? 'ground' : 'root'};
    if (0 === strpos($path, '//')) {
        if (0 !== strpos($path, '//' . $url->host)) {
            return $path; // Ignore external URL
        }
        $path = $url->protocol . substr($path, 2);
    } else {
        if (0 !== strpos($path, $url . "")) {
            return $path; // Ignore external URL
        }
    }
    return rtrim(substr($path, strlen($d)), '/');
});
