<?php echo $messages; ?>
<form class="form-repair form-shield" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Form::hidden('token', $token); ?>
<?php

$path_reject = $config->manager->slug . '/shield/' . $the_shield;
$path_destruct = $path_reject . '/kill/file:' . File::url(str_replace(SHIELD . DS . $the_shield . DS, "", $the_name));

include DECK . DS . 'workers' . DS . 'unit.editor.2.php';

?>
</form>