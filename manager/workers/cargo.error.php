<?php

echo $messages;

if($content) {
    $path_destruct = $config->url_path . '/kill';
    include __DIR__ . DS . 'unit.editor.1.php';
} else {
    echo '<p>' . Config::speak('notify_empty', strtolower($speak->errors)) . '</p>';
}