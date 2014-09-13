<?php echo $messages; ?>
<form class="form-repair form-shield" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <p><textarea name="content" class="textarea-block code MTE"><?php echo Text::parse(Guardian::wayback('content', $the_content))->to_encoded_html; ?></textarea></p>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->name; ?></span>
    <span class="grid span-5"><input name="name" type="text" value="<?php echo Guardian::wayback('name', $the_path); ?>"></span>
  </label>
  <hr>
  <p><?php if(strpos($config->url_current, 'file:') === false): ?><button class="btn btn-construct" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->create; ?></button><?php else: ?><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button><?php endif; ?> <?php if(strpos($config->url_current, 'file:') !== false): ?><a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/shield/' . $the_shield . '/kill/file:' . str_replace(array(SHIELD . DS . $shield . DS, '\\'), array("", '/'), $the_path); ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a><?php else: ?><a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield/<?php echo $the_shield; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->cancel; ?></a><?php endif; ?></p>
</form>