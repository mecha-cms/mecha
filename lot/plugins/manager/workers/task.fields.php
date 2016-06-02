<?php

// Take validation data internally
$field_d = Get::state_field(null, array(), true);
foreach($field as $k => $v) {
    $f = isset($field_d[$k]['type']) && $field_d[$k]['type'] === 'file';
    // Force validate input with `parser` property
    if(isset($field_d[$k]['parser']) && is_callable($field_d[$k]['parser'])) {
        $v = call_user_func($field_d[$k]['parser'], $v);
    }
    // Backend validation support(s) for HTML5 `pattern` and `required` attribute
    $attrs = $field_d[$k]['attributes'];
    $tt = trim($field_d[$k]['title']) !== "" ? $field_d[$k]['title'] : '<code>' . $k . '</code>';
    $vv = ! is_array($v) ? trim($v) : "";
    if( ! $vv) {
        if($attrs['required']) Notify::error(Config::speak('notify_error_empty_field', $tt));
    } else {
        if( ! empty($attrs['pattern'])) {
            $s = preg_replace('/(?<!\\\)#/', '\\#', $attrs['pattern']);
            if( ! preg_match('#^' . $s . '$#', $v)) {
                Notify::error(Config::speak('notify_invalid_format', array($tt, '<code>' . $s . '</code>')));
            }
        }
    }
    // Remove asset field value and data
    if($f && $v !== "") {
        File::open(SUBSTANCE . DS . $v)->delete();
        Weapon::fire(array('on_substance_update', 'on_substance_destruct'), array($G, $P));
        Notify::success(Config::speak('notify_file_deleted', '<code>' . $v . '</code>'));
        unset($field[$k]);
    }
    // Remove empty field value
    // File does not exist in `SUBSTANCE` folder
    if($v === "" || $f && ! is_file(SUBSTANCE . DS . $v)) {
        unset($field[$k]);
    }
}