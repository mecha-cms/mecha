<?php Weapon::fire('unit_composer_4_before', $hooks); ?>
<hr>
<p>
  <?php if(strpos($config->url_path, '/id:') === false): ?>
  <?php echo Jot::button('construct', $speak->publish, 'action:publish'); ?>
  <?php echo Jot::button('action:clock-o', $speak->save, 'action:save'); ?>
  <?php else: ?>
  <?php if(Guardian::wayback('state', $page->state) === 'published'): ?>
  <?php echo Jot::button('action', $speak->update, 'action:publish'); ?>
  <?php echo Jot::button('action:history', $speak->unpublish, 'action:save'); ?>
  <?php else: ?>
  <?php echo Jot::button('construct', $speak->publish, 'action:publish'); ?>
  <?php echo Jot::button('action:clock-o', $speak->save, 'action:save'); ?>
  <?php endif; ?>
  <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/' . $segment . '/kill/id:' . Guardian::wayback('id', $page->id)); ?>
  <?php endif; ?>
</p>
<?php Weapon::fire('unit_composer_4_after', $hooks); ?>