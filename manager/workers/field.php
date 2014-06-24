<div class="main-actions">
  <a class="btn btn-begin" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/field/ignite"><i class="fa fa-plus-square"></i> <?php echo $speak->manager->title_new_field; ?></a>
</div>
<?php echo Notify::read(); ?>
<?php if($pages): ?>
<table class="table-bordered table-full">
  <colgroup>
    <col>
    <col>
    <col style="width:5.2em;">
    <col style="width:2.6em;">
    <col style="width:2.6em;">
  </colgroup>
  <thead>
    <tr>
      <th><?php echo $speak->title; ?></th>
      <th><?php echo $speak->key; ?></th>
      <th class="text-center"><?php echo $speak->scope; ?></th>
      <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($pages as $key => $value): ?>
    <tr>
      <td><?php echo $value->title; ?></td>
      <td><?php echo $key; ?></td>
      <td class="text-center"><?php echo $value->scope; ?></td>
      <td class="text-center"><a class="text-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/field/repair/key:' . $key; ?>" title="<?php echo $speak->edit; ?>"><i class="fa fa-pencil"></i></a></td>
      <td class="text-center"><a class="text-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/field/kill/key:' . $key; ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times"></i></a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', array(strtolower($speak->fields))); ?></p>
<?php endif; ?>