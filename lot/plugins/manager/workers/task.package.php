<?php

if($package = File::exist($destination . DS . $name)) {
    Package::take($package)->extract(); // Extract the ZIP file
    File::open($package)->delete(); // Delete the ZIP file
    Config::load(); // Refresh the configuration data ...
    Guardian::kick(Config::get('manager.slug') . '/' . $segment);
}