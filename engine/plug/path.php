<?php

Path::_('long', function(string $path, $ground = true) {
    $path = strtr($path, '/', DS);
    $d = $ground ? GROUND : ROOT;
    if (0 === strpos($path, $d)) {
        return $path;
    }
    return rtrim($d . DS . trim($path, DS), DS);
});

Path::_('short', function(string $path, $ground = true) {
    $path = strtr($path, '/', DS);
    $d = $ground ? GROUND : ROOT;
    if (0 !== strpos($path, $d)) {
        return $path;
    }
    return rtrim(substr($path, strlen($d)), DS);
});
