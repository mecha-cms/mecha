<?php

$path = Guardian::wayback('path');
$shield = Request::get('shield') ? Request::get('shield') : $config->shield;
$qs = Request::get('shield') ? '?shield=' . Request::get('shield') : "";

?>
<?php echo Notify::read(); ?>
<form class="form-repair form-shield" action="<?php echo $config->url_current . $qs; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><strong><?php echo $speak->name; ?>:</strong> <?php echo basename($path); ?></p>
  <p><textarea name="content" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('content'))->to_encoded_html; ?></textarea></p>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <?php if($shield != $config->shield): ?><a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield/kill/file:<?php echo str_replace(array(SHIELD . DS . $shield . DS, '\\'), array("", '/'), $path) . $qs; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a><?php else: ?><a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield<?php echo $qs; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->cancel; ?></a><?php endif; ?></p>
</form>