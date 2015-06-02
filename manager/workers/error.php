<?php echo $messages; ?>
<?php

if($the_content) {
    $path_destruct = $config->url_path . '/kill';
    include DECK . DS . 'workers' . DS . 'unit.editor.1.php';
}

?>