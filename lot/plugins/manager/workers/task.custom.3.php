<?php

// Deleting custom CSS and JavaScript file of post ...
File::open(CUSTOM . DS . Date::slug($id) . '.txt')->delete();
File::open(CUSTOM . DS . Date::slug($id) . '.draft')->delete();
Weapon::fire(array('on_custom_update', 'on_custom_destruct'), array($G, $P));
// Deleting custom PHP file of post ...
File::open(File::D($post->path) . DS . $post->slug . '.php')->delete();