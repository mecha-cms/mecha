<?php $hooks = array($files, $segment); ?>
<div class="main-action-group">
  <?php Weapon::fire('main_action_before', $hooks); ?>
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->menu), $config->manager->slug . '/menu/ignite'); ?>
  <?php Weapon::fire('main_action_after', $hooks); ?>
</div>
<?php echo $messages; ?>
<?php $files_all = Get::state_menu(null, array()); ?>
<?php ksort($files_all); if($files_all): ?>
<table class="table-bordered table-full-width">
  <thead>
    <tr>
      <th><?php echo $speak->menu; ?></th>
      <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach(Mecha::O($files_all) as $key => $value): ?>
    <tr>
      <td><code>Menu::<?php echo $key; ?>()</code></td>
      <?php if(isset($files->{$key})): ?>
      <td class="td-icon">
      <?php echo Jot::a('construct', $config->manager->slug . '/menu/repair/key:' . $key, Jot::icon('pencil'), array(
          'title' => $speak->edit
      )); ?>
      </td>
      <td class="td-icon">
      <?php echo Jot::a('destruct', $config->manager->slug . '/menu/kill/key:' . $key, Jot::icon('times'), array(
          'title' => $speak->delete
      )); ?>
      </td>
      <?php else: ?>
      <td class="td-icon"><?php echo Jot::icon('pencil'); ?></td>
      <td class="td-icon"><?php echo Jot::icon('times'); ?></td>
      <?php endif; ?>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->menus)); ?></p>
<?php endif; ?>