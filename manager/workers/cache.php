<form class="form-cache" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <div class="main-action-group">
    <?php echo Jot::button('destruct', $speak->delete); ?>
  </div>
  <?php echo $messages; ?>
  <?php

  $c_path = CACHE . DS;

  $c_url = $config->manager->slug . '/cache';
  $c_url_kill = $c_url . '/kill/file:';
  $c_url_repair = $c_url . '/repair/file:';

  include DECK . DS . 'workers' . DS . 'unit.explorer.2.php';

  ?>
</form>
<?php if( ! empty($pager->step->url) || Request::get('q')): ?>
<hr>
<?php echo Jot::finder($config->manager->slug . '/cache', 'q'); ?>
<?php endif; ?>