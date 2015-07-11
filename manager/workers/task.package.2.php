<?php

if($uploaded = File::exist($task_connect_path . DS . $name)) {
    Package::take($uploaded)->extract(); // Extract the ZIP file
    File::open($uploaded)->delete(); // Delete the ZIP file
    Config::load(); // Refresh the configuration data ...
    Guardian::kick(Config::get('manager')->slug . '/' . $task_connect_kick);
}