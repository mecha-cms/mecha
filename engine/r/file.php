<?php

function open(string $path) {
    if (is_file($path)) {
        return new File($path);
    }
    if (is_dir($path)) {
        return new Folder($path);
    }
    return false;
}
