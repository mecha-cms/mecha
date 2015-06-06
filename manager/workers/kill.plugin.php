<?php echo $messages; ?>
<form class="form-kill form-plugin" id="form-kill" action="<?php echo $config->url_current; ?>" method="post">
  <h3><?php echo $file->title; ?></h3>
  <p><?php echo $file->content; ?></p>
  <p>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/plugin'); ?>
  </p>
  <?php echo Form::hidden('token', $token); ?>
</form>