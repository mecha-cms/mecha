<?php echo Notify::read(); ?>
<form class="form-repair form-asset" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><?php echo $speak->from; ?> <strong><?php echo $config->asset_name; ?></strong> <?php echo strtolower($speak->to); ?>&hellip;</p>
  <p><input name="name" type="text" value="<?php echo Guardian::wayback('name'); ?>" placeholder="<?php echo $speak->manager->placeholder_asset_name; ?>" autofocus></p>
  <p><button class="btn btn-primary btn-rename" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->rename; ?></button> <a class="btn btn-danger btn-cancel" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset"><i class="fa fa-times-circle"></i> <?php echo $speak->cancel; ?></a></p>
</form>