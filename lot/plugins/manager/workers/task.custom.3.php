<?php

// Deleting custom CSS and JavaScript file of article ...
File::open(CUSTOM . DS . Date::slug($id) . '.txt')->delete();
File::open(CUSTOM . DS . Date::slug($id) . '.draft')->delete();
Weapon::fire(array('on_custom_update', 'on_custom_destruct'), array($G, $P));
// Deleting custom PHP file of article ...
File::open(File::D($task_connect->path) . DS . $task_connect->slug . '.php')->delete();