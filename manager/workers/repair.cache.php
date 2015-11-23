<?php echo $messages; ?>
<form class="form-repair form-cache" id="form-repair" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php

$path_reject = $config->manager->slug . '/cache';
$path_destruct = $path_reject . '/kill/file:' . File::url(str_replace(CACHE . DS, "", $path));

include __DIR__ . DS . 'unit.editor.2.php';

?>
<?php echo Form::hidden('token', $token); ?>
</form>