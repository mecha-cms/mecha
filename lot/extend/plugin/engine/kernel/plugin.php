<?php

class Plugin extends Extend {

    public static function exist($input, $fail = false) {
        return Folder::exist(PLUGIN . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $id = str_replace('.', '\\', basename(array_shift($lot)));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: PLUGIN;
        return parent::state($id, $key, $fail, $folder);
    }

}