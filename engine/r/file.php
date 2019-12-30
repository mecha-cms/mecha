<?php

function open(string $from) {
    if (is_file($from)) {
        return new File($from);
    }
    if (is_dir($from)) {
        return new Folder($from);
    }
    return false;
}