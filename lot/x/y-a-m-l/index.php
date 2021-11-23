<?php

// <https://github.com/mecha-cms/mecha/issues/94>
define("YAML\\SOH", '---');
define("YAML\\ETB", '---');
define("YAML\\EOT", '...');

require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'from.php';
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'to.php';

File::$state['type']['text/yaml'] = 1;

File::$state['x']['yaml'] = 1;
File::$state['x']['yml'] = 1;