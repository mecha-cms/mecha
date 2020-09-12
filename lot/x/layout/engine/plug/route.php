<?php

Route::_('content', function(string $v, $exit = true) {
    $v = Hook::fire('content', [$v], $this);
    ob_start('ob_gzhandler');
    echo $v; // The response body
    echo ob_get_clean();
    if ($exit) {
        Hook::fire('let');
        exit;
    }
});

Route::_('layout', function(string $layout, array $lot = []) {
    $layout = Hook::fire('layout', [$layout, $lot]);
    $a = explode('/', strtr($layout, DS, '/'), 2);
    // Automatic response status based on the first layout path
    if (is_numeric($a[0])) {
        $this->status((int) $a[0]);
    }
    if (null !== ($content = Layout::get($layout, $lot))) {
        $this->content($content, false);
        Hook::fire('let');
        exit;
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Layout <code>' . $layout . '</code> does not exist.');
    }
});

Route::_('lot', function(...$v) {
    return count($v) > 1 || isset($v[0]) && is_array($v[0]) ? Lot::set(...$v) : Lot::get(...$v);
});

Route::_('status', function(...$v) {
    return Lot::status(...$v);
});

Route::_('type', function(...$v) {
    return Lot::type(...$v);
});
