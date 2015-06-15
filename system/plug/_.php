<?php

// First installation ...
if($installer = File::exist(ROOT . DS . 'install.php')) {
    Config::load();
    Guardian::kick(File::url($installer));
}