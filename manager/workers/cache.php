<?php echo Notify::read(); ?>
<form class="form-cache" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <?php if($files = Get::files(CACHE, '*', 'DESC', 'last_update')): ?>
  <table class="table-bordered">
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th><?php echo Config::speak('last_', array($speak->updated)); ?></th>
        <th><?php echo $speak->file; ?></th>
        <th colspan="2"><?php echo $speak->actions; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($files as $file): ?>
      <tr>
        <td><input name="selected[]" type="checkbox" value="<?php echo $file['name']; ?>"></td>
        <td><time datetime="<?php echo Date::format($file['last_update'], 'c'); ?>"><?php echo Date::format($file['last_update'], 'Y/m/d H:i:s'); ?></time></td>
        <td><span title="<?php echo $file['size']; ?>"><?php echo $file['name']; ?></span></td>
        <td><a class="text-error" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/kill/' . $file['name']; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></td>
        <td><a class="text-success" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/repair/' . $file['name']; ?>"><i class="fa fa-pencil-square"></i> <?php echo $speak->edit; ?></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p><button class="btn btn-danger btn-delete" type="submit"><i class="fa fa-times-circle"></i> <?php echo $speak->delete_selected_files; ?></button></p>
  <?php else: ?>
  <p><?php echo Config::speak('notify_empty', array(strtolower($speak->caches))); ?></p>
  <?php endif; ?>
</form>