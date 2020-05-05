<?php

Guard::_('kick', function(string $path = null) {
    $path = $path ?? $GLOBALS['url']->current;
    header('Location: ' . URL::long($path, false));
    exit;
});
