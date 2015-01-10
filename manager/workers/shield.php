<div class="main-actions">
  <a class="btn btn-begin" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/' . $the_shield_path; ?>/ignite"><i class="fa fa-plus-square"></i> <?php echo Config::speak('manager.title_new_', array($speak->shield)); ?></a>
</div>
<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-shield"></i> <?php echo $speak->shield; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->upload; ?></a>
  <?php if(count($the_shields) > 1): ?>
  <a class="tab" href="#tab-content-3"><i class="fa fa-fw fa-wrench"></i> <?php echo $speak->manage; ?></a>
  <?php endif; ?>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <?php if($the_shield_contents): ?>
    <h3 class="media-head"><?php echo $speak->shield; ?>: <?php echo $info->title; ?></h3>
    <p><strong><?php echo $speak->author; ?>:</strong> <?php echo Text::parse($info->author)->to_encoded_html; ?><?php if(isset($info->url) && $info->url != '#'): ?><br><strong><?php echo $speak->url; ?>:</strong> <a href="<?php echo $info->url; ?>" rel="nofollow" target="_blank"><?php echo $info->url; ?></a><?php endif; ?></p>
    <table class="table-bordered table-full">
      <colgroup>
        <col>
        <col style="width:2.6em;">
        <col style="width:2.6em;">
      </colgroup>
      <tbody>
        <?php foreach($the_shield_contents as $file): ?>
        <tr>
          <td><?php echo basename($file['path']); ?></td>
          <td class="text-center"><a class="text-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/' . $the_shield_path . '/repair/file:' . str_replace(array(SHIELD . DS . $the_shield_path . DS, '\\'), array("", '/'), $file['path']); ?>" title="<?php echo $speak->edit; ?>"><i class="fa fa-pencil"></i></a></td>
          <td class="text-center"><a class="text-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/' . $the_shield_path . '/kill/file:' . str_replace(array(SHIELD . DS . $the_shield_path . DS, '\\'), array("", '/'), $file['path']); ?>" title="<?php echo $speak->delete; ?>"><i class="fa fa-times"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p><small><a href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/' . $the_shield_path; ?>/backup" title="<?php echo $speak->download . ' ' . strtolower($speak->as) . ' `' . $the_shield_path . '.zip`'; ?>"><i class="fa fa-cloud-download"></i> <?php echo $the_shield_path; ?>.zip</a></small></p>
    <?php else: ?>
    <?php if(File::exist(SHIELD . DS . $the_shield_path)): ?>
    <p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->shields))); ?></p>
    <?php else: ?>
    <p class="empty"><?php echo $speak->notify_error_not_found; ?></p>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3 class="media-head"><?php echo $speak->manager->title_shield_upload; ?></h3>
    <form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield" method="post" enctype="multipart/form-data">
      <input name="token" type="hidden" value="<?php echo $token; ?>">
      <span class="input-wrapper btn btn-default">
        <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
        <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times" data-accepted-extensions="zip">
      </span> <button class="btn btn-action" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
    </form>
    <hr>
    <?php echo Config::speak('file:shield'); ?>
  </div>
  <?php if(count($the_shields) > 1): ?>
  <div class="tab-content hidden" id="tab-content-3">
    <h3 class="media-head"><?php echo $speak->shields; ?></h3>
    <?php foreach($the_shields as $shield): $shield = basename($shield); ?>
    <?php if($config->shield != $shield && strpos($shield, '__') !== 0): $info = Shield::info($shield); ?>
    <div class="media-item" id="shield:<?php echo $shield; ?>">
      <h4><i class="fa fa-shield"></i> <?php echo $info->title; ?></h4>
      <p><?php echo Get::summary($info->content); ?></p>
      <p>
        <a class="btn btn-sm btn-construct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/' . $shield; ?>"><i class="fa fa-cog"></i> <?php echo $speak->manage; ?></a> <a class="btn btn-sm btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/kill/shield:' . $shield; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
      </p>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>