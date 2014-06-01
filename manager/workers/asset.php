<?php echo Notify::read(); ?>
<form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset" method="post" enctype="multipart/form-data">
  <?php $token = Guardian::makeToken(); ?>
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <div class="grid-group">
    <span class="grid span-6">
      <span class="input-wrapper btn">
        <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
        <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times">
      </span> <button class="btn btn-primary btn-upload" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
    </span>
  </div>
</form>
<?php if($pages): ?>
<hr>
<h3 class="asset-headline"><?php echo $speak->assets; ?></h3>
<form class="form-asset" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset/kill" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
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
        <th><?php echo Config::speak('last_', array($speak->uploaded)); ?></th>
        <th><?php echo $speak->file; ?></th>
        <th colspan="2"><?php echo $speak->actions; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pages as $file): ?>
      <tr>
        <td class="text-center"><input name="selected[]" type="checkbox" value="<?php echo str_replace(array(ASSET . DS, '\\'), array("", '/'), $file->path); ?>"></td>
        <td><time datetime="<?php echo date('c', $file->last_update); ?>"><?php echo date('Y/m/d H:i:s', $file->last_update); ?></time></td>
        <td><a href="<?php echo $file->url; ?>" title="<?php echo $file->size; ?>" target="_blank"><?php echo $file->name; ?></a></td>
        <td><a class="text-error" href="<?php echo $config->url . '/' . $config->manager->slug . '/asset/kill/file:' . str_replace(array(ASSET . DS, '\\'), array("", '/'), $file->path); ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></td>
        <td><a class="text-success" href="<?php echo $config->url . '/' . $config->manager->slug . '/asset/repair/file:' . str_replace(array(ASSET . DS, '\\'), array("", '/'), $file->path); ?>"><i class="fa fa-pencil-square"></i> <?php echo $speak->rename; ?></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p><button class="btn btn-danger" type="submit"><i class="fa fa-times-circle"></i> <?php echo $speak->delete_selected_files; ?></button></p>
</form>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', array(strtolower($speak->assets))); ?></p>
<?php endif; ?>