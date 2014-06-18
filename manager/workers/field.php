<div class="main-actions">
  <a class="btn btn-success btn-new" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/field/ignite"><i class="fa fa-plus-square"></i> <?php echo $speak->manager->title_new_field; ?></a>
</div>
<?php echo Notify::read(); ?>
<?php if($pages): ?>
<table class="table-bordered table-full">
  <colgroup>
    <col>
    <col>
    <col style="width:7em;">
    <col style="width:7em;">
  </colgroup>
  <thead>
    <tr>
      <th><?php echo $speak->title; ?></th>
      <th><?php echo $speak->key; ?></th>
      <th colspan="2"><?php echo $speak->actions; ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($pages as $key => $value): ?>
    <tr>
      <td><?php echo $value->title; ?></td>
      <td><?php echo $key; ?></td>
      <td><a class="text-success" href="<?php echo $config->url . '/' . $config->manager->slug . '/field/repair/key:' . $key; ?>"><i class="fa fa-pencil-square"></i> <?php echo $speak->edit; ?></a></td>
      <td><a class="text-error" href="<?php echo $config->url . '/' . $config->manager->slug . '/field/kill/key:' . $key; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', array(strtolower($speak->fields))); ?></p>
<?php endif; ?>