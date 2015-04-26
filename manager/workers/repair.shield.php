<?php echo $messages; ?>
<form class="form-repair form-shield" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p>
  <?php echo Form::textarea('content', Guardian::wayback('content', $the_content), null, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code',
          'MTE'
      )
  )); ?>
  </p>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->name; ?></span>
    <span class="grid span-5"><?php echo Form::text('name', Guardian::wayback('name', File::url($the_path))); ?></span>
  </label>
  <hr>
  <p>
    <?php if(strpos($config->url_current, 'file:') === false): ?>
    <?php echo Jot::button('construct', $speak->create); ?>
    <?php else: ?>
    <?php echo Jot::button('action', $speak->update); ?>
    <?php endif; ?> <?php if(strpos($config->url_current, 'file:') !== false): ?>
    <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/shield/' . $the_shield . '/kill/file:' . File::url(str_replace(SHIELD . DS . $shield . DS, "", $the_path))); ?>
    <?php else: ?>
    <?php echo Jot::btn('reject', $speak->cancel, $config->manager->slug . '/shield/' . $the_shield); ?>
    <?php endif; ?>
  </p>
</form>