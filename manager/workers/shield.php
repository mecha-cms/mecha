<?php

$shield = Request::get('shield') ? Request::get('shield') : $config->shield;
$files = Get::files(SHIELD . DS . $shield, 'css,html,js,php,txt', 'ASC', 'name');
$qs = $shield != $config->shield ? '?shield=' . $shield : "";

?>
<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-file-code-o"></i> <?php echo $speak->shield; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->upload; ?></a>
  <a class="tab" href="#tab-content-3"><i class="fa fa-fw fa-wrench"></i> <?php echo $speak->manage; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <div class="tab-content" id="tab-content-1">
    <?php if($files): ?>
    <h3 class="media-head"><?php $info = Shield::info($shield); echo $speak->shield; ?>: <?php echo $info->title; ?></h3>
    <p><strong><?php echo $speak->author; ?>:</strong> <?php echo Text::parse($info->author)->to_encoded_html; ?><?php if(isset($info->url) && $info->url != '#'): ?><br><strong><?php echo $speak->url; ?>:</strong> <a href="<?php echo $info->url; ?>" rel="nofollow" target="_blank"><?php echo $info->url; ?></a><?php endif; ?></p>
    <table class="table-bordered table-full">
      <colgroup>
        <col>
        <col style="width:2.6em;">
        <?php if($shield != $config->shield): ?>
        <col style="width:2.6em;">
        <?php endif; ?>
      </colgroup>
      <tbody>
        <?php foreach($files as $file): ?>
        <tr>
          <td><?php echo basename($file['path']); ?></td>
          <td class="text-center"><a class="text-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/repair/file:' . str_replace(array(SHIELD . DS . $shield . DS, '\\'), array("", '/'), $file['path']) . $qs; ?>" title="<?php echo $speak->edit; ?>"><i class="fa fa-pencil"></i></a></td>
          <?php if($shield != $config->shield): ?>
          <td class="text-center"><a class="text-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/kill/file:' . str_replace(array(SHIELD . DS . $shield . DS, '\\'), array("", '/'), $file['path']) . $qs; ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times"></i></a></td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <?php if(File::exist(SHIELD . DS . $shield)): ?>
    <p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->shields))); ?></p>
    <?php else: ?>
    <p class="empty"><?php echo $speak->notify_error_not_found; ?></p>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3 class="media-head"><?php echo $speak->manager->title_shield_upload; ?></h3>
    <form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield" method="post" enctype="multipart/form-data">
      <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
      <span class="input-wrapper btn btn-default">
        <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
        <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times" data-accepted-extensions="zip">
      </span> <button class="btn btn-action" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
    </form>
    <hr>
    <?php echo Config::speak('file:shield'); ?>
  </div>
  <div class="tab-content hidden" id="tab-content-3">
    <h3 class="media-head"><?php echo $speak->shields; ?></h3>
    <?php

    $shields = glob(SHIELD . DS . '*', GLOB_ONLYDIR);
    sort($shields);

    ?>
    <?php foreach($shields as $shield): $shield = basename($shield); ?>
    <?php if($config->shield != $shield && strpos($shield, '__') !== 0 && Request::get('shield', "") != $shield): $info = Shield::info($shield); ?>
    <div class="media-item" id="shield:<?php echo $shield; ?>">
      <h4><i class="fa fa-shield"></i> <?php echo $info->title; ?></h4>
      <p><?php echo Get::summary($info->content); ?></p>
      <p>
        <a class="btn btn-sm btn-construct" href="<?php echo $config->url_current; ?>?shield=<?php echo $shield; ?>"><i class="fa fa-cog"></i> <?php echo $speak->manage; ?></a> <a class="btn btn-sm btn-destruct" href="<?php echo $config->url_current; ?>/kill/shield:<?php echo $shield; ?>?shield=<?php echo $shield; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
      </p>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>