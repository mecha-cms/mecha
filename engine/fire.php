<?php

// Enable/disable debug mode (default is `null`)
if (defined('DEBUG')) {
    if (!is_dir($d = ENGINE . DS . 'log')) {
        mkdir($d, 0775, true);
    }
    ini_set('error_log', $d . DS . 'error');
    if (DEBUG) {
        ini_set('max_execution_time', 300); // 5 minute(s)
        if (true === DEBUG) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
            ini_set('html_errors', 1);
        }
    } else if (false === DEBUG) {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
    }
}

$vars = [&$_GET, &$_POST, &$_REQUEST];
array_walk_recursive($vars, function(&$v) {
    // Trim white-space and normalize line-break
    $v = trim(strtr($v, ["\r\n" => "\n", "\r" => "\n"]));
    // Convert all empty value to `null`
    $v = "" === $v ? null : $v;
});

// Normalize `$_FILES` value to `$_POST`
if ('POST' === $_SERVER['REQUEST_METHOD']) {
    foreach ($_FILES as $k => $v) {
        foreach ($v as $kk => $vv) {
            if (is_array($vv)) {
                foreach ($vv as $kkk => $vvv) {
                    $_POST[$k][$kkk][$kk] = $vvv;
                }
            } else {
                $_POST[$k][$kk] = $vv;
            }
        }
    }
}

// Load class(es)…
d(($f = ENGINE . DS) . 'kernel', function($v, $n) use($f) {
    $f .= 'plug' . DS . $n . '.php';
    if (is_file($f)) {
        extract($GLOBALS, EXTR_SKIP);
        require $f;
    }
});

// Set default state(s)…
$state = is_file($f = ROOT . DS . 'state.php') ? require $f : [];
$GLOBALS['state'] = $state = new State($state);

// Boot…
require __DIR__ . DS . 'r' . DS . 'anemon.php';
require __DIR__ . DS . 'r' . DS . 'cache.php';
require __DIR__ . DS . 'r' . DS . 'cookie.php';
require __DIR__ . DS . 'r' . DS . 'file.php';
require __DIR__ . DS . 'r' . DS . 'guard.php';
require __DIR__ . DS . 'r' . DS . 'hook.php';
require __DIR__ . DS . 'r' . DS . 'lot.php';
require __DIR__ . DS . 'r' . DS . 'route.php';
require __DIR__ . DS . 'r' . DS . 'session.php';
require __DIR__ . DS . 'r' . DS . 'state.php';
require __DIR__ . DS . 'r' . DS . 'time.php';
require __DIR__ . DS . 'r' . DS . 'u-r-l.php';

$uses = [];
$uses_x = $GLOBALS['X'][0] ?? [];
foreach (glob(LOT . DS . 'x' . DS . '*' . DS . 'index.php', GLOB_NOSORT) as $v) {
    if (empty($uses_x[$v])) {
        $n = basename($r = dirname($v));
        $uses[$v] = content($r . DS . $n) ?? $n;
        // Load state(s)…
        State::set('x.' . ($k = strtr($n, ['.' => "\\."])), []);
        if (is_file($v = $r . DS . 'state.php')) {
            (function($k, $v) {
                extract($GLOBALS, EXTR_SKIP);
                State::set('x.' . $k, (array) require $v);
            })($k, $v);
        }
    }
}

// Sort by name
natsort($uses);
$GLOBALS['X'][1] = $uses = array_keys($uses);

// Load class(es)…
foreach ($uses as $v) {
    d(($k = dirname($v) . DS . 'engine' . DS) . 'kernel', function($v, $n) use($k) {
        $k .= 'plug' . DS . $n . '.php';
        if (is_file($k)) {
            extract($GLOBALS, EXTR_SKIP);
            require $k;
        }
    });
}

// Load extension(s)…
foreach ($uses as $v) {
    (function($v) {
        // Load task(s)…
        if (is_file($k = dirname($v) . DS . 'task.php')) {
            (function($k) {
                extract($GLOBALS, EXTR_SKIP);
                require $k;
            })($k);
        }
        extract($GLOBALS, EXTR_SKIP);
        require $v;
    })($v);
}
