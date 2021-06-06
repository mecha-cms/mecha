<?php

URL::_('long', function(string $value, $ground = true, URL $url = null) {
    $url = $url ?? $GLOBALS['url'];
    $d = is_string($ground) ? $ground : $url->{$ground ? 'ground' : 'root'};
    // `URL::long('//example.com')`
    if (0 === strpos($value, '//')) {
        return rtrim(substr($url->protocol, 0, -2) . $value, '/');
    }
    // `URL::long('./foo/bar/baz')`
    if ('.' === $value || 0 === strpos($value, './')) {
        $value = substr($value, 1);
    }
    // `URL::long('/foo/bar/baz')`
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
        0 !== strpos($value, 'blob:') &&
        0 !== strpos($value, 'data:') &&
        0 !== strpos($value, 'javascript:') &&
        0 !== strpos($value, 'mailto:') &&
        !is_string($ground)
    ) {
        $d = preg_split('/[?&#]/', $url->current, 2)[0];
        if ($value && false === strpos('.?&#', $value[0])) {
            $d = dirname($d);
        }
        if (0 !== ($count = substr_count($value . '/', '../'))) {
            $d = dirname($d, $count);
            $value = strtr($value . '/', [
                '../' => ""
            ]);
        }
        return strtr(rtrim($d . '/' . trim($value, '/'), '/'), [
            '/?' => '?',
            '/&' => '?',
            '/#' => '#'
        ]);
    }
    return $value;
});

URL::_('short', function(string $value, $ground = true, URL $url = null) {
    $url = $url ?? $GLOBALS['url'];
    $d = is_string($ground) ? $ground : $url->{$ground ? 'ground' : 'root'};
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
    $value = substr($value, strlen(rtrim($d, '/')));
    return !is_string($ground) && $ground && "" === $value ? '/' : $value;
});
