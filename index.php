<?php

/**
 * =========================================
 *  MECHA · CONTENT MANAGEMENT SYSTEM (CMS)
 * =========================================
 *  © 2014 – 2023 · Taufik Nurrohman
 * -----------------------------------------
 */

define('VERSION', '3.0.0'); // Current version

define('D', DIRECTORY_SEPARATOR); // Directory separator
define('N', PHP_EOL); // Line break
define('P', "\u{001A}"); // Placeholder character
define('S', "\u{200C}"); // Invisible character

define('PATH', __DIR__);
define('ENGINE', PATH . D . 'engine');
define('LOT', PATH . D . 'lot');

define('TEST', false); // Change to `true` to enable test mode

require ENGINE . D . 'f.php';
require ENGINE . D . 'fire.php';