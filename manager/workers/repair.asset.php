<?php echo $messages; ?>
<form class="form-repair form-asset" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php $editable = explode(',', SCRIPT_EXT); $editable = is_file($the_name) && in_array(strtolower(pathinfo($the_name, PATHINFO_EXTENSION)), $editable); if($editable): ?>
  <p>
  <?php echo Form::textarea('content', File::open(ASSET . DS . $the_name)->read(), null, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code',
          'MTE'
      )
  )); ?>
  </p>
  <?php endif; ?>
  <p>
  <?php echo Form::text('name', Guardian::wayback('name', basename($the_name)), $speak->manager->placeholder_asset_name); ?>
  <?php echo Jot::button('action', $editable ? $speak->update : $speak->rename); ?>
  <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/asset/kill/file:' . $the_name); ?>
  </p>
</form>