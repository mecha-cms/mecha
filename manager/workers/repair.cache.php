<?php echo $messages; ?>
<form class="form-repair form-cache" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <p><textarea name="content" class="textarea-block textarea-expand code MTE"><?php echo Text::parse(Guardian::wayback('content', $the_content), '->encoded_html'); ?></textarea></p>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill/file:<?php echo File::url(str_replace(CACHE . DS, "", $the_name)); ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></p>
</form>