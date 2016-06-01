<?php

// Deleting substance(s)
if(isset($post->fields) && is_object($post->fields)) {
    foreach($post->fields as $field) {
        if( ! is_string($field)) continue;
        if( ! $e = File::E($field)) continue;
        $file = SUBSTANCE . DS . $e . DS . File::path($field);
        if(is_file($file)) {
            File::open($file)->delete();
            Weapon::fire(array('on_substance_update', 'on_substance_destruct'), array($G, $P));
        }
    }
}