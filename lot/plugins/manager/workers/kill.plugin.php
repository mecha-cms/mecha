<?php $hooks = array($page, $segment); echo $messages; ?>
<h3><?php echo $page->title; ?></h3>
<?php echo $page->content; ?>
<form class="form-kill form-plugin" id="form-kill" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php Weapon::fire('action_before', $hooks); ?>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/plugin'); ?>
  <?php Weapon::fire('action_after', $hooks); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>