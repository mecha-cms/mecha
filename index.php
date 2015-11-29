<?php

/**
 * ================================================================
 *  Mecha -- Content Management System
 *  Copyright (c) 2014-2015 Taufik Nurrohman <http://mecha-cms.com>
 * ================================================================
 */

define('DS', DIRECTORY_SEPARATOR);
define('MECHA_VERSION', '1.2.0');
define('ROOT', rtrim(__DIR__, DS));
define('LOT', ROOT . DS . 'lot');
define('ENGINE', ROOT . DS . 'engine');
define('LOG', ENGINE . DS . 'log');
define('SESSION', null); // Replace this value with valid directory path to define custom `session_save_path`
define('LANGUAGE', LOT . DS . 'languages');
define('ASSET', LOT . DS . 'assets');
define('POST', LOT . DS . 'posts');
define('RESPONSE', LOT . DS . 'responses');
define('EXTEND', LOT . DS . 'extends');
define('STATE', LOT . DS . 'states');
define('PLUGIN', LOT . DS . 'plugins');
define('SHIELD', LOT . DS . 'shields');
define('CACHE', LOT . DS . 'scraps');
define('WORKER', LOT . DS . 'workers');

define('ARTICLE', POST . DS . 'article');
define('PAGE', POST . DS . 'page');
define('COMMENT', RESPONSE . DS . 'comment');
define('CHUNK', EXTEND . DS . 'chunk');
define('CUSTOM', EXTEND . DS . 'custom');
define('SUBSTANCE', EXTEND . DS . 'substance');

define('SEPARATOR', '===='); // Separator between the page header and page content
define('S', ':'); // Separator between the page header's field key and page header's field value
define('ASSET_VERSION_FORMAT', 'v=%d'); // For `foo/bar/baz.css?v=1425800809`
define('ES', '>'); // Self closing HTML tag's end character(s)
define('TAB', '  '); // Standard indentation on the page
define('NL', PHP_EOL); // New line character of HTML output
define('O_BEGIN', ""); // Begin HTML output
define('O_END', PHP_EOL); // End HTML output

define('FONT_EXT', 'eot,otf,svg,ttf,woff,woff2');
define('IMAGE_EXT', 'bmp,cur,gif,ico,jpeg,jpg,png,svg');
define('MEDIA_EXT', '3gp,avi,flv,mkv,mov,mp3,mp4,m4a,m4v,ogg,swf,wav,wma');
define('PACKAGE_EXT', 'gz,iso,rar,tar,zip,zipx');
define('SCRIPT_EXT', 'archive,cache,css,draft,htaccess,hold,htm,html,js,json,jsonp,less,md,markdown,php,scss,txt,xml');

define('DEBUG', false); // `true` to enable debug mode
define('MAX_ERROR_FILE_SIZE', 1048576); // 1 MB

require ENGINE . DS . 'ignite.php';
require ENGINE . DS . 'launch.php';