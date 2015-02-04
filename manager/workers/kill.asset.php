<?php echo $messages; ?>
<form class="form-kill form-asset" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <ul>
    <?php foreach($the_name as $name): ?>
    <li><?php echo ASSET . DS . str_replace(array('\\', '/', '---COMMA---'), array(DS, DS, ','), $name); ?></li>
    <?php endforeach; ?>
  </ul>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>