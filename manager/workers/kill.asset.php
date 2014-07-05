<?php echo Notify::read(); ?>
<form class="form-kill form-<?php echo $config->file_type; ?>" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <ul>
    <?php foreach($config->name as $name): ?>
    <li><?php echo ($config->file_type == 'asset' ? ASSET : CACHE) . DS . str_replace(array('\\', '/'), DS, $name); ?></li>
    <?php endforeach; ?>
  </ul>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->file_type; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>