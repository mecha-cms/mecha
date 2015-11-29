<?php

// default
Notify::plug('bare', function($text, $icon = 'bell', $kind = 'default') {
    Notify::add($kind, ($icon ? '<i class="fa fa-fw fa-' . $icon . '"></i> ' : "") . $text);
});

// success
Notify::plug('success', function($text, $icon = 'check') {
    Notify::bare($text, $icon, 'success');
    Guardian::forget();
});

// info
Notify::plug('info', function($text, $icon = 'info-circle') {
    Notify::bare($text, $icon, 'info');
});

// warning
Notify::plug('warning', function($text, $icon = 'exclamation-triangle') {
    Notify::bare($text, $icon, 'warning');
    Guardian::memorize();
    Notify::$errors++;
});

// error
Notify::plug('error', function($text, $icon = 'times') {
    Notify::bare($text, $icon, 'error');
    Guardian::memorize();
    Notify::$errors++;
});