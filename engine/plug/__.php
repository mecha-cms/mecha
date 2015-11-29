<?php

// First installation ...
if($installer = File::exist(ROOT . DS . 'knock.php')) {
    Config::load();
    Guardian::kick(File::url($installer));
}