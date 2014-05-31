<div class="tab-area cf">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-magic"></i> <?php echo $speak->plugins; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <div class="tab-content" id="tab-content-1">
    <h3 class="plugin-headline"><?php echo $speak->manager->title_plugin_list; ?></h3>
    <?php if($pages): ?>
    <?php foreach($pages as $plugin): ?>
    <div class="plugin-item">
      <h4><?php echo $plugin->about->title; ?></h4>
      <p><?php echo Get::summary($plugin->about->content); ?></p>
      <p>
        <?php if(File::exist(PLUGIN . DS . $plugin->slug . DS . 'launch.php')): ?>
        <a class="btn btn-sm btn-success btn-manage" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/' . $plugin->slug; ?>"><i class="fa fa-pencil-square"></i> <?php echo $speak->manage; ?></a> <a class="btn btn-sm btn-primary btn-uninstall" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/freeze/id:' . $plugin->slug; ?>"><i class="fa fa-minus-circle"></i> <?php echo $speak->uninstall; ?></a>
        <?php else: ?>
          <?php if(File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php')): ?>
          <a class="btn btn-sm btn-primary btn-install" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/fire/id:' . $plugin->slug; ?>"><i class="fa fa-plus-circle"></i> <?php echo $speak->install; ?></a>
          <?php endif; ?>
        <?php endif; ?>
        <?php if( ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'configurator.php') && ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'launch.php') && ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php')): ?>
        <span class="btn btn-sm btn-danger btn-disabled"><i class="fa fa-times-circle"></i> <?php echo $speak->remove; ?></span>
        <?php else: ?>
        <a class="btn btn-sm btn-danger btn-remove" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/kill/id:' . $plugin->slug; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->remove; ?></a>
      <?php endif; ?>
      </p>
    </div>
    <?php endforeach; ?>
    <nav class="blog-pager">
      <span class="pull-left"><?php echo $pager->prev->link; ?></span>
      <span class="pull-right"><?php echo $pager->next->link; ?></span>
    </nav>
    <?php else: ?>
    <p><?php echo Config::speak('notify_empty', array(strtolower($speak->plugins))); ?></p>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3 class="plugin-headline"><?php echo $speak->manager->title_plugin_upload; ?></h3>
    <form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/plugin" method="post" enctype="multipart/form-data">
      <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
      <div class="grid-group">
        <span class="grid span-6">
          <span class="input-wrapper btn">
            <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
            <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="&lt;i class=&quot;fa fa-check&quot;&gt;&lt;/i&gt;&nbsp;" data-icon-error="&lt;i class=&quot;fa fa-times&quot;&gt;&lt;/i&gt;&nbsp;" data-accepted-extensions="zip,rar">
          </span> <button class="btn btn-primary btn-upload" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
        </span>
      </div>
    </form>
    <hr>
    <?php echo Config::speak('file:plugin'); ?>
  </div>
</div>