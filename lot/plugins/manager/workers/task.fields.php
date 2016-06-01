<?php

// Take validation data internally
$field_d = Get::state_field(null, array(), true);
foreach($field as $k => $v) {
    $f = $v['type'] === 'file';
    // Force validate input with `parser` property
    if(isset($field_d[$k]['parser']) && is_callable($field_d[$k]['parser'])) {
        $v['value'] = call_user_func($field_d[$k]['parser'], isset($v['value']) ? $v['value'] : "");
    }
    // Backend validation support(s) for HTML5 `pattern` and `required` attribute
    $attrs = $field_d[$k]['attributes'];
    $tt = ! empty($field_d[$k]['title']) ? $field_d[$k]['title'] : $k;
    $vv = isset($v['value']) && is_string($v['value']) ? trim($v['value']) : "";
    if( ! $vv) {
        if($attrs['required']) Notify::error(Config::speak('notify_error_empty_field', $tt));
    } else {
        if( ! empty($attrs['pattern'])) {
            $s = preg_replace('/(?<!\\\)#/', '\\#', $attrs['pattern']);
            if( ! preg_match('#^' . $s . '$#', $v['value'])) {
                Notify::error(Config::speak('notify_invalid_format', array($tt, '<code>' . $s . '</code>')));
            }
        }
    }
    // Remove asset field value and data
    if($f && isset($v['x'])) {
        File::open(SUBSTANCE . DS . $v['x'])->delete();
        Weapon::fire(array('on_substance_update', 'on_substance_destruct'), array($G, $P));
        Notify::success(Config::speak('notify_file_deleted', '<code>' . $v['x'] . '</code>'));
        unset($field[$k]);
    }
    // Remove empty field value
    if( ! isset($v['value']) || $v['value'] === "") {
        unset($field[$k]);
    } else {
        // File does not exist in `SUBSTANCE` folder
        if($f && ! is_file(SUBSTANCE . DS . File::E($v['value']) . DS . $v['value'])) {
            unset($field[$k]);
        } else {
            $field[$k] = $v['value'];
        }
    }
}