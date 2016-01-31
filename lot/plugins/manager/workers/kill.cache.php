<?php $hooks = array($files, $segment); echo $messages; ?>
<?php if($files): ?>
<ul>
  <?php foreach($files as $file): ?>
  <li><?php echo CACHE . DS . File::path(Text::parse($file, '->decoded_url')); ?></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<form class="form-kill form-cache" id="form-kill" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php Weapon::fire('action_before', $hooks); ?>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/cache'); ?>
  <?php Weapon::fire('action_after', $hooks); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>