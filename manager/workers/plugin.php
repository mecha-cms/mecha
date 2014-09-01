<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-plug"></i> <?php echo $speak->plugins; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3 class="media-head"><?php echo $speak->manager->title_plugin_list; ?></h3>
    <?php if($files): ?>
    <?php foreach($files as $plugin): ?>
    <div class="media-item" id="plugin:<?php echo $plugin->slug; ?>">
      <h4><i class="fa <?php echo File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php') ? 'fa-unlock-alt' : 'fa-lock'; ?>"></i> <?php echo $plugin->about->title; ?></h4>
      <p><?php echo Get::summary($plugin->about->content); ?></p>
      <p>
        <?php if(File::exist(PLUGIN . DS . $plugin->slug . DS . 'launch.php')): ?>
        <a class="btn btn-sm btn-begin" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/' . $plugin->slug; ?>"><i class="fa fa-cog"></i> <?php echo $speak->manage; ?></a> <a class="btn btn-sm btn-action" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/freeze/id:' . $plugin->slug . '?o=' . $config->offset; ?>"><i class="fa fa-minus-circle"></i> <?php echo $speak->uninstall; ?></a>
        <?php else: ?>
          <?php if(File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php')): ?>
          <a class="btn btn-sm btn-action" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/fire/id:' . $plugin->slug . '?o=' . $config->offset; ?>"><i class="fa fa-plus-circle"></i> <?php echo $speak->install; ?></a>
          <?php endif; ?>
        <?php endif; ?>
        <?php if( ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'configurator.php') && ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'launch.php') && ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php')): ?>
        <span class="btn btn-sm btn-destruct btn-disabled"><i class="fa fa-times-circle"></i> <?php echo $speak->remove; ?></span>
        <?php else: ?>
        <a class="btn btn-sm btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/kill/id:' . $plugin->slug; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->remove; ?></a>
      <?php endif; ?>
      </p>
    </div>
    <?php endforeach; ?>
    <p class="pager cf"><?php echo $pager->step->link; ?></p>
    <?php else: ?>
    <p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->plugins))); ?></p>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3 class="media-head"><?php echo $speak->manager->title_plugin_upload; ?></h3>
    <form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/plugin" method="post" enctype="multipart/form-data">
      <input name="token" type="hidden" value="<?php echo $token; ?>">
      <span class="input-wrapper btn btn-default">
        <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
        <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times" data-accepted-extensions="zip">
      </span> <button class="btn btn-action" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
    </form>
    <hr>
    <?php echo Config::speak('file:plugin'); ?>
  </div>
</div>