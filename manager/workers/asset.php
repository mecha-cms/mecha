<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-briefcase"></i> <?php echo $speak->assets; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-cloud-upload"></i> <?php echo $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <?php if($files): ?>
    <h3 class="media-head"><?php echo Config::speak('manager.title_your_', array($speak->assets)); ?></h3>
    <form class="form-asset" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset/kill" method="post">
      <input name="token" type="hidden" value="<?php echo $token; ?>">
      <p><button class="btn btn-destruct" type="submit"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></button></p>
      <table class="table-bordered table-full-width">
        <colgroup>
          <col style="width:2.6em;">
          <col style="width:11em;">
          <col>
          <col style="width:2.6em;">
          <col style="width:2.6em;">
        </colgroup>
        <thead>
          <tr>
            <th><input type="checkbox" data-connection="selected[]"></th>
            <th><?php echo Config::speak('last_', array($speak->updated)); ?></th>
            <th><?php echo $speak->file; ?></th>
            <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($files as $file): ?>
          <tr>
            <td class="text-center"><input name="selected[]" type="checkbox" value="<?php echo str_replace(array(ASSET . DS, '\\'), array("", '/'), $file->path); ?>"></td>
            <td><time datetime="<?php echo Date::format($file->update, 'c'); ?>"><?php echo Date::format($file->update, 'Y/m/d H:i:s'); ?></time></td>
            <td><a href="<?php echo $file->url; ?>" title="<?php echo $file->size; ?>" target="_blank"><?php echo basename($file->path); ?></a></td>
            <td class="text-center"><a class="text-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/asset/repair/file:' . str_replace(array(ASSET . DS, '\\'), array("", '/'), $file->path); ?>" title="<?php echo $speak->edit; ?>"><i class="fa fa-pencil"></i></a></td>
            <td class="text-center"><a class="text-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/asset/kill/file:' . str_replace(array(ASSET . DS, '\\'), array("", '/'), $file->path); ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times"></i></a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="pager cf"><?php echo $pager->step->link; ?></p>
    </form>
    <?php else: ?>
    <p class="empty"><?php echo Config::speak('notify_' . (Request::get('q') || $config->offset !== 1 ? 'error_not_found' : 'empty'), array(strtolower($speak->assets))); ?></p>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3 class="media-head"><?php echo Config::speak('manager.title__upload_alt', array($speak->asset)); ?></h3>
    <form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset" method="post" enctype="multipart/form-data">
      <input name="token" type="hidden" value="<?php echo $token; ?>">
      <span class="input-outer btn btn-default">
        <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
        <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times">
      </span> <button class="btn btn-action" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
    </form>
  </div>
</div>