<?php

if(isset($_FILES) && ! empty($_FILES)) {
    $accept = File::$config['file_extension_allow'];
    foreach($_FILES as $k => $v) {
        if( ! empty($field_d[$k]['value'])) {
            File::$config['file_extension_allow'] = explode(',', $field_d[$k]['value']);
        }
        if($v['size'] > 0 && $v['error'] === 0) {
            $name = $name_o = Text::parse($v['name'], '->safe_file_name');
            // Group substance by extension
            if($e = File::E($name, false)) {
                $name = $e . DS . $name;
            }
            // File already exists. Don't over-write and don't show the error message
            if(file_exists(SUBSTANCE . DS . $name)) {
                $field[$k] = File::url($name_o);
                Notify::info(Config::speak('notify_file_exist', '<code>' . $name_o . '</code>'));
            // Upload new file
            } else {
                File::upload($v, SUBSTANCE . DS . File::D($name));
                if( ! Notify::errors()) {
                    $field[$k] = File::url($name_o);
                    Weapon::fire(array('on_substance_update', 'on_substance_construct'), array($G, $P));
                }
            }
        }
        File::$config['file_extension_allow'] = $accept;
    }
    unset($accept);
}