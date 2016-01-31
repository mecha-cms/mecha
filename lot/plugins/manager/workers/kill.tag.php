<?php $hooks = array($file, $segment); echo $messages; ?>
<table class="table-bordered table-full-width">
  <thead>
    <tr>
      <th class="th-collapse"><?php echo $speak->id; ?></th>
      <th><?php echo $speak->name; ?></th>
      <th><?php echo $speak->slug; ?></th>
      <th><?php echo $speak->scope; ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="text-right"><?php echo $id; ?></td>
      <td><?php echo $file->name; ?></td>
      <td><code><?php echo $file->slug; ?></code></td>
      <td><?php echo isset($file->scope) ? str_replace(',', '/', $file->scope) : '<em>' . $speak->all . '</em>'; ?></td>
    </tr>
  </tbody>
</table>
<form class="form-kill form-tag" id="form-kill" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php Weapon::fire('action_before', $hooks); ?>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/tag/repair/id:' . $id); ?>
  <?php Weapon::fire('action_after', $hooks); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>