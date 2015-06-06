<?php echo $messages; ?>
<form class="form-<?php echo is_null($the_name) ? 'ignite' : 'repair'; ?> form-shield" id="form-<?php echo is_null($the_name) ? 'ignite' : 'repair'; ?>" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php

$path_reject = $config->manager->slug . '/shield/' . $the_shield;
$path_destruct = $path_reject . '/kill/file:' . File::url(str_replace(SHIELD . DS . $the_shield . DS, "", $the_name));

include DECK . DS . 'workers' . DS . 'unit.editor.2.php';

?>
<?php echo Form::hidden('token', $token); ?>
</form>