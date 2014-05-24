<?php echo Notify::read(); ?>
<form class="form-repair form-cache" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><textarea name="content" class="input-block"><?php echo Guardian::wayback('content'); ?></textarea></p>
  <p><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-danger btn-delete" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill/<?php echo $config->asset_name; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></p>
</form>