<?php

Path::_('long', function(string $value, $root = true) {
    $value = strtr($value, '/', DS);
    $d = is_string($root) ? $root : ($root ? GROUND : ROOT);
    if (0 === strpos($value, $d)) {
        return $value;
    }
    return rtrim($d . DS . trim($value, DS), DS);
});

Path::_('short', function(string $value, $root = true) {
    $value = strtr($value, '/', DS);
    $d = is_string($root) ? $root : ($root ? GROUND : ROOT);
    if (0 !== strpos($value, $d)) {
        return $value;
    }
    return rtrim(substr($value, strlen($d)), DS);
});
