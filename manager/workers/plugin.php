<?php echo Notify::read(); ?>
<?php if($pages): ?>
  <?php foreach($pages as $plugin): ?>
  <h3><?php echo $plugin->about->title; ?></h3>
  <p><?php echo Get::summary($plugin->about->content); ?></p>
  <p>
    <?php if(File::exist(PLUGIN . '/' . $plugin->slug . '/launch.php')): ?>
    <a class="btn btn-sm btn-success btn-manage" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/' . $plugin->slug; ?>"><i class="fa fa-pencil-square"></i> <?php echo $speak->manage; ?></a> <a class="btn btn-sm btn-primary btn-uninstall" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/freeze/' . $plugin->slug; ?>"><i class="fa fa-minus-circle"></i> <?php echo $speak->uninstall; ?></a>
    <?php else: ?>
      <?php if(File::exist(PLUGIN . '/' . $plugin->slug . '/pending.php')): ?>
      <a class="btn btn-sm btn-primary btn-install" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/fire/' . $plugin->slug; ?>"><i class="fa fa-plus-circle"></i> <?php echo $speak->install; ?></a>
      <?php endif; ?>
    <?php endif; ?>
    <?php if( ! File::exist(PLUGIN . '/' . $plugin->slug . '/configurator.php') && ! File::exist(PLUGIN . '/' . $plugin->slug . '/launch.php') && ! File::exist(PLUGIN . '/' . $plugin->slug . '/pending.php')): ?>
    <span class="btn btn-sm btn-danger btn-disabled"><i class="fa fa-times-circle"></i> <?php echo $speak->remove; ?></span>
    <?php else: ?>
    <a class="btn btn-sm btn-danger btn-remove" href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/kill/' . $plugin->slug; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->remove; ?></a>
    <?php endif; ?>
  </p>
  <?php endforeach; ?>
<?php endif; ?>
<nav class="blog-pager">
  <span class="pull-left"><?php echo $pager->prev->link; ?></span>
  <span class="pull-right"><?php echo $pager->next->link; ?></span>
</nav>