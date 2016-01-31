<?php $hooks = array($page, $segment); echo $messages; ?>
<form class="form-<?php echo $path === false ? 'ignite' : 'repair'; ?> form-shield" id="form-<?php echo $path === false ? 'ignite' : 'repair'; ?>" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p>
  <?php echo Form::textarea('content', Request::get('content', Guardian::wayback('content', $content !== false ? $content : "")), $speak->manager->placeholder_content, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      )
  )); ?>
  </p>
  <p>
    <?php echo Form::text('name', Request::get('name', Guardian::wayback('name', $path !== false ? File::url($path) : "")), $speak->manager->placeholder_file_name); ?>
    <?php if(strpos($config->url_path, '/repair/file:') === false): ?>
    <?php Weapon::fire('action_before', $hooks); ?>
    <?php echo Jot::button('construct', $speak->create); ?>
    <?php else: ?>
    <?php echo Jot::button('action', $speak->update); ?>
    <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/shield/' . $folder . '/kill/file:' . $path); ?>
    <?php Weapon::fire('action_after', $hooks); ?>
    <?php endif; ?>
  </p>
</form>