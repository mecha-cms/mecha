<?php

/**
 * =========================================
 *  MECHA · CONTENT MANAGEMENT SYSTEM (CMS)
 * =========================================
 *  © 2014 – 2026 · Taufik Nurrohman
 * -----------------------------------------
 */

define('VERSION', '4.0.0'); // Current version

define('D', DIRECTORY_SEPARATOR); // Directory separator
define('N', PHP_EOL); // Line break
define('P', "\u{001A}"); // Placeholder character
define('S', "\u{200C}"); // Invisible character

define('PATH', __DIR__);
define('ENGINE', PATH . D . 'engine');
define('LOT', PATH . D . 'lot');

define('TEST', true); // Change to `true` to enable test mode

// When working with virtual host feature on a server —especially an Apache web server— you may find that the
// application’s URL format is not formed properly after installation. Typically, this issue is indicated by a messy
// appearance of the site, as the CSS and JavaScript file(s) fail to load. When you open the site’s source code, the URL
// prefix usually becomes the private root file path, which should be the public root site domain.
//
// Mecha uses `$_SERVER['DOCUMENT_ROOT']` value to determine whether the application is installed in a sub-folder or in
// the root directory. If the `$_SERVER['DOCUMENT_ROOT']` value is not recognized or does not align with the `PATH`
// constant value (if the application is installed in a sub-folder, the `$_SERVER['DOCUMENT_ROOT']` value will start the
// `PATH` constant value; if the application is installed in the root directory, the `$_SERVER['DOCUMENT_ROOT']` value
// will be the same as the `PATH` constant value), Mecha will most likely fail to automatically construct the site URL.
//
// If you don’t have access to modify the web server configuration file(s) to make the `DocumentRoot` value consistent
// with the `PATH` constant value, you can enter the value manually here. Mecha does not use this variable much for file
// and folder operation(s), so this change will not affect much.
$_SERVER['DOCUMENT_ROOT'] = $r = rtrim(strtr($_SERVER['DOCUMENT_ROOT'] ?? PATH, '/', D), D);
if (0 !== strpos(PATH . D, $r . D)) {
    // $_SERVER['DOCUMENT_ROOT'] = '/srv/http';
}

require ENGINE . D . 'f.php';
require ENGINE . D . 'fire.php';