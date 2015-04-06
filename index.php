<?php

/**
 * ================================================================
 *  Mecha - Content Management System
 *  Copyright (c) 2014-2015 Taufik Nurrohman <http://mecha-cms.com>
 * ================================================================
 */

define('DS', DIRECTORY_SEPARATOR);
define('MECHA_VERSION', '1.1.3');
define('ROOT', rtrim(__DIR__, '\\/'));
define('SYSTEM', ROOT . DS . 'system');
define('DECK', ROOT . DS . 'manager');
define('LANGUAGE', ROOT . DS . 'cabinet' . DS . 'languages');
define('ARTICLE', ROOT . DS . 'cabinet' . DS . 'articles');
define('PAGE', ROOT . DS . 'cabinet' . DS . 'pages');
define('RESPONSE', ROOT . DS . 'cabinet' . DS . 'responses');
define('STATE', ROOT . DS . 'cabinet' . DS . 'states');
define('CUSTOM', ROOT . DS . 'cabinet' . DS . 'custom');
define('ASSET', ROOT . DS . 'cabinet' . DS . 'assets');
define('PLUGIN', ROOT . DS . 'cabinet' . DS . 'plugins');
define('SHIELD', ROOT . DS . 'cabinet' . DS . 'shields');
define('CACHE', ROOT . DS . 'cabinet' . DS . 'scraps');

define('SEPARATOR', '===='); // Separator between the page header and page content
define('S', ':'); // Separator between the page header key and page header value
define('ASSET_VERSION_FORMAT', 'v=%d'); // For `foo/bar/baz.css?v=1425800809`
define('ES', '>'); // Self closing HTML tag's end character(s)
define('TAB', '  '); // Standard indentation on the page
define('NL', PHP_EOL); // New line character of HTML output
define('O_BEGIN', ""); // Begin HTML output
define('O_END', PHP_EOL); // End HTML output
define('HTML_PARSER', 'Markdown Extra'); // Depends on the type of HTML parser in the `plugins` folder

define('CSS_LIBRARY_PATH', "");
define('ICON_LIBRARY_PATH', 'maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
define('JS_LIBRARY_PATH', 'cdnjs.cloudflare.com/ajax/libs/zepto/1.1.4/zepto.min.js');

define('FONT_EXT', 'eot,otf,svg,ttf,woff,woff2');
define('IMAGE_EXT', 'bmp,cur,gif,ico,jpg,jpeg,png,svg');
define('MEDIA_EXT', 'avi,flv,mkv,mov,mp3,mp4,m4a,m4v,swf,wav,wma');
define('PACKAGE_EXT', 'gz,iso,rar,tar,zip,zipx');
define('SCRIPT_EXT', 'cache,css,draft,htaccess,hold,htm,html,js,json,jsonp,less,md,markdown,php,scss,txt,xml');

define('MAX_ERROR_FILE_SIZE', 1048576); // 1 MB

require SYSTEM . DS . 'ignite.php';
require SYSTEM . DS . 'launch.php';