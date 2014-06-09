<?php echo Notify::read(); ?>
<form class="form-menu" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><textarea name="content" class="input-block"><?php echo Text::parse(Guardian::wayback('content'))->to_encoded_html; ?></textarea></p>
  <p><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></p>
</form>
<hr>
<?php echo preg_replace('#/("|&quot;)&gt;(.*?)&lt;/a&gt;#', '\1&gt;\2&lt;/a&gt;', Config::speak('file:menu')); ?>