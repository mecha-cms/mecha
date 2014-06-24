<?php echo Notify::read(); ?>
<form class="form-login" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->username; ?></span>
    <span class="grid span-4"><input name="username" type="text" autocomplete="off" autofocus></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->password; ?></span>
    <span class="grid span-4"><input name="password" type="password" autocomplete="off"></span>
  </label>
  <div class="grid-group">
    <span class="grid span-2"></span>
    <span class="grid span-4"><button class="btn btn-action" type="submit"><i class="fa fa-key"></i> <?php echo $speak->login; ?></button></span>
  </div>
</form>