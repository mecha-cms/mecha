<?php

// Deleting custom CSS and JavaScript file of article ...
File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.txt')->delete();
File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.draft')->delete();
Weapon::fire('on_custom_update', array($G, $P));
Weapon::fire('on_custom_destruct', array($G, $P));
// Deleting custom PHP file of article ...
File::open(File::D($task_connect->path) . DS . $task_connect->slug . '.php')->delete();