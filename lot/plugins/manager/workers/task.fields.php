<?php

// Take validation data internally
$field_d = Get::state_field(null, array(), true);
include __DIR__ . DS . 'task.substance.ignite.php';
foreach($field as $k => $v) {
    // Force validate input with `parser` property
    if(isset($field_d[$k]['parser']) && is_callable($field_d[$k]['parser'])) {
        $field[$k] = $v = call_user_func($field_d[$k]['parser'], $v);
    }
    // Backend validation support(s) for HTML5 `pattern` and `required` attribute
    $attrs = $field_d[$k]['attributes'];
    $tt = trim($field_d[$k]['title']) !== "" ? $field_d[$k]['title'] : '<code>' . $k . '</code>';
    $vv = ! is_array($v) ? trim($v) : "";
    if( ! $vv) {
        if( ! empty($attrs['required'])) {
            Notify::error(Config::speak('notify_error_empty_field', $tt));
        }
    } else {
        if( ! empty($attrs['pattern'])) {
            $s = preg_replace('%(?<!\\\)#%', '\\#', $attrs['pattern']);
            if( ! preg_match('#^' . $s . '$#', $v)) {
                Notify::error(Config::speak('notify_invalid_format', array($tt, '<code>' . $s . '</code>')));
            }
        }
    }
    // Remove asset field value and data
    if(isset($field_d[$k]['type']) && $field_d[$k]['type'] === 'file') {
        // Perform file delete
        if(strpos($v, '*') === 0) {
            $v = substr($v, 1);
            File::open(SUBSTANCE . DS . File::E($v) . DS . $v)->delete();
            Weapon::fire(array('on_substance_update', 'on_substance_destruct'), array($G, $P));
            Notify::success(Config::speak('notify_file_deleted', '<code>' . $v . '</code>'));
            unset($field[$k]);
        // File does not exist in `SUBSTANCE` folder
        } else if( ! is_file(SUBSTANCE . DS . File::E($v) . DS . $v)) {
            unset($field[$k]);
        }
    // else ...
    } else {
        // Remove empty field value
        if($v === "") unset($field[$k]);
    }
}

ksort($field);