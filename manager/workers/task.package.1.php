<?php

$name = $_FILES['file']['name'];
$mime = $_FILES['file']['type'];
$extension = File::E($name);
$path = File::N($name);
if( ! empty($name)) {
    if(File::exist($task_connect_path . DS . $path)) {
        Notify::error(Config::speak('notify_folder_exist', '<code>' . $path . '</code>'));
    } else {
        if(
            strpos(',application/download,application/octet-stream,application/x-compressed,application/x-zip-compressed,application/zip,multipart/x-zip,', ',' . $mime . ',') === false ||
            strpos(',zip,', ',' . $extension . ',') === false
        ) {
            Notify::error(Config::speak('notify_invalid_file_extension', 'ZIP'));
        }
    }
} else {
    Notify::error($speak->notify_error_no_file_selected);
}