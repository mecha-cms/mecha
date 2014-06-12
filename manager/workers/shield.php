<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-file-code-o"></i> <?php echo $speak->shield; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <div class="tab-content" id="tab-content-1">
    <h3 class="media-head"><?php $shield = Shield::info($config->shield); echo $speak->shield; ?>: <?php echo $shield->name; ?></h3>
    <p><strong><?php echo $speak->author; ?>:</strong> <?php echo Text::parse($shield->author)->to_encoded_html; ?></p>
    <?php if($pages): ?>
    <table class="table-bordered table-full">
      <colgroup>
        <col>
        <col style="width:7em;">
      </colgroup>
      <tbody>
        <?php foreach($pages as $file): ?>
        <tr>
          <td><?php echo $file->name; ?></td>
          <td><a class="text-success" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/repair/file:' . str_replace(array(SHIELD . DS . $config->shield . DS, '\\'), array("", '/'), $file->path); ?>"><i class="fa fa-pencil-square"></i> <?php echo $speak->edit; ?></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p><?php echo Config::speak('notify_empty', array(strtolower($speak->shields))); ?></p>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3 class="media-head"><?php echo $speak->manager->title_shield_upload; ?></h3>
    <form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield" method="post" enctype="multipart/form-data">
      <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
      <span class="input-wrapper btn">
        <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
        <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times" data-accepted-extensions="zip">
      </span> <button class="btn btn-primary btn-upload" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
    </form>
    <hr>
    <?php echo Config::speak('file:shield'); ?>
  </div>
</div>