<div class="main-action-group">
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->field), $config->manager->slug . '/field/ignite'); ?>
</div>
<?php echo $messages; ?>
<?php if($files): ?>
<table class="table-bordered table-full-width">
  <thead>
    <tr>
      <th><?php echo $speak->title; ?></th>
      <th><?php echo $speak->key; ?></th>
      <th><?php echo $speak->type; ?></th>
      <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($files as $key => $value): ?>
    <tr>
      <td><?php echo $value->title; ?></td>
      <td><?php echo $key; ?></td>
      <?php

      $s = Mecha::alter($value->type[0], array(
          't' => 'Text',
          'b' => 'Boolean',
          'o' => 'Option'
      ), 'Summary');

      ?>
      <td><?php echo Jot::em('info', $s); ?></td>
      <td class="td-icon">
      <?php echo Jot::a('construct', $config->manager->slug . '/field/repair/key:' . $key, Jot::icon('pencil'), array(
          'title' => $speak->edit
      )); ?>
      </td>
      <td class="td-icon">
      <?php echo Jot::a('destruct', $config->manager->slug . '/field/kill/key:' . $key, Jot::icon('times'), array(
          'title' => $speak->delete
      )); ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->fields)); ?></p>
<?php endif; ?>