<?php $article_data = $page->permalink != '#' ? Get::articleAnchor(basename(preg_replace('#\#.*$#', "", $page->permalink))) : false; echo Notify::read(); ?>
<form class="form-kill" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <p><strong><?php echo $page->name; ?></strong> <?php echo strtolower($speak->at); ?> <?php echo Date::format($page->time, 'Y/m/d H:i:s'); ?><?php echo $article_data ? ' ' . strtolower($speak->on) . ' <strong><a href="' . $article_data->url . '#comment-' . $page->id . '">' . $article_data->title . '</a></strong>' : ""; ?> </p>
  <p><?php echo $page->message; ?></p>
  <p><button class="btn btn-primary btn-delete" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a href="<?php echo $config->url . '/' . $config->manager->slug . '/comment/repair/' . $page->id; ?>" class="btn btn-danger btn-cancel"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>