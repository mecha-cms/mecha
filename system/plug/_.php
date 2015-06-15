<?php

// First installation ...
if($installer = File::exist(ROOT . DS . 'install.php')) {
    Guardian::kick(File::url($installer));
}