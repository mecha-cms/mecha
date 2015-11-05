<?php

// Deleting substance(s)
if(isset($task_connect->fields) && is_object($task_connect->fields)) {
    foreach($task_connect->fields as $field) {
        $file = SUBSTANCE . DS . File::path($field);
        if(file_exists($file) && is_file($file)) {
            File::open($file)->delete();
            Weapon::fire('on_substance_update', array($G, $P));
            Weapon::fire('on_substance_destruct', array($G, $P));
        }
    }
}