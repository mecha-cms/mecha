<?php

/**
 * =================================================================
 *  Mecha -- Content Management System (CMS)
 *  Copyright (c) 2014-2016 Taufik Nurrohman
 * =================================================================
 */

define('MECHA_VERSION', '1.2.9');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(__DIR__, DS));
define('CARGO', ROOT . DS . 'lot');
define('ENGINE', ROOT . DS . 'engine');
define('LOG', ENGINE . DS . 'log');
define('SESSION', null); // Replace this value with valid directory path to define custom `session_save_path`
define('LANGUAGE', CARGO . DS . 'languages');
define('ASSET', CARGO . DS . 'assets');
define('POST', CARGO . DS . 'posts');
define('RESPONSE', CARGO . DS . 'responses');
define('EXTEND', CARGO . DS . 'extends');
define('STATE', CARGO . DS . 'states');
define('PLUGIN', CARGO . DS . 'plugins');
define('SHIELD', CARGO . DS . 'shields');
define('CACHE', CARGO . DS . 'scraps');
define('WORKER', CARGO . DS . 'workers');

define('ARTICLE', POST . DS . 'article');
define('PAGE', POST . DS . 'page');
define('COMMENT', RESPONSE . DS . 'comment');

define('CHUNK', EXTEND . DS . 'chunk');
define('CUSTOM', EXTEND . DS . 'custom');
define('SUBSTANCE', EXTEND . DS . 'substance');

define('SEPARATOR', '===='); // Separator between the page header and page content
define('S', ':'); // Separator between the page header's field key and page header's field value
define('ES', '>'); // Self closing HTML tag's end character(s)
define('TAB', '  '); // Standard indentation on the page
define('NL', PHP_EOL); // New line character of HTML output
define('O_BEGIN', ""); // Begin HTML output
define('O_END', PHP_EOL); // End HTML output
define('X', "\x1A"); // Placeholder text (internal only)

// Librar(y|ies)
define('CSS_LIBRARY_PATH', "");
define('ICON_LIBRARY_PATH', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
define('JS_LIBRARY_PATH', '//cdnjs.cloudflare.com/ajax/libs/zepto/1.1.6/zepto.min.js');

define('FONT_EXT', 'eot,otf,svg,ttf,woff,woff2');
define('IMAGE_EXT', 'bmp,cur,gif,ico,jpeg,jpg,png,svg');
define('MEDIA_EXT', '3gp,avi,flv,mkv,mov,mp3,mp4,m4a,m4v,ogg,swf,wav,wma');
define('PACKAGE_EXT', 'gz,iso,rar,tar,zip,zipx');
define('SCRIPT_EXT', 'archive,cache,css,draft,htaccess,hold,htm,html,js,json,jsonp,less,log,md,markdown,php,scss,txt,xml');

define('DEBUG', false); // `true` to enable debug mode
define('MAX_ERROR_FILE_SIZE', 1048576); // 1 MB

// Common HTML tag(s) allowed to be written in the form field
define('WISE_CELL_I', '<a><abbr><b><br><cite><code><del><dfn><em><i><ins><kbd><mark><q><span><strong><sub><sup><time><u><var>');
define('WISE_CELL_B', '<address><blockquote><caption><dd><div><dl><dt><figcaption><figure><hr><h1><h2><h3><h4><h5><h6><li><ol><p><pre><table><tbody><tfoot><td><th><tr><ul>');
define('WISE_CELL', WISE_CELL_I . WISE_CELL_B);

require ENGINE . DS . 'ignite.php';
require ENGINE . DS . 'launch.php';