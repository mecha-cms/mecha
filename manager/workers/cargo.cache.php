<form class="form-kill form-cache" id="form-kill" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <div class="main-action-group">
    <?php echo Jot::button('destruct', $speak->delete); ?>
  </div>
  <?php

  echo $messages;

  $c_path = CACHE . DS;

  $c_url = $config->manager->slug . '/cache';
  $c_url_kill = $c_url . '/kill/file:';
  $c_url_repair = $c_url . '/repair/file:';

  include __DIR__ . DS . 'unit.explorer.2.php';

  ?>
</form>
<?php if( ! empty($pager->step->url) || Request::get('q')): ?>
<hr>
<?php echo Jot::finder($c_url, 'q'); ?>
<?php endif; ?>