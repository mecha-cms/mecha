<form class="form-cache" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <div class="main-action-group">
    <?php echo Jot::button('destruct', $speak->delete); ?>
  </div>
  <?php echo $messages; ?>
  <?php

  $b_path = CACHE . DS;

  $b_url = $config->manager->slug . '/cache';
  $b_url_kill = $b_url . '/kill/file:';
  $b_url_repair = $b_url . '/repair/file:';

  include DECK . DS . 'workers' . DS . 'unit.explorer.2.php';

  ?>
  <?php if( ! empty($pager->step->url)): ?>
  <p class="pager cf"><?php echo $pager->step->link; ?></p>
  <?php endif; ?>
</form>
<?php if( ! empty($pager->step->url) || Request::get('q')): ?>
<hr>
<?php echo Jot::finder($config->manager->slug . '/cache', 'q'); ?>
<?php endif; ?>