<?php echo Notify::read(); ?>
<?php if($pages): ?>
<form class="form-cache" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <table class="table-bordered">
    <colgroup>
      <col style="width:3em;">
      <col style="width:11em;">
      <col>
      <col style="width:7em;">
      <col style="width:7em;">
    </colgroup>
    <?php if( ! empty($pager->next->link)): ?>
    <tfoot>
      <tr>
        <td colspan="5">
          <span class="pull-left"><?php echo $pager->prev->link; ?></span>
          <span class="pull-right"><?php echo $pager->next->link; ?></span>
        </td>
      </tr>
    </tfoot>
    <?php endif; ?>
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th><?php echo Config::speak('last_', array($speak->updated)); ?></th>
        <th><?php echo $speak->file; ?></th>
        <th colspan="2"><?php echo $speak->actions; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pages as $file): ?>
      <tr>
        <td class="text-center"><input name="selected[]" type="checkbox" value="<?php echo str_replace(array(CACHE . DS, '\\'), array("", '/'), $file->path); ?>"></td>
        <td><time datetime="<?php echo Date::format($file->last_update, 'c'); ?>"><?php echo Date::format($file->last_update, 'Y/m/d H:i:s'); ?></time></td>
        <td><span title="<?php echo $file->size; ?>"><?php echo $file->name; ?></span></td>
        <td><a class="text-success" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/repair/file:' . str_replace(array(CACHE . DS, '\\'), array("", '/'), $file->path); ?>"><i class="fa fa-pencil-square"></i> <?php echo $speak->edit; ?></a></td>
        <td><a class="text-error" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/kill/file:' . str_replace(array(CACHE . DS, '\\'), array("", '/'), $file->path); ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p><button class="btn btn-danger btn-delete btn-delete-multiple" type="submit"><i class="fa fa-times-circle"></i> <?php echo $speak->delete_selected_files; ?></button></p>
</form>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', array(strtolower($speak->caches))); ?></p>
<?php endif; ?>