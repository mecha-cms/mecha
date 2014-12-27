<?php echo $messages; ?>
<form class="form-kill form-plugin" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <h3><?php echo $file->title; ?></h3>
  <p><?php echo $file->content; ?></p>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a href="<?php echo $config->url . '/' . $config->manager->slug; ?>/plugin" class="btn btn-reject"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>