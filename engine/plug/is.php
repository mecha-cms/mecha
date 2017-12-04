<?php

foreach([
    'anemon' => '__is_anemon__',
    'anemon_0' => '__is_anemon_0__',
    'anemon_a' => '__is_anemon_a__',
    'json' => '__is_json__',
    'j_s_o_n' => '__is_json__' , // alias
    'serialize' => '__is_serialize__'
] as $k => $v) {
    Is::_($k, $v);
}