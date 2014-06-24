<?php echo Notify::read(); ?>
<?php if($pages): ?>
<form class="form-cache" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <table class="table-bordered table-full">
    <colgroup>
      <col style="width:2.5em;">
      <col style="width:11em;">
      <col>
      <col style="width:2.5em;">
      <col style="width:2.5em;">
    </colgroup>
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th><?php echo Config::speak('last_', array($speak->updated)); ?></th>
        <th><?php echo $speak->file; ?></th>
        <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php $editable = array('css', 'html', 'js', 'json', 'jsonp', 'php', 'txt', 'xml'); foreach($pages as $file): ?>
      <tr>
        <td class="text-center"><input name="selected[]" type="checkbox" value="<?php echo str_replace(array(CACHE . DS, '\\'), array("", '/'), $file->path); ?>"></td>
        <td><time datetime="<?php echo Date::format($file->last_update, 'c'); ?>"><?php echo Date::format($file->last_update, 'Y/m/d H:i:s'); ?></time></td>
        <td><span title="<?php echo $file->size; ?>"><?php echo $file->name; ?></span></td>
        <?php if(in_array($file->extension, $editable)): ?>
        <td class="text-center"><a class="text-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/repair/file:' . str_replace(array(CACHE . DS, '\\'), array("", '/'), $file->path); ?>" title="<?php echo $speak->edit; ?>"><i class="fa fa-pencil"></i></a></td>
        <?php else: ?>
        <td></td>
        <?php endif; ?>
        <td class="text-center"><a class="text-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/kill/file:' . str_replace(array(CACHE . DS, '\\'), array("", '/'), $file->path); ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times-circle"></i></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p class="cf">
    <span class="pull-left"><?php echo $pager->prev->link; ?></span>
    <span class="pull-right"><?php echo $pager->next->link; ?></span>
  </p>
  <p><button class="btn btn-destruct" type="submit"><i class="fa fa-times-circle"></i> <?php echo $speak->delete_selected_files; ?></button></p>
</form>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', array(strtolower($speak->caches))); ?></p>
<?php endif; ?>