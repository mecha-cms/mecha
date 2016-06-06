<?php

$name = $_FILES['file']['name']; // set `$name` variable
$mime = $_FILES['file']['type']; // set `$mime` variable
$extension = File::E($name); // set `$extension` variable
$extension_accept = array('zip');
$mime_accept = array(
    'application/download',
    'application/octet-stream',
    'application/x-compressed',
    'application/x-zip-compressed',
    'application/zip',
    'multipart/x-zip'
);
$path = File::N($name); // set `$path` variable
if( ! empty($name)) {
    if(File::exist($destination . DS . $path)) {
        Notify::error(Config::speak('notify_folder_exist', '<code>' . $path . '</code>'));
    } else {
        if( ! Mecha::walk($mime_accept)->has($mime) || ! Mecha::walk($extension_accept)->has($extension)) {
            Notify::error(Config::speak('notify_invalid_file_extension', 'ZIP'));
        }
    }
} else {
    Notify::error($speak->notify_error_no_file_selected);
}