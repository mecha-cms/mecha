<?php echo $messages; ?>
<form class="form-repair form-asset" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Form::hidden('token', $token); ?>
<?php

$path_reject = $config->manager->slug . '/asset' . $config->url_query;
$path_destruct = $config->manager->slug . '/asset/kill/file:' . File::url(str_replace(ASSET . DS, "", $the_name)) . $config->url_query;

include DECK . DS . 'workers' . DS . 'unit.editor.2.php';

?>
</form>