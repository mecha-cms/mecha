<?php $aa = Get::articleAnchor($response->post); echo $messages; ?>
<form class="form-kill form-comment" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p><strong><?php echo $response->name; ?></strong><?php echo $aa ? ' ' . strtolower($speak->to) . ' <a href="' . $response->permalink . '" target="_blank">' . $aa->title . '</a>' : ""; ?></p>
  <p><?php echo $response->message; ?></p>
  <p><strong><?php echo $speak->date; ?>:</strong> <?php echo Date::format($response->time, 'Y/m/d H:i:s'); ?></p>
  <p>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/comment/repair/id:' . $response->id); ?>
  </p>
</form>