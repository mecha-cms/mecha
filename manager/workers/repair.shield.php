<?php echo Notify::read(); ?>
<form class="form-repair form-shield" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><strong><?php echo $speak->name; ?>:</strong> <?php echo basename(Guardian::wayback('path')); ?></p>
  <p><textarea name="content" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('content'))->to_encoded_html; ?></textarea></p>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield"><i class="fa fa-times-circle"></i> <?php echo $speak->cancel; ?></a></p>
</form>