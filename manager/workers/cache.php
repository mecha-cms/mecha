<?php echo $messages; ?>
<?php if($files): ?>
<form class="form-cache" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <div class="main-action-group">
    <button class="btn btn-destruct" type="submit"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></button>
  </div>
  <table class="table-bordered table-full-width">
    <thead>
      <tr>
        <th class="th-icon"><input type="checkbox" data-connection="selected[]"></th>
        <th><?php echo Config::speak('last_', array($speak->updated)); ?></th>
        <th><?php echo $speak->file; ?></th>
        <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php $editable = explode(',', SCRIPT_EXT); foreach($files as $file): $the_cache_url = File::url(str_replace(CACHE . DS, "", $file->path)); ?>
      <tr>
        <td class="td-icon"><input name="selected[]" type="checkbox" value="<?php echo $the_cache_url; ?>"></td>
        <td><time datetime="<?php echo Date::format($file->update, 'c'); ?>"><?php echo Date::format($file->update, 'Y/m/d H:i:s'); ?></time></td>
        <td><span title="<?php echo $file->size; ?>"><?php echo strpos($the_cache_url, '/') !== false ? '<span class="text-fade">' . dirname($the_cache_url) . '/</span>' . basename($the_cache_url) : $the_cache_url; ?></span></td>
        <?php if(in_array($file->extension, $editable)): ?>
        <td class="td-icon"><a class="text-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/repair/file:' . $the_cache_url; ?>" title="<?php echo $speak->edit; ?>"><i class="fa fa-pencil"></i></a></td>
        <?php else: ?>
        <td></td>
        <?php endif; ?>
        <td class="td-icon"><a class="text-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/cache/kill/file:' . $the_cache_url; ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times"></i></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if( ! empty($pager->step->url)): ?>
  <p class="pager cf"><?php echo $pager->step->link; ?></p>
  <?php endif; ?>
</form>
<?php if( ! empty($pager->step->url) || Request::get('q')): ?>
<hr>
<form class="form-find" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache" method="get">
  <input name="q" type="text" value="<?php echo Request::get('q'); ?>"> <button class="btn btn-action" type="submit"><i class="fa fa-search"></i> <?php echo $speak->find; ?></button>
</form>
<?php endif; ?>
<?php else: ?>
<p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->caches))); ?></p>
<?php endif; ?>