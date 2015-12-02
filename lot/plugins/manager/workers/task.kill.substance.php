<?php

// Deleting substance(s)
if(isset($post->fields) && is_object($post->fields)) {
    foreach($post->fields as $field) {
        $file = SUBSTANCE . DS . File::path($field);
        if(file_exists($file) && is_file($file)) {
            File::open($file)->delete();
            Weapon::fire(array('on_substance_update', 'on_substance_destruct'), array($G, $P));
        }
    }
}