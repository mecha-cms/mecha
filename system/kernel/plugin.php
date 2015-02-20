<?php

class Plugin {

    public static $bucket = array();

    public static function construct($name, $meta = array()) {}

    public static function destruct($fn) {}

    public static function update($fn) {}

    public static function freeze($fn) {}

    public static function fire($fn) {}

}