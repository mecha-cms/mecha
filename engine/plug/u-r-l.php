<?php

URL::_('long', function(string $value, $ground = true) {
    extract($GLOBALS, EXTR_SKIP);
    $d = $url->{$ground ? 'ground' : 'root'};
    // `URL::long('//example.com')`
    if (0 === strpos($value, '//')) {
        return rtrim($url['protocol'] . ':' . $value, '/');
    }
    // `URL::long('/foo/bar/baz/qux')`
    if (0 === strpos($value, '/')) {
        if (false !== strpos('?#', $value[1] ?? P)) {
            $value = substr($value, 1);
        } else if (1 === strpos($value, '&')) {
            $value = '?' . substr($value, 2);
        }
        return rtrim($d . $value, '/');
    }
    // `URL::long('?foo=bar&baz=qux')`
    if (
        false === strpos($value, '://') &&
        0 !== strpos($value, 'data:') &&
        0 !== strpos($value, 'javascript:')
    ) {
        return strtr(rtrim($d . '/' . trim($value, '/'), '/'), [
            '/?' => '?',
            '/&' => '?',
            '/#' => '#'
        ]);
    }
    return $value;
});

URL::_('short', function(string $value, $ground = true) {
    extract($GLOBALS, EXTR_SKIP);
    $d = $url->{$ground ? 'ground' : 'root'};
    if (0 === strpos($value, '//')) {
        if (0 !== strpos($value, '//' . $url->host)) {
            return $value; // Ignore external URL
        }
        $value = $url->protocol . substr($value, 2);
    } else {
        if (0 !== strpos($value, $url . "")) {
            return $value; // Ignore external URL
        }
    }
    return rtrim(substr($value, strlen($d)), '/');
});
