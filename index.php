<?php

/**
 * ===========================================================
 *  Mecha - Content Management System
 *  Copyright (c) 2014 Taufik Nurrohman <http://mecha-cms.com>
 * ===========================================================
 */

define('DS', DIRECTORY_SEPARATOR);
define('MECHA_VERSION', '1.0.3');
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
define('SEPARATOR', '====');

include SYSTEM . DS . 'ignite.php';
include DECK . DS . 'launch.php';
include SYSTEM . DS . 'launch.php';