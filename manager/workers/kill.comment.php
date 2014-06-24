<?php $article_data = Get::articleAnchor($page->post); echo Notify::read(); ?>
<form class="form-kill form-comment" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><strong><?php echo $page->name; ?></strong><?php echo $article_data ? ' ' . strtolower($speak->to) . ' <a href="' . $page->permalink . '" target="_blank">&ldquo;' . $article_data->title . '&rdquo;</a>' : ""; ?></p>
  <p><?php echo $page->message; ?></p>
  <p><strong><?php echo $speak->date; ?>:</strong> <?php echo Date::format($page->time, 'Y/m/d H:i:s'); ?></p>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $page->id; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>