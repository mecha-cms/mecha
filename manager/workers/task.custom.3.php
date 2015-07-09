<?php

// Deleting custom CSS and JavaScript file of article ...
File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.txt')->delete();
File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.draft')->delete();
// Deleting custom PHP file of article ...
File::open(File::D($task_connect->path) . DS . $task_connect->slug . '.php')->delete();