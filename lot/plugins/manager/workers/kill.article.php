<?php echo $messages; ?>
<h3><?php echo $page->title; ?></h3>
<p><?php echo $page->description; ?></p>
<p>
  <strong>
  <?php if($comments = Get::comments('DESC', 'post:' . $page->id)): ?>
  <?php echo count($comments) === 1 ? '1 ' . $speak->comment : count($comments) . ' ' . $speak->comments; ?>
  <?php else: ?>
  <?php echo '0 ' . $speak->comments; ?>
  <?php endif; ?>
  </strong>
</p>
<form class="form-kill form-article" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/article/repair/id:' . $page->id); ?>
<?php echo Form::hidden('token', $token); ?>
</form>