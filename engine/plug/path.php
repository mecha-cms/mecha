<?php

Path::_('long', function(string $value, $ground = true) {
    $value = strtr($value, '/', DS);
    $d = $ground ? GROUND : ROOT;
    if (0 === strpos($value, $d)) {
        return $value;
    }
    return rtrim($d . DS . trim($value, DS), DS);
});

Path::_('short', function(string $value, $ground = true) {
    $value = strtr($value, '/', DS);
    $d = $ground ? GROUND : ROOT;
    if (0 !== strpos($value, $d)) {
        return $value;
    }
    return rtrim(substr($value, strlen($d)), DS);
});
