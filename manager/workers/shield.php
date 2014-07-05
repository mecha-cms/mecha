<?php

$shield = Request::get('shield') ? Request::get('shield') : $config->shield;
$files = Get::files(SHIELD . DS . $shield, 'css,html,js,php,txt', 'ASC', 'name');
$qs = $shield != $config->shield ? '?shield=' . $shield : "";

?>
<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-file-code-o"></i> <?php echo $speak->shield; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->upload; ?></a>
  <a class="tab" href="#tab-content-3"><i class="fa fa-fw fa-shield"></i> <?php echo $speak->manage; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <div class="tab-content" id="tab-content-1">
    <h3 class="media-head"><?php $info = Shield::info($shield); echo $speak->shield; ?>: <?php echo $info->name; ?></h3>
    <p><strong><?php echo $speak->author; ?>:</strong> <?php echo Text::parse($info->author)->to_encoded_html; ?><?php if(isset($info->url) && $info->url != '#'): ?><br><strong><?php echo $speak->url; ?>:</strong> <a href="<?php echo $info->url; ?>" rel="nofollow" target="_blank"><?php echo $info->url; ?></a><?php endif; ?></p>
    <?php if($files): ?>
    <table class="table-bordered table-full">
      <colgroup>
        <col>
        <col style="width:2.6em;">
        <col style="width:2.6em;">
      </colgroup>
      <tbody>
        <?php foreach($files as $file): ?>
        <tr>
          <td><?php echo basename($file['path']); ?></td>
          <td class="text-center"><a class="text-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/repair/file:' . str_replace(array(SHIELD . DS . $shield . DS, '\\'), array("", '/'), $file['path']) . $qs; ?>" title="<?php echo $speak->edit; ?>"><i class="fa fa-pencil"></i></a></td>
          <td class="text-center"><a class="text-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/kill/file:' . str_replace(array(SHIELD . DS . $shield . DS, '\\'), array("", '/'), $file['path']) . $qs; ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times"></i></a></td>
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
    <table class="table-bordered table-full">
      <colgroup>
        <col>
        <col style="width:2.6em;">
        <col style="width:2.6em;">
      </colgroup>
      <tbody>
        <?php foreach($shields as $shield): ?>
        <?php if($config->shield != basename($shield) && strpos(basename($shield), '__') !== 0): ?>
        <tr>
          <td><i class="fa fa-shield"></i> <?php echo Shield::info(basename($shield))->name; ?></td>
          <td><a class="text-construct" href="<?php echo $config->url_current; ?>?shield=<?php echo basename($shield); ?>" title="<?php echo $speak->manage; ?>"><i class="fa fa-pencil"></i></a></td>
          <td><a class="text-destruct" href="<?php echo $config->url_current; ?>/kill/shield:<?php echo basename($shield); ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times"></i></a></td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>