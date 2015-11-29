<?php echo $messages; ?>
<form class="form-repair form-menu" id="form-repair" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php

  $action_update = true;
  $editor_tab_size = '    ';
  include __DIR__ . DS . 'unit.editor.1.php';

  ?>
</form>
<hr>
<?php echo Guardian::wizard($segment); ?>