<?php echo $messages; ?>
<form class="form-repair form-asset" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p>
  <?php echo Form::text('name', Guardian::wayback('name', basename($the_name)), $speak->manager->placeholder_asset_name, array(
      'class' => 'input-block',
      'autofocus' => true
  )); ?>
  </p>
  <?php $editable = explode(',', SCRIPT_EXT); if(in_array(strtolower(pathinfo($the_name, PATHINFO_EXTENSION)), $editable)): ?>
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
  <p>
  <?php echo Jot::button('action', $speak->update); ?>
  <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/asset/kill/file:' . $the_name); ?>
  </p>
  <?php else: ?>
  <p>
  <?php echo Jot::button('action', $speak->rename); ?>
  <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/asset/kill/file:' . $the_name); ?>
  </p>
  <?php endif; ?>
</form>