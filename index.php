<?php

/**
 * ================================================================
 *  Mecha -- Content Management System
 *  Copyright (c) 2014-2015 Taufik Nurrohman <http://mecha-cms.com>
 * ================================================================
 */

define('DS', DIRECTORY_SEPARATOR);
define('MECHA_VERSION', '1.1.5');
define('ROOT', rtrim(__DIR__, '\\/'));
define('CARGO', ROOT . DS . 'cabinet');
define('DECK', ROOT . DS . 'manager');
define('SYSTEM', ROOT . DS . 'system');
define('LOG', SYSTEM . DS . 'log');
define('SESSION', null); // Replace this value with valid directory path to define custom `session_save_path`
define('LANGUAGE', CARGO . DS . 'languages');
define('ASSET', CARGO . DS . 'assets');
define('ARTICLE', CARGO . DS . 'articles');
define('PAGE', CARGO . DS . 'pages');
define('RESPONSE', CARGO . DS . 'responses');
define('EXTEND', CARGO . DS . 'extends');
define('STATE', CARGO . DS . 'states');
define('PLUGIN', CARGO . DS . 'plugins');
define('SHIELD', CARGO . DS . 'shields');
define('CACHE', CARGO . DS . 'scraps');
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
define('HTML_PARSER', 'Markdown Extra'); // Depends on the type of HTML parser in the `plugins` folder

define('CSS_LIBRARY_PATH', "");
define('ICON_LIBRARY_PATH', 'maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css');
define('JS_LIBRARY_PATH', 'cdnjs.cloudflare.com/ajax/libs/zepto/1.1.6/zepto.min.js');

define('FONT_EXT', 'eot,otf,svg,ttf,woff,woff2');
define('IMAGE_EXT', 'bmp,cur,gif,ico,jpeg,jpg,png,svg');
define('MEDIA_EXT', '3gp,avi,flv,mkv,mov,mp3,mp4,m4a,m4v,ogg,swf,wav,wma');
define('PACKAGE_EXT', 'gz,iso,rar,tar,zip,zipx');
define('SCRIPT_EXT', 'cache,css,draft,htaccess,hold,htm,html,js,json,jsonp,less,md,markdown,php,scss,txt,xml');

define('DEBUG', false); // `true` to enable debug mode
define('MAX_ERROR_FILE_SIZE', 1048576); // 1 MB

require SYSTEM . DS . 'ignite.php';
require SYSTEM . DS . 'launch.php';