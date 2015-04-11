<?php echo $messages; ?>
<form class="form-kill form-cache" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <ul>
    <?php foreach($the_name as $name): ?>
    <li><?php echo CACHE . DS . File::path(Text::parse($name, '->decoded_url')); ?></li>
    <?php endforeach; ?>
  </ul>
  <p>
  <?php echo UI::button('action', $speak->yes); ?>
  <?php echo UI::btn('reject', $speak->no, $config->url . '/' . $config->manager->slug . '/cache'); ?>
  </p>
</form>