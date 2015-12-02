<?php echo $messages; ?>
<form class="form-<?php echo is_null($path) ? 'ignite' : 'repair'; ?> form-shield" id="form-<?php echo is_null($path) ? 'ignite' : 'repair'; ?>" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php

$path_reject = $config->manager->slug . '/shield/' . $folder;
$path_destruct = $path_reject . '/kill/file:' . File::url(str_replace(SHIELD . DS . $folder . DS, "", $path));

include __DIR__ . DS . 'unit' . DS . 'editor' . DS . '2.php';

?>
<?php echo Form::hidden('token', $token); ?>
</form>