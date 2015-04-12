<?php echo $messages; ?>
<form class="form-kill form-plugin" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <h3><?php echo $file->title; ?></h3>
  <p><?php echo $file->content; ?></p>
  <p>
  <?php echo UI::button('action', $speak->yes); ?>
  <?php echo UI::btn('reject', $speak->no, $config->manager->slug . '/plugin'); ?>
  </p>
</form>