<?php

foreach($field as $k => $v) {
    $f = $v['type'] === 'file' || $v['type'] === 'f';
    // Remove asset field value and data
    if($f && isset($v['remove'])) {
        File::open(SUBSTANCE . DS . $v['remove'])->delete();
        Weapon::fire(array('on_substance_update', 'on_substance_destruct'), array($G, $P));
        Notify::success(Config::speak('notify_file_deleted', '<code>' . $v['remove'] . '</code>'));
        unset($field[$k]);
    }
    // Remove empty field value
    if( ! isset($v['value']) || $v['value'] === "") {
        unset($field[$k]);
    } else {
        $e = File::E($v['value']);
        if($f && ! file_exists(SUBSTANCE . DS . $e . DS . $v['value'])) {
            unset($field[$k]);
        } else {
            $field[$k] = $v['value'];
        }
    }
}