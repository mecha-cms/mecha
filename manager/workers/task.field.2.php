<?php

// New file data
if(isset($_FILES) && ! empty($_FILES)) {
    $accept = File::$config['file_extension_allow'];
    foreach($_FILES as $k => $v) {
        if(isset($field[$k]['accept'])) {
            File::$config['file_extension_allow'] = explode(',', $field[$k]['accept']);
        }
        if($v['size'] > 0 && $v['error'] === 0) {
            $name = Text::parse($v['name'], '->safe_file_name');
            // File already exists. Don't overwrite and don't show the error message
            if(file_exists(SUBSTANCE . DS . $name)) {
                $field[$k]['value'] = $name;
                Notify::info(Config::speak('notify_file_exist', '<code>' . $name . '</code>'));
            // Upload new file
            } else {
                File::upload($v, SUBSTANCE);
                if( ! Notify::errors()) {
                    $field[$k]['value'] = $name;
                }
            }
        }
        File::$config['file_extension_allow'] = $accept;
    }
    unset($accept);
}