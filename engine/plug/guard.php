<?php

Guard::_('kick', function(string $path = null, int $status = null) {
    $path = $path ?? $GLOBALS['url']->current;
    header('location: ' . URL::long($path, false), true, $status ?? 301);
    exit;
});