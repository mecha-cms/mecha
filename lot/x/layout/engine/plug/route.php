<?php

Route::_('content', function(string $value, $exit = true) {
    $value = Hook::fire('content', [$value], $this);
    ob_start();
    ob_start('ob_gzhandler');
    echo $value; // The response body
    ob_end_flush();
    // <https://www.php.net/manual/en/function.ob-get-length.php#59294>
    $this->lot('content-length', ob_get_length());
    echo ob_get_clean();
    if ($exit) {
        Hook::fire('let');
        exit;
    }
});

Route::_('layout', function(string $id, array $lot = []) {
    $id = Hook::fire('layout', [$id, $lot]);
    $chops = explode('/', strtr($id, DS, '/'), 2);
    // Automatic response status based on the first layout path
    if (is_numeric($chops[0])) {
        $this->status((int) $chops[0]);
    }
    if (null !== ($content = Layout::get($id, $lot))) {
        $this->content($content, false);
        Hook::fire('let');
        exit;
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Layout <code>' . $id . '</code> does not exist.');
    }
});

Route::_('lot', function(...$lot) {
    return count($lot) > 1 || isset($lot[0]) && is_array($lot[0]) ? Lot::set(...$lot) : Lot::get(...$lot);
});

Route::_('status', function(...$lot) {
    return Lot::status(...$lot);
});

Route::_('type', function(...$lot) {
    return Lot::type(...$lot);
});