<?php echo $messages; ?>
<form class="form-repair form-menu" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php

  $action_update = true;
  $editor_tab_size = '    ';
  include DECK . DS . 'workers' . DS . 'unit.editor.1.php';

  ?>
</form>
<hr>
<?php echo Config::speak('file:menu'); ?>