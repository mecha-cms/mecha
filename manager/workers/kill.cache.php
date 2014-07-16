<?php echo $messages; ?>
<form class="form-kill form-cache" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <ul>
    <?php foreach($the_name as $name): ?>
    <li><?php echo CACHE . DS . str_replace(array('\\', '/'), DS, $name); ?></li>
    <?php endforeach; ?>
  </ul>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>