<?php echo Notify::read(); ?>
<form class="form-repair form-asset" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><input name="name" type="text" class="input-block" value="<?php echo Guardian::wayback('name'); ?>" placeholder="<?php echo $speak->manager->placeholder_asset_name; ?>" autofocus></p>
  <?php $editable = array('css', 'html', 'js', 'json', 'jsonp', 'php', 'txt', 'xml'); if(in_array(strtolower(pathinfo($config->name, PATHINFO_EXTENSION)), $editable)): ?>
  <p><textarea name="content" class="textarea-block code"><?php echo Text::parse(File::open(ASSET . DS . $config->name)->read())->to_encoded_html; ?></textarea></p>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset/kill/file:<?php echo $config->name; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></p>
  <?php else: ?>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->rename; ?></button> <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset/kill/file:<?php echo $config->name; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></p>
  <?php endif; ?>
</form>