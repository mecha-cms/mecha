<?php

/**
 * =========================================
 *  MECHA · CONTENT MANAGEMENT SYSTEM (CMS)
 * =========================================
 * © 2014 – 2017 Taufik Nurrohman
 * -----------------------------------------
 */

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('ROOT')) define('ROOT', __DIR__);

if (!defined('DENT')) define('DENT', '  '); // Default HTML indent
if (!defined('N')) define('N', "\n"); // Line break
if (!defined('T')) define('T', "\t"); // Tab
if (!defined('X')) define('X', "\x1A"); // Placeholder text

if (!defined('SESSION')) define('SESSION', null); // Change to a folder path to define `session_save_path`
if (!defined('DEBUG')) define('DEBUG', null); // Change to `true` to enable debug mode

if (!defined('ENGINE')) define('ENGINE', __DIR__ . DS . 'engine');
if (!defined('LOT')) define('LOT', __DIR__ . DS . 'lot');

foreach (glob(__DIR__ . DS . 'lot' . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $lot) {
    $s = strtoupper(str_replace(['-', '.'], ['_', '__'], basename($lot)));
    if (!defined($s)) {
        define($s, $lot);
    }
}

// Common HTML tag(s) allowed to be written in the form field
if (!defined('HTML_WISE_I')) define('HTML_WISE_I', 'a,abbr,b,br,cite,code,del,dfn,em,i,ins,kbd,mark,q,span,strong,sub,sup,time,u,var');
if (!defined('HTML_WISE_B')) define('HTML_WISE_B', 'address,blockquote,caption,dd,div,dl,dt,figcaption,figure,hr,h1,h2,h3,h4,h5,h6,li,ol,p,pre,table,tbody,tfoot,td,th,tr,ul');
if (!defined('HTML_WISE')) define('HTML_WISE', HTML_WISE_I . ',' . HTML_WISE_B);

// Common date format
if (!defined('DATE_WISE')) define('DATE_WISE', 'Y-m-d H:i:s');

// Common file type(s) allowed to be uploaded by the file manager
if (!defined('FONT_X')) define('FONT_X', 'eot,otf,svg,ttf,woff,woff2');
if (!defined('IMAGE_X')) define('IMAGE_X', 'bmp,cur,gif,ico,jpeg,jpg,png,svg');
if (!defined('MEDIA_X')) define('MEDIA_X', '3gp,avi,flv,mkv,mov,mp3,mp4,m4a,m4v,ogg,swf,wav,webm,wma');
if (!defined('PACKAGE_X')) define('PACKAGE_X', 'gz,iso,rar,tar,zip,zipx');
if (!defined('SCRIPT_X')) define('SCRIPT_X', 'archive,cache,css,data,draft,htaccess,html,js,json,log,page,php,stack,trash,txt,xml,yaml,yml');

require ENGINE . DS . 'ignite.php';
require ENGINE . DS . 'fire.php';