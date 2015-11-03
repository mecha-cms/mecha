<?php

// < 1.1.3

if(
    file_exists(STATE . DS . 'config.txt') &&
    file_exists(STATE . DS . 'field.txt') &&
    file_exists(STATE . DS . 'menu.txt') &&
    file_exists(STATE . DS . 'shortcode.txt') &&
    file_exists(STATE . DS . 'tag.txt') &&
    file_exists(EXTEND) &&
    file_exists(CUSTOM) &&
    strpos(File::open(SYSTEM . DS . 'log' . DS . 'users.txt')->read('-'), ' @pilot)') !== false
) {
    // Self destruct ...
    File::open(SYSTEM . DS . 'plug' . DS . '__candle-light--1.1.3.php')->delete();
}

if($pre_113 = File::exist(STATE . DS . 'fields.txt')) {
    File::open($pre_113)->renameTo('field.txt');
}

if($pre_113 = File::exist(STATE . DS . 'menus.txt')) {
    File::open($pre_113)->renameTo('menu.txt');
}

if($pre_113 = File::exist(STATE . DS . 'shortcodes.txt')) {
    File::open($pre_113)->renameTo('shortcode.txt');
}

if($pre_113 = File::exist(STATE . DS . 'tags.txt')) {
    File::open($pre_113)->renameTo('tag.txt');
}

if( ! $pre_113 = File::exist(EXTEND)) {
    File::pocket(EXTEND);
}

if($pre_113 = File::exist(CARGO . DS . 'custom')) {
    File::open($pre_113)->moveTo(EXTEND);
}

if($pre_113 = File::exist(SYSTEM . DS . 'log' . DS . 'users.txt')) {
    $o = File::open($pre_113)->read();
    File::write(str_replace(
        array(
            ':pilot)',
            ':passenger)'
        ),
        array(
            ' @pilot)',
            ' @passenger)'
        ),
    $o))->saveTo($pre_113, 0600);
}