<?php

Guard::_('kick', function(string $path = null) {
    $path = $path ?? $GLOBALS['url']->current;
    header('location: ' . URL::long($path, false));
    exit;
});
