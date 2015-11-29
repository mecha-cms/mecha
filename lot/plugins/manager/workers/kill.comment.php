<?php $aa = Get::articleAnchor($page->post); echo $messages; ?>
<p><strong><?php echo $page->name; ?></strong><?php echo $aa ? ' ' . strtolower($speak->to) . ' <a href="' . $page->permalink . '" target="_blank">' . $aa->title . '</a>' : ""; ?></p>
<p><?php echo $page->message; ?></p>
  <p><strong><?php echo $speak->date; ?>:</strong> <?php echo Date::format($page->time, 'Y/m/d H:i:s'); ?></p>
<form class="form-kill form-comment" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/comment/repair/id:' . $page->id); ?>
<?php echo Form::hidden('token', $token); ?>
</form>