<?php $aa = Get::articleAnchor($response->post); echo Notify::read(); ?>
<form class="form-kill form-comment" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><strong><?php echo $response->name; ?></strong><?php echo $aa ? ' ' . strtolower($speak->to) . ' <a href="' . $response->permalink . '" target="_blank">&ldquo;' . $aa->title . '&rdquo;</a>' : ""; ?></p>
  <p><?php echo $response->message; ?></p>
  <p><strong><?php echo $speak->date; ?>:</strong> <?php echo Date::format($response->time, 'Y/m/d H:i:s'); ?></p>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $response->id; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>