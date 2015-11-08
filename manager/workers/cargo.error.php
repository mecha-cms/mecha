<?php

echo $messages;

if($content) {
    $path_destruct = $config->url_path . '/kill';
    include DECK . DS . 'workers' . DS . 'unit.editor.1.php';
} else {
    echo '<p>' . Config::speak('notify_empty', strtolower($speak->errors)) . '</p>';
}