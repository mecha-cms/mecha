<?php

// Remove empty field value
foreach($field as $k => $v) {
    if(isset($v['remove']) && $v['type'][0] === 'f') {
        // Remove asset field and data
        File::open(SUBSTANCE . DS . $v['remove'])->delete();
        Notify::success(Config::speak('notify_file_deleted', '<code>' . $v['remove'] . '</code>'));
        unset($field[$k]);
    }
    if( ! isset($v['value']) || $v['value'] === "" || ( ! file_exists(SUBSTANCE . DS . $v['value']) && $v['type'][0] === 'f')) {
        unset($field[$k]);
    }
}