<?php $hooks = array($files, $segment); ?>
<div class="main-action-group">
  <?php Weapon::fire('main_action_before', $hooks); ?>
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->tag), $config->manager->slug . '/tag/ignite'); ?>
  <?php Weapon::fire('main_action_after', $hooks); ?>
</div>
<?php echo $messages; ?>
<?php $files_all = Get::state_tag(null, array()); ?>
<?php ksort($files_all); if($files_all): ?>
<table class="table-bordered table-full-width">
  <thead>
    <tr>
      <th class="th-collapse"><?php echo $speak->id; ?></th>
      <th><?php echo $speak->name; ?></th>
      <th><?php echo $speak->slug; ?></th>
      <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach(Mecha::O($files_all) as $key => $value): ?>
    <tr<?php echo Session::get('recent_item_update') === $key ? ' class="active"' : ""; ?>>
      <td class="text-right"><?php echo $key; ?></td>
      <td><?php echo $value->name; ?></td>
      <td><code><?php echo $value->slug; ?></code></td>
      <?php $files_a = (array) $files; ?>
      <?php if(isset($files_a[$key])): ?>
      <td class="td-icon">
      <?php echo Jot::a('construct', $config->manager->slug . '/tag/repair/id:' . $key, Jot::icon('pencil'), array(
          'title' => $speak->edit
      )); ?>
      </td>
      <td class="td-icon">
      <?php echo Jot::a('destruct', $config->manager->slug . '/tag/kill/id:' . $key, Jot::icon('times'), array(
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
<p><?php echo Config::speak('notify_empty', strtolower($speak->tags)); ?></p>
<?php endif; ?>