<?php

foreach($field as $k => $v) {
    // Remove asset field value and data
    if(isset($v['remove']) && $v['type'][0] === 'f') {
        File::open(SUBSTANCE . DS . $v['remove'])->delete();
        Weapon::fire(array('on_substance_update', 'on_substance_destruct'), array($G, $P));
        Notify::success(Config::speak('notify_file_deleted', '<code>' . $v['remove'] . '</code>'));
        unset($field[$k]);
    }
    // Remove empty field value
    if( ! isset($v['value']) || $v['value'] === "") {
        unset($field[$k]);
    } else {
        if( ! file_exists(SUBSTANCE . DS . $v['value']) && $v['type'][0] === 'f') {
            unset($field[$k]);
        } else {
            $field[$k] = $v['value'];
        }
    }
}